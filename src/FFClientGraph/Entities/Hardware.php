<?php
/**
 * Created by IntelliJ IDEA.
 * User: Johannes
 * Date: 19.10.2015
 * Time: 22:41
 */

namespace FFClientGraph\Entities;


use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="hardware")
 */
class Hardware
{

    /**
     * @Id
     * @Column(type="string")
     */
    protected $model;

    /**
     * @OneToMany(targetEntity="NodeInfo", mappedBy="hardware", cascade={"persist","remove"})
     * @var NodeInfo
     */
    protected $nodeInfo;

    /**
     * Hardware constructor.
     * @param NodeInfo $nodeInfo
     */
    public function __construct(NodeInfo $nodeInfo)
    {
        $this->nodeInfo = $nodeInfo;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param mixed $model
     */
    public function setModel($model)
    {
        $this->model = $model;
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
     * @param NodeInfo $nodeInfo
     * @param array $nodeInfoArray
     * @return Hardware
     */
    public static function create(NodeInfo $nodeInfo, $nodeInfoArray)
    {
        $hardware = new Hardware($nodeInfo);

        $hardware->setModel($nodeInfoArray['nodeinfo']['hardware']['model']);

        return $hardware;
    }

}