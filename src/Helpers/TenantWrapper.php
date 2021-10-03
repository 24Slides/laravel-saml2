<?php

declare(strict_types=1);

namespace Slides\Saml2\Helpers;

use Slides\Saml2\Models\Tenant;
use Illuminate\Support\Facades\URL;
use InvalidArgumentException;

/**
 * TenantWrapper wraps a Tenant and adds business logic.
 *
 * Done this way to the business logic can be unit tested, on mock Tenant,
 * since Eloquent models themselves are virtually impossible to unit test.
 *
 * @package App\Helpers
 */
class TenantWrapper
{
    private Tenant $tenant;

    final public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * Factory method for fluent calls,
     * e.g. TenantWrapper::with($tenant)->blah_blah
     *
     * @param Tenant $tenant
     * @return TenantWrapper
     */
    public static function with(Tenant $tenant): self
    {
        return new TenantWrapper($tenant);
    }

    /**
     * Use this to determine the SP Entity ID for a tenant.
     *
     * 1. Returns default SP entity ID (metadata URL) *UNLESS* sp_entity_id_override is filled in (non-empty string),
     * 2. ... in which case it will return that sp_entity_id_override value
     *
     * Q: Why not just make this Tenant::getSpEntityIdAttribute?
     * A: Because Eloquent models hate unit testing
     *
     * @return string SP Entity ID
     */
    public function getSpEntityId(): string
    {
        return $this->tenant->sp_entity_id_override ?: URL::route('saml.metadata', ['uuid' => $this->tenant->uuid]);
    }
}
