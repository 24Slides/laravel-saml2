<?php

namespace Slides\Saml2\Http\Middleware;

use Illuminate\Http\Request;
use Slides\Saml2\Concerns\ResolvesIdentityProvider;
use Slides\Saml2\Exceptions\IdentityProviderNotFound;
use Slides\Saml2\Repositories\TenantRepository;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Slides\Saml2\OneLoginBuilder;

class ResolveIdentityProvider
{
    /**
     * @var ResolvesIdentityProvider
     */
    protected $resolver;

    /**
     * @var OneLoginBuilder
     */
    protected $builder;

    /**
     * ResolveTenant constructor.
     *
     * @param ResolvesIdentityProvider $resolver
     * @param OneLoginBuilder $builder
     */
    public function __construct(ResolvesIdentityProvider $resolver, OneLoginBuilder $builder)
    {
        $this->resolver = $resolver;
        $this->builder = $builder;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     *
     * @throws NotFoundHttpException
     *
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        if (!$idp = $this->resolver->resolve($request)) {
            throw new IdentityProviderNotFound();
        }

        if (config('saml2.debug')) {
            Log::debug('[Saml2] Tenant resolved', [
                'uuid' => $idp->idpUuid()
            ]);
        }

        session()->flash('saml2.tenant.uuid', $idp->idpUuid());

        $this->builder->configureIdp($idp);

        return $next($request);
    }
}
