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
     * @ManyToOne(targetEntity="DataTimestamp", inversedBy="nodeStats", cascade={"persist", "remove"})
     * @var DataTimestamp
     */
    protected $dataTimestamp;

    /**
     * @Column(type="decimal",scale=16,precision=17 )
     * @var float
     */
    protected $memoryUsage;

    /**
     * @Column(type="bigint")
     * @var int
     */
    protected $rx_bytes;

    /**
     * @Column(type="bigint")
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
     * @return DataTimestamp
     */
    public function getDataTimestamp()
    {
        return $this->dataTimestamp;
    }

    /**
     * @param DataTimestamp $dataTimestamp
     */
    public function setDataTimestamp($dataTimestamp)
    {
        $this->dataTimestamp = $dataTimestamp;
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
     * @param $nodeDataArray
     * @return NodeStats
     */
    public static function create(Node $node, $nodeDataArray)
    {

        //TODO Implement
        $nodeStats = new NodeStats();
        $nodeStats->setNode($node);
        $nodeStats->setMemoryUsage($nodeDataArray['statistics']['memory_usage']);
        $nodeStats->setClients($nodeDataArray['statistics']['clients']);
        $nodeStats->setRxBytes($nodeDataArray['statistics']['traffic']['rx']['bytes']);
        $nodeStats->setTxBytes($nodeDataArray['statistics']['traffic']['tx']['bytes']);

        return $nodeStats;
    }


}