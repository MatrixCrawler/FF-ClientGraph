<?php
namespace FFClientGraph\Entities;

use FFClientGraph\TestUtils;
use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../TestUtils.php';

class NodeTest extends PHPUnit_Framework_TestCase
{

    public function testGetOrCreate_returnExistingNode()
    {
        TestUtils::clearDB();
        $nodeData = json_decode(file_get_contents(__DIR__ . '/../../../resources/test_small.json'), true);
        TestUtils::insertNode($nodeData['nodes']['68725120d3ed']);

        $entityManager = TestUtils::getEntityManager();
        $node = Node::getOrCreate($entityManager, $nodeData['nodes']['68725120d3ed']);
        self::assertNotNull($node);
        self::assertNotNull($node->getNodeInfo());
        self::assertInstanceOf('FFClientGraph\Entities\Node', $node);
        self::assertEquals('68725120d3ed', $node->getNodeId());
        self::assertTrue($entityManager->contains($node));
    }

    public function testGetOrCreate_returnNewNode()
    {
        TestUtils::clearDB();
        $nodeData = json_decode(file_get_contents(__DIR__ . '/../../../resources/test_small.json'), true);

        $entityManager = TestUtils::getEntityManager();
        $node = Node::getOrCreate($entityManager, $nodeData['nodes']['68725120d3ed']);
        self::assertNotNull($node);
        self::assertInstanceOf('FFClientGraph\Entities\Node', $node);
        self::assertEquals('68725120d3ed', $node->getNodeId());
        self::assertNotTrue($entityManager->contains($node));
    }

    public function testGetOrCreate_returnNull()
    {
        TestUtils::clearDB();

        $entityManager = TestUtils::getEntityManager();
        $node = Node::getOrCreate($entityManager, 'TestNode');
        self::assertNull($node);
    }

}