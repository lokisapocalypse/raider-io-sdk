<?php

declare(strict_types=1);

namespace Fusani\RaiderIO;

use Fusani\RaiderIO\Adapter\GuzzleAdapter;

/**
 * Builds the api services with the adapters they need.
 */
class ServiceFactory
{
    protected string $url;

    /**
     * Builds a factory for the given version of the api.
     *
     * @param string $version which version of the api to use
     */
    public function __construct(string $version = 'v1')
    {
        $this->url = 'https://raider.io/api/' . $version;
    }

    /**
     * Creates the character service, with a Guzzle adapter for the api.
     *
     * @return Character the character service
     */
    public function createCharacterService(): Character
    {
        $adapter = new GuzzleAdapter($this->url);
        return new Character($adapter);
    }
}
