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
     * @var string
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
     * @return string
     */
    public function getNodeId()
    {
        return $this->nodeId;
    }

    /**
     * @param string $nodeId
     */
    public function setNodeId($nodeId)
    {
        $this->nodeId = $nodeId;
    }

    /**
     * @return NodeStats[]
     */
    public function getNodeStats()
    {
        return $this->nodeStats;
    }

    /**
     * @param NodeStats $nodeData
     */
    public function addNodeStats(NodeStats $nodeData)
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

    /**
     * Create a Node
     *
     * @param string $nodeId
     * @return Node
     */
    private function create($nodeId)
    {
        $node = new Node();
        $node->setNodeId($nodeId);
        return $node;
    }

    /**
     * Look for Node or create a new one if not existing
     *
     * @param EntityManager $entityManager
     * @param $nodeId
     * @return Node
     */
    public static function getOrCreate(EntityManager $entityManager, $nodeId)
    {
        $nodeRepository = $entityManager->getRepository('FFClientGraph\Entities\Node');
        $result = $nodeRepository->findOneBy(['nodeId' => $nodeId]);
        if ($result) {
            return $result;
        }
        return self::create($nodeId);
    }

}