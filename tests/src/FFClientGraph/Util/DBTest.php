<?php

namespace FFClientGraph\Util;

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../TestUtils.php';

use DateInterval;
use DateTime;
use DateTimeImmutable;
use FFClientGraph\Entities\Node;
use FFClientGraph\Entities\NodeStatsTimestamp;
use FFClientGraph\TestUtils;
use Monolog\Logger;
use PHPUnit_Framework_TestCase;

class DBTest extends PHPUnit_Framework_TestCase
{

    private static $logLevel = Logger::EMERGENCY;

    public function testSaveSingleNode()
    {
        TestUtils::clearDB();
        $nodeData = json_decode(file_get_contents(__DIR__ . '/../../../resources/test_small.json'), true);
        $entityManager = TestUtils::getEntityManager();

        $db = new DB(self::$logLevel, $entityManager);

        $created = new DateTime($nodeData['timestamp']);

        $db->saveSingleNodeData($nodeData['nodes']['68725120d3ed'], $created);
        $entityManager->close();

        $entityManager = TestUtils::getEntityManager();
        $nodeRepository = $entityManager->getRepository('FFClientGraph\Entities\Node');
        $result = $nodeRepository->findOneBy(['nodeId' => '68725120d3ed']);

        self::assertNotNull($result);
        self::assertNotNull($result->getNodeInfo());
        self::assertNotNull($result->getNodeStats());
        self::assertEquals(1, count($result->getNodeStats()));
        self::assertInstanceOf('FFClientGraph\Entities\NodeStats', $result->getNodeStats()[0]);
        self::assertEquals('68725120d3ed', $result->getNodeId());
        self::assertNotNull($result->getNodeStats()[0]->getStatTimestamp());
        self::assertNotNull($result->getNodeStats()[0]->getStatTimestamp()->getDataTimestamp());

        return $db;
    }

//    /**
//     * @depends testSaveNode
//     * @param DB $db
//     */
//    public function testThatDataWillNotBeSavedIfWeHaveTheSameDBInstance(DB $db)
//    {
//        sleep(1);
//        $nodeData = json_decode(file_get_contents(__DIR__ . '/../../../resources/singleNode.json'), true);
//        $db->saveSingleNodeData($nodeData);
//
//        $nodeRepository = self::$entityManager->getRepository('FFClientGraph\Entities\Node');
//        $node = $nodeRepository->findBy(['nodeId' => '68725120d3ed']);
//
//        $nodeStatsRepository = self::$entityManager->getRepository('FFClientGraph\Entities\NodeStats');
//        $nodeData = $nodeStatsRepository->findBy(['node' => $node]);
//
//        self::assertEquals(1, count($nodeData));
//    }
//
//    /**
//     * @depends testThatDataWillNotBeSavedIfWeHaveTheSameDBInstance
//     */
//    public function testThatDataWillOnlyBeSavedIfWeHaveANewDBInstance()
//    {
//        sleep(1);
//        $db = new DB(self::$logLevel, self::$entityManager);
//        $nodeData = json_decode(file_get_contents(__DIR__ . '/../../../resources/singleNode.json'), true);
//        $db->saveSingleNodeData($nodeData);
//
//        $nodeRepository = self::$entityManager->getRepository('FFClientGraph\Entities\Node');
//        $node = $nodeRepository->findBy(['nodeId' => '68725120d3ed']);
//
//        $nodeStatsRepository = self::$entityManager->getRepository('FFClientGraph\Entities\NodeStats');
//        $nodeData = $nodeStatsRepository->findBy(['node' => $node]);
//
//        self::assertEquals(2, count($nodeData));
//    }
//
//    /**
//     * @depends testSaveNode
//     */
//    public function testGetNodeData()
//    {
//        $db = new DB(self::$logLevel, self::$entityManager);
//        TestUtils::clearDB(self::$schemaTool, self::$classes);
//
//        $dt = new DateTime();
//
//        TestUtils::insertNodeData(self::$entityManager, 'blaID', $dt);
//        TestUtils::insertNodeData(self::$entityManager, 'blaID', $dt);
//        $dt->sub(new DateInterval('PT4H'));
//        TestUtils::insertNodeData(self::$entityManager, 'blaID', $dt);
//        $dt->sub(new DateInterval('PT4H'));
//        TestUtils::insertNodeData(self::$entityManager, 'blaID', $dt);
//        $dt->sub(new DateInterval('PT4H'));
//        TestUtils::insertNodeData(self::$entityManager, 'blaID', $dt);
//        $dt->sub(new DateInterval('PT4H'));
//        TestUtils::insertNodeData(self::$entityManager, 'blaID', $dt);
//        $dt->sub(new DateInterval('PT4H'));
//        TestUtils::insertNodeData(self::$entityManager, 'blaID', $dt);
//        $dt->sub(new DateInterval('PT5H'));
//        TestUtils::insertNodeData(self::$entityManager, 'blaID', $dt);
//
//        $nodeDataRepository = self::$entityManager->getRepository('FFClientGraph\Entities\NodeStats');
//        $qb = $nodeDataRepository->createQueryBuilder('nd');
//        $qb->join('nd.dataTimestamp', 'ds')
//            ->join('nd.node', 'node')
//            ->where('ds.timestamp > :timestamp')
//            ->setParameter('timestamp', self::$dateTime->sub(new DateInterval('PT24H')));
//        $query = $qb->getQuery();
//        $result = $query->getResult();
//
//        $testResult = $db->getNodeData('blaID');
//        self::assertEquals(count($testResult), count($result));
//        self::assertInstanceOf('FFClientGraph\Entities\NodeStats', $testResult[0]);
//        self::assertInstanceOf('FFClientGraph\Entities\Node', $testResult[0]->getNode());
//        self::assertNotNull($testResult[0]->getNode()->getName());
//        self::assertNotEmpty($testResult[0]->getNode()->getName());
//    }
//
//    public function testGetNodes()
//    {
//        $db = new DB(self::$logLevel, self::$entityManager);
//        TestUtils::clearDB(self::$schemaTool, self::$classes);
//
//        for ($i = 0; $i < 10; $i++) {
//            TestUtils::insertNode(self::$entityManager, 'TestNode' . $i);
//        }
//
//        $result = $db->getNodes();
//        self::assertEquals(10, count($result));
//        self::assertInstanceOf('FFClientGraph\Entities\Node', $result[0]);
//    }
//


    public function testSaveNodesSavesAllNodesFromJSON()
    {
        TestUtils::clearDB();
        $nodeData = json_decode(file_get_contents(__DIR__ . '/../../../resources/test_small.json'), true);

        $entityManager = TestUtils::getEntityManager();
        $db = new DB(self::$logLevel, $entityManager);

        $db->saveNodes($nodeData);

        $entityManager->close();

        $entityManager = TestUtils::getEntityManager();
        $nodeRepository = $entityManager->getRepository('FFClientGraph\Entities\Node');
        $result = $nodeRepository->findAll();
        self::assertEquals(5, count($result));
        /** @var Node $node */
        foreach ($result as $node){
            self::assertInstanceOf('FFClientGraph\Entities\NodeStats', $node->getNodeStats()[0]);
        }
        $entityManager->close();

        $entityManager = TestUtils::getEntityManager();
        $entityRepository = $entityManager->getRepository('FFClientGraph\Entities\NodeStats');
        $resultNodeStats = $entityRepository->findAll();
        self::assertEquals(5, count($resultNodeStats));
    }

    public function testDeleteObsoleteNodeData()
    {
        TestUtils::clearDB();

        $entityManager = TestUtils::getEntityManager();
        $timestamp = new DateTimeImmutable();

        for ($i = 0; $i < 48; $i++) {
            $nst = NodeStatsTimestamp::getOrCreate($entityManager, $timestamp);
            $entityManager->persist($nst);
            $timestamp = $timestamp->sub(new DateInterval('PT1H'));
        }
        $entityManager->flush();
        $entityManager->close();

        $entityManager = TestUtils::getEntityManager();
        $db = new DB(self::$logLevel, $entityManager);
        $db->deleteOldNodeData();
        $entityManager->close();

        $entityManager = TestUtils::getEntityManager();
        $dsRepo = $entityManager->getRepository('FFClientGraph\Entities\NodeStatsTimestamp');
        $dsResult = $dsRepo->findAll();
        self::assertGreaterThan(0, count($dsResult));
        self::assertLessThanOrEqual(25, count($dsResult));
    }

    public function testNumberOfTimestamps()
    {
        TestUtils::clearDB();

        $entityManager = TestUtils::getEntityManager();

        $db = new DB(self::$logLevel, $entityManager);
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
        TestUtils::deleteDB();
    }
}