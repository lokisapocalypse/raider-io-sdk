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
     * Starts of the messages Raider.IO sends with a 400 when it can't find a character or realm. It has no
     * status of its own for these, so the wording is all there is to go on.
     */
    protected const NOT_FOUND_MESSAGE_PREFIXES = ['Could not find', 'Failed to find'];

    /**
     * Builds the exception for an error response, picking the subclass that matches its status, or for a 400,
     * whether the api's message says something wasn't found.
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

        return match (true) {
            $status === 404, $status === 400 && self::isNotFoundMessage($apiMessage)
                => new NotFoundException($message, $status, $previous),
            $status === 429 => new RateLimitException($message, $status, $previous),
            default => new self($message, $status, $previous),
        };
    }

    /**
     * Checks whether an api error message says a character or realm wasn't found.
     *
     * @param string|null $apiMessage the api's error message, if there was one
     * @return bool whether the message is a not-found message
     */
    protected static function isNotFoundMessage(?string $apiMessage): bool
    {
        if ($apiMessage === null) {
            return false;
        }

        foreach (self::NOT_FOUND_MESSAGE_PREFIXES as $prefix) {
            if (str_starts_with($apiMessage, $prefix)) {
                return true;
            }
        }

        return false;
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
