<?php

namespace Slides\Saml2\Contracts;

use Slides\Saml2\Auth;

interface ResolvesUser
{
    /**
     * Resolve the user from the SAML response.
     *
     * @param Auth $samlAuth
     *
     * @return \Illuminate\Foundation\Auth\User|\Illuminate\Database\Eloquent|null
     */
    public function resolve(Auth $samlAuth);
}
