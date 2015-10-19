<?php
/**
 * Created by IntelliJ IDEA.
 * User: Johannes Brunswicker
 * Date: 13.10.2015
 * Time: 15:25
 */

namespace FFClientGraph\Entities;


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
     * @return mixed
     */
    public function getClients()
    {
        return $this->clients;
    }

    /**
     * @param mixed $clients
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