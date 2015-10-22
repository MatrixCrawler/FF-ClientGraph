<?php

namespace FFClientGraph\Entities;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="node_stats_timestamp")
 */
class NodeStatsTimestamp
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
    protected $dataDateTime;

    /**
     * The NodeStats associated with this timestamp
     *
     * @OneToMany(targetEntity="NodeStats", mappedBy="statTimestamp", cascade={"persist", "remove"})
     * @var NodeStats[]
     */
    protected $nodeStats;

    /**
     * @Column(type="string")
     * @var string
     */
    protected $timezone;

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
    public function addStatData(NodeStats $nodeData)
    {
        $this->nodeStats[] = $nodeData;
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
     * @param DateTime|DateTimeImmutable $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
        $this->timezone = $timestamp->getTimezone()->getName();
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
    public function getDataDateTime()
    {
        if ($this->dataDateTime === null) {
            return null;
        }
        $this->dataDateTime = $this->dataDateTime->setTimezone(new DateTimeZone($this->getTimezone()));
        return $this->dataDateTime;
    }


    /**
     * @param DateTime|DateTimeImmutable $dataDateTime
     */
    public function setDataDateTime($dataDateTime)
    {
        $this->dataDateTime = $dataDateTime;
    }


    /**
     * @param DateTime|DateTimeImmutable $timeStamp
     * @param DateTime|DateTimeImmutable $dataTime
     * @return NodeStatsTimestamp
     */
    private function create($timeStamp, $dataTime = null)
    {
        $nodeStatsTimeStamp =  new NodeStatsTimestamp();
        $nodeStatsTimeStamp->setTimestamp($timeStamp);
        $nodeStatsTimeStamp->setDataDateTime($dataTime);
        return $nodeStatsTimeStamp;
    }

    /**
     * @param EntityManager $entityManager
     * @param DateTime|DateTimeImmutable $timeStamp
     * @param DateTime|DateTimeImmutable $dataTimestamp
     * @return NodeStatsTimestamp
     */
    public static function getOrCreate(EntityManager $entityManager, $timeStamp, $dataTimestamp = null)
    {
        $dataTimestampRepository = $entityManager->getRepository('FFClientGraph\Entities\NodeStatsTimestamp');
        $result = $dataTimestampRepository->findOneBy(['timestamp' => $timeStamp, 'dataDateTime' => $dataTimestamp]);
        if ($result) {
            return $result;
        }
        return self::create($timeStamp, $dataTimestamp);
    }


}