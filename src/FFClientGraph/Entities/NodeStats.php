<?php

namespace FFClientGraph\Entities;


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
     * @ManyToOne(targetEntity="Node", inversedBy="statData", cascade={"persist"})
     * @JoinColumn(name="node_id", referencedColumnName="nodeId")
     * @var DataTimestamp
     */
    protected $node;

    /**
     * @ManyToOne(targetEntity="DataTimestamp", inversedBy="statData", cascade={"persist", "remove"})
     * @var DataTimestamp
     */
    protected $dataTimestamp;

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


}