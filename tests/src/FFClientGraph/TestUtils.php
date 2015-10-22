<?php
/**
 * Created by IntelliJ IDEA.
 * User: Johannes Brunswicker
 * Date: 15.10.2015
 * Time: 13:48
 */

namespace FFClientGraph;


use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use FFClientGraph\Config\Constants;
use FFClientGraph\Entities\NodeStatsTimestamp;
use FFClientGraph\Entities\Hardware;
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
     *
     */
    public static function insertNode(EntityManager $entityManager, $nodeId)
    {
        $node = new Node();
        $node->setNodeId($nodeId);
        $entityManager->persist($node);
        $entityManager->flush($node);
    }

//    /**
//     * @param EntityManager $em
//     * @param String $nodeId
//     * @param DateTime $timestamp
//     */
//    public static function insertNodeData(EntityManager $em, $nodeId, $timestamp)
//    {
//        //TODO Adapt to new Scheme
//        $nodeRepo = $em->getRepository('FFClientGraph\Entities\Node');
//        $result = $nodeRepo->findBy(['nodeId' => $nodeId]);
//        if ($result && count($result) >= 1) {
//            $node = $result[0];
//        } else {
//            $node = new Node();
//            $node->setNodeId($nodeId);
//            $node->setName($nodeId);
//        }
//
//        $statData = new NodeStats();
//        $statData->setClients(mt_rand(0, 8));
//        $statData->setNode($node);
//
//        $dataTimestamp = new DataTimestamp($timestamp);
//        $dataTimestamp->addStatData($statData);
//
//        $statData->setDataTimestamp($dataTimestamp);
//
//        $node->addNodeStats($statData);
//
//        $em->persist($statData);
//        $em->flush($statData);
//    }

    /**
     * Set up entity classes
     *
     * @param EntityManager $entityManager
     * @return array
     */
    public static function setUpClasses(EntityManager $entityManager)
    {
        $classes = array();

        $classes[] = $entityManager->getClassMetadata('FFClientGraph\Entities\Node');
        $classes[] = $entityManager->getClassMetadata('FFClientGraph\Entities\NodeStats');
        $classes[] = $entityManager->getClassMetadata('FFClientGraph\Entities\NodeStatsTimestamp');
        $classes[] = $entityManager->getClassMetadata('FFClientGraph\Entities\Hardware');
        $classes[] = $entityManager->getClassMetadata('FFClientGraph\Entities\NodeInfo');

        return $classes;
    }

    /**
     * Set up connection
     *
     * @return array
     */
    public static function setUpConnection()
    {
        $DBConnection = array(
            'driver' => Constants::DB_DRIVER_SQLITE
        );
        $DBConnection['path'] = __DIR__ . '/../../resources/test.sqlite.db';

        return $DBConnection;
    }

    /**
     * @param EntityManager $entityManager
     * @param string $name
     */
    public static function insertHardware(EntityManager $entityManager, $name)
    {
        $hardware = new Hardware();
        $hardware->setModel($name);
        $entityManager->persist($hardware);
        $entityManager->flush($hardware);
    }

    /**
     * @param EntityManager $entityManager
     * @param DateTimeInterface $timestamp
     * @param DateTimeInterface $dataTime
     */
    public static function insertDataTimestamp(EntityManager $entityManager, DateTimeInterface $timestamp, DateTimeInterface $dataTime = null)
    {
        $dataTimestamp = new NodeStatsTimestamp();
//        $dataTimestamp = new NodeStatsTimestamp($timestamp, $dataTime);
        $dataTimestamp->setTimestamp($timestamp);
        $dataTimestamp->setDataDateTime($dataTime);

        $entityManager->persist($dataTimestamp);
        $entityManager->flush($dataTimestamp);

        return $dataTimestamp;
    }
}