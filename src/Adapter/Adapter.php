<?php

declare(strict_types=1);

namespace Fusani\RaiderIO\Adapter;

use Fusani\RaiderIO\Exception\RaiderIOException;

/**
 * This allows us to build several different types of adapters with
 * common methods.
 */
interface Adapter
{
    /**
     * Makes a GET request and returns the decoded response.
     *
     * @param string $path the path where the request will be made
     * @param array<string, string|int|float|bool|null> $params the query string parameters
     * @return array<mixed> the decoded response
     * @throws RaiderIOException if the request fails or the api responds with an error
     */
    public function get(string $path, array $params): array;

    /**
     * Makes a POST request and returns the decoded response.
     *
     * @param string $path the path where the request will be made
     * @param array<string, string|int|float|bool|null> $params the form parameters
     * @return array<mixed> the decoded response
     * @throws RaiderIOException if the request fails or the api responds with an error
     */
    public function post(string $path, array $params): array;

    /**
     * Makes a PUT request and returns the decoded response.
     *
     * @param string $path the path where the request will be made
     * @param array<string, string|int|float|bool|null> $params the parameters, sent as a JSON body
     * @return array<mixed> the decoded response
     * @throws RaiderIOException if the request fails or the api responds with an error
     */
    public function put(string $path, array $params): array;
}
