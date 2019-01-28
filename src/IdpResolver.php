<?php

namespace Slides\Saml2;

/**
 * Class IdpResolver
 *
 * @package Slides\Saml2
 */
class IdpResolver
{
    /**
     * The IdPs list.
     *
     * @var array
     */
    protected $idpConfig;

    /**
     * The referrer URL.
     *
     * @var string
     */
    protected $referrer;

    /**
     * IdpResolver constructor.
     *
     * @param array $idpConfig
     * @param string $referrer
     */
    public function __construct(array $idpConfig, $referrer)
    {
        $this->idpConfig = $idpConfig;
        $this->referrer = $referrer;
    }

    /**
     * Resolve an Identity Provider by incoming request.
     *
     * @return array
     */
    public function resolve()
    {
        foreach ($this->idpConfig as $key => $config) {
            if($key === 'default') {
                continue;
            }

            if(strpos($config['url'], $this->referrer) !== false) {
                return $config;
            }
        }

        if(!$default = $this->retrieveDefaultIdP()) {
            throw new \InvalidArgumentException('Default IdP is not defined');
        }

        return $default;
    }

    /**
     * Get the default IdP.
     *
     * @return array|null
     */
    protected function retrieveDefaultIdP()
    {
        return array_get($this->idpConfig,
            array_get($this->idpConfig, 'default')
        );
    }
}