<?php

namespace FFClientGraph\Entities;

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../TestUtils.php';

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use FFClientGraph\Config\Constants;
use FFClientGraph\TestUtils;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;

class HardwareTest extends PHPUnit_Framework_TestCase
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

    public function testCreate()
    {
        $nodeData = json_decode(file_get_contents(__DIR__ . '/../../../resources/test_small.json'), true);
        $hardware = Hardware::create(new NodeInfo(new Node()), $nodeData['nodes']['68725120d3ed']);

        self::assertEquals('Ubiquiti Bullet M', $hardware->getModel());
    }

    public function testGetOrCreate_createNewIfNotExisting()
    {
        /**
         * Clear database
         */
        TestUtils::clearDB(self::$schemaTool, self::$classes);
        $nodeData = json_decode(file_get_contents(__DIR__ . '/../../../resources/test_small.json'), true);

        $nodeInfo = new NodeInfo();
        $hardware = Hardware::getOrCreate(self::$entityManager, $nodeInfo, $nodeData['nodes']['68725120d3ed']);

        self::assertNotNull($hardware);
        self::assertInstanceOf('FFClientGraph\Entities\Hardware', $hardware);
        self::assertEquals($nodeInfo, $hardware->getNodeInfo());
    }

    public function testGetOrCreate_returnExistingIfExisting()
    {
        TestUtils::clearDB(self::$schemaTool, self::$classes);

        TestUtils::insertHardware(self::$entityManager, 'Ubiquiti Bullet M');

        $nodeData = json_decode(file_get_contents(__DIR__ . '/../../../resources/test_small.json'), true);
        $nodeInfo = new NodeInfo();
        $hardware = Hardware::getOrCreate(self::$entityManager, $nodeInfo, $nodeData['nodes']['68725120d3ed']);

        self::assertNull($hardware->getNodeInfo());
    }

    public static function tearDownAfterClass()
    {
        self::$schemaTool->dropDatabase();
    }
}