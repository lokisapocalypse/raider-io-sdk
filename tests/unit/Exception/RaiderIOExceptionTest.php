<?php

declare(strict_types=1);

namespace Fusani\RaiderIO\Exception;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(RaiderIOException::class)]
class RaiderIOExceptionTest extends TestCase
{
    /**
     * Checks that an error response gives the exception class for its status, with the status as the code.
     *
     * @param int $status the response status
     * @param class-string<RaiderIOException> $class the exception class expected
     * @return void
     */
    #[DataProvider('statusProvider')]
    public function testFromResponsePicksClassForStatus(int $status, string $class): void
    {
        $previous = new RuntimeException('previous');

        $e = RaiderIOException::fromResponse(new Response($status), $previous);

        $this->assertSame($class, $e::class);
        $this->assertSame($status, $e->getCode());
        $this->assertSame($previous, $e->getPrevious());
    }

    /**
     * Lists response statuses with the exception class each should give.
     *
     * @return array<string, array{int, class-string<RaiderIOException>}>
     */
    public static function statusProvider(): array
    {
        return [
            'not found' => [404, NotFoundException::class],
            'rate limited' => [429, RateLimitException::class],
            'bad request' => [400, RaiderIOException::class],
            'server error' => [500, RaiderIOException::class],
        ];
    }

    /**
     * Checks that the message includes the api's error message when the body has one.
     *
     * @return void
     */
    public function testFromResponseIncludesApiMessage(): void
    {
        $response = new Response(400, [], '{"statusCode":400,"error":"Bad Request","message":"Unknown realm"}');

        $this->assertSame(
            'Raider.IO responded with 400 Bad Request: Unknown realm',
            RaiderIOException::fromResponse($response)->getMessage()
        );
    }

    /**
     * Checks that the message is just the status when the body has no usable error message.
     *
     * @param string $body the response body
     * @return void
     */
    #[DataProvider('bodyWithoutMessageProvider')]
    public function testFromResponseWithoutApiMessage(string $body): void
    {
        $this->assertSame(
            'Raider.IO responded with 502 Bad Gateway',
            RaiderIOException::fromResponse(new Response(502, [], $body))->getMessage()
        );
    }

    /**
     * Lists response bodies that have no usable error message.
     *
     * @return array<string, array{string}>
     */
    public static function bodyWithoutMessageProvider(): array
    {
        return [
            'empty' => [''],
            'html' => ['<html>Bad Gateway</html>'],
            'no message' => ['{"error":"Bad Gateway"}'],
            'empty message' => ['{"message":""}'],
            'non-string message' => ['{"message":["a"]}'],
        ];
    }

    /**
     * Checks that a transfer failure gives a code of 0 and keeps the original message and exception.
     *
     * @return void
     */
    public function testFromTransferFailure(): void
    {
        $previous = new RuntimeException('Operation timed out');

        $e = RaiderIOException::fromTransferFailure($previous);

        $this->assertSame(RaiderIOException::class, $e::class);
        $this->assertSame(0, $e->getCode());
        $this->assertSame('Raider.IO request failed: Operation timed out', $e->getMessage());
        $this->assertSame($previous, $e->getPrevious());
    }
}
