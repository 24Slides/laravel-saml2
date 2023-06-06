<?php

if (!function_exists('saml_url'))
{
    /**
     * Generate a URL to saml/{uuid}/login which redirects to a target URL.
     *
     * @param string $path
     * @param string|null $uuid A tenant UUID.
     * @param array $parameters
     * @param bool $secure
     *
     * @return string
     */
    function saml_url(string $path, string $uuid = null, $parameters = [], bool $secure = null)
    {
        $target = \Illuminate\Support\Facades\URL::to($path, $parameters, $secure);

        if(!$uuid) {
            if(!$uuid = saml_tenant_uuid()) {
                return $target;
            }
        }

        return \Illuminate\Support\Facades\URL::route('saml.login', ['uuid' => $uuid, 'returnTo' => $target]);
    }
}

if (!function_exists('saml_route'))
{
    /**
     * Generate a URL to saml/{uuid}/login which redirects to a target route.
     *
     * @param string $name
     * @param string|null $uuid A tenant UUID.
     * @param array $parameters
     *
     * @return string
     */
    function saml_route(string $name, string $uuid = null, $parameters = [])
    {
        $target = \Illuminate\Support\Facades\URL::route($name, $parameters, true);

        if(!$uuid) {
            if(!$uuid = saml_tenant_uuid()) {
                return $target;
            }
        }

        return \Illuminate\Support\Facades\URL::route('saml.login', ['uuid' => $uuid, 'returnTo' => $target]);
    }
}

if (!function_exists('saml_tenant_uuid'))
{
    /**
     * Get a resolved Tenant UUID based on current URL.
     *
     * @return string|null
     */
    function saml_tenant_uuid()
    {
        return session()->get('saml2.tenant.uuid');
    }
}