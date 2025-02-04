<?php

namespace Slides\Saml2\Events;

use Slides\Saml2\Auth as SamlAuth;
use Slides\Saml2\Contracts\IdentityProvidable;

class IdentityProviderResolved
{
    /**
     * The authentication handler.
     *
     * @var SamlAuth
     */
    public SamlAuth $auth;

    /**
     * The identity provider.
     *
     * @var IdentityProvidable
     */
    public IdentityProvidable $idp;

    /**
     * LoggedIn constructor.
     *
     * @param SamlAuth $auth
     * @param IdentityProvidable $idp
     */
    public function __construct(SamlAuth $auth, IdentityProvidable $idp)
    {
        $this->auth = $auth;
        $this->idp = $idp;
    }
}
