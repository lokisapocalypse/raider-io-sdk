# Changelog

All notable changes to this package are listed here. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and versions follow [Semantic Versioning](https://semver.org/). [CONTRIBUTING.md](CONTRIBUTING.md) says what counts as a breaking change.

## [Unreleased]

1.0.0 is the first stable release: from here on, breaking changes only come in a new major version. It has breaking changes from 0.0.2, so read the upgrade steps below first.

### Upgrading from 0.x

1. Upgrade to PHP 8.5 or later. The package now needs Guzzle 8.2 or later, so if your project requires Guzzle 6 or 7 directly, raise that too.
2. Pass a `Region` to `getMythicPlusScores()` instead of a string: `getMythicPlusScores('Naowh', 'Tarren Mill', 'eu')` becomes `getMythicPlusScores('Naowh', 'Tarren Mill', Region::EU)`, with `use Fusani\RaiderIO\Region;`. For a region string from user input or config, use `Region::from($region)`, which throws a `ValueError` for an unknown region, or `Region::tryFrom($region)`, which returns `null`.
3. Replace checks of the returned array for `success` and `statusCode` with a `try`/`catch`. A failed request throws a `Fusani\RaiderIO\Exception\RaiderIOException` and never returns an error array. Catch `NotFoundException` for an unknown character or realm and `RateLimitException` for a 429, and read the HTTP status from `$e->getCode()`. A connection failure or timeout, which 0.0.2 let through as a Guzzle exception, is a `RaiderIOException` with a code of 0.
4. Stop using `Fusani\RaiderIO\Client` and `Fusani\RaiderIO\Adapter\AdapterStub`, which are removed. `Client` did nothing; build services with `ServiceFactory`. In tests, mock the `Adapter` interface, or pass `ServiceFactory` a Guzzle client with a `MockHandler`.
5. If you wrote your own `Adapter`: the interface no longer declares a constructor, `post()` or `put()`, so those are up to you. Give `get()` the signature `get(string $path, array $params): array`, and have it throw a `RaiderIOException` on failure instead of returning an error array.
6. Requests now time out after 10 seconds, or 5 seconds to connect. To wait longer, pass `timeout` and `connectTimeout` to `ServiceFactory`; `0` waits forever, as 0.0.2 did.

### Added

- `Region` enum for the US, EU, Korea, Taiwan and China regions.
- Typed exceptions in `Fusani\RaiderIO\Exception`: `RaiderIOException`, and `NotFoundException` and `RateLimitException` extending it. The exception code is the HTTP status, or 0 when no response came back, and the message includes the API's own error message when it sends one.
- `ServiceFactory` takes an optional Guzzle `ClientInterface`, for adding middleware, a proxy or other options, and optional `$timeout` and `$connectTimeout` arguments in seconds.
- `declare(strict_types=1)` and native types throughout, with PHPStan at level max.
- This changelog, a contributing guide with the release process, a README and the MIT license.
- Package metadata on Packagist: the license, a description, keywords, the homepage and the author.

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
