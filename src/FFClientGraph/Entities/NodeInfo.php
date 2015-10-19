<?php
namespace FFClientGraph\Entities;

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
}