<?php

declare(strict_types=1);

namespace Fusani\RaiderIO\Live;

use Fusani\RaiderIO\Adapter\GuzzleAdapter;
use Fusani\RaiderIO\Character;
use Fusani\RaiderIO\Exception\NotFoundException;
use Fusani\RaiderIO\Exception\RaiderIOException;
use Fusani\RaiderIO\Region;
use Fusani\RaiderIO\ServiceFactory;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Calls the real Raider.IO api, to check the SDK still matches it. Run with `composer test:live`.
 */
#[CoversNothing]
#[Group('live')]
class CharacterTest extends TestCase
{
    /**
     * A long-standing character to look up. Its runs change, so only its identity is checked.
     */
    protected const NAME = 'Naowh';

    protected const REALM = 'tarren-mill';

    protected const REGION = Region::EU;

    protected Character $character;

    /**
     * Builds the character service the way a consumer of the SDK would.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->character = (new ServiceFactory())->createCharacterService();
    }

    /**
     * Checks that a known character's profile comes back with its Mythic+ best runs.
     *
     * @return void
     */
    public function testGetMythicPlusScores(): void
    {
        $profile = $this->character->getMythicPlusScores(self::NAME, self::REALM, self::REGION);

        $this->assertSame(self::NAME, $profile['name'] ?? null);
        $this->assertSame(self::REGION->value, $profile['region'] ?? null);
        $this->assertSame('Tarren Mill', $profile['realm'] ?? null);
        $this->assertArrayHasKey('mythic_plus_best_runs', $profile);
        $this->assertIsList($profile['mythic_plus_best_runs']);
    }

    /**
     * Checks that the api's response for an unknown character still gives a NotFoundException.
     *
     * @return void
     */
    public function testUnknownCharacter(): void
    {
        $this->expectException(NotFoundException::class);

        $this->character->getMythicPlusScores('Notarealcharacterxyz', self::REALM, self::REGION);
    }

    /**
     * Checks that the api's response for an unknown realm still gives a NotFoundException.
     *
     * @return void
     */
    public function testUnknownRealm(): void
    {
        $this->expectException(NotFoundException::class);

        $this->character->getMythicPlusScores(self::NAME, 'notarealrealmxyz', self::REGION);
    }

    /**
     * Checks that other bad input, such as a region the api doesn't know, gives the base exception rather than a
     * NotFoundException.
     *
     * @return void
     */
    public function testInvalidRegion(): void
    {
        $adapter = new GuzzleAdapter('https://raider.io/api/v1');

        try {
            $adapter->get('/characters/profile', ['region' => 'xx', 'realm' => self::REALM, 'name' => self::NAME]);
            $this->fail('Expected a RaiderIOException.');
        } catch (RaiderIOException $e) {
            $this->assertSame(RaiderIOException::class, $e::class);
            $this->assertSame(400, $e->getCode());
        }
    }
}
