<?php

namespace FFClientGraph\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;


/**
 * @Entity
 * @Table(name="node_stats")
 */
class NodeStats
{

    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;


    /**
     * @Column(type="integer")
     */
    protected $clients;

    /**
     * @ManyToOne(targetEntity="Node", inversedBy="nodeStats", cascade={"persist"})
     * @JoinColumn(name="node_id", referencedColumnName="nodeId")
     * @var Node
     */
    protected $node;

    /**
     * @ManyToOne(targetEntity="NodeStatsTimestamp", inversedBy="nodeStats", cascade={"persist"}, fetch="EAGER")
     * @JoinColumn(name="timestamp_id", referencedColumnName="id")
     * @var NodeStatsTimestamp
     */
    protected $statTimestamp;

    /**
     * @Column(type="decimal",scale=16,precision=17, nullable=true)
     * @var float
     */
    protected $memoryUsage;

    /**
     * @Column(type="bigint", nullable=true)
     * @var int
     */
    protected $rx_bytes;

    /**
     * @Column(type="bigint", nullable=true)
     * @var int
     */
    protected $tx_bytes;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getClients()
    {
        return $this->clients;
    }

    /**
     * @param int $clients
     */
    public function setClients($clients)
    {
        $this->clients = $clients;
    }

    /**
     * @return Node
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * @param Node $node
     */
    public function setNode($node)
    {
        $this->node = $node;
    }

    /**
     * @return NodeStatsTimestamp
     */
    public function getStatTimestamp()
    {
        return $this->statTimestamp;
    }

    /**
     * @param NodeStatsTimestamp $statTimestamp
     */
    public function setStatTimestamp($statTimestamp)
    {
        $this->statTimestamp = $statTimestamp;
    }

    /**
     * @return mixed
     */
    public function getMemoryUsage()
    {
        return $this->memoryUsage;
    }

    /**
     * @param mixed $memoryUsage
     */
    public function setMemoryUsage($memoryUsage)
    {
        $this->memoryUsage = $memoryUsage;
    }

    /**
     * @return mixed
     */
    public function getRxBytes()
    {
        return $this->rx_bytes;
    }

    /**
     * @param mixed $rx_bytes
     */
    public function setRxBytes($rx_bytes)
    {
        $this->rx_bytes = $rx_bytes;
    }

    /**
     * @return mixed
     */
    public function getTxBytes()
    {
        return $this->tx_bytes;
    }

    /**
     * @param mixed $tx_bytes
     */
    public function setTxBytes($tx_bytes)
    {
        $this->tx_bytes = $tx_bytes;
    }


    /**
     *
     * @param Node $node
     * @param NodeStatsTimestamp $nodeStatsTimestamp
     * @param $nodeDataArray
     * @return NodeStats
     */
    public static function create(Node $node, NodeStatsTimestamp $nodeStatsTimestamp, $nodeDataArray)
    {
        $nodeStats = new NodeStats();
        $nodeStats->setNode($node);
        $nodeStats->setMemoryUsage(isset($nodeDataArray['statistics']['memory_usage'])?$nodeDataArray['statistics']['memory_usage']:null);
        $nodeStats->setClients(isset($nodeDataArray['statistics']['clients'])?$nodeDataArray['statistics']['clients']:null);
        $nodeStats->setRxBytes(isset($nodeDataArray['statistics']['traffic']['rx']['bytes'])?$nodeDataArray['statistics']['traffic']['rx']['bytes']:null);
        $nodeStats->setTxBytes(isset($nodeDataArray['statistics']['traffic']['tx']['bytes'])?$nodeDataArray['statistics']['traffic']['tx']['bytes']:null);

        $nodeStats->setStatTimestamp($nodeStatsTimestamp);
        $nodeStatsTimestamp->addStatData($nodeStats);

        $node->addNodeStats($nodeStats);

        return $nodeStats;
    }


}