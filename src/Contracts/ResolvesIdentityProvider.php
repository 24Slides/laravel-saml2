<?php

namespace Slides\Saml2\Contracts;

use Illuminate\Http\Request;

interface ResolvesIdentityProvider
{
    /**
     * Resolve the Identity Provider instance that has relevant credentials.
     *
     * @param Request $request
     *
     * @return IdentityProvidable|null
     */
    public function resolve(Request $request): ?IdentityProvidable;
}
