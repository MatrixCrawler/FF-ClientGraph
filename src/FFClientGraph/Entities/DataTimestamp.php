<?php

namespace FFClientGraph\Entities;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="data_timestamp")
 */
class DataTimestamp
{

    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;

    /**
     * The timestamp when the data was fetched
     *
     * @Column(type="datetime")
     * @var DateTime
     */
    protected $timestamp;

    /**
     * The timestamp the fetched data was signed with
     *
     * @Column(type="datetime", nullable=true)
     * @var DateTime
     */
    protected $dataTimestamp;

    /**
     * The NodeStats associated with this timestamp
     *
     * @OneToMany(targetEntity="NodeStats", mappedBy="dataTimestamp", cascade={"persist"})
     * @var NodeStats[]
     */
    protected $nodeStats;

    /**
     * @Column(type="string")
     * @var string
     */
    protected $timezone;

    /**
     * @param DateTimeInterface $timestamp
     * @param DateTimeInterface $dataTimestamp
     */
    public function __construct(DateTimeInterface $timestamp, DateTimeInterface $dataTimestamp = null)
    {
        $this->timestamp = $timestamp;
        $this->dataTimestamp = $dataTimestamp;
        $this->timezone = $timestamp->getTimezone()->getName();
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
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return DateTime
     */
    public function getTimestamp()
    {
        $this->timestamp->setTimeZone(new DateTimeZone($this->timezone));

        return $this->timestamp;
    }

    /**
     * @param mixed $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param string $timezone
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * @return DateTime
     */
    public function getDataTimestamp()
    {
        $this->dataTimestamp->setTimeZone(new DateTimeZone($this->dataTimestamp));
        return $this->dataTimestamp;
    }

    /**
     * @param DateTime $dataTimestamp
     */
    public function setDataTimestamp($dataTimestamp)
    {
        $this->dataTimestamp = $dataTimestamp;
    }

    public static function getOrCreate() {

    }


}