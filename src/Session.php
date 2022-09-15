<?php

namespace Slides\Saml2;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cookie;
use Slides\Saml2\Models\Tenant;
use Slides\Saml2\Facades\Auth;
use Slides\Saml2\Repositories\TenantRepository;
use Slides\Saml2\OneLoginBuilder;
use OneLogin\Saml2\Error as OneLoginError;

/**
 * Class Session
 *
 * @package Slides\Saml2
 */
class SamlSession
{
    /**
     * @var TenantRepository
     */
    protected $tenants;

    /**
     * @var OneLoginBuilder
     */
    protected $builder;

    public function __construct(TenantRepository $tenants, OneLoginBuilder $builder)
    {
        $this->tenants = $tenants;
        $this->builder = $builder;
    }

    public function exists(): bool
    {
        return Cookie::has('saml_tenant_id');
    }

    public function store(Tenant $tenant, Saml2User $samlUser): void
    {
        Cookie::queue(cookie()->make('saml_tenant_id', $tenant->id, config('session.lifetime')));
        Cookie::queue(cookie()->make('saml_session_id', $samlUser->getSessionIndex(), config('session.lifetime')));
        Cookie::queue(cookie()->make('saml_name_id', $samlUser->getNameId(), config('session.lifetime')));
    }

    public function clear(): void
    {
        Cookie::queue(cookie()->forget('saml_tenant_id'));
        Cookie::queue(cookie()->forget('saml_session_id'));
        Cookie::queue(cookie()->forget('saml_name_id'));
    }

    /**
     * Generates the redirect url to initiate a global session
     * sign out for a user with the IdP.
     */
    public function logout(): ?RedirectResponse
    {
        if (!$this->exists()) {
            return null;
        }

        $tenant = $this->resolveTenant();
        if (empty($tenant)) {
            return null;
        }

        $this->builder
            ->withTenant($tenant)
            ->bootstrap();

        try {
            $sloUrl = Auth::logout(
                config('saml2.logoutRoute'),
                Cookie::get('saml_name_id'),
                Cookie::get('saml_session_id'),
                null,
                true
            );
        } catch (OneLoginError $e) {
            report($e);
            return null;
        }

        return redirect($sloUrl)->withHeaders([
            'Pragma' => 'no-cache',
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
    }

    /**
     * Resolve a tenant from the session.
     */
    protected function resolveTenant(): ?Tenant
    {
        $id = Cookie::get('saml_tenant_id');
        if (empty($id)) {
            return null;
        }

        return $this->tenants->findById($id);
    }
}
