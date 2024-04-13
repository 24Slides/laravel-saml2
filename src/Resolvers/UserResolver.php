<?php

namespace Slides\Saml2\Resolvers;

use Illuminate\Database\Eloquent;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Slides\Saml2\Auth as SamlAuth;
use Slides\Saml2\Concerns\UserResolverHelpers;
use Slides\Saml2\Contracts\ResolvesUser;
use Slides\Saml2\Exceptions\ConfigurationException;
use Slides\Saml2\Exceptions\UserResolutionException;

class UserResolver implements ResolvesUser
{
    use UserResolverHelpers;

    /**
     * Resolve a user from the request.
     *
     * @param SamlAuth $samlAuth
     *
     * @return User|Eloquent|null
     */
    public function resolve(SamlAuth $samlAuth)
    {
        if (!$email = $this->resolveUserEmail($samlAuth->getSaml2User())) {
            throw new UserResolutionException('Unable to resolve user email', $samlAuth->getSaml2User());
        }

        if (!$provider = config('auth.defaults.passwords')) {
            throw new ConfigurationException('No default password provider configured');
        }

        // Attempt to retrieve user by email.
        return Auth::createUserProvider($provider)->retrieveByCredentials(['email' => $email]);
    }
}
