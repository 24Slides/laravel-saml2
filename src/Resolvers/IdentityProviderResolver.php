<?php

namespace Slides\Saml2\Resolvers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Slides\Saml2\Contracts\IdentityProvider;
use Slides\Saml2\Contracts\ResolvesIdentityProvider;
use Slides\Saml2\Repositories\TenantRepository;

class IdentityProviderResolver implements ResolvesIdentityProvider
{
    /**
     * @var TenantRepository
     */
    protected $tenants;

    /**
     * @param TenantRepository $tenants
     */
    public function __construct(TenantRepository $tenants)
    {
        $this->tenants = $tenants;
    }

    /**
     * Resolve a tenant from the request.
     *
     * @param Request $request
     *
     * @return IdentityProvider|null
     */
    public function resolve($request): ?IdentityProvider
    {
        if (!$uuid = $request->route('uuid')) {
            if (config('saml2.debug')) {
                Log::debug('[Saml2] Tenant UUID is not present in the URL so cannot be resolved', [
                    'url' => $request->fullUrl()
                ]);
            }

            return null;
        }

        if (!$idp = $this->tenants->findByUUID($uuid)) {
            if (config('saml2.debug')) {
                Log::debug('[Saml2] Tenant doesn\'t exist', [
                    'uuid' => $uuid
                ]);
            }

            return null;
        }

        if ($idp->trashed()) {
            if (config('saml2.debug')) {
                Log::debug('[Saml2] Tenant #' . $idp->id. ' resolved but marked as deleted', [
                    'id' => $idp->id,
                    'uuid' => $uuid,
                    'deleted_at' => $idp->deleted_at->toDateTimeString()
                ]);
            }

            return null;
        }

        return $idp;
    }
}
