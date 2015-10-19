<?php

namespace FFClientGraph\Util;

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../TestUtils.php';

use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use FFClientGraph\Config\Config;
use FFClientGraph\Config\Constants;
use FFClientGraph\TestUtils;
use InvalidArgumentException;
use Monolog\Logger;
use PHPUnit_Framework_TestCase;

class GraphTest extends PHPUnit_Framework_TestCase
{

    private static $logLevel = Logger::EMERGENCY;


    /**
     * @var EntityManager
     */
    private static $entityManager;

    /**
     * @var array
     */
    private static $classes;

    /**
     * @var SchemaTool
     */
    private static $schemaTool;

    public static function setUpBeforeClass()
    {
        /**
         * Setup ORM and EntityManager
         */
        $ORMConfig = Setup::createAnnotationMetadataConfiguration(array(Constants::ENTITY_PATH), true);

        //TODO Externalize DBConnection Setup and Config
        $DBConnection = array(
            'driver' => Constants::DB_DRIVER_SQLITE
        );
        $DBConnection['path'] = __DIR__ . '/../../../resources/test.sqlite.db';

        try {
            self::$entityManager = EntityManager::create($DBConnection, $ORMConfig);
            self::$classes[] = self::$entityManager->getClassMetadata('FFClientGraph\Entities\Node');
            self::$classes[] = self::$entityManager->getClassMetadata('FFClientGraph\Entities\NodeStats');
            self::$classes[] = self::$entityManager->getClassMetadata('FFClientGraph\Entities\DataTimestamp');
            self::$schemaTool = new SchemaTool(self::$entityManager);
            self::$schemaTool->updateSchema(self::$classes);
        } catch (ORMException $exception) {
            die('There was an ORMException in ' . get_class() . '\n Please check your configuration.\n' . $exception->getMessage());
        } catch (InvalidArgumentException $exception) {
            die('There was an invalid argument exception in ' . get_class() . '\n Please check your configuration.\n' . $exception->getMessage());
        }
    }

    public function testThatGraphIsCreated()
    {
        $db = new DB(self::$logLevel, self::$entityManager);
        $nodeID = 'blaID';

        TestUtils::clearDB(self::$schemaTool, self::$classes);

        $dt = new DateTime();

        if (file_exists(Config::CACHE_FOLDER . '/' . $nodeID . '-clients.png')) {
            unlink(Config::CACHE_FOLDER . '/' . $nodeID . '-clients.png');
        }

        $nodeData = json_decode(file_get_contents(__DIR__ . '/../../../resources/singleNode.json'), true);
        for ($i = 0; $i < 50; $i++) {
            TestUtils::insertNodeData(self::$entityManager, 'blaID', $dt);
            $dt->sub(new DateInterval('PT' . mt_rand(5, 15) . 'M'));
        }
        $db->saveSingleNodeData($nodeData);

        $graph = new Graph(self::$logLevel, self::$entityManager);
        $graph->createGraph($nodeID);
        self::assertFileExists(Config::CACHE_FOLDER . '/' . $nodeID . '-clients.png');

//        if (file_exists(Config::CACHE_FOLDER . '/' . $nodeID . '-clients.png')) {
//            unlink(Config::CACHE_FOLDER . '/' . $nodeID . '-clients.png');
//        }

    }

    public function testCreateAllGraphs()
    {
        TestUtils::clearDB(self::$schemaTool, self::$classes);

        $dt = new DateTime();

        $this->deleteTestFiles();

        for ($i = 0; $i < 50; $i++) {
            TestUtils::insertNodeData(self::$entityManager, 'blaID', $dt);
            TestUtils::insertNodeData(self::$entityManager, 'blaID2', $dt);
            TestUtils::insertNodeData(self::$entityManager, 'blaID3', $dt);
            $dt->sub(new DateInterval('PT' . mt_rand(5, 15) . 'M'));
        }

        $graph = new Graph(self::$logLevel, self::$entityManager);
        $graph->createAllGraphs();
        self::assertFileExists(Config::CACHE_FOLDER . '/blaID-clients.png');
        self::assertFileExists(Config::CACHE_FOLDER . '/blaID2-clients.png');
        self::assertFileExists(Config::CACHE_FOLDER . '/blaID3-clients.png');

        $this->deleteTestFiles();

    }

    private function deleteTestFiles()
    {
        if (file_exists(Config::CACHE_FOLDER . '/blaID-clients.png')) {
            unlink(Config::CACHE_FOLDER . '/blaID-clients.png');
        }
        if (file_exists(Config::CACHE_FOLDER . '/blaID2-clients.png')) {
            unlink(Config::CACHE_FOLDER . '/blaID2-clients.png');
        }
        if (file_exists(Config::CACHE_FOLDER . '/blaID3-clients.png')) {
            unlink(Config::CACHE_FOLDER . '/blaID3-clients.png');
        }
    }


    public static function tearDownAfterClass()
    {
        self::$schemaTool->dropDatabase();
    }
}