<?php

namespace FFClientGraph\Util;

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../TestUtils.php';

use DateInterval;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use FFClientGraph\Config\Constants;
use FFClientGraph\TestUtils;
use InvalidArgumentException;
use Monolog\Logger;
use PHPUnit_Framework_TestCase;

class DBTest extends PHPUnit_Framework_TestCase
{

    private static $logLevel = Logger::EMERGENCY;
    private static $classes;

    /**
     * @var SchemaTool
     */
    private static $schemaTool;

    /**
     * @var EntityManager
     */
    private static $entityManager;

    /**
     * @var DateTimeInterface
     */
    private static $dateTime;

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
            self::$classes = TestUtils::setUpClasses(self::$entityManager);
            self::$schemaTool = new SchemaTool(self::$entityManager);
            self::$schemaTool->updateSchema(self::$classes);
        } catch (ORMException $exception) {
            die('There was an ORMException in ' . get_class() . '\n Please check your configuration.\n' . $exception->getMessage());
        } catch (InvalidArgumentException $exception) {
            die('There was an invalid argument exception in ' . get_class() . '\n Please check your configuration.\n' . $exception->getMessage());
        }
        self::$dateTime = new DateTime();
    }

//    public function testLoggerIsNotNull()
//    {
//        $db = new DB(self::$logLevel, self::$entityManager);
//        self::assertNotNull($db->getLogger());
//        unset($db);
//    }

    public function testSaveNode()
    {

        $db = new DB(self::$logLevel, self::$entityManager);

        TestUtils::clearDB(self::$schemaTool, self::$classes);

        $nodeData = json_decode(file_get_contents(__DIR__ . '/../../../resources/test_small.json'), true);
        $db->saveSingleNodeData($nodeData['nodes']['68725120d3ed'], new DateTime($nodeData['timestamp']));

        $nodeRepository = self::$entityManager->getRepository('FFClientGraph\Entities\Node');
        $result = $nodeRepository->findBy(['nodeId' => '68725120d3ed']);

        self::assertEquals(1, count($result));
        self::assertEquals('68725120d3ed', $result[0]->getNodeId());

        return $db;

    }

    /**
     * @depends testSaveNode
     * @param DB $db
     */
    public function testThatDataWillNotBeSavedIfWeHaveTheSameDBInstance(DB $db)
    {
        sleep(1);
        $nodeData = json_decode(file_get_contents(__DIR__ . '/../../../resources/singleNode.json'), true);
        $db->saveSingleNodeData($nodeData);

        $nodeRepository = self::$entityManager->getRepository('FFClientGraph\Entities\Node');
        $node = $nodeRepository->findBy(['nodeId' => '68725120d3ed']);

        $nodeStatsRepository = self::$entityManager->getRepository('FFClientGraph\Entities\NodeStats');
        $nodeData = $nodeStatsRepository->findBy(['node' => $node]);

        self::assertEquals(1, count($nodeData));
    }

    /**
     * @depends testThatDataWillNotBeSavedIfWeHaveTheSameDBInstance
     */
    public function testThatDataWillOnlyBeSavedIfWeHaveANewDBInstance()
    {
        sleep(1);
        $db = new DB(self::$logLevel, self::$entityManager);
        $nodeData = json_decode(file_get_contents(__DIR__ . '/../../../resources/singleNode.json'), true);
        $db->saveSingleNodeData($nodeData);

        $nodeRepository = self::$entityManager->getRepository('FFClientGraph\Entities\Node');
        $node = $nodeRepository->findBy(['nodeId' => '68725120d3ed']);

        $nodeStatsRepository = self::$entityManager->getRepository('FFClientGraph\Entities\NodeStats');
        $nodeData = $nodeStatsRepository->findBy(['node' => $node]);

        self::assertEquals(2, count($nodeData));
    }

    /**
     * @depends testSaveNode
     */
    public function testGetNodeData()
    {
        $db = new DB(self::$logLevel, self::$entityManager);
        TestUtils::clearDB(self::$schemaTool, self::$classes);

        $dt = new DateTime();

        TestUtils::insertNodeData(self::$entityManager, 'blaID', $dt);
        TestUtils::insertNodeData(self::$entityManager, 'blaID', $dt);
        $dt->sub(new DateInterval('PT4H'));
        TestUtils::insertNodeData(self::$entityManager, 'blaID', $dt);
        $dt->sub(new DateInterval('PT4H'));
        TestUtils::insertNodeData(self::$entityManager, 'blaID', $dt);
        $dt->sub(new DateInterval('PT4H'));
        TestUtils::insertNodeData(self::$entityManager, 'blaID', $dt);
        $dt->sub(new DateInterval('PT4H'));
        TestUtils::insertNodeData(self::$entityManager, 'blaID', $dt);
        $dt->sub(new DateInterval('PT4H'));
        TestUtils::insertNodeData(self::$entityManager, 'blaID', $dt);
        $dt->sub(new DateInterval('PT5H'));
        TestUtils::insertNodeData(self::$entityManager, 'blaID', $dt);

        $nodeDataRepository = self::$entityManager->getRepository('FFClientGraph\Entities\NodeStats');
        $qb = $nodeDataRepository->createQueryBuilder('nd');
        $qb->join('nd.dataTimestamp', 'ds')
            ->join('nd.node', 'node')
            ->where('ds.timestamp > :timestamp')
            ->setParameter('timestamp', self::$dateTime->sub(new DateInterval('PT24H')));
        $query = $qb->getQuery();
        $result = $query->getResult();

        $testResult = $db->getNodeData('blaID');
        self::assertEquals(count($testResult), count($result));
        self::assertInstanceOf('FFClientGraph\Entities\NodeStats', $testResult[0]);
        self::assertInstanceOf('FFClientGraph\Entities\Node', $testResult[0]->getNode());
        self::assertNotNull($testResult[0]->getNode()->getName());
        self::assertNotEmpty($testResult[0]->getNode()->getName());
    }

    public function testGetNodes()
    {
        $db = new DB(self::$logLevel, self::$entityManager);
        TestUtils::clearDB(self::$schemaTool, self::$classes);

        for ($i = 0; $i < 10; $i++) {
            TestUtils::insertNode(self::$entityManager, 'TestNode' . $i);
        }

        $result = $db->getNodes();
        self::assertEquals(10, count($result));
        self::assertInstanceOf('FFClientGraph\Entities\Node', $result[0]);
    }

    public function testSaveNodesSavesAllNodesFromJSON()
    {
        $db = new DB(self::$logLevel, self::$entityManager);
        TestUtils::clearDB(self::$schemaTool, self::$classes);

        $nodeData = json_decode(file_get_contents(__DIR__ . '/../../../resources/test_small.json'), true);
        $db->saveNodes($nodeData);

        $nodeRepository = self::$entityManager->getRepository('FFClientGraph\Entities\Node');
        $result = $nodeRepository->findAll();

        self::assertEquals(5, count($result));
    }

    public function testDeleteObsoleteNodes()
    {
        $db = new DB(self::$logLevel, self::$entityManager);
        $timestamp = new DateTime();
        TestUtils::clearDB(self::$schemaTool, self::$classes);

        for ($i = 0; $i < 48; $i++) {
            TestUtils::insertNodeData(self::$entityManager, 'someNodeId', $timestamp);
            $timestamp = $timestamp->sub(new DateInterval('PT1H'));
        }

        $db->deleteOldNodeData();

        $dsRepo = self::$entityManager->getRepository('FFClientGraph\Entities\DataTimestamp');
        $dsResult = $dsRepo->findAll();
        self::assertLessThanOrEqual(25, count($dsResult));
    }

    public function testNumberOfTimestamps()
    {
        $db = new DB(self::$logLevel, self::$entityManager);
        TestUtils::clearDB(self::$schemaTool, self::$classes);
        $timestamp = new DateTime();
        for ($i = 0; $i < 48; $i++) {
            TestUtils::insertNodeData(self::$entityManager, 'someNodeId', $timestamp);
            $timestamp = $timestamp->sub(new DateInterval('PT1H'));
        }

        $result = $db->getNumberOfTimestamps();

        self::assertLessThanOrEqual(25, $result);
    }

    public static function tearDownAfterClass()
    {
        self::$schemaTool->dropDatabase();
    }
}