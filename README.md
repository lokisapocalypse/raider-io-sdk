# Raider.IO SDK for PHP

[![CI](https://github.com/lokisapocalypse/raider-io-sdk/actions/workflows/ci.yml/badge.svg?branch=development)](https://github.com/lokisapocalypse/raider-io-sdk/actions/workflows/ci.yml)
[![Packagist](https://img.shields.io/packagist/v/fusani/raider-io)](https://packagist.org/packages/fusani/raider-io)
[![License](https://img.shields.io/packagist/l/fusani/raider-io)](LICENSE)

A small PHP client for the [Raider.IO API](https://raider.io/api), which has World of Warcraft Mythic+ and raid
data for characters and guilds.

## Requirements

- PHP 8.5 or later
- Guzzle 8.2 or later (installed with the package)

## Installation

```bash
composer require fusani/raider-io
```

## Usage

`ServiceFactory` builds the services, with a Guzzle adapter pointed at the API:

```php
use Fusani\RaiderIO\Region;
use Fusani\RaiderIO\ServiceFactory;

$characters = (new ServiceFactory())->createCharacterService();

$profile = $characters->getMythicPlusScores('Naowh', 'Tarren Mill', Region::EU);

echo $profile['name'];                               // Naowh
echo count($profile['mythic_plus_best_runs']);       // the character's best run in each dungeon
```

Responses come back as the API's JSON, decoded into arrays. See the
[Raider.IO API documentation](https://raider.io/api) for their fields.

### Services and methods

| Service     | Method                                                          | Endpoint              |
|-------------|-----------------------------------------------------------------|-----------------------|
| `Character` | `getMythicPlusScores(string $character, string $realm, Region $region): array` | `/characters/profile` |

`getMythicPlusScores()` returns the character's profile with its Mythic+ best runs and alternate runs.

`Region` has a case for each region Raider.IO supports: `US`, `EU`, `KR`, `TW` and `CN`. A realm can be its name
(`Tarren Mill`) or its slug (`tarren-mill`).

### Options

`ServiceFactory` takes optional arguments:

```php
use Fusani\RaiderIO\ServiceFactory;
use GuzzleHttp\Client;

$factory = new ServiceFactory(
    version: 'v1',
    client: new Client(['proxy' => 'tcp://localhost:8125']),
    timeout: 10.0,
    connectTimeout: 5.0,
);
```

| Argument         | Default             | What it does                                                         |
|------------------|---------------------|----------------------------------------------------------------------|
| `version`        | `'v1'`              | The API version.                                                     |
| `client`         | a new Guzzle client | The Guzzle client to send requests with, e.g. one with middleware or a proxy. |
| `timeout`        | `10.0`              | Seconds to wait for a whole request, or `0` to wait forever.         |
| `connectTimeout` | `5.0`               | Seconds to wait to connect, or `0` to wait forever.                  |

The timeouts are sent with every request, so they apply to a client you pass in as well.

## Error handling

Every failed request throws a `Fusani\RaiderIO\Exception\RaiderIOException`. Its code is the HTTP status, or `0` when
no response came back (a connection failure or timeout), and its message includes the API's own error message when
there is one. Two subclasses cover the errors you'd usually handle on their own:

- `NotFoundException` when the character or realm doesn't exist;
- `RateLimitException` when Raider.IO responds with 429 Too Many Requests.

```php
use Fusani\RaiderIO\Exception\NotFoundException;
use Fusani\RaiderIO\Exception\RaiderIOException;
use Fusani\RaiderIO\Exception\RateLimitException;

try {
    $profile = $characters->getMythicPlusScores('Naowh', 'Tarren Mill', Region::EU);
} catch (NotFoundException $e) {
    // no such character or realm
} catch (RateLimitException $e) {
    // slow down and try again later
} catch (RaiderIOException $e) {
    // anything else: a 5xx, a timeout, a connection failure
}
```

## Running the tests

```bash
composer install
composer test         # the unit tests, with no network access
composer test:live    # calls the real Raider.IO API
```

## Upgrading from 0.x

1.0 has breaking changes: it needs PHP 8.5, takes a `Region` enum instead of a string, and throws exceptions instead
of returning error arrays. See [the changelog](CHANGELOG.md) for the full list and how to upgrade.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for the development setup, the coding standard and the release process.

## License

MIT. See [LICENSE](LICENSE).
