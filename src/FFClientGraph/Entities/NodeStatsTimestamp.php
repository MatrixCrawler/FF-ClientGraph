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
    protected $createdTimezone;

    /**
     * @Column(type="string", nullable=true)
     * @var string
     */
    protected $dataTimestampTimezone;

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
        $this->created->setTimeZone(new DateTimeZone($this->createdTimezone));

        return $this->created;
    }

    /**
     * @param DateTime|DateTimeImmutable $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
        $this->createdTimezone = $created->getTimezone()->getName();
    }

    /**
     * @return string
     */
    public function getCreatedTimezone()
    {
        return $this->createdTimezone;
    }

    /**
     * @param string $createdTimezone
     */
    public function setCreatedTimezone($createdTimezone)
    {
        $this->createdTimezone = $createdTimezone;
    }


    /**
     * @return DateTime
     */
    public function getDataTimestamp()
    {
        if ($this->dataTimestamp === null) {
            return null;
        }
        return $this->dataTimestamp;
    }


    /**
     * @param DateTime|DateTimeImmutable $dataTimestamp
     */
    public function setDataTimestamp($dataTimestamp)
    {
        $this->dataTimestamp = $dataTimestamp;
        $this->dataTimestampTimezone = $dataTimestamp ? $dataTimestamp->getTimezone()->getName() : null;
    }

    /**
     * @return string
     */
    public function getDataTimestampTimezone()
    {
        return $this->dataTimestampTimezone;
    }

    /**
     * @param string $dataTimestampTimezone
     */
    public function setDataTimestampTimezone($dataTimestampTimezone)
    {
        $this->dataTimestampTimezone = $dataTimestampTimezone;
    }


    /**
     * @param DateTime|DateTimeImmutable $created
     * @param DateTime|DateTimeImmutable $dataTimestamp
     * @return NodeStatsTimestamp
     */
    private static function create($created, $dataTimestamp = null)
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