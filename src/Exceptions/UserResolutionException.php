<?php

namespace Slides\Saml2\Exceptions;

use Illuminate\Support\Facades\Log;
use Slides\Saml2\Saml2User;

class UserResolutionException extends \InvalidArgumentException
{
    /**
     * @param string $message
     * @param Saml2User|null $saml2User
     */
    public function __construct(string $message, Saml2User $saml2User = null)
    {
        if ($saml2User && config('saml2.debug')) {
            Log::debug('[Saml2] User resolution failed', [
                'message' => $message,
                'attributes' => $saml2User->getAttributes()
            ]);
        }

        parent::__construct($message);
    }
}
