<?php

namespace FFClientGraph\Entities;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
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
     * @var DateTime|DateTimeImmutable
     */
    protected $timestamp;

    /**
     * The timestamp the fetched data was signed with
     *
     * @Column(type="datetime", nullable=true)
     * @var DateTime|DateTimeImmutable
     */
    protected $dataDateTime;

    /**
     * The NodeStats associated with this timestamp
     *
     * @OneToMany(targetEntity="NodeStats", mappedBy="statTimestamp", cascade={"persist"})
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
     * @param DateTimeInterface $timeStamp
     * @param DateTimeInterface|null $dataTime
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
     * @param DateTimeInterface $timeStamp
     * @param DateTimeInterface $dataTimestamp
     * @return NodeStatsTimestamp
     */
    public static function getOrCreate(EntityManager $entityManager, DateTimeInterface $timeStamp, DateTimeInterface $dataTimestamp = null)
    {
        $dataTimestampRepository = $entityManager->getRepository('FFClientGraph\Entities\NodeStatsTimestamp');
        $result = $dataTimestampRepository->findOneBy(['timestamp' => $timeStamp]);
        if ($result) {
            return $result;
        }
        return self::create($timeStamp, $dataTimestamp);
    }


}