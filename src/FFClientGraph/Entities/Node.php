<?php
/**
 * Created by IntelliJ IDEA.
 * User: Johannes Brunswicker
 * Date: 12.10.2015
 * Time: 16:01
 */

namespace FFClientGraph\Entities;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="node")
 */
class Node
{

    /**
     * @Id
     * @Column(type="string", unique=true)
     */
    protected $nodeId;

    /**
     * @OneToOne(targetEntity="NodeInfo", mappedBy="node")
     * @JoinColumn(name="nodeinfo_id", referencedColumnName="id", nullable=true)
     * @var NodeInfo
     */
    protected $nodeInfo;

    /**
     * @OneToMany(targetEntity="NodeStats", mappedBy="node", cascade={"persist","remove"},)
     * @var NodeStats[]
     */
    protected $nodeStats;

    /**
     * @return mixed
     */
    public function getNodeId()
    {
        return $this->nodeId;
    }

    /**
     * @param mixed $nodeId
     */
    public function setNodeId($nodeId)
    {
        $this->nodeId = $nodeId;
    }

    /**
     * @return mixed
     */
    public function getNodeStats()
    {
        return $this->nodeStats;
    }

    /**
     * @param NodeStats $nodeData
     */
    public function addStatData(NodeStats $nodeData)
    {
        $this->nodeStats[] = $nodeData;
    }

    /**
     * @return NodeInfo
     */
    public function getNodeInfo()
    {
        return $this->nodeInfo;
    }

    /**
     * @param NodeInfo $nodeInfo
     */
    public function setNodeInfo($nodeInfo)
    {
        $this->nodeInfo = $nodeInfo;
    }

}