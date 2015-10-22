<?php
/**
 * Created by IntelliJ IDEA.
 * User: Johannes Brunswicker
 * Date: 22.10.2015
 * Time: 08:46
 */

namespace FFClientGraph\Entities;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use FFClientGraph\Config\Constants;
use FFClientGraph\TestUtils;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../TestUtils.php';

class NodeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private static $classes;

    /**
     * @var SchemaTool
     */
    private static $schemaTool;

    /**
     * @var EntityManager
     */
    private static $entityManager;


    public static function setUpBeforeClass()
    {

        /**
         * Setup ORM and EntityManager
         */
        $ORMConfig = Setup::createAnnotationMetadataConfiguration(array(Constants::ENTITY_PATH), true);

        $DBConnection = TestUtils::setUpConnection();
        try {
            self::$entityManager = EntityManager::create($DBConnection, $ORMConfig);
            self::$classes = TestUtils::setUpClasses(self::$entityManager);
            self::$schemaTool = new SchemaTool(self::$entityManager);
            self::$schemaTool->updateSchema(self::$classes);
        } catch (ORMException $exception) {
            die('There was an ORMException in ' . get_class() . '\n Please check your configuration.\n' . $exception->getMessage());
        } catch (InvalidArgumentException $exception) {
            die('There was an invalid argument exception in ' . get_class() . '\n Please check your configuration.\n' . $exception->getMessage());
        }
    }

    public function testGetOrCreate_returnExistingNode() {
        TestUtils::clearDB(self::$schemaTool, self::$classes);
        TestUtils::insertNode(self::$entityManager, 'TestNode');

        $node = Node::getOrCreate(self::$entityManager, 'TestNode');
        self::assertNotNull($node);
        self::assertInstanceOf('FFClientGraph\Entities\Node', $node);
        self::assertEquals('TestNode', $node->getNodeId());
        self::assertTrue(self::$entityManager->contains($node));
    }

    public function testGetOrCreate_returnNewNode() {
        TestUtils::clearDB(self::$schemaTool, self::$classes);

        $node = Node::getOrCreate(self::$entityManager, 'TestNode');
        self::assertNotNull($node);
        self::assertInstanceOf('FFClientGraph\Entities\Node', $node);
        self::assertEquals('TestNode', $node->getNodeId());
        self::assertNotTrue(self::$entityManager->contains($node));
    }


    public static function tearDownAfterClass()
    {
        self::$schemaTool->dropDatabase();
    }
}