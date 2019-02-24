<?php

namespace Slides\Saml2;

use OneLogin\Saml2\Auth as OneLoginAuth;
use OneLogin\Saml2\Utils as OneLoginUtils;
use Illuminate\Support\Facades\URL;

/**
 * Class ServiceProvider
 *
 * @package Slides\Saml2
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * @var IdpResolver
     */
    protected $idpResolver;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->bootRoutes();
        $this->bootPublishes();

        if ($this->app['config']->get('saml2.proxyVars', false)) {
            OneLoginUtils::setProxyVars(true);
        }
    }

    /**
     * Bootstrap the routes.
     *
     * @return void
     */
    protected function bootRoutes()
    {
        if($this->app['config']['saml2.useRoutes'] == true) {
            include __DIR__ . '/Http/routes.php';
        }
    }

    /**
     * Bootstrap the publishable files.
     *
     * @return void
     */
    protected function bootPublishes()
    {
        $this->publishes([
            __DIR__ . '../config/saml2.php' => config_path('saml2.php'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerAuthenticationHandler();

        $this->app->singleton('Slides\Saml2\Auth', function ($app) {
            return new \Slides\Saml2\Auth(
                $app['OneLogin_Saml2_Auth'],
                $this->idpResolver->getLastResolvedKey()
            );
        });
    }

    /**
     * Register the authentication handler.
     *
     * @return void
     */
    protected function registerAuthenticationHandler()
    {
        $this->app->singleton('OneLogin_Saml2_Auth', function ($app) {
            $config = $app['config']['saml2'];

            $this->setConfigDefaultValues($config);

            $oneLoginConfig = $config;
            $oneLoginConfig['idp'] = $this->resolveIdentityProvider($config['idp']);

            return new OneLoginAuth($this->normalizeConfigParameters($oneLoginConfig));
        });
    }

    /**
     * Configuration default values that must be replaced with custiom ones.
     *
     * @return array
     */
    protected function configDefaultValues()
    {
        return [
            'sp.entityId' => URL::route('saml.metadata'),
            'sp.assertionConsumerService.url' => URL::route('saml.acs'),
            'sp.singleLogoutService.url' => URL::route('saml.sls')
        ];
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
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
     * Assign a default value to variable if its empty.
     *
     * @param mixed $var
     * @param mixed $default
     *
     * @return void
     */
    protected function setDefaultValue(&$var, $default)
    {
        if (empty($var)) {
            $var = $default;
        }
    }

    /**
     * Resolve an Identity Provider.
     *
     * @param array $config The IdPs config.
     *
     * @return array
     */
    protected function resolveIdentityProvider(array $config): array
    {
        return ($this->idpResolver = new IdpResolver($config, URL::previous()))
            ->resolve();
    }
}
