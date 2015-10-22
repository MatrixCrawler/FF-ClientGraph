<?php
/**
 * Created by IntelliJ IDEA.
 * User: Johannes Brunswicker
 * Date: 15.10.2015
 * Time: 13:48
 */

namespace FFClientGraph;


use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use FFClientGraph\Config\Constants;
use FFClientGraph\Entities\Hardware;
use FFClientGraph\Entities\Node;
use FFClientGraph\Entities\NodeStatsTimestamp;

class TestUtils
{

    /**
     * Clear database for testing
     */
    public static function clearDB()
    {
        $em = self::getEntityManager();
        $schemaTool = new SchemaTool($em);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema(self::setUpClasses($em));
        $em->close();
    }

    /**
     * @param String $nodeId
     */
    public static function insertNode($nodeId)
    {
        $node = new Node();
        $node->setNodeId($nodeId);
        $entityManager = self::getEntityManager();
        $entityManager->persist($node);
        $entityManager->flush($node);
    }

    public static function getEntityManager()
    {
        /**
         * Setup ORM and EntityManager
         */
        $ORMConfig = Setup::createAnnotationMetadataConfiguration(array(Constants::ENTITY_PATH), true);

        $DBConnection = TestUtils::setUpConnection();
        return EntityManager::create($DBConnection, $ORMConfig);
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
     * Delete the DB
     */
    public static function deleteDB()
    {
        $dbFilename = __DIR__ . '/../../resources/test.sqlite.db';
        if (file_exists($dbFilename)) {
            unlink($dbFilename);
        }
    }

    /**
     * @param string $name
     */
    public static function insertHardware($name)
    {
        $hardware = new Hardware();
        $hardware->setModel($name);
        $entityManager = self::getEntityManager();
        $entityManager->persist($hardware);
        $entityManager->flush($hardware);
    }

    /**
     * @param DateTime|DateTimeImmutable $timestamp
     * @param DateTime|DateTimeImmutable $dataTime
     */
    public static function insertDataTimestamp($timestamp, $dataTime = null)
    {
        $dataTimestamp = new NodeStatsTimestamp();
        $dataTimestamp->setTimestamp($timestamp);
        $dataTimestamp->setDataDateTime($dataTime);

        $entityManager = self::getEntityManager();
        $entityManager->persist($dataTimestamp);
        $entityManager->flush($dataTimestamp);
        $entityManager->close();
    }
}