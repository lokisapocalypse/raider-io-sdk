<?php

declare(strict_types=1);

namespace Fusani\RaiderIO\Exception;

/**
 * Thrown when the api can't find what was asked for: a 404, or a 400 saying a character or realm wasn't found.
 */
class NotFoundException extends RaiderIOException
{
}
