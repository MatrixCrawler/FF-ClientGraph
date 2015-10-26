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
     * @OneToOne(targetEntity="NodeInfo", cascade={"persist", "remove"})
     * @JoinColumn(name="nodeInfo_id", referencedColumnName="id")
     * @var NodeInfo
     */
    protected $nodeInfo;

    /**
     * @OneToMany(targetEntity="NodeStats", mappedBy="node", cascade={"persist", "remove"},)
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
        $nodeInfo->setNode($this);
    }

    /**
     * Create a Node
     *
     * @param $entityManager
     * @param $nodeDataArray
     * @return Node
     */
    private static function create($entityManager, $nodeDataArray)
    {
        $node = new Node();
        $node->setNodeId($nodeDataArray['nodeinfo']['node_id']);

        NodeInfo::create($entityManager, $node, $nodeDataArray);

        return $node;
    }

    /**
     * Look for Node or create a new one if not existing
     *
     * @param EntityManager $entityManager
     * @param array $nodeDataArray
     * @return Node
     */
    public static function getOrCreate(EntityManager $entityManager, $nodeDataArray)
    {
        if (!is_array($nodeDataArray) || !array_key_exists('nodeinfo', $nodeDataArray) || !array_key_exists('node_id', $nodeDataArray['nodeinfo'])) {
            return null;
        }
        $nodeRepository = $entityManager->getRepository('FFClientGraph\Entities\Node');
        $result = $nodeRepository->findOneBy(['nodeId' => $nodeDataArray['nodeinfo']['node_id']]);

        if ($result) {
            return $result;
        }
        return self::create($entityManager, $nodeDataArray);
    }

}