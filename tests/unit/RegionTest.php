<?php

declare(strict_types=1);

namespace Fusani\RaiderIO;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Region::class)]
class RegionTest extends TestCase
{
    /**
     * Checks that a region is backed by the value the api expects, and can be built back from it.
     *
     * @param Region $region the region
     * @param string $value the value the api uses for it
     * @return void
     */
    #[DataProvider('regionProvider')]
    public function testValue(Region $region, string $value): void
    {
        $this->assertSame($value, $region->value);
        $this->assertSame($region, Region::from($value));
    }

    /**
     * Checks that every region has a value listed for it, so a new one gets checked too.
     *
     * @return void
     */
    public function testEveryRegionIsListed(): void
    {
        $listed = array_map(fn (array $case): Region => $case[0], self::regionProvider());

        $this->assertSame(Region::cases(), array_values($listed));
    }

    /**
     * Lists each region with the value the api uses for it.
     *
     * @return array<string, array{Region, string}>
     */
    public static function regionProvider(): array
    {
        return [
            'US' => [Region::US, 'us'],
            'EU' => [Region::EU, 'eu'],
            'KR' => [Region::KR, 'kr'],
            'TW' => [Region::TW, 'tw'],
            'CN' => [Region::CN, 'cn'],
        ];
    }
}
