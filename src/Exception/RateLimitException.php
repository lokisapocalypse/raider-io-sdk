<?php

declare(strict_types=1);

namespace Fusani\RaiderIO\Exception;

/**
 * Thrown when the api responds with 429 Too Many Requests.
 */
class RateLimitException extends RaiderIOException
{
}
