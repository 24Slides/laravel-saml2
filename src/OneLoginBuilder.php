<?php

namespace Slides\Saml2;

use OneLogin\Saml2\Auth as OneLoginAuth;
use OneLogin\Saml2\Utils as OneLoginUtils;
use Illuminate\Support\Facades\URL;
use Illuminate\Contracts\Container\Container;
use Slides\Saml2\Contracts\IdentityProvider;
use Slides\Saml2\Contracts\ResolvesIdpConfig;
use Illuminate\Support\Arr;

/**
 * Class OneLoginBuilder
 *
 * @package Slides\Saml2
 */
class OneLoginBuilder
{
    /**
     * @var Container
     */
    protected $app;

    /**
     * The config resolver.
     *
     * @var ResolvesIdpConfig
     */
    protected $configResolver;

    /**
     * OneLoginBuilder constructor.
     *
     * @param Container $app
     * @param ResolvesIdpConfig $configResolver
     */
    public function __construct(Container $app, ResolvesIdpConfig $configResolver)
    {
        $this->app = $app;
        $this->configResolver = $configResolver;
    }

    /**
     * Adjust OneLogin configuration according to the given identity provider.
     *
     * @param IdentityProvider $idp
     *
     * @return void
     */
    public function configureIdp(IdentityProvider $idp)
    {
        if ($this->app['config']->get('saml2.proxyVars', false)) {
            OneLoginUtils::setProxyVars(true);
        }

        $this->app->singleton(OneLoginAuth::class, function ($app) use ($idp) {
            $config = $app['config']['saml2'];

            $this->setConfigDefaultValues($idp->idpUuid(), $config);

            return new OneLoginAuth(
                $this->configResolver->resolve($idp, $config)
            );
        });

        $this->app->singleton(Auth::class, function ($app) use ($idp) {
            return new \Slides\Saml2\Auth($app[OneLoginAuth::class], $idp);
        });
    }

    /**
     * Set default config values if they weren't set.
     *
     * @param string $uuid
     * @param array $config
     *
     * @return void
     */
    protected function setConfigDefaultValues(string $uuid, array &$config): void
    {
        foreach ($this->configDefaultValues($uuid) as $key => $default) {
            if (!Arr::get($config, $key)) {
                Arr::set($config, $key, $default);
            }
        }
    }

    /**
     * Configuration default values that must be replaced with custom ones.
     *
     * @param string $uuid
     *
     * @return array
     */
    protected function configDefaultValues(string $uuid): array
    {
        return [
            'sp.entityId' => URL::route('saml.metadata', compact('uuid')),
            'sp.assertionConsumerService.url' => URL::route('saml.acs', compact('uuid')),
            'sp.singleLogoutService.url' => URL::route('saml.sls', compact('uuid'))
        ];
    }
}
