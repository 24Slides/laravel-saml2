<?php

namespace Slides\Saml2\Concerns;

use Illuminate\Database\Eloquent\Model;
use Slides\Saml2\Contracts\IdentityProvidable;
use Slides\Saml2\Models\IdentityProvider;

trait IdentityProviderAuthenticatable
{
    /**
     * The identity provider.
     *
     * @return Model|IdentityProvider|IdentityProvider
     */
    public function identityProvider()
    {
        $this->morphOne(config('saml2.idpModel'), 'tenant');
    }
}
