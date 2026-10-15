<?php

declare(strict_types=1);

namespace Fusani\RaiderIO;

use Fusani\RaiderIO\Adapter\GuzzleAdapter;
use GuzzleHttp\Client;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ServiceFactory::class)]
#[UsesClass(Character::class)]
#[UsesClass(GuzzleAdapter::class)]
class ServiceFactoryTest extends TestCase
{
    /**
     * Checks that the factory builds a character service whose Guzzle adapter calls the v1 api by default.
     *
     * @return void
     */
    public function testCreateCharacterService(): void
    {
        $this->assertSame('https://raider.io/api/v1', $this->baseUrlOf(new ServiceFactory()));
    }

    /**
     * Checks that the factory builds a character service for the api version it's given.
     *
     * @return void
     */
    public function testCreateCharacterServiceForVersion(): void
    {
        $this->assertSame('https://raider.io/api/v2', $this->baseUrlOf(new ServiceFactory('v2')));
    }

    /**
     * Checks that the factory gives the adapter the default timeouts and its own client when none are passed.
     *
     * @return void
     */
    public function testCreateCharacterServiceDefaults(): void
    {
        $adapter = $this->adapterOf(new ServiceFactory());

        $this->assertInstanceOf(Client::class, (fn () => $this->client)->call($adapter));
        $this->assertSame(10.0, (fn () => $this->timeout)->call($adapter));
        $this->assertSame(5.0, (fn () => $this->connectTimeout)->call($adapter));
    }

    /**
     * Checks that the factory passes the client and timeouts it's given to the adapter.
     *
     * @return void
     */
    public function testCreateCharacterServiceWithClientAndTimeouts(): void
    {
        $client = new Client();

        $adapter = $this->adapterOf(new ServiceFactory('v1', $client, 30.0, 2.5));

        $this->assertSame($client, (fn () => $this->client)->call($adapter));
        $this->assertSame(30.0, (fn () => $this->timeout)->call($adapter));
        $this->assertSame(2.5, (fn () => $this->connectTimeout)->call($adapter));
    }

    /**
     * Builds a character service with the factory and returns its adapter's base url.
     *
     * @param ServiceFactory $factory the factory to build the service with
     * @return string the base url of the service's adapter
     */
    protected function baseUrlOf(ServiceFactory $factory): string
    {
        return $this->adapterOf($factory)->getBaseUrl();
    }

    /**
     * Builds a character service with the factory and returns its Guzzle adapter.
     *
     * @param ServiceFactory $factory the factory to build the service with
     * @return GuzzleAdapter the service's adapter
     */
    protected function adapterOf(ServiceFactory $factory): GuzzleAdapter
    {
        $adapter = (fn () => $this->adapter)->call($factory->createCharacterService());
        $this->assertInstanceOf(GuzzleAdapter::class, $adapter);

        return $adapter;
    }
}
