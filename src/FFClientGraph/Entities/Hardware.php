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

}