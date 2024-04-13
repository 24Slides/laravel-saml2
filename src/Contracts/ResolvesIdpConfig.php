<?php

namespace Slides\Saml2\Contracts;

interface ResolvesIdpConfig
{
    /**
     * Adjust SAML configuration for the given identity provider.
     *
     * @param IdentityProvidable $idp
     * @param array $config
     *
     * @return void
     */
    public function resolve(IdentityProvidable $idp, array $config): array;
}
