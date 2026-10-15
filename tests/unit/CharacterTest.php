<?php

declare(strict_types=1);

namespace Fusani\RaiderIO;

use Fusani\RaiderIO\Adapter\Adapter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Character::class)]
#[UsesClass(Region::class)]
class CharacterTest extends TestCase
{
    /**
     * Checks that the Mythic+ scores are requested from the character profile with the best and alternate runs,
     * and that the response is returned as it is.
     *
     * @return void
     */
    public function testGetMythicPlusScores(): void
    {
        $response = ['name' => 'Thrall', 'mythic_plus_best_runs' => []];

        $adapter = $this->createMock(Adapter::class);
        $adapter->expects($this->once())
            ->method('get')
            ->with('/characters/profile', [
                'region' => 'us',
                'realm' => 'Area 52',
                'name' => 'Thrall',
                'fields' => 'mythic_plus_best_runs:all,mythic_plus_alternate_runs:all',
            ])
            ->willReturn($response);

        $character = new Character($adapter);

        $this->assertSame($response, $character->getMythicPlusScores('Thrall', 'Area 52', Region::US));
    }
}
