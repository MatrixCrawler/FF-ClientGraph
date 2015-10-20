<?php
namespace FFClientGraph\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
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
     * @ORM\Column(type="string")
     */
    protected $nodeName;

    /**
     * @ManyToOne(targetEntity="Hardware", inversedBy="nodeInfo", cascade={"persist"})
     * @JoinColumn(name="hardware_id", referencedColumnName="model")
     * @var
     */
    protected $hardware;

    /**
     * @ORM\Column(type="string")
     */
    protected $hostname;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=8)
     */
    protected $latitude;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=8)
     */
    protected $longitude;

    /**
     * @ORM\Column(type="string")
     */
    protected $owner;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $firstseen;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $lastseen;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getNodeName()
    {
        return $this->nodeName;
    }

    /**
     * @param string $nodeName
     */
    public function setNodeName($nodeName)
    {
        $this->nodeName = $nodeName;
    }

    /**
     * @return Hardware
     */
    public function getHardware()
    {
        return $this->hardware;
    }

    /**
     * @param Hardware $hardware
     */
    public function setHardware($hardware)
    {
        $this->hardware = $hardware;
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
     * Set the NodeInfo
     *
     * @param Node $node
     * @param array $nodeInfoArray
     * @return NodeInfo|null
     */
    public static function create(Node $node, $nodeInfoArray)
    {
        //TODO Testing
        if ($node->getNodeInfo() !== null) {
            //TODO Implement
            $nodeInfo = new NodeInfo();
        } else {
            $nodeInfo = $node->getNodeInfo();
        }
        return null;
    }
}