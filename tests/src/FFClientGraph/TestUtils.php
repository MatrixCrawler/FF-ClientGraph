<?php
/**
 * Created by IntelliJ IDEA.
 * User: Johannes Brunswicker
 * Date: 15.10.2015
 * Time: 13:48
 */

namespace FFClientGraph;


use DateTimeInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use FFClientGraph\Entities\DataTimestamp;
use FFClientGraph\Entities\Node;
use FFClientGraph\Entities\NodeStats;

class TestUtils
{

    /**
     * Clear database for testing
     *
     * @param SchemaTool $schemaTool
     * @param array $classes
     */
    public static function clearDB(SchemaTool $schemaTool, $classes)
    {
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($classes);
    }

    /**
     * @param EntityManager $entityManager
     * @param String $nodeId
     */
    public static function insertNode(EntityManager $entityManager, $nodeId)
    {
        $node = new Node();
        $node->setNodeId($nodeId);
        $entityManager->persist($node);
        $entityManager->flush($node);
    }

    /**
     * @param EntityManager $em
     * @param String $nodeId
     * @param DateTimeInterface $timestamp
     */
    public static function insertNodeData(EntityManager $em, $nodeId, $timestamp)
    {
        $nodeRepo = $em->getRepository('FFClientGraph\Entities\Node');
        $result = $nodeRepo->findBy(['nodeId' => $nodeId]);
        if ($result && count($result) >= 1) {
            $node = $result[0];
        } else {
            $node = new Node();
            $node->setNodeId($nodeId);
        }

        $statData = new NodeStats();
        $statData->setClients(mt_rand(0, 8));
        $statData->setNode($node);

        $dataTimestamp = new DataTimestamp($timestamp);
        $dataTimestamp->addStatData($statData);

        $statData->setDataTimestamp($dataTimestamp);

        $node->addStatData($statData);

        $em->persist($statData);
        $em->flush($statData);
    }
}