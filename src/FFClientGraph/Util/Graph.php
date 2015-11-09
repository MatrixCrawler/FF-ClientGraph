<?php

namespace FFClientGraph\Util;

use CpChart\Classes\pData;
use CpChart\Classes\pImage;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMInvalidArgumentException;
use Exception;
use FFClientGraph\Config\Config;
use FFClientGraph\Config\Constants;
use FFClientGraph\Entities\NodeStats;
use FFClientGraph\Entities\NodeStatsTimestamp;
use InvalidArgumentException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class Graph
 *
 * Function for generating the graphical representation of the "Node Load"
 *
 * @package FFClientGraph\Util
 */
class Graph
{
    private $_maxClients;
    private $_minClients;
    private $_averageClients;
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var int
     */
    private $logLevel;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param int $logLevel
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

        $this->logLevel = $logLevel;
        if (!$entityManager) {
            $this->entityManager = Util::createEntityManager();
        } else {
            $this->entityManager = $entityManager;
        }
    }

    /**
     * Function to create all graphs of nodes that are in the database
     */
    public function createAllGraphs()
    {
        $db = new DB($this->logLevel, $this->entityManager);
        $nodes = $db->getNodes();
        foreach ($nodes as $node) {
            $this->createGraph($node->getNodeId());
        }
    }

    /**
     * Function to create a graph for a given node
     *
     * @param string $nodeID
     */
    public function createGraph($nodeID)
    {
        $this->logger->addDebug('Create Graph :' . $nodeID, [get_class()]);
        $db = new DB($this->logLevel, $this->entityManager);
        $nodeData = $db->getNodeData($nodeID);

        $nodeName = 'Unbekannt';
        if ($nodeData && count($nodeData) >= 1) {
            $nodeName = $db->getExistingNode($nodeID)->getNodeInfo()->getHostname();
            $this->logger->addDebug('Nodename :' . $nodeName, [get_class()]);
        }

        $newestNodeStatsTimestamp = $db->getNewestNodeStatsTimestamp($nodeID);

        $numberOfNodeStatsTimestamps = $db->getNumberOfNodeStatsTimestamps();

        $data = $this->prepareData($nodeData);

        $image = $this->prepareGraph($data, $newestNodeStatsTimestamp, $numberOfNodeStatsTimestamps, $nodeName);
        $image->render(Config::CACHE_FOLDER . '/' . $nodeID . '-clients.png');
    }

    /**
     * Function to prepare the data that is to be shown in the graph
     *
     * @param NodeStats[] $nodeStats An array of NodeStats
     * @return pData
     */
    private function prepareData($nodeStats)
    {
        $this->_maxClients = 0;
        $this->_minClients = 9999999;
        $data = new pData();

        $clientPoints = array();
        $timestampPoints = array();

        if (!$nodeStats || count($nodeStats) === 0) {
            $clientPoints[] = VOID;
            $timestampPoints[] = VOID;
        }
        foreach ($nodeStats as $nodeStat) {
            try {
                $this->entityManager->refresh($nodeStat);
                $clients = $nodeStat->getClients();
                if ($clients > $this->_maxClients) {
                    $this->_maxClients = $clients;
                }
                if ($clients < $this->_minClients) {
                    $this->_minClients = $clients;
                }
                $this->_averageClients += $clients;
                $clientPoints[] = $clients;
                $timestamp = $nodeStat->getStatTimestamp()->getCreated()->setTimezone(new DateTimeZone('Europe/Berlin'))->format('D H:i');
                $timestampPoints[] = $timestamp;
            } catch (ORMInvalidArgumentException $exception) {
                $this->logger->addError('An ORMInvalidArgumentException occured. Switch to DEBUG Loggin for more inforamtion', [get_class()]);
                $this->logger->addError($exception->getCode(), [get_class()]);
                $this->logger->addDebug($exception->getTraceAsString(), [get_class()]);
            }
        }

        $this->_averageClients /= count($clientPoints);

        $data->addPoints($clientPoints, 'Clients');
        $data->addPoints($timestampPoints, 'Timestamp');

        /**
         * Add name to the y axis
         */
        $data->setAxisName(0, 'Connected Clients');

        /**
         * Set Abcissa, Abcissa Name and Abcissa format rules
         */
        $data->setAbscissa('Timestamp');
        $data->setAbscissaName('Time');

        return $data;
    }

    /**
     * Function to prepare the graph
     *
     * @param pData $data
     * @param NodeStatsTimestamp $newestNodeStatsTimestamp
     * @param $numberOfDatatsets
     * @param $nodeName
     * @return pImage
     */
    private function prepareGraph(pData $data, $newestNodeStatsTimestamp, $numberOfDatatsets, $nodeName)
    {
        /**
         * Create new image
         */
        $image = new pImage(Constants::GRAPH_WIDTH, Constants::GRAPH_HEIGHT, $data);

        /**
         * Set image background
         */
        $image->drawGradientArea(0, 0, Constants::GRAPH_WIDTH, Constants::GRAPH_HEIGHT, DIRECTION_VERTICAL, [
            'StartR' => 220,
            'StartG' => 220,
            'StartB' => 220,
            'EndR' => 255,
            'EndG' => 255,
            'EndB' => 255,
            'Alpha' => 100
        ]);

        /**
         * Define graph Area
         */
        $image->setGraphArea(Constants::GRAPH_LEFT_OFFSET, Constants::GRAPH_TOP_OFFSET, Constants::GRAPH_WIDTH - Constants::GRAPH_RIGHT_OFFSET, Constants::GRAPH_HEIGHT - Constants::GRAPH_BOTTOM_OFFSET);

        /**
         * Define Font Properties
         */
        $image->setFontProperties([
            'FontName' => Constants::RESOURCE_PATH . '/ShareTechMono-Regular.ttf',
            'FontSize' => 8
        ]);

        $scaleFormat = [
            'DrawSubTicks' => true,
            'Mode' => SCALE_MODE_START0,
            'XMargin' => 0,
            'CycleBackground' => true
        ];

        $scaleFormat['LabelSkip'] = round($numberOfDatatsets / 6);

        $image->drawScale($scaleFormat);

        $image->drawAreaChart([
            'DisplayValues' => FALSE,
            'DisplayColor' => DISPLAY_MANUAL,
            'DisplayR' => 255,
            'DisplayG' => 0,
            'DisplayB' => 0
        ]);


        $dateTime = new DateTime();

        $lastValidDataTimestamp = $newestNodeStatsTimestamp->getDataTimestamp() !== null ? $newestNodeStatsTimestamp->getDataTimestamp()->format('d.m.Y H:i:s') : 'Unbekannt';
        $image->drawText(Constants::GRAPH_WIDTH - Constants::GRAPH_RIGHT_OFFSET, Constants::GRAPH_HEIGHT - Constants::GRAPH_BOTTOM_OFFSET + 35, "Last valid data: " . $lastValidDataTimestamp, array('FontSize' => 7, "Align" => TEXT_ALIGN_BOTTOMRIGHT));
        $image->drawText(Constants::GRAPH_WIDTH - Constants::GRAPH_RIGHT_OFFSET, Constants::GRAPH_HEIGHT - Constants::GRAPH_BOTTOM_OFFSET + 50, "Image generated: " . $dateTime->format('d.m.Y H:i:s'), array('FontSize' => 7, "Align" => TEXT_ALIGN_BOTTOMRIGHT));
        $image->drawText(Constants::GRAPH_RIGHT_OFFSET, Constants::GRAPH_HEIGHT - Constants::GRAPH_BOTTOM_OFFSET + 40, 'Node: ' . $nodeName, array('FontSize' => 10, 'Align' => TEXT_ALIGN_BOTTOMLEFT));
        $image->drawText(Constants::GRAPH_WIDTH / 2, Constants::GRAPH_HEIGHT - Constants::GRAPH_BOTTOM_OFFSET + 35, "Min/Max clients: " . $this->_minClients . "/" . $this->_maxClients, array('FontSize' => 7, 'Align' => TEXT_ALIGN_BOTTOMMIDDLE));
        $image->drawText(Constants::GRAPH_WIDTH / 2, Constants::GRAPH_HEIGHT - Constants::GRAPH_BOTTOM_OFFSET + 50, "Average clients online: " . round($this->_averageClients, 2), array('FontSize' => 7, 'Align' => TEXT_ALIGN_BOTTOMMIDDLE));

        return $image;
    }

}