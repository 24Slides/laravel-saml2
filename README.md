## [Laravel 5.4+] SAML Service Provider 

[![Latest Stable Version][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Code Coverage][ico-code-coverage]][link-code-coverage]
[![Total Downloads][ico-downloads]][link-downloads]

An integration to add SSO to your service via SAML2 protocol based on [SAML PHP Toolkit] toolkit. 

This package turns your application into Service Provider with the support of multiple Identity Providers.

## Requirements

- Laravel 5.4+
- PHP 7.0+

## Getting Started

### Installing

##### Step 1. Install dependency

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

##### Step 2. Publish the configuration file.

```
php artisan vendor:publish --provider="Slides\Saml2\ServiceProvider"
```


### Configuring

Once you publish `saml2.php` to `app/config`, you need to configure your service provider (SP). 
Most of the options are inherited from [SAML PHP Toolkit], so you can check documentation there.
This relates to identity providers (IdPs) as well.



#### Identity Providers

Identity providers (IdPs) are configured in the same `saml2.php` configuration file under `idps` key.
**N.B.** That it is plural (`idp**S**`), unlike in [SAML PHP Toolkit], because we support multiple IdPs.


#### Default routes

The following routes are registered by default:

- `GET saml2/{key}/login`
- `GET saml2/{key}/logout`
- `GET saml2/{key}/metadata`
- `POST saml2/{key}/acs`
- `POST saml2/{key}/sls`

You may disable them by setting `saml2.useRoutes` to `false`.

> `/saml2` prefix can be changed via `saml2.routesPrefix` config parameter.

## Usage

### Authentication events

The simplest way to handle SAML authentication is to add listeners on `Slides\Saml2\SignedIn` and `Slides\Saml2\SignedOut` events.

```php
Event::listen(\Slides\Saml2\Events\SignedIn::class, function (\Slides\Saml2\Events\SignedIn $event) {
    $messageId = $event->getAuth()->getLastMessageId();
    
    // your own code preventing reuse of a $messageId to stop replay attacks
    $samlUser = $event->getSaml2User();
    
    $userData = [
        'id' => $samlUser->getUserId(),
        'attributes' => $samlUser->getAttributes(),
        'assertion' => $samlUser->getRawSamlAssertion()
    ];
    
    $user = // find user by ID or attribute
    
    // Login a user.
    Auth::login($user);
});
```

### Middleware

To define a middleware for default routes, add its name to `config/saml2.php`:

```php
/*
|--------------------------------------------------------------------------
| Built-in routes prefix
|--------------------------------------------------------------------------
|
| Here you may define the prefix for built-in routes.
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
- By logging out of the global SSO Session. In this case the IdP will notify you on `/saml2/{key}/sls` endpoint (already provided).

For the first case, call `Saml2Auth::logout();` or redirect the user to the route `saml.logout` which does just that. 
Do not close the session immediately as you need to receive a response confirmation from the IdP (redirection). 
That response will be handled by the library at `/saml2/sls` and will fire an event for you to complete the operation.

For the second case you will only receive the event. Both cases receive the same event. 

Note that for the second case, you may have to manually save your session to make the logout stick (as the session is saved by middleware, but the [SAML PHP Toolkit] library will redirect back to your IdP before that happens):

```php
Event::listen('Slides\Saml2\Events\SignedOut', function (SignedOut $event) {
    Auth::logout();
    Session::save();
});
```

### SSO-friendly links

Sometimes, you need to create links to your application with support of SSO lifecycle. It means you expect a user to be signed in once you click on that link.

The most popular example is generating links from emails, where you need to make sure when user goes to your application from email, they will be logged in.
To solve this issue, you can use helpers that allow you to create SSO-friendly routes and URLs — `saml_url()` and `saml_route()`.

To generate a link, you need to call one of functions and pass the IdP key as a second parameter, unless your session knows that user was resolved by SSO.

Then, it generates a link like this:
```
https://yourdomain/saml2/default/login?returnTo=https://yourdomain.com/your/actual/link
```

where `default` is the IdP key from the `saml2.php` configuration file. 

Basically, when user clicks on a link, it initiates SSO login process and redirects it back to your needed URL. 

## Examples

### Azure AD

At this point, we assume you have an application on Azure AD that supports Single Sign On.

##### Step 1. Retrieve Identity Provider credentials

![Azure AD](https://i.imgur.com/xKLswxB.png)

You need to retrieve the following parameters:

- Login URL
- Azure AD Identifier
- Logout URL
- Certificate (Base64)

##### Step 2. Configure Identity Provider

Based on information you received in step one, configure your IdP like this:

```shell
cat config/saml2.php
...

    'idps' => [
        // The key will be used as an IdP identifier as well as in routes.
        'azure_testing' => [
            'relay_state_url' => env('SAML2_RELAY_STATE_URL', ''),
            // Place any other IdP related configuration from the 'idp' section
            // in the https://github.com/SAML-Toolkits/php-saml#settings below.
            // Identifier of the IdP entity  (must be a URI).
            'entityId' => 'https://sts.windows.net/fb536a7a-7251-4895-a09a-abd8e614c70b/',
            // SSO endpoint info of the IdP. (Authentication Request protocol)
            'singleSignOnService' => [
                // URL Target of the IdP where the Authentication Request Message will be sent.
                'url' => 'https://login.microsoftonline.com/fb536a7a-7251-4895-a09a-abd8e614c70b/saml2',
            ],
            // SLO endpoint info of the IdP.
            'singleLogoutService' => [
                // URL Location of the IdP where SLO Request will be sent.
                'url' => 'https://login.microsoftonline.com/common/wsfederation?wa=wsignout1.0',
                // URL location of the IdP where SLO Response will be sent (ResponseLocation)
                // if not set, url for the SLO Request will be used.
                'responseUrl' => '',
            ],
            'x509cert' => env('SAML2_IDP_X509', ''),
        ],
    ],
];
printenv SAML2_IDP_X509
MIIC0jCCAbqgAw...
```


##### Step 3. Register your service provider in Identity Provider

Assign parameters to your IdP on the application Single-Sign-On settings page.

![Azure AD](https://i.imgur.com/3hkjFLZ.png)

- Identifier (Entity ID) - `https://yourdomain.com/saml2/azure_testing/metadata` or ID you assigned to your SP in the `saml2.php` 
- Reply URL (Assertion Consumer Service URL) - `https://yourdomain.com/saml2/azure_testing/acs`
- Sign on URL - `https://yourdomain.com/saml2/azure_testing/login`
- Logout URL - `https://yourdomain.com/saml2/azure_testing/logout`


##### Step 4. Make sure your application accessible by Azure AD

Test your application directly from Azure AD and make sure it's accessible worldwide. 

###### Running locally

If you want to test it locally, you may use [ngrok](https://ngrok.com/).

In case if you have a problem with URL creation in your application, you can overwrite host header in your nginx host 
config file by adding the following parameters:

```
fastcgi_param HTTP_HOST your.ngrok.io;
fastcgi_param HTTPS on;
```

> Replace `your.ngrok.io` with your actual ngrok URL 

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

[SAML PHP Toolkit]: https://github.com/SAML-Toolkits/php-saml
