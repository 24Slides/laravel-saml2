<?php

namespace Slides\Saml2\Resolvers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Slides\Saml2\Contracts\IdentityProvidable;
use Slides\Saml2\Contracts\ResolvesIdentityProvider;
use Slides\Saml2\Repositories\IdentityProviderRepository;

class IdentityProviderResolver implements ResolvesIdentityProvider
{
    /**
     * @var IdentityProviderRepository
     */
    protected IdentityProviderRepository $repository;

    /**
     * @param IdentityProviderRepository $repository
     */
    public function __construct(IdentityProviderRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Resolve a tenant from the request.
     *
     * @param Request $request
     *
     * @return IdentityProvidable|null
     */
    public function resolve(Request $request): ?IdentityProvidable
    {
        if (!$uuid = $request->route('uuid')) {
            if (config('saml2.debug')) {
                Log::debug('[Saml2] Identity Provider UUID is not present in the URL so cannot be resolved', [
                    'url' => $request->fullUrl()
                ]);
            }

            return null;
        }

        if (!$idp = $this->repository->findByUUID($uuid)) {
            if (config('saml2.debug')) {
                Log::debug('[Saml2] Identity Provider cannot be found', ['uuid' => $uuid]);
            }

            return null;
        }

        if ($idp->trashed()) {
            if (config('saml2.debug')) {
                Log::debug("[Saml2] Identity Provider #{$idp->id} resolved but marked as deleted", [
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
