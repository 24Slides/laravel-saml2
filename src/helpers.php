<?php

if (!function_exists('saml_url'))
{
    /**
     * Generate a URL to saml/{key}/login which redirects to a target URL.
     *
     * @param string $path
     * @param string|null $key An IdP key.
     * @param array $parameters
     * @param bool $secure
     *
     * @return string
     */
    function saml_url(string $path, string $key = null, array $parameters = [], bool $secure = null)
    {
        $target = \Illuminate\Support\Facades\URL::to($path, $parameters, $secure);

        if(!$key) {
            if(!$key = saml_idp_key()) {
                return $target;
            }
        }

        return \Illuminate\Support\Facades\URL::route('saml.login', ['key' => $key, 'returnTo' => $target]);
    }
}

if (!function_exists('saml_route'))
{
    /**
     * Generate a URL to saml/{key}/login which redirects to a target route.
     *
     * @param string $name
     * @param string|null $key An IdP key.
     * @param array $parameters
     *
     * @return string
     */
    function saml_route(string $name, string $key = null, array $parameters = [])
    {
        $target = \Illuminate\Support\Facades\URL::route($name, $parameters, true);

        if(!$key) {
            if(!$key = saml_idp_key()) {
                return $target;
            }
        }

        return \Illuminate\Support\Facades\URL::route('saml.login', ['key' => $key, 'returnTo' => $target]);
    }
}

if (!function_exists('saml_idp_key'))
{
    /**
     * Get a resolved IdP key based on the current URL.
     *
     * @return string|null
     */
    function saml_idp_key()
    {
        return session()->get('saml2.idp.key');
    }
}
