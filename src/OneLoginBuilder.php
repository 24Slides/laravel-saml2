<?php

namespace Slides\Saml2;

use OneLogin\Saml2\Auth as OneLoginAuth;
use OneLogin\Saml2\Utils as OneLoginUtils;
use Illuminate\Support\Facades\URL;
use Illuminate\Contracts\Container\Container;
use Slides\Saml2\Models\Tenant;

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
     * The resolved tenant.
     *
     * @var Tenant
     */
    protected $tenant;

    /**
     * OneLoginBuilder constructor.
     *
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Set a tenant.
     *
     * @param Tenant $tenant
     *
     * @return $this
     */
    public function withTenant(Tenant $tenant)
    {
        $this->tenant = $tenant;

        return $this;
    }

    /**
     * Bootstrap the OneLogin toolkit.
     *
     * @param Tenant $tenant
     *
     * @return void
     */
    public function bootstrap()
    {
        if ($this->app['config']->get('saml2.proxyVars', false)) {
            OneLoginUtils::setProxyVars(true);
        }

        $this->app->singleton('OneLogin_Saml2_Auth', function ($app) {
            $config = $app['config']['saml2'];

            $this->setConfigDefaultValues($config);

            $oneLoginConfig = $config;
            $oneLoginConfig['idp'] = [
                'entityId' => $this->tenant->idp_entity_id,
                'singleSignOnService' => ['url' => $this->tenant->idp_login_url],
                'singleLogoutService' => ['url' => $this->tenant->idp_logout_url],
                'certs' => ['x509' => $this->tenant->idp_x509_cert]
            ];

            return new OneLoginAuth($this->normalizeConfigParameters($oneLoginConfig));
        });

        $this->app->singleton('Slides\Saml2\Auth', function ($app) {
            return new \Slides\Saml2\Auth($app['OneLogin_Saml2_Auth'], $this->tenant);
        });
    }

    /**
     * Normalize config parameters for OneLogin authentication handler.
     *
     * @param array $config
     *
     * @return array
     */
    protected function normalizeConfigParameters(array $config)
    {
        $config['idp']['x509cert'] = array_get($config['idp'], 'certs.x509');
        $config['idp']['certFingerprint'] = array_get($config['idp'], 'certs.fingerprint');

        return $config;
    }

    /**
     * Set default config values if they weren't set.
     *
     * @param array $config
     *
     * @return void
     */
    protected function setConfigDefaultValues(array &$config)
    {
        foreach ($this->configDefaultValues() as $key => $default) {
            if(!array_get($config, $key)) {
                array_set($config, $key, $default);
            }
        }
    }

    /**
     * Configuration default values that must be replaced with custom ones.
     *
     * @return array
     */
    protected function configDefaultValues()
    {
        return [
            'sp.entityId' => URL::route('saml.metadata', ['uuid' => $this->tenant->uuid]),
            'sp.assertionConsumerService.url' => URL::route('saml.acs', ['uuid' => $this->tenant->uuid]),
            'sp.singleLogoutService.url' => URL::route('saml.sls', ['uuid' => $this->tenant->uuid])
        ];
    }
}