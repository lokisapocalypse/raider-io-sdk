<?php

declare(strict_types=1);

namespace Fusani\RaiderIO;

use Fusani\RaiderIO\Adapter\Adapter;

/**
 * Holds the adapter used to call the api.
 */
class Client
{
    // a way to connect to the api
    protected Adapter $adapter;

    /**
     * Builds the client with the adapter it uses to call the api.
     *
     * @param Adapter $adapter an adapter for interfacing with the api
     */
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }
}
