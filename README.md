## [Laravel 5.4+] SAML Service Provider 

[![Latest Stable Version][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Code Coverage][ico-code-coverage]][link-code-coverage]
[![Total Downloads][ico-downloads]][link-downloads]

An integration to add SSO to your service via SAML2 protocol based on [OneLogin](https://github.com/onelogin/php-saml) toolkit. 

This package turns your application into Service Provider with the support of multiple Identity Providers.

## Requirements

- Laravel 5.4+
- PHP 5.4+

## Getting Started

### Installing

Install dependency via Composer

```
composer require 24slides/laravel-saml2
```

If you are using Laravel 5.5 and higher, the service provider will be automatically registered.

For older versions, you have to add the service provider and alias to your `config/app.php`:

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

Publish the configuration file:

```
php artisan vendor:publish --provider="Slides\Saml2\ServiceProvider
```

### Configuring

Once you publish your `saml2.php` to `app/config`, you need to configure your SP and IdP servers.
Almost all parameters are configurable through environment variables.

Remember that you don't need to implement those routes, but you'll need to add them to your IDP configuration. For example, if you use SimpleSAMLphp, add the following to `/metadata/sp-remote.php`

To make sure it works, check metadata at `http://yourdomain/saml2/metadata`.

#### Default routes

The following routes are registered by default:

- `GET saml2/login`
- `GET saml2/logout`
- `GET saml2/metadata`
- `POST saml2/acs`
- `POST saml2/sls`

You may disabled them by setting `saml2.useRoutes` to `false`.

> `/saml2` prefix can be changed via `saml2.routesPrefix` config parameter.

#### Multiple Identity Providers

You may define multiple IdPs you want to integrate.

It works by resolving requester referrer URL. When request comes to application, 
we initialize matching referrer URL with IdP URL (eg. `SAML2_IDP_ONE_LOGIN_URL`)

In case if IdP cannot be resolved, `idp.default` will be initialized. 

## Usage

### Authentication events

The simplest way to handle SAML authentication is to add listeners on `Slides\Saml2\SignedIn` and `Slides\Saml2\SignedOut` events.

```php
Event::listen(\Slides\Saml2\Events\SignedIn::class, function (\Slides\Saml2\Events\SignedIn $event) {
    $messageId = $event->getAuth()->getLastMessageId();
    
    // your own code preventing reuse of a $messageId to stop replay attacks
    $samlUser = $event->getSaml2User();
    
    $userData = [
        'id' => $user->getUserId(),
        'attributes' => $user->getAttributes(),
        'assertion' => $user->getRawSamlAssertion()
    ];
    
    $user = // find user by ID or attribute
    
    // Login a user.
    Auth::login($user);
});
```

### Middleware

To define a middleware for default routes, add its name to `config/saml2.php`:

```
/*
|--------------------------------------------------------------------------
| Middle groups to use for the SAML routes
|--------------------------------------------------------------------------
|
| Note, Laravel 5.2 requires a group which includes StartSession
|
*/

'routesMiddleware' => ['saml'],
```

Then you need to define necessary middlewares for your group in `app/Http/Kernel.php`:

```php
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

### Logging out

There are two ways the user can logout:
- By logging out in your app. In this case you SHOULD notify the IdP first so it'll close the global session.
- By logging out of the global SSO Session. In this case the IdP will notify you on `/saml2/slo` endpoint (already provided).

For the first case, call `Saml2Auth::logout();` or redirect the user to the route `saml.logout` which does just that. 
Do not close the session immediately as you need to receive a response confirmation from the IdP (redirection). 
That response will be handled by the library at `/saml2/sls` and will fire an event for you to complete the operation.

For the second case you will only receive the event. Both cases receive the same event. 

Note that for the second case, you may have to manually save your session to make the logout stick (as the session is saved by middleware, but the OneLogin library will redirect back to your IdP before that happens):

```php
Event::listen('Slides\Saml2\Events\SignedOut', function (SignedOut $event) {
    Auth::logout();
    Session::save();
});
```

## Tests

Run the following in the package folder:

```
vendor/bin/phpunit
```

## Security

If you discover any security related issues, please email **brezzhnev@gmail.com** instead of using the issue tracker.

## Credits

- [aacotroneo][link-original-author]
- [brezzhnev][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://poser.pugx.org/24slides/laravel-saml2/v/stable?format=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/24Slides/laravel-saml2.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/24slides/laravel-saml2.svg?style=flat-square
[ico-code-coverage]: https://img.shields.io/scrutinizer/coverage/g/24slides/laravel-saml2.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/24slides/laravel-saml2.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/24slides/laravel-saml2
[link-travis]: https://travis-ci.org/24Slides/laravel-saml2
[link-scrutinizer]: https://scrutinizer-ci.com/g/24slides/laravel-saml2/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/24slides/laravel-saml2
[link-code-coverage]: https://scrutinizer-ci.com/g/24Slides/laravel-saml2
[link-downloads]: https://packagist.org/packages/24slides/laravel-saml2
[link-original-author]: https://github.com/aacotroneo
[link-author]: https://github.com/brezzhnev
[link-contributors]: ../../contributors