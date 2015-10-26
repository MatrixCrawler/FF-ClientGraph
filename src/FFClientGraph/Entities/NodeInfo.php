<?php
namespace FFClientGraph\Entities;

use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="node_info")
 */
class NodeInfo
{

    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Hardware", inversedBy="nodeInfo", cascade={"persist"})
     * @var Hardware
     */
    protected $hardware;

    /**
     * @Column(type="string", nullable=false, nullable=true)
     * @var string
     */
    protected $hostname;

    /**
     * @Column(type="decimal", precision=10, scale=8, nullable=true)
     */
    protected $latitude;

    /**
     * @Column(type="decimal", precision=10, scale=8, nullable=true)
     */
    protected $longitude;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $owner;

    /**
     * @Column(type="datetime", nullable=true)
     */
    protected $firstseen;

    /**
     * @Column(type="datetime", nullable=true)
     */
    protected $lastseen;

    /**
     * NodeInfo constructor.
     * @param Node $node
     */
    public function __construct(Node $node = null)
    {
        $this->node = $node;
        $node->setNodeInfo($this);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Hardware
     */
    public function getHardware()
    {
        return $this->hardware;
    }

    /**
     * Set Hardware and add NodeInfo to Hardware Entity
     * @param Hardware $hardware
     */
    public function setHardware($hardware)
    {
        $this->hardware = $hardware;
        $hardware->addNodeInfo($this);
    }

    /**
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * @param string $hostname
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;
    }

    /**
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param float $latitude
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param float $longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * @return string
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param string $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return DateTime
     */
    public function getFirstseen()
    {
        return $this->firstseen;
    }

    /**
     * @param DateTime $firstseen
     */
    public function setFirstseen($firstseen)
    {
        $this->firstseen = $firstseen;
    }

    /**
     * @return DateTime
     */
    public function getLastseen()
    {
        return $this->lastseen;
    }

    /**
     * @param DateTime $lastseen
     */
    public function setLastseen($lastseen)
    {
        $this->lastseen = $lastseen;
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
     * Set the NodeInfo
     *
     * @param EntityManager $entityManager
     * @param Node $node
     * @param array $nodeInfoArray
     * @return NodeInfo
     */
    public static function create(EntityManager $entityManager, Node $node, $nodeInfoArray)
    {
        if (!is_array($nodeInfoArray) || !array_key_exists('nodeinfo', $nodeInfoArray)) {
            return null;
        }
        $nodeInfo = new NodeInfo($node);

        $nodeInfo->setHostname(isset($nodeInfoArray['nodeinfo']['hostname']) ? $nodeInfoArray['nodeinfo']['hostname'] : null);
        $nodeInfo->setFirstseen(isset($nodeInfoArray['firstseen']) ? new DateTime($nodeInfoArray['firstseen']) : null);
        $nodeInfo->setLastseen(isset($nodeInfoArray['lastseen']) ? new DateTime($nodeInfoArray['lastseen']) : null);
        $nodeInfo->setLatitude(isset($nodeInfoArray['nodeinfo']['location']['latitude']) ? floatval($nodeInfoArray['nodeinfo']['location']['latitude']) : null);
        $nodeInfo->setLongitude(isset($nodeInfoArray['nodeinfo']['location']['longitude']) ? floatval($nodeInfoArray['nodeinfo']['location']['longitude']) : null);
        $nodeInfo->setOwner(isset($nodeInfoArray['nodeinfo']['owner']['contact']) ? $nodeInfoArray['nodeinfo']['owner']['contact'] : null);

        $nodeInfo->setHardware(Hardware::getOrCreate($entityManager, $nodeInfoArray));

        return $nodeInfo;
    }
}