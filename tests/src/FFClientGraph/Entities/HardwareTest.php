<?php
/**
 * Created by IntelliJ IDEA.
 * User: Johannes Brunswicker
 * Date: 21.10.2015
 * Time: 08:59
 */

namespace FFClientGraph\Entities;

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../TestUtils.php';

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use FFClientGraph\Config\Constants;
use InvalidArgumentException;
use Monolog\Logger;
use PHPUnit_Framework_TestCase;

class HardwareTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var int
     */
    private static $logLevel = Logger::EMERGENCY;

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

        //TODO Externalize DBConnection Setup and Config
        $DBConnection = array(
            'driver' => Constants::DB_DRIVER_SQLITE
        );
        $DBConnection['path'] = __DIR__ . '/../../../resources/test.sqlite.db';

        try {
            self::$entityManager = EntityManager::create($DBConnection, $ORMConfig);
            self::$classes[] = self::$entityManager->getClassMetadata('FFClientGraph\Entities\Node');
            self::$classes[] = self::$entityManager->getClassMetadata('FFClientGraph\Entities\NodeStats');
            self::$classes[] = self::$entityManager->getClassMetadata('FFClientGraph\Entities\DataTimestamp');
            self::$schemaTool = new SchemaTool(self::$entityManager);
            self::$schemaTool->updateSchema(self::$classes);
        } catch (ORMException $exception) {
            die('There was an ORMException in ' . get_class() . '\n Please check your configuration.\n' . $exception->getMessage());
        } catch (InvalidArgumentException $exception) {
            die('There was an invalid argument exception in ' . get_class() . '\n Please check your configuration.\n' . $exception->getMessage());
        }
    }

    public function testCreate()
    {
        $nodeData = json_decode(file_get_contents(__DIR__ . '/../../../resources/test_small.json'), true);
        $hardware = Hardware::create(new NodeInfo(new Node()), $nodeData['nodes']['68725120d3ed']);

        self::assertEquals('Ubiquiti Bullet M', $hardware->getModel());
    }

    public static function tearDownAfterClass()
    {
        self::$schemaTool->dropDatabase();
    }
}