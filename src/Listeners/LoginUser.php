<?php

namespace Slides\Saml2\Listeners;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Slides\Saml2\Auth as SamlAuth;
use Slides\Saml2\Concerns\UserResolverHelpers;
use Slides\Saml2\Contracts\ResolvesUser;
use Slides\Saml2\Events\SignedIn;
use Slides\Saml2\Exceptions\UserResolutionException;
use Slides\Saml2\Models\Session;
use Slides\Saml2\Saml2User;

class LoginUser
{
    use UserResolverHelpers;

    /**
     * Handle the event.
     *
     * @param SignedIn $event
     *
     * @return void
     */
    public function handle(SignedIn $event)
    {
        if (!config('saml2.auth.enabled')) {
            return;
        }

        $user = $this->resolveOrCreateUser($event->getAuth());

        Auth::login($user);

        tap(new Session([
            'idp_id' => $event->getAuth()->getIdp()->id,
            'user_id' => $user,
            'payload' => $event->getAuth()->getSaml2User()->getAttributes()
        ]))->save();
    }

    /**
     * Resolve or create a new user.
     *
     * @param SamlAuth $samlAuth
     *
     * @return Authenticatable
     */
    protected function resolveOrCreateUser(SamlAuth $samlAuth): Authenticatable
    {
        $resolvedUser = app(ResolvesUser::class)->resolve($samlAuth);

        if ($resolvedUser) {
            return $resolvedUser;
        }

        if (!config('saml2.auth.createUser')) {
            throw new UserResolutionException('Cannot signup a new user. Enable this ability or create your own logic', $samlAuth->getSaml2User());
        }

        if (config('saml2.debug')) {
            Log::debug('[Saml2] Creating a new user', [
                'idpUuid' => $samlAuth->getIdp()->idpUuid(),
                'userId' => $samlAuth->getSaml2User()->getUserId(),
                'userAttributes' => $samlAuth->getSaml2User()->getAttributes(),
            ]);
        }

        return $this->createUser($samlAuth->getSaml2User());
    }

    /**
     * Create a new user.
     *
     * @param Saml2User $samlUser
     *
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    protected function createUser(Saml2User $samlUser)
    {
        $model = config('saml2.auth.userModel');

        $user = new $model;
        $user->name = $this->resolveUserName($samlUser);
        $user->email = $this->resolveUserEmail($samlUser);
        $user->password = Hash::make(Str::random());
        $user->save();

        return $user;
    }
}
