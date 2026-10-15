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
     * Checks that a client keeps the adapter it's built with.
     *
     * @return void
     */
    public function testConstruct(): void
    {
        $adapter = $this->createStub(Adapter::class);
        $client = new Client($adapter);

        $this->assertSame($adapter, (fn () => $this->adapter)->call($client));
    }
}
