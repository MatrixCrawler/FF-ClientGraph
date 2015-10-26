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
    protected $created;

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
    public function getCreated()
    {
        $this->created->setTimeZone(new DateTimeZone($this->timezone));

        return $this->created;
    }

    /**
     * @param DateTime|DateTimeImmutable $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
        $this->timezone = $created->getTimezone()->getName();
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
        if ($this->dataTimestamp === null) {
            return null;
        }
        $this->dataTimestamp = $this->dataTimestamp->setTimezone(new DateTimeZone($this->getTimezone()));
        return $this->dataTimestamp;
    }


    /**
     * @param DateTime|DateTimeImmutable $dataTimestamp
     */
    public function setDataTimestamp($dataTimestamp)
    {
        $this->dataTimestamp = $dataTimestamp;
    }


    /**
     * @param DateTime|DateTimeImmutable $created
     * @param DateTime|DateTimeImmutable $dataTimestamp
     * @return NodeStatsTimestamp
     */
    private function create($created, $dataTimestamp = null)
    {
        $nodeStatsTimeStamp = new NodeStatsTimestamp();
        $nodeStatsTimeStamp->setCreated($created);
        $nodeStatsTimeStamp->setDataTimestamp($dataTimestamp);
        return $nodeStatsTimeStamp;
    }

    /**
     * @param EntityManager $entityManager
     * @param DateTime|DateTimeImmutable $created
     * @param DateTime|DateTimeImmutable $dataTimestamp
     * @return NodeStatsTimestamp
     */
    public static function getOrCreate(EntityManager $entityManager, $created, $dataTimestamp = null)
    {
        $dataTimestampRepository = $entityManager->getRepository('FFClientGraph\Entities\NodeStatsTimestamp');
        $result = $dataTimestampRepository->findOneBy(['created' => $created, 'dataTimestamp' => $dataTimestamp]);
        if ($result) {
            return $result;
        }
        return self::create($created, $dataTimestamp);
    }


}