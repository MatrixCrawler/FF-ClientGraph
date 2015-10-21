<?php
/**
 * Created by IntelliJ IDEA.
 * User: Johannes Brunswicker
 * Date: 21.10.2015
 * Time: 07:53
 */

namespace FFClientGraph\Entities;

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../TestUtils.php';

use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use FFClientGraph\Config\Constants;
use InvalidArgumentException;
use Monolog\Logger;
use PHPUnit_Framework_TestCase;

class NodeInfoTest extends PHPUnit_Framework_TestCase
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

    public function testCreate() {
        $nodeData = json_decode(file_get_contents(__DIR__ . '/../../../resources/test_small.json'), true);
        $nodeInfo = NodeInfo::create(new Node(), $nodeData['nodes']['68725120d3ed']);

        self::assertEquals('FF-Is-Heimatversorger-060', $nodeInfo->getHostname());
        self::assertEquals(new DateTime('2015-09-30T19:10:11'), $nodeInfo->getFirstseen());
        self::assertEquals(new DateTime('2015-10-12T12:41:49'), $nodeInfo->getLastseen());
        self::assertEquals(51.37435, $nodeInfo->getLatitude());
        self::assertEquals(7.69723, $nodeInfo->getLongitude());
        self::assertEquals('info@freifunk-iserlohn.de', $nodeInfo->getOwner());

//        $hardware = Hardware::getOrCreate(self::$entityManager, $nodeInfo);
//
//        assertEquals('', $nodeInfo->getHardware());
    }

    public static function tearDownAfterClass()
    {
        self::$schemaTool->dropDatabase();
    }
}