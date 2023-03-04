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
        $validUntil = config('saml2.sp.validUntil');
        $metadata = $auth->getMetadata($validUntil);

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
    public function acs(Auth $auth)
    {
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
    public function sls(Auth $auth)
    {
        $errors = $auth->sls(config('saml2.retrieveParametersFromServer'));

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
    public function login(Request $request, Auth $auth)
    {
        $redirectUrl = $auth->getTenant()->relay_state_url ?: config('saml2.loginRoute');

        $auth->login($request->query('returnTo', $redirectUrl));
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
        $auth->logout(
            $request->query('returnTo'),
            $request->query('nameId'),
            $request->query('sessionIndex')
        );
    }
}
