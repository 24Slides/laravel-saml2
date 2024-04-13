<?php

namespace Slides\Saml2;

use Slides\Saml2\Events\SignedIn;
use Slides\Saml2\Listeners\LoginUser;

class EventServiceProvider extends \Illuminate\Foundation\Support\Providers\EventServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array[]
     */
    protected $listen = [
        SignedIn::class => [
            LoginUser::class,
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
