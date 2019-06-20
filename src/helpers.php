<?php

if (!function_exists('saml_url'))
{
    /**
     * Generate a URL to saml/{idp_key}/login which redirects to a target URL.
     *
     * @param string $path
     * @param string|null $idpKey
     * @param array $parameters
     * @param bool $secure
     *
     * @return string
     */
    function saml_url(string $path, string $idpKey = null, $parameters = [], bool $secure = null)
    {
        $target = \Illuminate\Support\Facades\URL::to($path, $parameters, $secure);

        if(!$idpKey) {
            $idpKey = saml_idp_key();
        }

        return \Illuminate\Support\Facades\URL::route('saml.login', ['idpKey' => $idpKey, 'returnTo' => $target]);
    }
}

if (!function_exists('saml_route'))
{
    /**
     * Generate a URL to saml/{idp_key}/login which redirects to a target route.
     *
     * @param string $name
     * @param string|null $idpKey
     * @param array $parameters
     *
     * @return string
     */
    function saml_route(string $name, string $idpKey = null, $parameters = [])
    {
        $target = \Illuminate\Support\Facades\URL::route($name, $parameters, true);

        if(!$idpKey) {
            $idpKey = saml_idp_key();
        }

        return \Illuminate\Support\Facades\URL::route('saml.login', ['idpKey' => $idpKey, 'returnTo' => $target]);
    }
}

if (!function_exists('saml_idp_key'))
{
    /**
     * Get a resolved IdP key based on current URL.
     *
     * @return string|null
     */
    function saml_idp_key()
    {
        return \Slides\Saml2\Facades\Auth::getResolvedIdPKey();
    }
}