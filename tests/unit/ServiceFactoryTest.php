<?php

declare(strict_types=1);

namespace Fusani\RaiderIO;

use Fusani\RaiderIO\Adapter\GuzzleAdapter;
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
     * Builds a character service with the factory and returns its adapter's base url.
     *
     * @param ServiceFactory $factory the factory to build the service with
     * @return string the base url of the service's adapter
     */
    protected function baseUrlOf(ServiceFactory $factory): string
    {
        $adapter = (fn () => $this->adapter)->call($factory->createCharacterService());
        $this->assertInstanceOf(GuzzleAdapter::class, $adapter);

        return $adapter->getBaseUrl();
    }
}
