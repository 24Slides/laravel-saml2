# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [2.0.8] - 2020-11-12

### Fixed
- Version require ramsey/uuid and phpunit/phpunit
- Update branch aliases

## [2.0.7] - 2020-10-28

### Added
- Laravel 8 support

## [2.0.6] - 2020-10-23

### Fixed
- Setting Name ID Format on SP bootstrap 

## [2.0.5] - 2020-10-23

### Added
- The ability to customize Name ID Format for different Identity Providers

## [2.0.4] - 2020-10-22

### Added
- Custom Relay State URL per Tenant (to specify a redirection URL after sign in)

## [2.0.3] - 2020-07-01

### Added
- Support for Laravel 7 (#4)
- Add branch-aliases in composer.json

## [2.0.2] - 2020-03-24

### Fixed
- Only log debug messages when debug is enabled in config file (#3)

## [2.0.1] - 2019-10-17

### Added
- Support for Laravel 6 (#1)

### Fixed
- Typos in README.md (#2)

## [2.0.0] - 2019-06-26

### Added
- Completely changed the way of supporting multiple Identity Providers by adding Tenants
- Helper functions `saml_url()`, `saml_route()`, `saml_tenant_uuid()`
- Initializing SP in middleware
- Database migrations
- Console commands `saml2:create-tenant`, `saml2:update-tenant`, `saml2:delete-tenant`, 
`saml2:restore-tenant`, `saml2:list-tenants`, `saml2:tenant-credentials`

### Fixed
- Routes are now accepting UUID of tenants instead of `idpKey`

### Removed
- IdP Resolver, now it resolves by `ResolveTenant` middleware by matching UUID on routes
- Building SSO SP in Laravel ServiceProvider

## [1.2.0] - 2019-06-20

### Added
- Refactored the way of resolving identity provider, now we take it from URL
- Implemented helper saml_idp_key() to retrieve a resolved IdP
- Implemented helpers saml_url(), saml_route() to generate SSO-friendly links (fx. on emails)

### Fixed
- Fixed redirecting to a custom URL on login request using the `returnTo` query parameter

### Removed
- Removed referrer URLs from config parameters

## [1.1.3] - 2019-02-25

### Added
- Implemented keeping resolved IdP (`Saml2::getResolvedIdPKey()`)

## [1.1.2] - 2019-02-17

### Fixed
- Fixed tests
- PHPUnit version

## [1.1.1] - 2019-02-15

### Added
- Added CHANGELOG.md

### Fixed
- Restricted support from Laravel 5.4+
- Restricted support PHP 7.0+
- Updated README.md

## [1.1.0] - 2019-01-28

### Added
- Support of multiple IdPs (Identity Providers)

### Fixed
- Renamed configuration file from `saml2_settings` to `saml2`
- Replaced underscores with dots in routes
- Minor refactoring, formatting

[Unreleased]: https://github.com/24Slides/laravel-saml2/compare/2.0.8...HEAD
[2.0.7]: https://github.com/24Slides/laravel-saml2/compare/2.0.6...2.0.7
[2.0.6]: https://github.com/24Slides/laravel-saml2/compare/2.0.5...2.0.6
[2.0.5]: https://github.com/24Slides/laravel-saml2/compare/2.0.4...2.0.5
[2.0.4]: https://github.com/24Slides/laravel-saml2/compare/2.0.3...2.0.4
[2.0.3]: https://github.com/24Slides/laravel-saml2/compare/2.0.2...2.0.3
[2.0.2]: https://github.com/24Slides/laravel-saml2/compare/2.0.1...2.0.2
[2.0.1]: https://github.com/24Slides/laravel-saml2/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/24Slides/laravel-saml2/compare/1.2.0...2.0.0
[1.2.0]: https://github.com/24Slides/laravel-saml2/compare/1.1.3...1.2.0
[1.1.3]: https://github.com/24Slides/laravel-saml2/compare/1.1.2...1.1.3
[1.1.2]: https://github.com/24Slides/laravel-saml2/compare/1.1.1...1.1.2
[1.1.1]: https://github.com/24Slides/laravel-saml2/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/24Slides/laravel-saml2/compare/1.0.0...1.1.0
