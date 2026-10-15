<?php

declare(strict_types=1);

namespace Fusani\RaiderIO\Adapter;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

#[CoversClass(GuzzleAdapter::class)]
class GuzzleAdapterTest extends TestCase
{
    /** @var list<array{request: RequestInterface}> */
    protected array $history = [];

    /**
     * Builds an adapter whose Guzzle client returns the given responses instead of calling the network.
     *
     * @param list<Response> $responses the responses to return, in order
     * @return GuzzleAdapter
     */
    protected function adapterReturning(array $responses): GuzzleAdapter
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($this->history));

        $adapter = new GuzzleAdapter('https://raider.io/api/v1');
        (fn () => $this->client = new Client(['handler' => $stack]))->call($adapter);

        return $adapter;
    }

    /**
     * Checks that the base url keeps the scheme, host and path but drops the query string.
     *
     * @return void
     */
    public function testConstructBuildsBaseUrl(): void
    {
        $adapter = new GuzzleAdapter('https://raider.io/api/v1?ignored=1');

        $this->assertSame('https://raider.io/api/v1', $adapter->getBaseUrl());
    }

    /**
     * Checks that a url without a path gives a base url without one.
     *
     * @return void
     */
    public function testConstructWithoutPath(): void
    {
        $adapter = new GuzzleAdapter('https://raider.io');

        $this->assertSame('https://raider.io', $adapter->getBaseUrl());
    }

    /**
     * Checks that the base url can be changed after the adapter is built.
     *
     * @return void
     */
    public function testChangeBaseUrl(): void
    {
        $adapter = new GuzzleAdapter('https://raider.io/api/v1');

        $this->assertSame($adapter, $adapter->changeBaseUrl('https://example.com/api'));
        $this->assertSame('https://example.com/api', $adapter->getBaseUrl());
    }

    /**
     * Checks that a GET request goes to the base url plus the path, with the params as the query string, and
     * returns the decoded JSON body.
     *
     * @return void
     */
    public function testGetSuccess(): void
    {
        $adapter = $this->adapterReturning([new Response(200, [], '{"name":"Thrall"}')]);

        $result = $adapter->get('/characters/profile', ['region' => 'us', 'name' => 'Thrall']);

        $this->assertSame(['name' => 'Thrall'], $result);
        $this->assertSame('GET', $this->history[0]['request']->getMethod());
        $this->assertSame(
            'https://raider.io/api/v1/characters/profile?region=us&name=Thrall',
            (string) $this->history[0]['request']->getUri()
        );
    }

    /**
     * Checks that an error response from a GET request is returned as an array with the status and reason.
     *
     * @return void
     */
    public function testGetFailure(): void
    {
        $adapter = $this->adapterReturning([new Response(404)]);

        $this->assertSame(
            ['success' => false, 'statusCode' => 404, 'message' => 'Not Found'],
            $adapter->get('/characters/profile', [])
        );
    }

    /**
     * Checks that a POST request sends the params as a form body and returns the decoded JSON body.
     *
     * @return void
     */
    public function testPostSuccess(): void
    {
        $adapter = $this->adapterReturning([new Response(200, [], '{"ok":true}')]);

        $this->assertSame(['ok' => true], $adapter->post('/test', ['a' => 'b']));
        $this->assertSame('POST', $this->history[0]['request']->getMethod());
        $this->assertSame('https://raider.io/api/v1/test', (string) $this->history[0]['request']->getUri());
        $this->assertSame('a=b', (string) $this->history[0]['request']->getBody());
    }

    /**
     * Checks that an error response from a POST request returns false.
     *
     * @return void
     */
    public function testPostFailure(): void
    {
        $adapter = $this->adapterReturning([new Response(500)]);

        $this->assertFalse($adapter->post('/test', []));
    }
}
