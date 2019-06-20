# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

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

[Unreleased]: https://github.com/24Slides/laravel-saml2/compare/1.1.1...HEAD
[1.1.3]: https://github.com/24Slides/laravel-saml2/compare/1.1.2...1.1.3
[1.1.2]: https://github.com/24Slides/laravel-saml2/compare/1.1.1...1.1.2
[1.1.1]: https://github.com/24Slides/laravel-saml2/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/24Slides/laravel-saml2/compare/1.0.0...1.1.0