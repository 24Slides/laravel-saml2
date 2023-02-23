<?php

namespace Slides\Saml2\Http\Controllers;

use Slides\Saml2\Events\SignedIn;
use Slides\Saml2\Auth;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use OneLogin\Saml2\Error as OneLoginError;

/**
 * Class Saml2Controller
 *
 * @package Slides\Saml2\Http\Controllers
 */
class Saml2Controller extends Controller
{
    /**
     * Render the metadata.
     *
     * @param Auth $auth
     *
     * @return \Illuminate\Support\Facades\Response
     *
     * @throws OneLoginError
     */
    public function metadata(Auth $auth)
    {
        $metadata = $auth->getMetadata();

        return response($metadata, 200, ['Content-Type' => 'text/xml']);
    }

    /**
     * Process the SAML Response sent by the IdP.
     *
     * Fires "SignedIn" event if a valid user is found.
     *
     * @param Auth $auth
     *
     * @return \Illuminate\Support\Facades\Redirect
     *
     * @throws OneLoginError
     * @throws \OneLogin\Saml2\ValidationError
     */
    public function acs(Auth $auth, $idpName, Request $request)
    {
        $this->setRequest($request);
        $errors = $auth->acs();

        if (!empty($errors)) {
            logger()->error('saml2.error_detail', ['error' => $auth->getLastErrorReason()]);
            session()->flash('saml2.error_detail', [$auth->getLastErrorReason()]);

            logger()->error('saml2.error', $errors);
            session()->flash('saml2.error', $errors);

            return redirect(config('saml2.errorRoute'));
        }

        $user = $auth->getSaml2User();

        event(new SignedIn($user, $auth));

        $redirectUrl = $user->getIntendedUrl();

        $this->unsetRequest();

        if ($redirectUrl) {
            return redirect($redirectUrl);
        }

        return redirect($auth->getTenant()->relay_state_url ?: config('saml2.loginRoute'));
    }

    /**
     * Process the SAML Logout Response / Logout Request sent by the IdP.
     *
     * Fires 'saml2.logoutRequestReceived' event if its valid.
     *
     * This means the user logged out of the SSO infrastructure, you 'should' log him out locally too.
     *
     * @param Auth $auth
     *
     * @return \Illuminate\Support\Facades\Redirect
     *
     * @throws OneLoginError
     * @throws \Exception
     */
    public function sls(Auth $auth, $idpName, Request $request)
    {
        $this->setRequest($request);

        $errors = $auth->sls(config('saml2.retrieveParametersFromServer'));

        $this->unsetRequest();

        if (!empty($errors)) {
            logger()->error('saml2.error_detail', ['error' => $auth->getLastErrorReason()]);
            session()->flash('saml2.error_detail', [$auth->getLastErrorReason()]);

            logger()->error('saml2.error', $errors);
            session()->flash('saml2.error', $errors);

            return redirect(config('saml2.errorRoute'));
        }

        return redirect(config('saml2.logoutRoute')); //may be set a configurable default
    }

    /**
     * Initiate a login request.
     *
     * @param Illuminate\Http\Request $request
     * @param Auth $auth
     *
     * @return void
     *
     * @throws OneLoginError
     */
    public function login(Request $request, Auth $auth, $idpName)
    {
        $this->setRequest($request);

        $redirectUrl = $auth->getTenant()->relay_state_url ?: config('saml2.loginRoute');

        $redirectUrl = $auth->login(
            $request->query('returnTo', $redirectUrl),
            [],
            false,
            false,
            true
        );

        $this->unsetRequest();

        return redirect($redirectUrl);
    }

    /**
     * Initiate a logout request.
     *
     * @param Illuminate\Http\Request $request
     * @param Auth $auth
     *
     * @return void
     *
     * @throws OneLoginError
     */
    public function logout(Request $request, Auth $auth)
    {
        $this->setRequest($request);

        $redirectUrl = $auth->logout(
            $request->query('returnTo'),
            $request->query('nameId'),
            $request->query('sessionIndex'),
            null,
            true
        );

        $this->unsetRequest();

        return redirect($redirectUrl);
    }

    /**
     * Add needed superglobals for php-saml that swoole does not provide
     *
     * @param Request $request
     *
     * @return void
     */
    private function setRequest(Request $request)
    {
        $_POST['SAMLResponse'] = array_key_exists('SAMLResponse', $request->post()) ? $request->post()['SAMLResponse'] : null;
        $_GET['SAMLResponse'] = array_key_exists('SAMLResponse', $request->query()) ? $request->query()['SAMLResponse'] : null;
        $_GET['SAMLRequest'] = array_key_exists('SAMLRequest', $request->query()) ? $request->query()['SAMLRequest'] : null;
        $_GET['RelayState'] = array_key_exists('RelayState', $request->query()) ? $request->query()['RelayState'] : null;
        $_GET['Signature'] = array_key_exists('Signature', $request->query()) ? $request->query()['Signature'] : null;
        $_REQUEST['RelayState'] = array_key_exists('RelayState', $request->all()) ? $request->all()['RelayState'] : null;

        if (!empty($request->server->get('HTTP_X_FORWARDED_PROTO'))) {
            $_SERVER['HTTP_X_FORWARDED_PROTO'] = $request->server->get('HTTP_X_FORWARDED_PROTO');
        }
        if (!empty($request->server->get('HTTP_X_FORWARDED_HOST'))) {
            $_SERVER['HTTP_X_FORWARDED_HOST'] = $request->server->get('HTTP_X_FORWARDED_HOST');
        } else {
            $_SERVER['HTTP_HOST'] = parse_url(config('app.url'), PHP_URL_HOST);
        }
    }

    /**
     * Remove superglobals that were needed for php-saml that swoole does not provide
     *
     *
     * @return void
     */
    private function unsetRequest()
    {
        unset(
            $_POST['SAMLResponse'],
            $_GET['SAMLResponse'],
            $_GET['SAMLRequest'],
            $_GET['RelayState'],
            $_GET['Signature'],
            $_REQUEST['RelayState'],
            $_SERVER['HTTP_X_FORWARDED_PROTO'],
            $_SERVER['HTTP_X_FORWARDED_HOST'],
            $_SERVER['HTTP_HOST'],
        );
    }
}
