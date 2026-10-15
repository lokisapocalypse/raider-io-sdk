<?php

declare(strict_types=1);

namespace Fusani\RaiderIO\Adapter;

/**
 * This allows us to build several different types of adapters with
 * common methods.
 */
interface Adapter
{
    /**
     * Builds an adapter that makes its requests to the given url.
     *
     * @param string $url the url where the requests will be made
     */
    public function __construct(string $url);

    /**
     * Makes a GET request and returns the decoded response.
     *
     * @param string $path the path where the request will be made
     * @param array<string, string|int|float|bool|null> $params the query string parameters
     * @return array<mixed> the decoded response
     */
    public function get(string $path, array $params): array;

    /**
     * Makes a POST request and returns the decoded response.
     *
     * @param string $path the path where the request will be made
     * @param array<string, string|int|float|bool|null> $params the form parameters
     * @return array<mixed>|false the decoded response, or false if the request failed
     */
    public function post(string $path, array $params): array|false;

    /**
     * Makes a PUT request and returns the decoded response.
     *
     * @param string $path the path where the request will be made
     * @param array<string, string|int|float|bool|null> $params the parameters, sent as a JSON body
     * @return array<mixed>|false the decoded response, or false if the request failed
     */
    public function put(string $path, array $params): array|false;
}
