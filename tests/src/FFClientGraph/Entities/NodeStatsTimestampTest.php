<?php
/**
 * Created by IntelliJ IDEA.
 * User: Johannes Brunswicker
 * Date: 22.10.2015
 * Time: 15:31
 */

namespace FFClientGraph\Entities;

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../TestUtils.php';

use DateInterval;
use DateTime;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\SchemaTool;
use FFClientGraph\TestUtils;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;

class NodeStatsTimestampTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SchemaTool
     */
    private static $schemaTool;

//    public static function setUpBeforeClass()
//    {
//        try {
//            $entityManager = TestUtils::getEntityManager();
//            self::$schemaTool = new SchemaTool($entityManager);
//            self::$schemaTool->updateSchema(TestUtils::setUpClasses($entityManager));
//        } catch (ORMException $exception) {
//            die('There was an ORMException in ' . get_class() . '\n Please check your configuration.\n' . $exception->getMessage());
//        } catch (InvalidArgumentException $exception) {
//            die('There was an invalid argument exception in ' . get_class() . '\n Please check your configuration.\n' . $exception->getMessage());
//        }
//    }

    public function testGetOrCreate_returnNew()
    {
        TestUtils::clearDB();
        $entityManager = TestUtils::getEntityManager();
        $dataTimestamp = NodeStatsTimestamp::getOrCreate($entityManager, new DateTime());

        self::assertNotNull($dataTimestamp);
        self::assertInstanceOf('FFClientGraph\Entities\NodeStatsTimestamp', $dataTimestamp);
        self::assertNull($dataTimestamp->getDataDateTime());
        self::assertNotTrue($entityManager->contains($dataTimestamp));

    }

    public function testGetOrCreate_returnNewWithDataTime()
    {
        TestUtils::clearDB();
        $entityManager = TestUtils::getEntityManager();

        $dataTime = new DateTime();
        $dataTime = $dataTime->sub(new DateInterval('PT5H'));

        $dataTimestamp = NodeStatsTimestamp::getOrCreate($entityManager, new DateTime(), $dataTime);

        self::assertNotNull($dataTimestamp);
        self::assertInstanceOf('FFClientGraph\Entities\NodeStatsTimestamp', $dataTimestamp);
        self::assertNotNull($dataTimestamp->getDataDateTime());
        self::assertEquals($dataTime->format('c'), $dataTimestamp->getDataDateTime()->format('c'));
        self::assertNotTrue($entityManager->contains($dataTimestamp));

    }

    public function testGetOrCreate_returnExisting()
    {
        $dateTime = new \DateTimeImmutable();

        TestUtils::clearDB();
        TestUtils::insertDataTimestamp($dateTime);

        $entityManager = TestUtils::getEntityManager();
        $dataTimestamp = NodeStatsTimestamp::getOrCreate($entityManager, $dateTime);

        self::assertNotNull($dataTimestamp);
        self::assertInstanceOf('FFClientGraph\Entities\NodeStatsTimestamp', $dataTimestamp);
        self::assertNull($dataTimestamp->getDataDateTime());
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
        self::assertNotNull($dataTimestamp->getDataDateTime());
        self::assertEquals($dataTime->format('c'), $dataTimestamp->getDataDateTime()->format('c'));

    }

//    public static function tearDownAfterClass()
//    {
//        TestUtils::deleteDB();
//    }
}