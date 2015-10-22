<?php

namespace FFClientGraph\Entities;

use FFClientGraph\TestUtils;
use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../TestUtils.php';

class NodeStatsTest extends PHPUnit_Framework_TestCase
{

    public function testCreate()
    {

        TestUtils::clearDB();
        $nodeData = json_decode(file_get_contents(__DIR__ . '/../../../resources/test_small.json'), true);
        $node = new Node();
        $nodeStats = NodeStats::create($node, $nodeData['nodes']['68725120d3ed']);

        self::assertEquals($node, $nodeStats->getNode());
        self::assertEquals(26, $nodeStats->getClients());
        self::assertEquals(0.8072072072072072, $nodeStats->getMemoryUsage());
        self::assertEquals(28439067065, $nodeStats->getRxBytes());
        self::assertEquals(3656795468, $nodeStats->getTxBytes());

    }

}