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
     * @Column(type="datetime")
     * @var DateTime
     */
    protected $timestamp;

    /**
     * @Column(type="datetime")
     * @var DateTime
     */
    protected $dataTime;

    /**
     * @OneToMany(targetEntity="NodeStats", mappedBy="dataTimestamp", cascade={"persist"})
     */
    protected $statData;

    /**
     * @Column(type="string")
     * @var string
     */
    protected $timezone;

    /**
     * @param DateTimeInterface $timestamp
     */
    public function __construct(DateTimeInterface $timestamp)
    {
        $this->timestamp = $timestamp;
        $this->timezone = $timestamp->getTimezone()->getName();
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
    public function getDataTime()
    {
        $this->dataTime->setTimeZone(new DateTimeZone($this->dataTime));
        return $this->dataTime;
    }

    /**
     * @param DateTime $dataTime
     */
    public function setDataTime($dataTime)
    {
        $this->dataTime = $dataTime;
    }


}