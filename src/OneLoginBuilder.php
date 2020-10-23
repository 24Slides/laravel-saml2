<?php

namespace Slides\Saml2;

use OneLogin\Saml2\Auth as OneLoginAuth;
use OneLogin\Saml2\Utils as OneLoginUtils;
use Illuminate\Support\Facades\URL;
use Illuminate\Contracts\Container\Container;
use Slides\Saml2\Models\Tenant;
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
                'x509cert' => $this->tenant->idp_x509_cert
            ];

            $oneLoginConfig['sp']['NameIDFormat'] = $this->resolveNameIdFormatPrefix($this->tenant->name_id_format);

            return new OneLoginAuth($oneLoginConfig);
        });

        $this->app->singleton('Slides\Saml2\Auth', function ($app) {
            return new \Slides\Saml2\Auth($app['OneLogin_Saml2_Auth'], $this->tenant);
        });
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
            if(!Arr::get($config, $key)) {
                Arr::set($config, $key, $default);
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

    /**
     * Resolve the Name ID Format prefix.
     *
     * @param string $format
     *
     * @return string
     */
    protected function resolveNameIdFormatPrefix(string $format): string
    {
        switch ($format) {
            case 'emailAddress':
            case 'X509SubjectName':
            case 'WindowsDomainQualifiedName':
            case 'unspecified':
                return 'urn:oasis:names:tc:SAML:1.1:nameid-format:' . $format;
            default:
                return 'urn:oasis:names:tc:SAML:2.0:nameid-format:'. $format;
        }
    }
}