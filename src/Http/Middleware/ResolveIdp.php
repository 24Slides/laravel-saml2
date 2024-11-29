<?php

namespace Slides\Saml2\Http\Middleware;

use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Slides\Saml2\OneLoginBuilder;

/**
 * Class ResolveIdp
 *
 * @package Slides\Saml2\Http\Middleware
 */
class ResolveIdp
{

    /**
     * @var OneLoginBuilder
     */
    protected $builder;

    /**
     * ResolveIdp constructor.
     *
     * @param OneLoginBuilder $builder
     */
    public function __construct(OneLoginBuilder $builder)
    {
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
    public function handle($request, \Closure $next)
    {
        if(!$idp = $this->resolveIdp($request)) {
            throw new NotFoundHttpException();
        }

        if (config('saml2.debug')) {
            Log::debug('[Saml2] IdP resolved', [
                'key' => $idp['key'],
            ]);
        }

        session()->flash('saml2.idp.key', $idp['key']);

        $this->builder
            ->withIdp($idp)
            ->bootstrap();

        return $next($request);
    }

    /**
     * Resolve an IdP by a request.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return array|null
     */
    protected function resolveIdp($request)
    {
        if(!$key = $request->route('key')) {
            if (config('saml2.debug')) {
                Log::debug('[Saml2] IdP key is not present in the URL so cannot be resolved', [
                    'url' => $request->fullUrl()
                ]);
            }

            return null;
        }

        if(!$idp = config("saml2.idps.$key")) {
            if (config('saml2.debug')) {
                Log::debug('[Saml2] Unknown IdP requested', [
                  'key' => $key
                ]);
            }

            return null;
        }

        $idp['key'] = $key;

        return $idp;
    }
}
