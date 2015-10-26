<?php

namespace FFClientGraph\Util;

use CpChart\Classes\pData;
use CpChart\Classes\pImage;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManager;
use Exception;
use FFClientGraph\Config\Config;
use FFClientGraph\Config\Constants;
use FFClientGraph\Entities\NodeStats;
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
        $this->entityManager = $entityManager;

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
        $this->logger->addDebug("Create Graph :" . $nodeID, [get_class()]);
        $db = new DB($this->logLevel, $this->entityManager);
        $nodeData = $db->getNodeData($nodeID);

        $nodeName = 'Unbekannt';
        if ($nodeData && count($nodeData) >= 1) {
            $nodeName = $db->getExistingNode($nodeID)->getName();
            $this->logger->addDebug("Nodename :" . $nodeName, [get_class()]);
        }

        $lastTimestamp = $db->getLastTimestamp($nodeID);

        $numberOfStamps = $db->getNumberOfTimestamps();

        $data = $this->prepareData($nodeData);
        $image = $this->prepareGraph($data, $lastTimestamp, $numberOfStamps, $nodeName);
        $image->render(Config::CACHE_FOLDER . '/' . $nodeID . '-clients.png');
    }

    /**
     * Function to prepare the data that is to be shown in the graph
     *
     * @param NodeStats[] $nodeData An array of NodeStats
     * @return pData
     */
    private function prepareData($nodeData)
    {
        $data = new pData();

        $clientPoints = array();
        $labelPoints = array();

        if (!$nodeData || count($nodeData) === 0) {
            $clientPoints[] = VOID;
            $labelPoints[] = VOID;
        }
        foreach ($nodeData as $nodeStat) {
            $clientPoints[] = $nodeStat->getClients();
            $timestamp = $nodeStat->getStatTimestamp()->getCreated()->setTimezone(new DateTimeZone('Europe/Berlin'))->format('D H:i');
            $labelPoints[] = $timestamp;
        }

        $data->addPoints($clientPoints, 'Clients');
        $data->addPoints($labelPoints, 'Timestamp');

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
     * @param DateTime|null $lastTimestamp
     * @param $skipLabel
     * @param $nodeName
     * @return pImage
     * @throws Exception
     */
    private function prepareGraph(pData $data, $lastTimestamp, $skipLabel, $nodeName)
    {
        /**
         * Create new image
         */
        $image = new pImage(Constants::GRAPH_WIDTH, Constants::GRAPH_HEIGHT, $data);

        if (!$lastTimestamp) {
            $lastTimestamp = new DateTime();
        }

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

        $scaleFormat['LabelSkip'] = round($skipLabel / 6);

        $image->drawScale($scaleFormat);

        $image->drawAreaChart([
            'DisplayValues' => FALSE,
            'DisplayColor' => DISPLAY_MANUAL,
            'DisplayR' => 255,
            'DisplayG' => 0,
            'DisplayB' => 0
        ]);


        $dateTime = new DateTime();
        $dateTime->setTimezone(new DateTimeZone('UTC'));

        $image->drawText(Constants::GRAPH_WIDTH - Constants::GRAPH_RIGHT_OFFSET, Constants::GRAPH_HEIGHT - Constants::GRAPH_BOTTOM_OFFSET + 35, "Last valid data: " . $lastTimestamp->format('d.m.Y H:i:s'), array('FontSize' => 7, "Align" => TEXT_ALIGN_BOTTOMRIGHT));
        $image->drawText(Constants::GRAPH_WIDTH - Constants::GRAPH_RIGHT_OFFSET, Constants::GRAPH_HEIGHT - Constants::GRAPH_BOTTOM_OFFSET + 50, "Image generated: " . $dateTime->format('d.m.Y H:i:s'), array('FontSize' => 7, "Align" => TEXT_ALIGN_BOTTOMRIGHT));
        $image->drawText(Constants::GRAPH_RIGHT_OFFSET, Constants::GRAPH_HEIGHT - Constants::GRAPH_BOTTOM_OFFSET + 40, 'Node: ' . $nodeName, array('FontSize' => 10, 'Align' => TEXT_ALIGN_BOTTOMLEFT));

        return $image;
    }

}