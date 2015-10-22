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
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use FFClientGraph\Config\Constants;
use FFClientGraph\TestUtils;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;

class NodeStatsTimestampTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private static $classes;

    /**
     * @var SchemaTool
     */
    private static $schemaTool;

    /**
     * @var EntityManager
     */
    private static $entityManager;


    public static function setUpBeforeClass()
    {

        /**
         * Setup ORM and EntityManager
         */
        $ORMConfig = Setup::createAnnotationMetadataConfiguration(array(Constants::ENTITY_PATH), true);

        $DBConnection = TestUtils::setUpConnection();
        try {
            self::$entityManager = EntityManager::create($DBConnection, $ORMConfig);
            self::$classes = TestUtils::setUpClasses(self::$entityManager);
            self::$schemaTool = new SchemaTool(self::$entityManager);
            self::$schemaTool->updateSchema(self::$classes);
        } catch (ORMException $exception) {
            die('There was an ORMException in ' . get_class() . '\n Please check your configuration.\n' . $exception->getMessage());
        } catch (InvalidArgumentException $exception) {
            die('There was an invalid argument exception in ' . get_class() . '\n Please check your configuration.\n' . $exception->getMessage());
        }
    }

//    public function testGetOrCreate_returnNew()
//    {
//        TestUtils::clearDB(self::$schemaTool, self::$classes);
//
//        $dataTimestamp = NodeStatsTimestamp::getOrCreate(self::$entityManager, new DateTime());
//
//        self::assertNotNull($dataTimestamp);
//        self::assertInstanceOf('FFClientGraph\Entities\NodeStatsTimestamp', $dataTimestamp);
//        self::assertNull($dataTimestamp->getDataDateTime());
//        self::assertNotTrue(self::$entityManager->contains($dataTimestamp));
//
//    }
//
//    public function testGetOrCreate_returnNewWithDataTime()
//    {
//        TestUtils::clearDB(self::$schemaTool, self::$classes);
//
//        $dataTime = new DateTime();
//        $dataTime = $dataTime->sub(new DateInterval('PT5H'));
//
//        $dataTimestamp = NodeStatsTimestamp::getOrCreate(self::$entityManager, new DateTime(), $dataTime);
//
//        self::assertNotNull($dataTimestamp);
//        self::assertInstanceOf('FFClientGraph\Entities\NodeStatsTimestamp', $dataTimestamp);
//        self::assertNotNull($dataTimestamp->getDataDateTime());
//        self::assertEquals($dataTime->format('c'), $dataTimestamp->getDataDateTime()->format('c'));
//        self::assertNotTrue(self::$entityManager->contains($dataTimestamp));
//
//    }
//
    public function testGetOrCreate_returnExisting()
    {
        echo "existing_without\n";
        $dateTime = new \DateTimeImmutable();

        TestUtils::clearDB(self::$schemaTool, self::$classes);
        TestUtils::insertDataTimestamp(self::$entityManager, $dateTime);
        $dataTimestamp = NodeStatsTimestamp::getOrCreate(self::$entityManager, $dateTime);

        self::assertNotNull($dataTimestamp);
        self::assertInstanceOf('FFClientGraph\Entities\NodeStatsTimestamp', $dataTimestamp);
        self::assertNull($dataTimestamp->getDataDateTime());
        self::assertTrue(self::$entityManager->contains($dataTimestamp));

    }

    public function testGetOrCreate_returnExistingWithDataTime()
    {
        echo "existing_with\n";
        $dateTime = new DateTime();
        $dataTime = new DateTime();
        $dataTime = $dataTime->sub(new DateInterval('PT5H'));

        TestUtils::clearDB(self::$schemaTool, self::$classes);
        $inserted = TestUtils::insertDataTimestamp(self::$entityManager, $dateTime, $dataTime);
        echo "INSERTED OBJECT\n";
        print_r($inserted);
        sleep(1);
        self::$entityManager->close();
        self::$entityManager = EntityManager::create(TestUtils::setUpConnection(), Setup::createAnnotationMetadataConfiguration(array(Constants::ENTITY_PATH), true));
        $dataTimestamp = NodeStatsTimestamp::getOrCreate(self::$entityManager, $dateTime, $dataTime);

        self::assertNotNull($dataTimestamp);
        self::assertInstanceOf('FFClientGraph\Entities\NodeStatsTimestamp', $dataTimestamp);
        echo "GOT OBJECT\n";
        self::assertTrue(self::$entityManager->contains($dataTimestamp));
        self::assertNotNull($dataTimestamp->getDataDateTime());
        self::assertEquals($dataTime->format('c'), $dataTimestamp->getDataDateTime()->format('c'));

    }

    public static function tearDownAfterClass()
    {
        self::$schemaTool->dropDatabase();
    }
}