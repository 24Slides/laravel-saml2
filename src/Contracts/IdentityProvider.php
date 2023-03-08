<?php

namespace Slides\Saml2\Contracts;

interface IdentityProvider
{
    public function idpUuid();

    public function idpEntityId(): string;

    public function idpLoginUrl(): string;

    public function idpLogoutUrl(): string;

    public function idpX509cert(): string;

    public function idpNameIdFormat(): string;
}
