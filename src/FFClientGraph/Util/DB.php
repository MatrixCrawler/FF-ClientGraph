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
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Exception;
use FFClientGraph\Config\Config;
use FFClientGraph\Config\Constants;
use FFClientGraph\Entities\DataTimestamp;
use FFClientGraph\Entities\Node;
use FFClientGraph\Entities\NodeStats;
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
     * @var DataTimestamp
     */
    private $dataTimeStamp = null;

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
        $this->dataTimeStamp = null;
    }

    /**
     * Save the given Node-Data
     *
     * @param array $nodeArray An associative Array of Node data
     */
    public function saveSingleNodeData($nodeArray)
    {
        if (!array_key_exists('nodeinfo', $nodeArray)) {
            $this->logger->addError('Wrong JSON data', [get_class()]);
            return;
        }

        $nodeId = $nodeArray['nodeinfo']['node_id'];

        /**
         * Looking for DataTimestamp and store it in class
         * When we are saving multiple nodes, this will save queries as all nodeStat-Data that is saved
         * by calling DB::saveNodes() should have the same DataTimestamp
         */
        if (!$this->dataTimeStamp) {
            $this->dataTimeStamp = $this->getDataTimestamp();
        }

        /**
         * Check if node has already a hostname
         * If not: Set it
         */
        $node = $this->getNode($nodeId);
        if (!$node->getName() or $node->getName() === '') {
            $node->setName($nodeArray['nodeinfo']['hostname']);
        }

        $nodeStatRepository = $this->entityManager->getRepository('FFClientGraph\Entities\NodeStats');

        /**
         * Check if this data is already stored
         */
        $nodeData = $nodeStatRepository->findBy(['dataTimestamp' => $this->dataTimeStamp, 'node' => $node]);
        if (!$nodeData || count($nodeData) <= 0) {
            $nodeData = new NodeStats();
            $nodeData->setClients($nodeArray['statistics']['clients']);
            $nodeData->setDataTimestamp($this->dataTimeStamp);
            $nodeData->setNode($node);
            $this->dataTimeStamp->addStatData($nodeData);
            $node->addStatData($nodeData);
            $this->entityManager->persist($nodeData);
            $this->entityManager->flush($nodeData);
        }
    }

    /**
     * Save the node data from a given nodeData Array
     *
     * @param array $nodeData
     */
    public function saveNodes($nodeData)
    {
        foreach ($nodeData['nodes'] as $nodeDetail) {
            $this->saveSingleNodeData($nodeDetail);
        }

        $this->deleteOldNodeData();
    }

    /**
     * Get list of nodes from DB
     *
     * @return mixed|null
     */
    public function getNodes()
    {
        $nodeRepository = $this->entityManager->getRepository('FFClientGraph\Entities\Node');
        return $nodeRepository->findAll();
    }

    /**
     * Get data for specified node of the last 24hrs
     *
     * @param $nodeID
     * @return array|null An array of NodeStats or null
     */
    public function getNodeData($nodeID)
    {
        $timestampMinus24H = new DateTime($this->timeStamp->format('c'));
        $timestampMinus24H->sub(new DateInterval('PT24H'));

        $nodeDataRepository = $this->entityManager->getRepository('FFClientGraph\Entities\NodeStats');

        $qb = $nodeDataRepository->createQueryBuilder('nd');
        $qb->join('nd.dataTimestamp', 'ds')
            ->join('nd.node', 'node')
            ->where('ds.timestamp > :timestamp')
            ->andWhere('node.nodeId = :nodeId')
            ->orderBy('ds.timestamp', 'ASC')
            ->setParameter('timestamp', $timestampMinus24H)
            ->setParameter('nodeId', $nodeID);
        $query = $qb->getQuery();
        return $query->getResult();
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @return Logger|null
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Get last data timestamp for given nodeId
     *
     * @param $nodeId
     * @return DateTime|null
     */
    public function getLastTimestamp($nodeId)
    {
        $nodeStatsRepository = $this->entityManager->getRepository('FFClientGraph\Entities\NodeStats');
        $qb = $nodeStatsRepository->createQueryBuilder('ns');
        $qb->join('ns.node', 'node')
            ->join('ns.dataTimestamp', 'ds')
            ->where('node.nodeId = :nodeId')
            ->orderBy('ds.timestamp', 'desc')
            ->setMaxResults(1)
            ->setParameter('nodeId', $nodeId);
        $query = $qb->getQuery();
        $result = $query->getResult();
        if ($result && count($result) >= 1) {
            return $result[0]->getDataTimestamp()->getTimestamp();
        }
        return null;
    }

    /**
     * Check if the last data fetch is at least 5 minutes ago
     *
     * @param string|null $nodeId
     * @return bool
     * @deprecated
     */
    private function isLastDataFetchFiveMinutesAgo($nodeId = null)
    {
        $this->logger->addDebug('Check if data fetch is five minutes ago for node ' . $nodeId, [get_class()]);
        $this->logger->addDebug('Current timestamp ' . $this->timeStamp->format('c'), [get_class()]);

        $nodeTimestampRepository = $this->entityManager->getRepository('FFClientGraph\Entities\DataTimestamp');
        $timestampMinusFiveMinutes = new DateTime($this->timeStamp->format('c'));

        $timestampMinusFiveMinutes->sub(new DateInterval('PT5M'));

        $this->logger->addDebug('Timestamp minus 5 minutes ' . $timestampMinusFiveMinutes->format('c'), [get_class()]);

        $qb = $nodeTimestampRepository->createQueryBuilder('dt');
        $qb->orderBy('dt.id', 'DESC')
            ->setMaxResults(1);
        $queryForNewestEntry = $qb->getQuery();
        $newestEntry = $queryForNewestEntry->getResult();
        if (!$newestEntry) {
            return true;
        }
        $this->logger->addDebug('Newest timestamp ' . $newestEntry[0]->getTimestamp()->format('c'), [get_class()]);

        if ($nodeId) {
            $nodeRepo = $this->entityManager->getRepository('FFClientGraph\Entities\Node');
            $node = $nodeRepo->findBy(['nodeId' => $nodeId]);

            if (!$node) {
                return true;
            }

            $this->logger->addDebug('Found node', [get_class()]);

            $nodeDataRepo = $this->entityManager->getRepository('FFClientGraph\Entities\NodeStats');
            $nodeData = $nodeDataRepo->findBy(['node' => $node, 'dataTimestamp' => $newestEntry[0]]);
            if (!$nodeData) {
                return true;
            }
        }

        return $newestEntry[0]->getTimestamp() < $timestampMinusFiveMinutes;
    }

    /**
     * Look for Datatimestamp that is equal to the curent timestamp
     * If non is found, create one else return the existing
     *
     * @return DataTimestamp A new or existing DataTimestamp
     */
    private function getDataTimestamp()
    {
        $this->logger->addDebug('Getting DataTimestamp', [get_class()]);

        $nodeTimestampRepository = $this->entityManager->getRepository('FFClientGraph\Entities\DataTimestamp');
        $result = $nodeTimestampRepository->findBy(['timestamp' => $this->timeStamp]);
        if ($result) {
            return $result[0];
        } else {
            /**
             * There was no timestamp
             */
            $this->logger->addDebug('Creating new DataTimestamp ' . $this->timeStamp->format('c'), [get_class()]);
            $timestamp = new DataTimestamp($this->timeStamp);
            return $timestamp;
        }
    }

    /**
     * Look for a Node with the nodeId $nodeId
     * Create one if none is found
     *
     * @param $nodeId
     * @return Node
     */
    private function getNode($nodeId)
    {
        $nodeTimestampRepository = $this->entityManager->getRepository('FFClientGraph\Entities\Node');
        $result = $nodeTimestampRepository->findBy(['nodeId' => $nodeId]);
        if ($result) {
            return $result[0];
        } else {
            /**
             * There was no timestamp
             */
            $node = new Node();
            $node->setNodeId($nodeId);
            return $node;
        }
    }

    /**
     * Return the number of dataTimestamps that are younger than 24hrs
     * @return int
     */
    public function getNumberOfTimestamps()
    {
        $timestamp = new DateTime($this->timeStamp->format('c'));
        $timestamp = $timestamp->sub(new DateInterval('PT24H'));
        $dataTimestampRepo = $this->entityManager->getRepository('FFClientGraph\Entities\DataTimestamp');
        $qb = $dataTimestampRepo->createQueryBuilder('dts');
        $qb->where('dts.timestamp > :timestamp')
            ->setParameter('timestamp', $timestamp);
        $query = $qb->getQuery();
        $result = $query->getResult();
        return count($result);
    }

    /**
     * Function to delete obsolete data from the database
     * Delete all data that is older than 24hrs
     */
    public function deleteOldNodeData()
    {
        $timestampMinus24H = new DateTime();
        $timestampMinus24H = $timestampMinus24H->sub(new DateInterval('PT24H'));

        $dataTimestampRepository = $this->entityManager->getRepository('FFClientGraph\Entities\NodeStats');
        $queryBuilder = $dataTimestampRepository->createQueryBuilder('ns');
        $queryBuilder->join('ns.dataTimestamp', 'dst')
            ->where('dst.timestamp < :timestamp')
            ->setParameter('timestamp', $timestampMinus24H);
        $query = $queryBuilder->getQuery();
        $result = $query->execute();
        foreach ($result as $nodeStat) {
            $this->entityManager->remove($nodeStat);
        }
        $this->entityManager->flush();
    }

    /**
     * Update the database schema if needed
     */
    public function updateSchema() {
        $classes[] = $this->entityManager->getClassMetadata('FFClientGraph\Entities\Node');
        $classes[] = $this->entityManager->getClassMetadata('FFClientGraph\Entities\NodeStats');
        $classes[] = $this->entityManager->getClassMetadata('FFClientGraph\Entities\DataTimestamp');

        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->updateSchema($classes);
    }
}