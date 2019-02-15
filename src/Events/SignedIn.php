<?php

namespace Slides\Saml2\Events;

use Slides\Saml2\Saml2User;
use Slides\Saml2\Auth;

/**
 * Class LoggedIn
 *
 * @package Slides\Saml2\Events
 */
class SignedIn
{
    /**
     * The signed-up user.
     *
     * @var Saml2User
     */
    public $user;

    /**
     * The authentication handler.
     *
     * @var Auth
     */
    public $auth;

    /**
     * LoggedIn constructor.
     *
     * @param Saml2User $user
     * @param Auth $auth
     */
    public function __construct(Saml2User $user, Auth $auth)
    {
        $this->user = $user;
        $this->auth = $auth;
    }
}
