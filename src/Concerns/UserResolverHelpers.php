<?php

namespace Slides\Saml2\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Slides\Saml2\Saml2User;

trait UserResolverHelpers
{
    /**
     * The list of attribute names containing the user full name..
     *
     * @var array|string[]
     */
    protected array $userNameAttributes = [
        'FullName',
        'name',
        'givenname',
        'displayname'
    ];

    /**
     * The list of attribute names containing the user email.
     *
     * @var array|string[]
     */
    protected array $userEmailAttributes = [
        'emailaddress',
        'Email'
    ];

    /**
     * Resolve a user email from the request.
     *
     * @param Saml2User $user
     *
     * @return string|null
     */
    protected function resolveUserEmail(Saml2User $user): ?string
    {
        // There is a chance that email is passed as a UserID.
        if (filter_var($user->getUserId(), FILTER_VALIDATE_EMAIL)) {
            return $user->getUserId();
        }

        // Otherwise, we need to lookup through attributes.
        return $this->firstUserAttribute($user, $this->userEmailAttributes);
    }

    /**
     * Resolve a user name.
     *
     * @param Saml2User $user
     *
     * @return mixed|string|null
     */
    protected function resolveUserName(Saml2User $user)
    {
        // First of all, we need to look up through attributes
        if ($name = $this->firstUserAttribute($user, $this->userNameAttributes)) {
            return $name;
        }

        Log::warning('[SSO] Not able to resolve user name, extracting from email.', [
            'samlAttributes' => $user->getAttributes(),
            'samlUserId' => $user->getUserId(),
            'samlNameId' => $user->getNameId()
        ]);

        // Not the best solution, but if user name cannot be resolved,
        // we can try to extract it from the email address
        return $this->extractNameFromEmail(
            $this->resolveUserEmail($user)
        );
    }

    /**
     * Find a user attribute value using a list of names.
     *
     * @param Saml2User $user
     * @param array $attributes
     * @param string|null $default
     *
     * @return mixed|string|null
     */
    protected function firstUserAttribute(Saml2User $user, array $attributes, $default = null)
    {
        return Arr::first($attributes, fn($attribute) => $this->getUserAttribute($user, $attribute), $default);
    }

    /**
     * Get user's attribute.
     *
     * @param Saml2User $user
     * @param string $attribute
     * @param string|null $default
     *
     * @return string|mixed|null
     */
    protected function getUserAttribute(Saml2User $user, string $attribute, string $default = null): ?string
    {
        foreach ($user->getAttributes() as $claim => $value) {
            $value = $value[0];

            if (strpos($claim, $attribute) !== false) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Attempt to extract full name from the email address.
     *
     * @param string $email
     *
     * @return string|null
     */
    protected function extractNameFromEmail(string $email): ?string
    {
        // Extract words from the email name
        preg_match_all('/[a-z]+/i', Str::before($email, '@'), $matches);

        $words = $matches[0];

        if (!$words) {
            return null;
        }

        // Keep only two first words and capitalize them
        $words = array_map(
            fn(string $word) => Str::title($word),
            array_slice($words, 0, 2)
        );

        return implode(' ', $words);
    }
}
