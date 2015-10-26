<?php
/**
 * Created by IntelliJ IDEA.
 * User: Johannes Brunswicker
 * Date: 15.10.2015
 * Time: 09:06
 */

namespace FFClientGraph\Util;

use DateInterval;
use DateTime;
use DateTimeZone;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Doctrine\ORM\Tools\Setup;
use Exception;
use FFClientGraph\Config\Config;
use FFClientGraph\Config\Constants;
use FFClientGraph\Entities\Node;
use FFClientGraph\Entities\NodeInfo;
use FFClientGraph\Entities\NodeStats;
use FFClientGraph\Entities\NodeStatsTimestamp;
use InvalidArgumentException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class DB
 *
 * Function for manipulating the stored node data
 *
 * @package FFClientGraph\Util
 */
class DB
{
    /**
     * @var DateTime
     */
    private $timeStamp;

    /**
     * @var NodeStatsTimestamp
     */
    private $nodeStatsTimestamp = null;

    /**
     * @var EntityManager|null The EntityManager used to manipulate the data
     */
    private $entityManager = null;

    /**
     * @var Logger|null
     */
    private $logger = null;

    /**
     * Constructor
     *
     * @param int $logLevel The loglevel you would like to use.
     * @param EntityManager $entityManager
     */
    public function __construct($logLevel = Config::LOGLEVEL, EntityManager $entityManager = null)
    {

        /**
         * Set up Logger
         */
        try {
            $this->logger = new Logger('FFClientLogger');
            $this->logger->pushHandler(new StreamHandler(Constants::LOGPATH, $logLevel));

        } catch (InvalidArgumentException $exception) {
            die('There was an invalid argument exception in ' . get_class() . '\n Please check your configuration.\n' . $exception->getMessage());
        } catch (Exception $exception) {
            die('There was an exception in ' . get_class() . '\n Please check your configuration.\n' . $exception->getMessage());
        }

        if (!$entityManager) {

            /**
             * Setup ORM and EntityManager
             */
            $ORMConfig = Setup::createAnnotationMetadataConfiguration(array(Constants::ENTITY_PATH), Constants::DEVMODE);

            $DBConnection = array(
                'driver' => Config::DB_DRIVER
            );
            switch (Config::DB_DRIVER) {
                case Constants::DB_DRIVER_SQLITE:
                    $DBConnection['path'] = Config::DB_PATH;
                    break;
                case Constants::DB_DRIVER_MYSQL:
                    $mysqlConfig = array(
                        'user' => Config::DB_USER,
                        'password' => Config::DB_PASSWORD,
                        'host' => Config::DB_HOST,
                        'dbname' => Config::DB_NAME
                    );
                    $DBConnection = array_merge($DBConnection, $mysqlConfig);
                    break;
            }

            try {
                $this->entityManager = EntityManager::create($DBConnection, $ORMConfig);
            } catch (ORMException $exception) {
                die('There was an ORMException in ' . get_class() . '\n Please check your configuration.\n' . $exception->getMessage());
            } catch (InvalidArgumentException $exception) {
                die('There was an invalid argument exception in ' . get_class() . '\n Please check your configuration.\n' . $exception->getMessage());
            }
        } else {
            $this->entityManager = $entityManager;
        }
        $this->timeStamp = new DateTime();
        $this->timeStamp->setTimezone(new DateTimeZone('UTC'));
        $this->nodeStatsTimestamp = null;
    }

    /**
     * Save the given Node-Data
     *
     * @param array $nodeDataArray An associative Array of Node data
     * @param DateTime $dataTimestamp the time from the data in the nodes.json
     */
    public function saveSingleNodeData($nodeDataArray, DateTime $dataTimestamp = null)
    {
        if (!array_key_exists('nodeinfo', $nodeDataArray)) {
            $this->logger->addError('Wrong JSON data', [get_class()]);
            return;
        }
        try {
            $this->logger->addDebug('Saving node data for ' . $nodeDataArray['nodeinfo']['node_id'], [get_class()]);
            $this->entityManager->getConnection()->beginTransaction();
            $entityManager = $this->entityManager;

            /**
             * Looking for DataTimestamp and store it in class
             * When we are saving multiple nodes, this will save queries as all nodeStat-Data that is saved
             * by calling DB::saveNodes() should have the same DataTimestamp
             */
            if ($this->nodeStatsTimestamp === null) {
                $created = new DateTime();
                $this->logger->addDebug('Creating new nodeStatsTimestamp', [get_class()]);
                $this->nodeStatsTimestamp = NodeStatsTimestamp::getOrCreate($entityManager, $created, $dataTimestamp);
                $this->logger->addDebug('NodeStats created: ' . $this->nodeStatsTimestamp->getCreated()->format('c'), [get_class()]);
                $this->logger->addDebug('NodeStats data timestamp: ' . $this->nodeStatsTimestamp->getDataTimestamp()->format('c'), [get_class()]);
            }

            $this->logger->addDebug('Creating node entity', [get_class()]);
            $node = Node::getOrCreate($entityManager, $nodeDataArray);

            $this->logger->addDebug('Creating nodeInfo entity', [get_class()]);
            NodeInfo::create($entityManager, $node, $nodeDataArray);

            $this->logger->addDebug('Creating nodeStats entity', [get_class()]);
            $nodeStats = NodeStats::create($node, $this->nodeStatsTimestamp, $nodeDataArray);
            $node->addNodeStats($nodeStats);

            $this->logger->addDebug('Persisting data', [get_class()]);
            $entityManager->persist($nodeStats);
            $entityManager->flush($nodeStats);
            $this->entityManager->getConnection()->commit();
        } catch (ORMInvalidArgumentException $exception) {
            $this->logger->addError('There was an ORMInvalidArgumentException. Switch to debug level for more information', [get_class()]);
            $this->logger->addError($exception->getCode());
            $this->logger->addDebug($exception->getTraceAsString());
        } catch (OptimisticLockException $exception) {
            $this->logger->addError('There was an OptimisticLockException. Switch to debug level for more information', [get_class()]);
            $this->logger->addError($exception->getCode());
            $this->logger->addDebug($exception->getTraceAsString());
        } catch (ConnectionException $exception) {
            $this->logger->addError('There was an OptimisticLockException. Switch to debug level for more information', [get_class()]);
            $this->logger->addError($exception->getCode());
            $this->logger->addDebug($exception->getTraceAsString());
        }
    }

    /**
     * Save the node data from a given nodeData Array
     *
     * @param array $nodeData
     */
    public function saveNodes($nodeData)
    {
        $this->logger->addDebug('Reading dataTimestamp from nodes.json', [get_class()]);
        if (!array_key_exists('timestamp', $nodeData)) {
            $dataTimestamp = null;
            $this->logger->addNotice('No dataTimestamp in nodes.json', [get_class()]);
        } else {
            $this->logger->addDebug('Create timestamp from nodes.json data', [get_class()]);
            $dataTimestamp = new DateTime($nodeData['timestamp']);
            $this->logger->addDebug('Timestamp: ' . $dataTimestamp->format('c'), [get_class()]);
        }

        foreach ($nodeData['nodes'] as $nodeDetail) {
            $this->saveSingleNodeData($nodeDetail, $dataTimestamp);
        }

        /**
         * Clear up old dataset
         */
        $this->deleteOldNodeData();
    }
//
//    /**
//     * Get list of nodes from DB
//     *
//     * @return Node[]|null
//     */
//    public function getNodes()
//    {
//        $nodeRepository = $this->entityManager->getRepository('FFClientGraph\Entities\Node');
//        return $nodeRepository->findAll();
//    }
//
//    /**
//     * Get data for specified node of the last 24hrs
//     *
//     * @param $nodeID
//     * @return NodeStats[]|null An array of NodeStats or null
//     */
//    public function getNodeData($nodeID)
//    {
//        $timestampMinus24H = new DateTime($this->timeStamp->format('c'));
//        $timestampMinus24H->sub(new DateInterval('PT24H'));
//
//        $nodeDataRepository = $this->entityManager->getRepository('FFClientGraph\Entities\NodeStats');
//
//        $qb = $nodeDataRepository->createQueryBuilder('nd');
//        $qb->join('nd.dataTimestamp', 'ds')
//            ->join('nd.node', 'node')
//            ->where('ds.timestamp > :timestamp')
//            ->andWhere('node.nodeId = :nodeId')
//            ->orderBy('ds.timestamp', 'ASC')
//            ->setParameter('timestamp', $timestampMinus24H)
//            ->setParameter('nodeId', $nodeID);
//        $query = $qb->getQuery();
//        return $query->getResult();
//    }

//    /**
//     * @return Logger|null
//     */
//    public function getLogger()
//    {
//        return $this->logger;
//    }

//    /**
//     * Get last data timestamp for given nodeId
//     *
//     * @param $nodeId
//     * @return DateTime|null
//     */
//    public function getLastTimestamp($nodeId)
//    {
//        $nodeStatsRepository = $this->entityManager->getRepository('FFClientGraph\Entities\NodeStats');
//        $qb = $nodeStatsRepository->createQueryBuilder('ns');
//        $qb->join('ns.node', 'node')
//            ->join('ns.dataTimestamp', 'ds')
//            ->where('node.nodeId = :nodeId')
//            ->orderBy('ds.timestamp', 'desc')
//            ->setMaxResults(1)
//            ->setParameter('nodeId', $nodeId);
//        $query = $qb->getQuery();
//        $result = $query->getResult();
//        if ($result && count($result) >= 1) {
//            return $result[0]->getOrCreateDataTimestamp()->getTimestamp();
//        }
//        return null;
//    }

//    /**
//     * Look for Datatimestamp that is equal to the curent timestamp
//     * If non is found, create one else return the existing
//     *
//     * @param DateTimeInterface $dataTimestamp The timestamp the nodes.json is signed with
//     * @return DataTimestamp A new or existing DataTimestamp
//     */
//    private function getOrCreateDataTimestamp(DateTimeInterface $dataTimestamp)
//    {
//        $this->logger->addDebug('Getting DataTimestamp', [get_class()]);
//
//        $nodeTimestampRepository = $this->entityManager->getRepository('FFClientGraph\Entities\DataTimestamp');
//        $result = $nodeTimestampRepository->findBy(['timestamp' => $this->timeStamp]);
//        if ($result) {
//            return $result[0];
//        } else {
//            /**
//             * There was no timestamp
//             */
//            $this->logger->addDebug('Creating new DataTimestamp ' . $this->timeStamp->format('c'), [get_class()]);
//            $timestamp = new DataTimestamp($this->timeStamp, $dataTimestamp);
//            return $timestamp;
//        }
//    }

//    /**
//     * Look for a Node with the nodeId $nodeId
//     * Create one if none is found
//     *
//     * @param $nodeId
//     * @return Node
//     */
//    private function getOrCreateNode($nodeId)
//    {
//        $nodeTimestampRepository = $this->entityManager->getRepository('FFClientGraph\Entities\Node');
//        $result = $nodeTimestampRepository->findBy(['nodeId' => $nodeId]);
//        if ($result) {
//            return $result[0];
//        } else {
//            /**
//             * There was no node
//             */
//            $node = new Node();
//            $node->setNodeId($nodeId);
//            return $node;
//        }
//    }

//    /**
//     * Return existing node
//     *
//     * @param string $nodeId
//     * @return Node|null
//     */
//    public function getExistingNode($nodeId)
//    {
//        $nodeTimestampRepository = $this->entityManager->getRepository('FFClientGraph\Entities\Node');
//        $result = $nodeTimestampRepository->findBy(['nodeId' => $nodeId]);
//        if ($result) {
//            return $result[0];
//        }
//        return null;
//    }
//
//    /**
//     * Return the number of dataTimestamps that are younger than 24hrs
//     * @return int
//     */
//    public function getNumberOfTimestamps()
//    {
//        $timestamp = new DateTime($this->timeStamp->format('c'));
//        $timestamp = $timestamp->sub(new DateInterval('PT24H'));
//        $dataTimestampRepo = $this->entityManager->getRepository('FFClientGraph\Entities\DataTimestamp');
//        $qb = $dataTimestampRepo->createQueryBuilder('dts');
//        $qb->where('dts.timestamp > :timestamp')
//            ->setParameter('timestamp', $timestamp);
//        $query = $qb->getQuery();
//        $result = $query->getResult();
//        return count($result);
//    }
//
    /**
     * Function to delete obsolete data from the database
     * Delete all data that is older than 24hrs
     */
    public function deleteOldNodeData()
    {
        $timestampMinus24H = new DateTime();
        $timestampMinus24H = $timestampMinus24H->sub(new DateInterval('PT24H'));

        $dataTimestampRepository = $this->entityManager->getRepository('FFClientGraph\Entities\NodeStatsTimestamp');
        $queryBuilder = $dataTimestampRepository->createQueryBuilder('nst');
        $queryBuilder
            ->where('nst.created < :timestamp')
            ->setParameter('timestamp', $timestampMinus24H);
        $query = $queryBuilder->getQuery();
        $result = $query->execute();
        foreach ($result as $nodeStatsTimestamp) {
            $this->entityManager->remove($nodeStatsTimestamp);
        }
        $this->entityManager->flush();
    }
//
//    /**
//     * Update the database schema if needed
//     */
//    public function updateSchema()
//    {
//        $classes[] = $this->entityManager->getClassMetadata('FFClientGraph\Entities\Node');
//        $classes[] = $this->entityManager->getClassMetadata('FFClientGraph\Entities\NodeStats');
//        $classes[] = $this->entityManager->getClassMetadata('FFClientGraph\Entities\DataTimestamp');
//
//        $schemaTool = new SchemaTool($this->entityManager);
//        $schemaTool->updateSchema($classes);
//    }
}