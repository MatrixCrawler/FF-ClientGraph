<?php

namespace FFClientGraph\Util;

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../TestUtils.php';

use DateInterval;
use DateTime;
use FFClientGraph\Config\Config;
use FFClientGraph\TestUtils;
use Monolog\Logger;
use PHPUnit_Framework_TestCase;

class GraphTest extends PHPUnit_Framework_TestCase
{

    private $logLevel = Logger::DEBUG;

    public function testThatGraphIsCreated()
    {
        TestUtils::clearDB();
        $entityManager = TestUtils::getEntityManager();

        $nodeID = 'blaID';
        $dt = new DateTime();
        if (file_exists(Config::CACHE_FOLDER . '/' . $nodeID . '-clients.png')) {
            unlink(Config::CACHE_FOLDER . '/' . $nodeID . '-clients.png');
        }
        for ($i = 0; $i < 50; $i++) {
            TestUtils::insertNodeData($entityManager, $nodeID, $dt);
            $dt->sub(new DateInterval('PT' . mt_rand(5, 15) . 'M'));
        }

        $graph = new Graph($this->logLevel, $entityManager);
        $graph->createGraph($nodeID);
        self::assertFileExists(Config::CACHE_FOLDER . '/' . $nodeID . '-clients.png');

        if (file_exists(Config::CACHE_FOLDER . '/' . $nodeID . '-clients.png')) {
            unlink(Config::CACHE_FOLDER . '/' . $nodeID . '-clients.png');
        }

    }

    public function testCreateAllGraphs()
    {
        TestUtils::clearDB();
        $entityManager = TestUtils::getEntityManager();

        $dt = new DateTime();

        $this->deleteTestFiles();

        for ($i = 0; $i < 50; $i++) {
            TestUtils::insertNodeData($entityManager, 'blaID', $dt);
            TestUtils::insertNodeData($entityManager, 'blaID2', $dt);
            TestUtils::insertNodeData($entityManager, 'blaID3', $dt);
            $dt->sub(new DateInterval('PT' . mt_rand(5, 15) . 'M'));
        }

        $graph = new Graph($this->logLevel, $entityManager);
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
//
//
//    public static function tearDownAfterClass()
//    {
//        self::$schemaTool->dropDatabase();
//    }
}