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
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        if($this->app['config']['saml2.useRoutes'] == true) {
            include __DIR__ . '/Http/routes.php';
        }

        $this->publishes([
            __DIR__ . '../config/saml2.php' => config_path('saml2.php'),
        ]);

        if (config('saml2.proxyVars', false)) {
            OneLoginUtils::setProxyVars(true);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerOneLoginInContainer();

        $this->app->singleton('Slides\Saml2\Auth', function ($app) {
            return new \Slides\Saml2\Auth($app['OneLogin_Saml2_Auth']);
        });
    }


    protected function registerOneLoginInContainer()
    {
        $this->app->singleton('OneLogin_Saml2_Auth', function ($app) {
            $config = config('saml2');

            if (empty($config['sp']['entityId'])) {
                $config['sp']['entityId'] = URL::route('saml.metadata');
            }
            if (empty($config['sp']['assertionConsumerService']['url'])) {
                $config['sp']['assertionConsumerService']['url'] = URL::route('saml.acs');
            }
            if (!empty($config['sp']['singleLogoutService']) &&
                empty($config['sp']['singleLogoutService']['url'])) {
                $config['sp']['singleLogoutService']['url'] = URL::route('saml.sls');
            }
            if (strpos($config['sp']['privateKey'], 'file://')===0) {
                $config['sp']['privateKey'] = $this->extractPkeyFromFile($config['sp']['privateKey']);
            }
            if (strpos($config['sp']['x509cert'], 'file://')===0) {
                $config['sp']['x509cert'] = $this->extractCertFromFile($config['sp']['x509cert']);
            }
            if (strpos($config['idp']['x509cert'], 'file://')===0) {
                $config['idp']['x509cert'] = $this->extractCertFromFile($config['idp']['x509cert']);
            }

            $config['idp'] = array_get($config, 'idp.default');

            return new OneLoginAuth($config);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }

    protected function extractPkeyFromFile($path) {
        $res = openssl_get_privatekey($path);
        if (empty($res)) {
            throw new \Exception('Could not read private key-file at path \'' . $path . '\'');
        }
        openssl_pkey_export($res, $pkey);
        openssl_pkey_free($res);
        return $this->extractOpensslString($pkey, 'PRIVATE KEY');
    }

    protected function extractCertFromFile($path) {
        $res = openssl_x509_read(file_get_contents($path));
        if (empty($res)) {
            throw new \Exception('Could not read X509 certificate-file at path \'' . $path . '\'');
        }
        openssl_x509_export($res, $cert);
        openssl_x509_free($res);
        return $this->extractOpensslString($cert, 'CERTIFICATE');
    }

    protected function extractOpensslString($keyString, $delimiter) {
        $keyString = str_replace(["\r", "\n"], "", $keyString);
        $regex = '/-{5}BEGIN(?:\s|\w)+' . $delimiter . '-{5}\s*(.+?)\s*-{5}END(?:\s|\w)+' . $delimiter . '-{5}/m';
        preg_match($regex, $keyString, $matches);
        return empty($matches[1]) ? '' : $matches[1];
    }
}
