<?php
/**
 * Created by IntelliJ IDEA.
 * User: Johannes Brunswicker
 * Date: 12.10.2015
 * Time: 16:01
 */

namespace FFClientGraph\Entities;

use Doctrine\ORM\Mapping;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
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
     * @Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @OneToMany(targetEntity="NodeStats", mappedBy="node", cascade={"persist","remove"},)
     */
    protected $statData;

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
    public function getStatData()
    {
        return $this->statData;
    }

    /**
     * @param NodeStats $nodeData
     */
    public function addStatData(NodeStats $nodeData)
    {
        $this->statData[] = $nodeData;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

}