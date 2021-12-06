<?php

declare(strict_types=1);

namespace Slides\Saml2\Helpers;

use Slides\Saml2\Models\Tenant;
use Illuminate\Support\Facades\URL;

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
     * 1. Returns default SP entity ID (metadata URL) *UNLESS* id_app_url_override is filled in (non-empty string),
     * 2. ... in which case it will return the SP entity ID path appended to that value instead of
     *    what URL::route uses (APP_URL at command line, request HOST during a request)
     *
     * Q: Why not just make this Tenant::getSpEntityIdAttribute?
     * A: Because Eloquent models hate unit testing
     *
     * @return string SP Entity ID
     */
    public function getSpEntityId(): string
    {
        return $this->getUrlWithDomainOverrideIfConfigured('saml.metadata');
    }

    /**
     * Use this to determine the ACS URL for a tenant.
     *
     * 1. Returns default ACS URL *UNLESS* id_app_url_override is filled in (non-empty string),
     * 2. ... in which case it will return the ACS path appended to that value instead of
     *    what URL::route uses (APP_URL at command line, request HOST during a request)
     *
     * Q: Why not just make this Tenant::getAcsUrlAttribute?
     * A: Because Eloquent models hate unit testing
     *
     * @return string ACS URL (full path)
     */
    public function getAcsUrl(): string
    {
        return $this->getUrlWithDomainOverrideIfConfigured('saml.acs');
    }

    /**
     * Use this to determine the Single Logout Service URL for a tenant.
     *
     * 1. Returns default SLS URL *UNLESS* id_app_url_override is filled in (non-empty string),
     * 2. ... in which case it will return the SLS path appended to that value instead of
     *    what URL::route uses (APP_URL at command line, request HOST during a request)
     *
     * @return string SLS URL (full path)
     */
    public function getSlsUrl(): string
    {
        return $this->getUrlWithDomainOverrideIfConfigured('saml.sls');
    }

    /**
     * By default (id_app_url_override not configured), returns default route URL
     * (which, for URL::route, is a /-relative path, not an absolute including domain)
     *
     * When id_app_url_override is configured, the route *path* appended to that id_app_url_override value
     *
     * @param string $routeName Valid route — intended to be one of the saml.* routes
     * @return string path or full URL of that route
     */
    private function getUrlWithDomainOverrideIfConfigured(string $routeName): string
    {
        if ($this->tenant->id_app_url_override) {
            $absolute = false;
            return $this->tenant->id_app_url_override . URL::route($routeName, ['uuid' => $this->tenant->uuid], $absolute);
        }

        return URL::route($routeName, ['uuid' => $this->tenant->uuid]);
    }
}
