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
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use FFClientGraph\Config\Constants;
use FFClientGraph\Entities\Hardware;
use FFClientGraph\Entities\Node;
use FFClientGraph\Entities\NodeInfo;
use FFClientGraph\Entities\NodeStats;
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
     * @param $nodeDataArray
     */
    public static function insertNode($nodeDataArray)
    {
        $node = new Node();
        $node->setNodeId($nodeDataArray['nodeinfo']['node_id']);
        NodeInfo::create(self::getEntityManager(), $node, $nodeDataArray);
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

    /**
     * @param EntityManager $em
     * @param String $nodeId
     * @param DateTime $timestamp
     */
    public static function insertNodeData(EntityManager $em, $nodeId, $timestamp)
    {
        try {

            $nodeRepo = $em->getRepository('FFClientGraph\Entities\Node');
            $node = $nodeRepo->findOneBy(['nodeId' => $nodeId]);
            if (!$node) {
                $node = new Node();
                $node->setNodeId($nodeId);
            }

            $dataTimestamp = NodeStatsTimestamp::getOrCreate($em, $timestamp, $timestamp);

            $nodeDataArray['statistics']['memory_usage'] = mt_rand(0, 100) / 100;
            $nodeDataArray['statistics']['clients'] = mt_rand(0, 20);
            $nodeDataArray['statistics']['traffic']['rx']['bytes'] = 0;
            $nodeDataArray['statistics']['traffic']['tx']['bytes'] = 0;

            $statData = NodeStats::create($node, $dataTimestamp, $nodeDataArray);

            $nodeInfoArray['nodeinfo']['hostname'] = $nodeId;
            $nodeInfoArray['nodeinfo']['hardware']['model'] = 'testmodel';

            NodeInfo::create($em, $node, $nodeInfoArray);

            $em->persist($statData);
            $em->flush($statData);
        } catch (ORMInvalidArgumentException $exception) {

        } catch (OptimisticLockException $exception) {

        }
    }

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
        $dataTimestamp->setCreated($timestamp);
        $dataTimestamp->setDataTimestamp($dataTime);

        $entityManager = self::getEntityManager();
        $entityManager->persist($dataTimestamp);
        $entityManager->flush($dataTimestamp);
        $entityManager->close();
    }
}