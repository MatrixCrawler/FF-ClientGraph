<?php

namespace FFClientGraph\Entities;

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../TestUtils.php';

use FFClientGraph\TestUtils;
use PHPUnit_Framework_TestCase;

class HardwareTest extends PHPUnit_Framework_TestCase
{

    public function testGetOrCreate_returnNewHardware()
    {
        TestUtils::clearDB();
        $nodeData = json_decode(file_get_contents(__DIR__ . '/../../../resources/test_small.json'), true);

        $entityManager = TestUtils::getEntityManager();
        $hardware = Hardware::getOrCreate($entityManager, $nodeData['nodes']['68725120d3ed']);

        self::assertNotNull($hardware);
        self::assertInstanceOf('FFClientGraph\Entities\Hardware', $hardware);
        self::assertNotTrue($entityManager->contains($hardware));
    }

    public function testGetOrCreate_returnExistingHardware()
    {
        TestUtils::clearDB();
        $nodeData = json_decode(file_get_contents(__DIR__ . '/../../../resources/test_small.json'), true);

        TestUtils::insertHardware('Ubiquiti Bullet M');
        $entityManager = TestUtils::getEntityManager();


        $hardware = Hardware::getOrCreate($entityManager, $nodeData['nodes']['68725120d3ed']);

        self::assertNotNull($hardware);
        self::assertInstanceOf('FFClientGraph\Entities\Hardware', $hardware);
        self::assertTrue($entityManager->contains($hardware));
    }

}