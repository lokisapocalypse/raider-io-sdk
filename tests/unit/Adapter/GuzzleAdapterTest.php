<?php

declare(strict_types=1);

namespace Fusani\RaiderIO\Adapter;

use Fusani\RaiderIO\Exception\NotFoundException;
use Fusani\RaiderIO\Exception\RaiderIOException;
use Fusani\RaiderIO\Exception\RateLimitException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Throwable;

#[CoversClass(GuzzleAdapter::class)]
#[UsesClass(RaiderIOException::class)]
class GuzzleAdapterTest extends TestCase
{
    /** @var list<RequestInterface> */
    protected array $requests = [];

    protected MockHandler $handler;

    /**
     * Builds an adapter whose Guzzle client returns the given responses instead of calling the network.
     *
     * @param list<Response|Throwable> $responses the responses to return, or exceptions to throw, in order
     * @param string $url the url where the requests will be made
     * @param float $timeout the seconds to wait for a whole request
     * @param float $connectTimeout the seconds to wait to connect
     * @return GuzzleAdapter
     */
    protected function adapterReturning(
        array $responses,
        string $url = 'https://raider.io/api/v1',
        float $timeout = GuzzleAdapter::DEFAULT_TIMEOUT,
        float $connectTimeout = GuzzleAdapter::DEFAULT_CONNECT_TIMEOUT
    ): GuzzleAdapter {
        $this->handler = new MockHandler($responses);

        $stack = HandlerStack::create($this->handler);
        $stack->push(Middleware::mapRequest(function (RequestInterface $request): RequestInterface {
            $this->requests[] = $request;
            return $request;
        }));

        return new GuzzleAdapter($url, new Client(['handler' => $stack]), $timeout, $connectTimeout);
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
     * Checks that a url without a scheme or host is rejected.
     *
     * @return void
     */
    public function testConstructRejectsRelativeUrl(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GuzzleAdapter('/api/v1');
    }

    /**
     * Checks that a negative timeout is rejected.
     *
     * @return void
     */
    public function testConstructRejectsNegativeTimeout(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GuzzleAdapter('https://raider.io/api/v1', null, -1.0);
    }

    /**
     * Checks that a negative connect timeout is rejected.
     *
     * @return void
     */
    public function testConstructRejectsNegativeConnectTimeout(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GuzzleAdapter('https://raider.io/api/v1', null, 10.0, -1.0);
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
     * Checks that a GET request goes through the client passed in, to the base url plus the path, with the params
     * as the query string, and returns the decoded JSON body.
     *
     * @return void
     */
    public function testGetSuccess(): void
    {
        $adapter = $this->adapterReturning([new Response(200, [], '{"name":"Thrall"}')]);

        $result = $adapter->get('/characters/profile', ['region' => 'us', 'name' => 'Thrall']);

        $this->assertSame(['name' => 'Thrall'], $result);
        $this->assertSame('GET', $this->requests[0]->getMethod());
        $this->assertSame(
            'https://raider.io/api/v1/characters/profile?region=us&name=Thrall',
            (string) $this->requests[0]->getUri()
        );
    }

    /**
     * Checks that the region param is only sent in the query string, and doesn't change the base url between
     * requests.
     *
     * @return void
     */
    public function testGetRegionDoesNotChangeBaseUrl(): void
    {
        $adapter = $this->adapterReturning(
            [new Response(200, [], '{}'), new Response(200, [], '{}')],
            'https://us.example.com/api'
        );

        $adapter->get('/characters/profile', ['region' => 'eu']);
        $adapter->get('/characters/profile', ['region' => 'kr']);

        $this->assertSame('https://us.example.com/api', $adapter->getBaseUrl());
        $this->assertSame(
            'https://us.example.com/api/characters/profile?region=eu',
            (string) $this->requests[0]->getUri()
        );
        $this->assertSame(
            'https://us.example.com/api/characters/profile?region=kr',
            (string) $this->requests[1]->getUri()
        );
    }

    /**
     * Checks that requests are sent with the default timeouts.
     *
     * @return void
     */
    public function testRequestsUseDefaultTimeouts(): void
    {
        $adapter = $this->adapterReturning([new Response(200, [], '{}')]);

        $adapter->get('/characters/profile', []);

        $options = $this->handler->getLastOptions();
        $this->assertSame(10.0, $options['timeout']);
        $this->assertSame(5.0, $options['connect_timeout']);
    }

    /**
     * Checks that requests are sent with the timeouts the adapter is built with.
     *
     * @return void
     */
    public function testRequestsUseGivenTimeouts(): void
    {
        $adapter = $this->adapterReturning([new Response(200, [], '{}')], 'https://raider.io/api/v1', 30.0, 2.5);

        $adapter->post('/test', []);

        $options = $this->handler->getLastOptions();
        $this->assertSame(30.0, $options['timeout']);
        $this->assertSame(2.5, $options['connect_timeout']);
    }

    /**
     * Checks that a 404 response throws a NotFoundException with the status as its code and the api's message.
     *
     * @return void
     */
    public function testGetNotFound(): void
    {
        $adapter = $this->adapterReturning([
            new Response(404, [], '{"statusCode":404,"error":"Not Found","message":"Could not find character"}'),
        ]);

        try {
            $adapter->get('/characters/profile', []);
            $this->fail('Expected a NotFoundException.');
        } catch (NotFoundException $e) {
            $this->assertSame(404, $e->getCode());
            $this->assertSame('Raider.IO responded with 404 Not Found: Could not find character', $e->getMessage());
            $this->assertNotNull($e->getPrevious());
        }
    }

    /**
     * Checks that a 429 response throws a RateLimitException.
     *
     * @return void
     */
    public function testGetRateLimited(): void
    {
        $adapter = $this->adapterReturning([new Response(429)]);

        $this->expectException(RateLimitException::class);
        $this->expectExceptionCode(429);

        $adapter->get('/characters/profile', []);
    }

    /**
     * Checks that a connection failure throws a RaiderIOException with a code of 0.
     *
     * @return void
     */
    public function testGetConnectionFailure(): void
    {
        $adapter = $this->adapterReturning([
            new ConnectException('Connection refused', new Request('GET', 'https://raider.io/api/v1')),
        ]);

        try {
            $adapter->get('/characters/profile', []);
            $this->fail('Expected a RaiderIOException.');
        } catch (RaiderIOException $e) {
            $this->assertSame(RaiderIOException::class, $e::class);
            $this->assertSame(0, $e->getCode());
            $this->assertSame('Raider.IO request failed: Connection refused', $e->getMessage());
            $this->assertInstanceOf(ConnectException::class, $e->getPrevious());
        }
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
        $this->assertSame('POST', $this->requests[0]->getMethod());
        $this->assertSame('https://raider.io/api/v1/test', (string) $this->requests[0]->getUri());
        $this->assertSame('a=b', (string) $this->requests[0]->getBody());
    }

    /**
     * Checks that an error response from a POST request throws a RaiderIOException with the status as its code.
     *
     * @return void
     */
    public function testPostFailure(): void
    {
        $adapter = $this->adapterReturning([new Response(500)]);

        $this->expectException(RaiderIOException::class);
        $this->expectExceptionCode(500);

        $adapter->post('/test', []);
    }

    /**
     * Checks that a PUT request goes to the base url plus the path, sends the params as a JSON body and returns
     * the decoded JSON body.
     *
     * @return void
     */
    public function testPutSuccess(): void
    {
        $adapter = $this->adapterReturning([new Response(200, [], '{"ok":true}')]);

        $this->assertSame(['ok' => true], $adapter->put('/test', ['a' => 'b']));
        $this->assertSame('PUT', $this->requests[0]->getMethod());
        $this->assertSame('https://raider.io/api/v1/test', (string) $this->requests[0]->getUri());
        $this->assertSame('application/json', $this->requests[0]->getHeaderLine('Content-Type'));
        $this->assertSame('{"a":"b"}', (string) $this->requests[0]->getBody());
    }

    /**
     * Checks that an error response from a PUT request throws a RaiderIOException with the status as its code.
     *
     * @return void
     */
    public function testPutFailure(): void
    {
        $adapter = $this->adapterReturning([new Response(500)]);

        $this->expectException(RaiderIOException::class);
        $this->expectExceptionCode(500);

        $adapter->put('/test', []);
    }

    /**
     * Checks that a response body that isn't a JSON object or array is returned as an empty array.
     *
     * @return void
     */
    public function testGetNonJsonBody(): void
    {
        $adapter = $this->adapterReturning([new Response(200, [], 'not json')]);

        $this->assertSame([], $adapter->get('/characters/profile', []));
    }
}
