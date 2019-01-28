## [Laravel 5] - SAML 2.0 Service Provider

[![Latest Stable Version][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Code Coverage][ico-code-coverage]][link-code-coverage]
[![Total Downloads][ico-downloads]][link-downloads]

A Laravel package for SAML2 integration as a SP (service provider) based on [OneLogin](https://github.com/onelogin/php-saml) toolkit, which is much lighter and easier to install than SimpleSAMLphp SP. 
It doesn't need separate routes or session storage to work!

## Installation - Composer

You can install the package via composer:

```
composer require 24slides/laravel-saml2
```

If you are using Laravel 5.5 and up, the service provider will automatically get registered.

For older versions of Laravel (<5.5), you have to add the service provider and alias to config/app.php:

```php
'providers' => [
        ...
    	Slides\Saml2\ServiceProvider::class,
]

'alias' => [
        ...
        'Saml2' => Slides\Saml2\Facades\Auth::class,
]
```

Then publish the config file with `php artisan vendor:publish --provider="Slides\Saml2\ServiceProvider"`. 
This will add the file `app/config/saml2.php`. 
This config is handled almost directly by [OneLogin](https://github.com/onelogin/php-saml) so you may get further references there, but will cover here what's really necessary. 
There are some other config about routes you may want to check, they are pretty straightforward.

### Configuration

Once you publish your saml2.php to your own files, you need to configure your SP and IdP (remote server). 
The only real difference between this config and the one that OneLogin uses, is that the SP `entityId`, `assertionConsumerService` url and `singleLogoutService` URL are injected by the library. 
They are taken from routes 'saml.metadata', 'saml.acs' and 'saml.sls' respectively.

Remember that you don't need to implement those routes, but you'll need to add them to your IDP configuration. For example, if you use SimpleSAMLphp, add the following to `/metadata/sp-remote.php`

```php
$metadata['http://laravel_url/saml2/metadata'] = array(
    'AssertionConsumerService' => 'http://laravel_url/saml2/acs',
    'SingleLogoutService' => 'http://laravel_url/saml2/sls',
    //the following two affect what the $Saml2user->getUserId() will return
    'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
    'simplesaml.nameidattribute' => 'uid' 
);
```

You can check that metadata if you actually navigate to `http://laravel_url/saml2/metadata`


### Usage

When you want your user to login, just call `Auth::login()` or redirect to route 'saml2.login'. 
Just remember that it does not use any session storage, so if you ask it to login it will redirect to the IDP whether the user is logged in or not. 
For example, you can change your authentication middleware.

```php
public function handle($request, Closure $next)
{
    if ($this->auth->guest())
    {
        if ($request->ajax())
        {
            return response('Unauthorized.', 401);
        }
        
        return Saml2::login(URL::full());
    }
    
    return $next($request);
}
```

Since Laravel 5.3, you can change your unauthenticated method in `app/Exceptions/Handler.php`.

```php
protected function unauthenticated($request, AuthenticationException $exception)
{
    if ($request->expectsJson())
    {
        return response()->json(['error' => 'Unauthenticated.'], 401);
    }
    
    return Saml2::login();
}
```

The `Saml2::login` will redirect the user to the IdP and will came back to an endpoint the library serves at `/saml2/acs`. 
That will process the response and fire an event when ready. 
The next step for you is to handle that event. 
You just need to login the user or refuse.

```php
Event::listen('Slides\Saml2\Events\SignedIn', function (Saml2LoginEvent $event) {
    $messageId = $event->getAuth()->getLastMessageId();
    
    // your own code preventing reuse of a $messageId to stop replay attacks
    $user = $event->getSaml2User();
    
    $userData = [
        'id' => $user->getUserId(),
        'attributes' => $user->getAttributes(),
        'assertion' => $user->getRawSamlAssertion()
    ];
    
    $laravelUser = // find user by ID or attribute
    
    // if it does not exist create it and go on or show an error message
    Auth::login($laravelUser);
});
```

### Auth persistence

Be careful about necessary Laravel middleware for Auth persistence in Session.

For example, it could be:

```php
# in App\Http\Kernel
protected $middlewareGroups = [
    'web' => [
        ...
    ],
    'api' => [
        ...
    ],
    'saml' => [
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
    ],

```

And in `config/saml2.php`:
```
/**
 * Which middleware group to use for the saml routes.
 * Laravel 5.2 will need a group which includes StartSession.
 */
'routesMiddleware' => ['saml'],
```

### Log out

Now there are two ways the user can log out.
- By logging out in your app: In this case you 'should' notify the IDP first so it closes global session.
- By logging out of the global SSO Session. In this case the IDP will notify you on `/saml2/slo` endpoint (already provided)

For case 1 call `Saml2Auth::logout();` or redirect the user to the route 'saml.logout' which does just that. 
Do not close the session immediately as you need to receive a response confirmation from the IdP (redirection). 
That response will be handled by the library at `/saml2/sls` and will fire an event for you to complete the operation.

For case 2 you will only receive the event. Both cases 1 and 2 receive the same event. 

Note that for case 2, you may have to manually save your session to make the logout stick (as the session is saved by middleware, but the OneLogin library will redirect back to your IdP before that happens)

```php
Event::listen('Slides\Saml2\Events\SignedOut', function ($event) {
    Auth::logout();
    Session::save();
});
```

That's it. Feel free to ask any questions, make PR or suggestions, or open Issues.

[ico-version]: https://poser.pugx.org/24slides/auth-connector/v/stable?format=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/24Slides/auth-connector.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/24slides/auth-connector.svg?style=flat-square
[ico-code-coverage]: https://img.shields.io/scrutinizer/coverage/g/24slides/auth-connector.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/24slides/auth-connector.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/24slides/laravel-saml2
[link-travis]: https://travis-ci.org/24Slides/laravel-saml2
[link-scrutinizer]: https://scrutinizer-ci.com/g/24slides/laravel-saml2/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/24slides/laravel-saml2
[link-code-coverage]: https://scrutinizer-ci.com/g/24Slides/laravel-saml2
[link-downloads]: https://packagist.org/packages/24slides/laravel-saml2