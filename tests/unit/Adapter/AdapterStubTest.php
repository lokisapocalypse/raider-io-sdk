<?php

declare(strict_types=1);

namespace Fusani\RaiderIO\Adapter;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AdapterStub::class)]
class AdapterStubTest extends TestCase
{
    /** @var AdapterStub */
    protected AdapterStub $adapter;

    /**
     * Builds a stub with no responses queued.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->adapter = new AdapterStub('https://raider.io/api/v1');
    }

    /**
     * Checks that a stub with no responses queued returns an empty array.
     *
     * @return void
     */
    public function testNoResponses(): void
    {
        $this->assertSame([], $this->adapter->get('/path', []));
    }

    /**
     * Checks that every request method returns the queued responses in order.
     *
     * @return void
     */
    public function testReturnsResponsesInOrder(): void
    {
        foreach (['call', 'get', 'post', 'put'] as $response) {
            $this->adapter->addResponse([$response]);
        }

        $this->assertSame(['call'], $this->adapter->call('service', 'method', []));
        $this->assertSame(['get'], $this->adapter->get('/path', []));
        $this->assertSame(['post'], $this->adapter->post('/path', []));
        $this->assertSame(['put'], $this->adapter->put('/path', []));
        $this->assertSame([], $this->adapter->get('/path', []));
    }
}
