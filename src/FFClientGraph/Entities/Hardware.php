<?php
namespace FFClientGraph\Entities;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
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
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @Column(type="string", unique=true)
     */
    protected $model;

    /**
     * @OneToMany(targetEntity="NodeInfo", mappedBy="hardware", cascade={"persist","remove"})
     * @var NodeInfo[]
     */
    protected $nodeInfo;

    /**
     * Hardware constructor.
     * @param NodeInfo $nodeInfo
     */
    public function __construct(NodeInfo $nodeInfo = null)
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
     * @return NodeInfo[]
     */
    public function getNodeInfo()
    {
        return $this->nodeInfo;
    }

    /**
     * @param NodeInfo $nodeInfo
     */
    public function addNodeInfo($nodeInfo)
    {
        $this->nodeInfo[] = $nodeInfo;
    }

    /**
     * Create a new Hardware
     *
     * @param string $model
     * @return Hardware
     */
    private static function create($model)
    {
        $hardware = new Hardware();
        $hardware->setModel($model);
        return $hardware;
    }

    /**
     * Get an existing Hardware-Entity or create a new one
     *
     * @param EntityManager $entityManager
     * @param array $nodeInfoArray
     * @return Hardware|null
     */
    public static function getOrCreate(EntityManager $entityManager, $nodeInfoArray)
    {
        $model = isset($nodeInfoArray['nodeinfo']['hardware']['model']) ? $nodeInfoArray['nodeinfo']['hardware']['model'] : 'UNKNOWN';

        $hardwareRepo = $entityManager->getRepository('FFClientGraph\Entities\Hardware');
        $result = $hardwareRepo->findOneBy(['model' => $model]);
        if ($result) {
            return $result;
        }
        return self::create($model);
    }

}