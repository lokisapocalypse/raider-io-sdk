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
     * Checks that the factory builds a character service.
     *
     * @return void
     */
    public function testCreateCharacterService(): void
    {
        $factory = new ServiceFactory();

        $this->assertInstanceOf(Character::class, $factory->createCharacterService());
    }
}
