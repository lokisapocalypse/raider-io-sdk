<?php

declare(strict_types=1);

namespace Fusani\RaiderIO\Exception;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;

/**
 * Thrown when a request to the Raider.IO api fails. The code is the HTTP status, or 0 if no response came back.
 */
class RaiderIOException extends RuntimeException
{
    /**
     * Builds the exception for an error response, picking the subclass that matches its status.
     *
     * @param ResponseInterface $response the error response
     * @param Throwable|null $previous the exception that reported the error
     * @return self the exception for the response's status
     */
    public static function fromResponse(ResponseInterface $response, ?Throwable $previous = null): self
    {
        $status = $response->getStatusCode();

        $message = sprintf('Raider.IO responded with %d %s', $status, $response->getReasonPhrase());

        $apiMessage = self::apiMessage((string) $response->getBody());
        if ($apiMessage !== null) {
            $message .= ': ' . $apiMessage;
        }

        return match ($status) {
            404 => new NotFoundException($message, $status, $previous),
            429 => new RateLimitException($message, $status, $previous),
            default => new self($message, $status, $previous),
        };
    }

    /**
     * Builds the exception for a request that got no usable response, such as a connection failure or timeout.
     *
     * @param Throwable $previous the exception that reported the failure
     * @return self the exception, with a code of 0
     */
    public static function fromTransferFailure(Throwable $previous): self
    {
        return new self('Raider.IO request failed: ' . $previous->getMessage(), 0, $previous);
    }

    /**
     * Pulls the error message out of a Raider.IO error body, e.g. `{"error":"Bad Request","message":"..."}`.
     *
     * @param string $body the response body
     * @return string|null the api's error message, or null if the body doesn't have one
     */
    protected static function apiMessage(string $body): ?string
    {
        $decoded = json_decode($body, true);

        if (!is_array($decoded) || !isset($decoded['message']) || !is_string($decoded['message'])) {
            return null;
        }

        return $decoded['message'] !== '' ? $decoded['message'] : null;
    }
}
