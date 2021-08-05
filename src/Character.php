<?php

namespace Fusani\RaiderIO;

use Fusani\RaiderIO\Adapter;

class Character
{
    // a way to connect to the api
    protected $adapter;

    /**
     * This function initializes the client by setting the adapter.
     *
     * @param Adapter\Adapter $adapter : an adapter for interfacing with the api
     * @return void
     */
    public function __construct(Adapter\Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * This method gets the mythic plus scores from the raider.io profile. It includes best runs and alt runs which
     * will include both fortified and tyrannical settings.
     *
     * @param string $character : the character whose information we need
     * @param string $realm : the realm the character lives on
     * @param string $region : the region the realm lives on
     * @return array : mythic plus data
     */
    public function getMythicPlusScores(string $character, string $realm, string $region) : array
    {
        $params = [
            'region' => $region,
            'realm' => $realm,
            'name' => $character,
            'fields' => 'mythic_plus_best_runs:all,mythic_plus_alternate_runs:all'
        ];

        return $this->adapter->get(
            "/characters/profile",
            $params
        );
    }
}
