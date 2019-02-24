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
     * The last resolved Identity Provider.
     *
     * @var string
     */
    protected $lastResolved;

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
                $this->lastResolved = $key;

                return $config;
            }
        }

        if(!$default = $this->retrieveDefaultIdP()) {
            throw new \InvalidArgumentException('Default IdP is not defined');
        }

        $this->lastResolved = $this->defaultIdPKey();

        return $default;
    }

    /**
     * Get the latest resolved IdP's key.
     *
     * @return string
     */
    public function getLastResolvedKey(): string
    {
        return $this->lastResolved;
    }

    /**
     * Get the default IdP config.
     *
     * @return array|null
     */
    protected function retrieveDefaultIdP()
    {
        return array_get($this->idpConfig, $this->defaultIdPKey());
    }

    /**
     * Get the default's IdP key.
     *
     * @return string|null
     */
    protected function defaultIdPKey()
    {
        return array_get($this->idpConfig, 'default');
    }
}