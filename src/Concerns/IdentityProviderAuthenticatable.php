<?php

namespace Slides\Saml2\Concerns;

use Illuminate\Database\Eloquent\Model;
use Slides\Saml2\Contracts\IdentityProvider;
use Slides\Saml2\Models\Tenant;

trait IdentityProviderAuthenticatable
{
    /**
     * The identity provider.
     *
     * @return Model|IdentityProvider|Tenant
     */
    public function identityProvider()
    {
        $this->morphOne(config('saml2.tenantModel'), 'authenticatable');
    }
}
