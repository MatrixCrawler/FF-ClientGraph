<?php

namespace FFClientGraph\Entities;

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../TestUtils.php';

use DateTime;
use FFClientGraph\TestUtils;
use PHPUnit_Framework_TestCase;

class NodeInfoTest extends PHPUnit_Framework_TestCase
{

    public function testCreate()
    {
        TestUtils::clearDB();
        $entityManager = TestUtils::getEntityManager();
        $nodeData = json_decode(file_get_contents(__DIR__ . '/../../../resources/test_small.json'), true);
        $nodeInfo = NodeInfo::create($entityManager, new Node(), $nodeData['nodes']['68725120d3ed']);

        self::assertNotNull($nodeInfo);
        self::assertEquals('FF-Is-Heimatversorger-060', $nodeInfo->getHostname());
        self::assertEquals(new DateTime('2015-09-30T19:10:11'), $nodeInfo->getFirstseen());
        self::assertEquals(new DateTime('2015-10-12T12:41:49'), $nodeInfo->getLastseen());
        self::assertEquals(51.37435, $nodeInfo->getLatitude());
        self::assertEquals(7.69723, $nodeInfo->getLongitude());
        self::assertEquals('info@freifunk-iserlohn.de', $nodeInfo->getOwner());

        $hardware = Hardware::getOrCreate($entityManager, $nodeData['nodes']['68725120d3ed']);

        self::assertEquals($hardware->getModel(), $nodeInfo->getHardware()->getModel());
        self::assertNotTrue($entityManager->contains($nodeInfo));
    }

}