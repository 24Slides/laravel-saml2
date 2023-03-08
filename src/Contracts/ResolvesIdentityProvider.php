<?php

namespace Slides\Saml2\Contracts;

interface ResolvesIdentityProvider
{
    /**
     * Resolve the Identity Provider instance that has relevant credentials.
     *
     * @param Request $request
     *
     * @return IdentityProvider|null
     */
    public function resolve($request): ?IdentityProvider;
}
