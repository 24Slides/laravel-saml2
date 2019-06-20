<?php

namespace Slides\Saml2;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

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
     * The last resolved Identity Provider.
     *
     * @var string
     */
    protected $lastResolved;

    /**
     * IdpResolver constructor.
     *
     * @param array $idpConfig
     */
    public function __construct(array $idpConfig)
    {
        $this->idpConfig = $idpConfig;
    }

    /**
     * Resolve an Identity Provider by incoming request.
     *
     * @return array|null
     */
    public function resolve()
    {
        if(!$idpKey = Request::segment(2)) {
            Log::debug('[saml2] IdP is not present in the URL so cannot be resolved', [
                'url' => Request::fullUrl()
            ]);

            return null;
        }

        if(!array_key_exists($idpKey, $this->idpConfig)) {
            Log::debug('[saml2] IdP key ' . $idpKey . ' is not listed in your config', [
                'idpKey' => $idpKey,
                'idpKeys' => array_keys($this->idpConfig)
            ]);

            return null;
        }

        $this->lastResolved = $idpKey;

        return $this->idpConfig[$idpKey];
    }

    /**
     * Get the latest resolved IdP's key.
     *
     * @return string|null
     */
    public function getLastResolvedKey()
    {
        return $this->lastResolved;
    }
}