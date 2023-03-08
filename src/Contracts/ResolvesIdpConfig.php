<?php

namespace Slides\Saml2\Contracts;

interface ResolvesIdpConfig
{
    /**
     * Adjust SAML configuration for the given identity provider.
     *
     * @param IdentityProvider $idp
     * @param array $config
     *
     * @return void
     */
    public function resolve(IdentityProvider $idp, array $config): array;
}
