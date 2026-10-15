<?php

declare(strict_types=1);

namespace Fusani\RaiderIO\Adapter;

use GuzzleHttp\Client;
use GuzzleHttp\Exception;
use InvalidArgumentException;

/**
 * Adapter that makes its requests with Guzzle.
 */
class GuzzleAdapter implements Adapter
{
    protected string $baseUrl;

    protected Client $client;

    /**
     * Builds an adapter whose base url is the scheme, host and path of the given url.
     *
     * @param string $url the url where the requests will be made
     * @throws InvalidArgumentException if the url has no scheme or host
     */
    public function __construct(string $url)
    {
        // break the url down
        $parts = parse_url($url);

        if ($parts === false || !isset($parts['scheme'], $parts['host'])) {
            throw new InvalidArgumentException(sprintf('"%s" is not an absolute url.', $url));
        }

        // build the base url
        $baseUrl = sprintf(
            '%s://%s%s',
            $parts['scheme'],
            $parts['host'],
            $parts['path'] ?? ''
        );

        $this->baseUrl = $baseUrl;

        // build the guzzle client
        $this->client = new Client();
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
     * An error response is returned as an array with `success`, `statusCode` and `message`.
     *
     * @param string $path the path where the request will be made
     * @param array<string, string|int|float|bool|null> $params the query string parameters
     * @return array<mixed> the decoded response, or the error details
     * @throws \GuzzleHttp\Exception\GuzzleException if the request fails without a response
     */
    public function get(string $path, array $params): array
    {
        $url = $path . '?' . http_build_query($params);

        // if a region is set, override the base url
        if (isset($params['region']) && is_string($params['region']) && $params['region'] !== '') {
            $this->baseUrl = str_replace('us.', $params['region'] . '.', $this->baseUrl);
        }

        try {
            $response = $this->client->get($this->baseUrl . $url);
            return $this->decode($response->getBody()->getContents());
        } catch (Exception\BadResponseException $e) {
            // Guzzle throws exceptions on non-200 series HTTP codes.
            // BadResponseException means authentication failed,
            // other exceptions probably means that the request is bad
            $response = $e->getResponse();

            return [
                'success' => false,
                'statusCode' => $response->getStatusCode(),
                'message' => $response->getReasonPhrase(),
            ];
        }
    }

    /**
     * {@inheritDoc}
     *
     * @param string $path the path where the request will be made
     * @param array<string, string|int|float|bool|null> $params the form parameters
     * @return array<mixed>|false the decoded response, or false if the request failed
     * @throws \GuzzleHttp\Exception\GuzzleException if the request fails without a response
     */
    public function post(string $path, array $params): array|false
    {
        try {
            $response = $this->client->post($this->baseUrl . $path, ['form_params' => $params]);
            $results = $this->decode($response->getBody()->getContents());
        } catch (Exception\BadResponseException $e) {
            // Guzzle throws exceptions on non-200 series HTTP codes.
            // BadResponseException means authentication failed,
            // other exceptions probably means that the request is bad
            return false;
        }

        return $results;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $path the path where the request will be made
     * @param array<string, string|int|float|bool|null> $params the parameters, sent as a JSON body
     * @return array<mixed>|false the decoded response, or false if the request failed
     * @throws \GuzzleHttp\Exception\GuzzleException if the request fails without a response
     * @throws \JsonException if the parameters can't be encoded as JSON
     */
    public function put(string $path, array $params): array|false
    {
        try {
            $response = $this->client->put(
                $this->baseUrl . $path,
                [
                    'headers' => ['Content-Type' => 'application/json'],
                    'body' => json_encode($params, JSON_THROW_ON_ERROR),
                ]
            );
            $results = $this->decode($response->getBody()->getContents());
        } catch (Exception\BadResponseException $e) {
            // Guzzle throws exceptions on non-200 series HTTP codes.
            // BadResponseException means authentication failed,
            // other exceptions probably means that the request is bad
            return false;
        }

        return $results;
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
