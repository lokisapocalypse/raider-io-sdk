<?php

namespace Fusani\RaiderIO;

use Fusani\RaiderIO\Adapter;

class ServiceFactory
{
    /** @var string */
    protected $url;

    /**
     * @param string $version : which version of the api to use
     */
    public function __construct(string $version = 'v1')
    {
        $this->url = 'https://raider.io/api/' . $version;
    }

    /**
     * This method creates the character service and sets up the guzzle adapter
     * needed for it.
     *
     * @return Character : the character service
     */
    public function createCharacterService() : Character
    {
        $adapter = new Adapter\GuzzleAdapter($this->url);
        return new Character($adapter);
    }
}
