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
        TestUtils::insertNode('TestNode');

        $entityManager = TestUtils::getEntityManager();
        $node = Node::getOrCreate($entityManager, 'TestNode');
        self::assertNotNull($node);
        self::assertInstanceOf('FFClientGraph\Entities\Node', $node);
        self::assertEquals('TestNode', $node->getNodeId());
        self::assertTrue($entityManager->contains($node));
    }

    public function testGetOrCreate_returnNewNode()
    {
        TestUtils::clearDB();

        $entityManager = TestUtils::getEntityManager();
        $node = Node::getOrCreate($entityManager, 'TestNode');
        self::assertNotNull($node);
        self::assertInstanceOf('FFClientGraph\Entities\Node', $node);
        self::assertEquals('TestNode', $node->getNodeId());
        self::assertNotTrue($entityManager->contains($node));
    }

}