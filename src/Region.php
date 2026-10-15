<?php

declare(strict_types=1);

namespace Fusani\RaiderIO;

/**
 * The regions Raider.IO supports, backed by the value the api expects.
 */
enum Region: string
{
    case US = 'us';
    case EU = 'eu';
    case KR = 'kr';
    case TW = 'tw';
    case CN = 'cn';
}
