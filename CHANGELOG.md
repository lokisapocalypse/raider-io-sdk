# Changelog

All notable changes to this package are listed here. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and versions follow [Semantic Versioning](https://semver.org/). [CONTRIBUTING.md](CONTRIBUTING.md) says what counts as a breaking change.

## [Unreleased]

### Added

- `Region` enum for the US, EU, Korea, Taiwan and China regions.
- Typed exceptions in `Fusani\RaiderIO\Exception`: `RaiderIOException`, and `NotFoundException` and `RateLimitException` extending it. The exception code is the HTTP status, or 0 when no response came back, and the message includes the API's own error message when it sends one.
- `ServiceFactory` takes an optional Guzzle `ClientInterface`, for adding middleware, a proxy or other options, and optional `$timeout` and `$connectTimeout` arguments in seconds.
- `declare(strict_types=1)` and native types throughout, with PHPStan at level max.
- This changelog and a contributing guide with the release process.

### Changed

- **Breaking:** requires PHP `^8.5` and Guzzle `^8.2` (0.0.2 allowed PHP 7.0 and used Guzzle 6).
- **Breaking:** `Character::getMythicPlusScores()` takes a `Region` instead of a string: `$characters->getMythicPlusScores('Naowh', 'Tarren Mill', Region::EU)`.
- **Breaking:** errors throw a `RaiderIOException` instead of returning `false` or an array with `success` and `statusCode`. An unknown character or realm throws a `NotFoundException`, and a 429 throws a `RateLimitException`.
- **Breaking:** the `Adapter` interface has no constructor and only a `get()` method.

### Removed

- **Breaking:** `Client` and `Adapter\AdapterStub`.
- **Breaking:** `Adapter::post()` and `Adapter::put()`. The Raider.IO API is read-only.

### Fixed

- Requests time out after 10 seconds (5 seconds to connect) instead of waiting forever when Raider.IO stalls.
- An adapter no longer rewrites its base url for a region, so later requests aren't sent to the wrong host.

## [0.0.2] - 2021-08-04

Earlier versions have no changelog.

[Unreleased]: https://github.com/lokisapocalypse/raider-io-sdk/compare/0.0.2...development
[0.0.2]: https://github.com/lokisapocalypse/raider-io-sdk/releases/tag/0.0.2
