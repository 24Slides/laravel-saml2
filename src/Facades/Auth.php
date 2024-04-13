<?php

namespace Slides\Saml2\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Saml2Auth
 *
 * @method static \Slides\Saml2\Models\IdentityProvider|null getTenant()
 *
 * @package Slides\Saml2\Facades
 */
class Auth extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Slides\Saml2\Auth';
    }
}
