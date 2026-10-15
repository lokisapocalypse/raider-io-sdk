<?php

declare(strict_types=1);

namespace Fusani\RaiderIO;

use Fusani\RaiderIO\Adapter\Adapter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Client::class)]
class ClientTest extends TestCase
{
    /**
     * Checks that a client can be built with an adapter.
     *
     * @return void
     */
    public function testConstruct(): void
    {
        $this->assertInstanceOf(Client::class, new Client($this->createStub(Adapter::class)));
    }
}
