<?php

declare(strict_types=1);

namespace Fusani\RaiderIO\Adapter;

/**
 * Testing adapter that returns queued responses in order instead of making requests.
 */
class AdapterStub implements Adapter
{
    /** @var list<array<mixed>> */
    protected array $responses;

    /**
     * Builds a stub with no responses queued.
     */
    public function __construct()
    {
        $this->responses = [];
    }

    /**
     * Queues a response to be returned by the next request.
     *
     * @param array<mixed> $response the response to return
     */
    public function addResponse(array $response): void
    {
        $this->responses[] = $response;
    }

    /**
     * Returns the next queued response, whatever the service and method.
     *
     * @param string $service the service being called
     * @param string $method the method being called
     * @param array<string, string|int|float|bool|null> $params the parameters that would be sent
     * @return array<mixed> the next queued response, or an empty array if none are left
     */
    public function call(string $service, string $method, array $params): array
    {
        return $this->getNextResponse();
    }

    /**
     * {@inheritDoc}
     *
     * @param string $path the path where the request would be made
     * @param array<string, string|int|float|bool|null> $params the parameters that would be sent
     * @return array<mixed> the next queued response, or an empty array if none are left
     */
    public function get(string $path, array $params): array
    {
        return $this->getNextResponse();
    }

    /**
     * Removes and returns the next queued response.
     *
     * @return array<mixed> the next queued response, or an empty array if none are left
     */
    protected function getNextResponse(): array
    {
        return array_shift($this->responses) ?? [];
    }

    /**
     * {@inheritDoc}
     *
     * @param string $path the path where the request would be made
     * @param array<string, string|int|float|bool|null> $params the parameters that would be sent
     * @return array<mixed> the next queued response, or an empty array if none are left
     */
    public function post(string $path, array $params): array
    {
        return $this->getNextResponse();
    }

    /**
     * {@inheritDoc}
     *
     * @param string $path the path where the request would be made
     * @param array<string, string|int|float|bool|null> $params the parameters that would be sent
     * @return array<mixed> the next queued response, or an empty array if none are left
     */
    public function put(string $path, array $params): array
    {
        return $this->getNextResponse();
    }
}
