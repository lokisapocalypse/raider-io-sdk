<?php

declare(strict_types=1);

namespace Fusani\RaiderIO;

use Fusani\RaiderIO\Adapter\Adapter;
use Fusani\RaiderIO\Exception\NotFoundException;
use Fusani\RaiderIO\Exception\RaiderIOException;

/**
 * Service for the Raider.IO character endpoints.
 */
class Character
{
    // a way to connect to the api
    protected Adapter $adapter;

    /**
     * Builds the service with the adapter it uses to call the api.
     *
     * @param Adapter $adapter an adapter for interfacing with the api
     */
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Gets the Mythic+ best and alternate runs from a character's Raider.IO profile.
     *
     * @param string $character the character whose information we need
     * @param string $realm the realm the character lives on
     * @param Region $region the region the realm lives on
     * @return array<mixed> the character profile with its Mythic+ runs
     * @throws NotFoundException if the api can't find the character or realm
     * @throws RaiderIOException if the request fails or the api responds with another error
     */
    public function getMythicPlusScores(string $character, string $realm, Region $region): array
    {
        $params = [
            'region' => $region->value,
            'realm' => $realm,
            'name' => $character,
            'fields' => 'mythic_plus_best_runs:all,mythic_plus_alternate_runs:all',
        ];

        return $this->adapter->get(
            '/characters/profile',
            $params
        );
    }
}
