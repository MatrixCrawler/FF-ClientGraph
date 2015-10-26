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
        /** @var Node $result */
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

    /**
     * @depends testSaveSingleNode
     */
    public function testGetNodeData()
    {
        TestUtils::clearDB();
        $entityManager = TestUtils::getEntityManager();
        $db = new DB(self::$logLevel, $entityManager);

        $dt = new DateTime();
        $dateTime = new DateTime();

        TestUtils::insertNodeData($entityManager, 'blaID', $dt);
        TestUtils::insertNodeData($entityManager, 'blaID', $dt);
        $dt->sub(new DateInterval('PT4H'));
        TestUtils::insertNodeData($entityManager, 'blaID', $dt);
        $dt->sub(new DateInterval('PT4H'));
        TestUtils::insertNodeData($entityManager, 'blaID', $dt);
        $dt->sub(new DateInterval('PT4H'));
        TestUtils::insertNodeData($entityManager, 'blaID', $dt);
        $dt->sub(new DateInterval('PT4H'));
        TestUtils::insertNodeData($entityManager, 'blaID', $dt);
        $dt->sub(new DateInterval('PT4H'));
        TestUtils::insertNodeData($entityManager, 'blaID', $dt);
        $dt->sub(new DateInterval('PT5H'));
        TestUtils::insertNodeData($entityManager, 'blaID', $dt);

        $nodeDataRepository = $entityManager->getRepository('FFClientGraph\Entities\NodeStats');
        $qb = $nodeDataRepository->createQueryBuilder('nd');
        $qb->join('nd.statTimestamp', 'ds')
            ->join('nd.node', 'node')
            ->where('ds.created > :timestamp')
            ->andWhere('node.nodeId = :nodeId')
            ->setParameter('timestamp', $dateTime->sub(new DateInterval('PT24H')))
            ->setParameter('nodeId', 'blaID');
        $query = $qb->getQuery();
        $result = $query->getResult();

        $testResult = $db->getNodeData('blaID');
        self::assertEquals(count($testResult), count($result));
        self::assertInstanceOf('FFClientGraph\Entities\NodeStats', $testResult[0]);
        self::assertInstanceOf('FFClientGraph\Entities\Node', $testResult[0]->getNode());
        self::assertNotNull($testResult[0]->getNode()->getNodeInfo());
        self::assertNotEmpty($testResult[0]->getNode()->getNodeInfo()->getHostname());
    }

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
        foreach ($result as $node) {
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

    public function testGetExistingNodeIsNotNull()
    {
        TestUtils::clearDB();
        $entityManager = TestUtils::getEntityManager();
        TestUtils::insertNodeData($entityManager, 'blaID', new DateTime());

        $db = new DB(self::$logLevel, $entityManager);
        $node = $db->getExistingNode('blaID');

        self::assertNotNull($node);
    }

    public function testGetExistingNodeIsNullIfNodeDoesNotExist()
    {
        TestUtils::clearDB();
        $entityManager = TestUtils::getEntityManager();

        $db = new DB(self::$logLevel, $entityManager);
        $node = $db->getExistingNode('blaID');

        self::assertNull($node);
    }

    public function testGetNewestNodeStatsTimestamp()
    {
        TestUtils::clearDB();
        $entityManager = TestUtils::getEntityManager();

        $dateTime = new DateTimeImmutable();

        for ($i = 0; $i < 50; $i++) {
            $modifiedDateTime = new DateTime($dateTime->sub(new DateInterval('PT' . $i . 'M'))->format('c'));
            TestUtils::insertNodeData($entityManager, 'TestNode', $modifiedDateTime);
        }
        $db = new DB(self::$logLevel, $entityManager);
        $result = $db->getNewestNodeStatsTimestamp('TestNode');
        self::assertNotNull($result);
        self::assertInstanceOf('FFClientGraph\Entities\NodeStatsTimestamp', $result);
        self::assertEquals($dateTime->format('c'), $result->getCreated()->format('c'));
    }

    public function testGetNumberOfNodeStatsTimestamps()
    {
        TestUtils::clearDB();
        $entityManager = TestUtils::getEntityManager();

        $dateTime = new DateTimeImmutable();

        for ($i = 0; $i < 50; $i++) {
            $modifiedDateTime = new DateTime($dateTime->sub(new DateInterval('PT' . $i . 'M'))->format('c'));
            TestUtils::insertNodeData($entityManager, 'TestNode', $modifiedDateTime);
        }
        $db = new DB(self::$logLevel, $entityManager);
        $result = $db->getNumberOfNodeStatsTimestamps();
        self::assertNotNull($result);
        self::assertEquals(50, $result);
    }

    public function testGetNodes() {
        TestUtils::clearDB();
        $entityManager = TestUtils::getEntityManager();
        $dateTime = new DateTimeImmutable();

        for ($i = 0; $i < 50; $i++) {
            TestUtils::insertNodeData($entityManager, 'TestNode_'.$i, $dateTime);
        }
        $db = new DB(self::$logLevel, $entityManager);
        $result = $db->getNodes();
        self::assertNotNull($result);
        self::assertEquals(50, count($result));
    }

    public static function tearDownAfterClass()
    {
        TestUtils::deleteDB();
    }
}