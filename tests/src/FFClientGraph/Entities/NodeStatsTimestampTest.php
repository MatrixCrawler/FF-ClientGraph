<?php
namespace FFClientGraph\Entities;

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../TestUtils.php';

use DateInterval;
use DateTime;
use FFClientGraph\TestUtils;
use PHPUnit_Framework_TestCase;

class NodeStatsTimestampTest extends PHPUnit_Framework_TestCase
{

    public function testGetOrCreate_returnNew()
    {
        TestUtils::clearDB();
        $entityManager = TestUtils::getEntityManager();
        $dataTimestamp = NodeStatsTimestamp::getOrCreate($entityManager, new DateTime());

        self::assertNotNull($dataTimestamp);
        self::assertInstanceOf('FFClientGraph\Entities\NodeStatsTimestamp', $dataTimestamp);
        self::assertNull($dataTimestamp->getDataTimestamp());
        self::assertNotTrue($entityManager->contains($dataTimestamp));

    }

    public function testGetOrCreate_returnNewWithDataTime()
    {
        TestUtils::clearDB();
        $entityManager = TestUtils::getEntityManager();

        $dataTime = new DateTime();
        $dataTime = $dataTime->sub(new DateInterval('PT5H'));

        $dateTime = new DateTime();
        $dataTimestamp = NodeStatsTimestamp::getOrCreate($entityManager, $dateTime, $dataTime);

        self::assertNotNull($dataTimestamp);
        self::assertInstanceOf('FFClientGraph\Entities\NodeStatsTimestamp', $dataTimestamp);
        self::assertNotNull($dataTimestamp->getDataTimestamp());
        self::assertEquals($dataTime->format('c'), $dataTimestamp->getDataTimestamp()->format('c'));
        self::assertNotTrue($entityManager->contains($dataTimestamp));
        self::assertEquals($dateTime->format('c'), $dataTimestamp->getCreated()->format('c'));

    }

    public function testGetOrCreate_returnExisting()
    {
        $dateTime = new DateTime();

        TestUtils::clearDB();
        TestUtils::insertDataTimestamp($dateTime);

        $entityManager = TestUtils::getEntityManager();
        $dataTimestamp = NodeStatsTimestamp::getOrCreate($entityManager, $dateTime);

        self::assertNotNull($dataTimestamp);
        self::assertInstanceOf('FFClientGraph\Entities\NodeStatsTimestamp', $dataTimestamp);
        self::assertNull($dataTimestamp->getDataTimestamp());
        self::assertTrue($entityManager->contains($dataTimestamp));

    }

    public function testGetOrCreate_returnExistingWithDataTime()
    {
        $dateTime = new DateTime();
        $dataTime = new DateTime();
        $dataTime = $dataTime->sub(new DateInterval('PT5H'));

        TestUtils::clearDB();
        TestUtils::insertDataTimestamp($dateTime, $dataTime);
        $entityManager = TestUtils::getEntityManager();
        $dataTimestamp = NodeStatsTimestamp::getOrCreate($entityManager, $dateTime, $dataTime);

        self::assertNotNull($dataTimestamp);
        self::assertInstanceOf('FFClientGraph\Entities\NodeStatsTimestamp', $dataTimestamp);
        self::assertTrue($entityManager->contains($dataTimestamp));
        self::assertNotNull($dataTimestamp->getDataTimestamp());
        self::assertEquals($dataTime->format('c'), $dataTimestamp->getDataTimestamp()->format('c'));
        self::assertEquals($dateTime->format('c'), $dataTimestamp->getCreated()->format('c'));

    }

}