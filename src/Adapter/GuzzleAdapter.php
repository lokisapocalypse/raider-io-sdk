<?php

declare(strict_types=1);

namespace Fusani\RaiderIO\Adapter;

use Fusani\RaiderIO\Exception\RaiderIOException;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;

/**
 * Adapter that makes its requests with Guzzle.
 */
class GuzzleAdapter implements Adapter
{
    public const DEFAULT_TIMEOUT = 10.0;

    public const DEFAULT_CONNECT_TIMEOUT = 5.0;

    protected string $baseUrl;

    protected ClientInterface $client;

    protected float $timeout;

    protected float $connectTimeout;

    /**
     * Builds an adapter whose base url is the scheme, host and path of the given url.
     *
     * The timeouts are sent with every request, so they apply to a client passed in as well.
     *
     * @param string $url the url where the requests will be made
     * @param ClientInterface|null $client the Guzzle client to send requests with, or null for a default one
     * @param float $timeout the seconds to wait for a whole request, or 0 to wait forever
     * @param float $connectTimeout the seconds to wait to connect, or 0 to wait forever
     * @throws InvalidArgumentException if the url has no scheme or host, or a timeout is negative
     */
    public function __construct(
        string $url,
        ?ClientInterface $client = null,
        float $timeout = self::DEFAULT_TIMEOUT,
        float $connectTimeout = self::DEFAULT_CONNECT_TIMEOUT
    ) {
        // break the url down
        $parts = parse_url($url);

        if ($parts === false || !isset($parts['scheme'], $parts['host'])) {
            throw new InvalidArgumentException(sprintf('"%s" is not an absolute url.', $url));
        }

        if ($timeout < 0 || $connectTimeout < 0) {
            throw new InvalidArgumentException('Timeouts can\'t be negative.');
        }

        // build the base url
        $this->baseUrl = sprintf(
            '%s://%s%s',
            $parts['scheme'],
            $parts['host'],
            $parts['path'] ?? ''
        );

        $this->client = $client ?? new Client();
        $this->timeout = $timeout;
        $this->connectTimeout = $connectTimeout;
    }

    /**
     * Returns the url that request paths are appended to.
     *
     * @return string the base url
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Changes the url that request paths are appended to.
     *
     * @param string $url the new base url
     * @return self this adapter
     */
    public function changeBaseUrl(string $url): self
    {
        $this->baseUrl = $url;
        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $path the path where the request will be made
     * @param array<string, string|int|float|bool|null> $params the query string parameters
     * @return array<mixed> the decoded response
     * @throws RaiderIOException if the request fails or the api responds with an error
     */
    public function get(string $path, array $params): array
    {
        return $this->request('GET', $path . '?' . http_build_query($params));
    }

    /**
     * Sends a request to the base url plus the path, with the timeouts, and decodes the response.
     *
     * @param string $method the HTTP method
     * @param string $path the path, and query string if any, appended to the base url
     * @return array<mixed> the decoded response
     * @throws RaiderIOException if the request fails or the api responds with an error
     */
    protected function request(string $method, string $path): array
    {
        $options = [
            'timeout' => $this->timeout,
            'connect_timeout' => $this->connectTimeout,
        ];

        try {
            $response = $this->client->request($method, $this->baseUrl . $path, $options);
        } catch (BadResponseException $e) {
            // Guzzle throws on 4xx and 5xx responses
            throw RaiderIOException::fromResponse($e->getResponse(), $e);
        } catch (GuzzleException $e) {
            // no usable response: connection failures, timeouts, too many redirects
            throw RaiderIOException::fromTransferFailure($e);
        }

        return $this->decode((string) $response->getBody());
    }

    /**
     * Decodes a JSON response body into an array.
     *
     * @param string $body the response body
     * @return array<mixed> the decoded body, or an empty array if it isn't a JSON object or array
     */
    protected function decode(string $body): array
    {
        $decoded = json_decode($body, true);

        return is_array($decoded) ? $decoded : [];
    }
}
