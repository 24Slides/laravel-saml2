<?php

namespace Slides\Saml2\Http\Controllers;

use Slides\Saml2\Events\SignedIn;
use Slides\Saml2\Auth;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

/**
 * Class Saml2Controller
 *
 * @package Slides\Saml2\Http\Controllers
 */
class Saml2Controller extends Controller
{
    /**
     * The authentication handler.
     *
     * @var Auth
     */
    protected $auth;

    /**
     * Saml2Controller constructor.
     *
     * @param Auth $auth
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Render the metadata.
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function metadata()
    {
        $metadata = $this->auth->getMetadata();

        return response($metadata, 200, ['Content-Type' => 'text/xml']);
    }

    /**
     * Process the SAML Response sent by the IdP.
     *
     * Fires "SignedIn" event if a valid user is found.
     *
     * @return \Illuminate\Support\Facades\Redirect
     */
    public function acs()
    {
        $errors = $this->auth->acs();

        if (!empty($errors)) {
            logger()->error('Saml2 error_detail', ['error' => $this->auth->getLastErrorReason()]);
            session()->flash('saml2_error_detail', [$this->auth->getLastErrorReason()]);

            logger()->error('Saml2 error', $errors);
            session()->flash('saml2_error', $errors);

            return redirect(config('saml2.errorRoute'));
        }

        $user = $this->auth->getSaml2User();

        event(new SignedIn($user, $this->auth));

        $redirectUrl = $user->getIntendedUrl();

        if ($redirectUrl !== null) {
            return redirect($redirectUrl);
        } else {

            return redirect(config('saml2.loginRoute'));
        }
    }

    /**
     * Process the SAML Logout Response / Logout Request sent by the IdP.
     *
     * Fires 'saml2.logoutRequestReceived' event if its valid.
     *
     * This means the user logged out of the SSO infrastructure, you 'should' log him out locally too.
     *
     * @return \Illuminate\Support\Facades\Redirect
     */
    public function sls()
    {
        $error = $this->auth->sls(config('saml2.retrieveParametersFromServer'));

        if (!empty($error)) {
            throw new \Exception("Could not log out");
        }

        return redirect(config('saml2.logoutRoute')); //may be set a configurable default
    }

    /**
     * Initiate a login request.
     *
     * @return void
     */
    public function login()
    {
        $this->auth->login(config('saml2.loginRoute'));
    }

    /**
     * Initiate a logout request.
     *
     * @return void
     */
    public function logout(Request $request)
    {
        $this->auth->logout(
            $request->query('returnTo'),
            $request->query('nameId'),
            $request->query('sessionIndex')
        );
    }
}
