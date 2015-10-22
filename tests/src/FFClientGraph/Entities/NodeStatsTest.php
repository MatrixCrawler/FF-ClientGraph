<?php

namespace FFClientGraph\Entities;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use FFClientGraph\Config\Constants;
use FFClientGraph\TestUtils;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../TestUtils.php';

class NodeStatsTest extends PHPUnit_Framework_TestCase
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

    public function testCreate() {

        TestUtils::clearDB(self::$schemaTool, self::$classes);
        $nodeData = json_decode(file_get_contents(__DIR__ . '/../../../resources/test_small.json'), true);
        $node = new Node();
        $nodeStats = NodeStats::create($node, $nodeData['nodes']['68725120d3ed']);

        self::assertEquals($node, $nodeStats->getNode());
        self::assertEquals(26, $nodeStats->getClients());
        self::assertEquals(0.8072072072072072, $nodeStats->getMemoryUsage());
        self::assertEquals(28439067065, $nodeStats->getRxBytes());
        self::assertEquals(3656795468, $nodeStats->getTxBytes());

    }

    public static function tearDownAfterClass()
    {
        self::$schemaTool->dropDatabase();
    }

}