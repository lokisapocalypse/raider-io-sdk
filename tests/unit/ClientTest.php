<?php

namespace Fusani\RaiderIO;

use Fusani\RaiderIO\SimpleTestCase;

/**
 * @covers Fusani\Blizzard\Client
 */
class ClientTest extends SimpleTestCase
{
    public function testConstruct()
    {
        $adapter = $this->getMockBuilder('Fusani\RaiderIO\Adapter\Adapter')
            ->disableOriginalConstructor()
            ->getMock();
        $client = new Client($adapter);

        $this->assertAttributeInstanceOf('Fusani\RaiderIO\Adapter\Adapter', 'adapter', $client);
    }
}
