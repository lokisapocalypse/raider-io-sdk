<?php

declare(strict_types=1);

namespace Fusani\RaiderIO;

use Fusani\RaiderIO\Adapter\GuzzleAdapter;
use GuzzleHttp\ClientInterface;
use InvalidArgumentException;

/**
 * Builds the api services with the adapters they need.
 */
class ServiceFactory
{
    protected string $url;

    protected ?ClientInterface $client;

    protected float $timeout;

    protected float $connectTimeout;

    /**
     * Builds a factory for the given version of the api.
     *
     * @param string $version which version of the api to use
     * @param ClientInterface|null $client the Guzzle client to send requests with, e.g. one with middleware or a
     *     proxy, or null for a default one
     * @param float $timeout the seconds to wait for a whole request, or 0 to wait forever
     * @param float $connectTimeout the seconds to wait to connect, or 0 to wait forever
     */
    public function __construct(
        string $version = 'v1',
        ?ClientInterface $client = null,
        float $timeout = GuzzleAdapter::DEFAULT_TIMEOUT,
        float $connectTimeout = GuzzleAdapter::DEFAULT_CONNECT_TIMEOUT
    ) {
        $this->url = 'https://raider.io/api/' . $version;
        $this->client = $client;
        $this->timeout = $timeout;
        $this->connectTimeout = $connectTimeout;
    }

    /**
     * Creates the character service, with a Guzzle adapter for the api.
     *
     * @return Character the character service
     * @throws InvalidArgumentException if a timeout is negative
     */
    public function createCharacterService(): Character
    {
        $adapter = new GuzzleAdapter($this->url, $this->client, $this->timeout, $this->connectTimeout);
        return new Character($adapter);
    }
}
