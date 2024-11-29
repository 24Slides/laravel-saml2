<?php

namespace Slides\Saml2;

use OneLogin\Saml2\Auth as OneLoginAuth;
use OneLogin\Saml2\Utils as OneLoginUtils;
use Illuminate\Support\Facades\URL;
use Illuminate\Contracts\Container\Container;
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
     * IdP configuration.
     *
     * @var array
     *
     * @see https://github.com/SAML-Toolkits/php-saml#settings
     */
    protected $idp;

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
     * Set IdP configuration.
     *
     * @param array $idp
     *
     * @return $this
     */
    public function withIdp(array $idp)
    {
        $this->idp = $idp;

        return $this;
    }

    /**
     * Bootstrap the OneLogin toolkit.
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
            // Supply only what is needed.
            unset($config['idps']);
            $this->setConfigDefaultValues($config);

            $oneLoginConfig = $config;
            $oneLoginConfig['idp'] = $this->idp;

            if ($config['sp']['NameIDFormatFromIdp']) {
                $oneLoginConfig['sp']['NameIDFormat'] = $oneLoginConfig['idp']['NameIDFormat'];
            }

            return new OneLoginAuth($oneLoginConfig);
        });

        $this->app->singleton('Slides\Saml2\Auth', function ($app) {
            return new \Slides\Saml2\Auth($app['OneLogin_Saml2_Auth'], $this->idp);
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
            'sp.entityId' => URL::route('saml.metadata', ['key' => $this->idp['key']]),
            'sp.assertionConsumerService.url' => URL::route('saml.acs', ['key' => $this->idp['key']]),
            'sp.singleLogoutService.url' => URL::route('saml.sls', ['key' => $this->idp['key']])
        ];
    }
}
