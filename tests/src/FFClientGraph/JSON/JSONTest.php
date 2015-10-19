<?php
/**
 * Created by IntelliJ IDEA.
 * User: Johannes Brunswicker
 * Date: 14.10.2015
 * Time: 13:57
 */

namespace FFClientGraph\JSON;

use FFClientGraph\Config\Constants;
use Monolog\Logger;
use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/../../../../vendor/autoload.php';

class JSONTest extends PHPUnit_Framework_TestCase
{

    const PATH_TO_LOCAL_JSON = __DIR__ . '/../../../resources/test_small.JSON';

    /**
     * @var int
     */
    private $logLevel = Logger::ALERT;

    public function testGetJSONFromLocalIsNotNull()
    {
        $json = new JSON(self::PATH_TO_LOCAL_JSON, $this->logLevel);
        $result = $json->getJSON();

        self::assertNotNull($result);

        if (file_exists(Constants::CACHED_NODE_FILE)) {
            unlink(Constants::CACHED_NODE_FILE);
        }
    }

    /**
     * @depends testGetJSONFromLocalIsNotNull
     */
    public function testGetJSONFromNonExistingLocalIsNull()
    {
        $json = new JSON('nodes.ThisSHouldbetterNotExist.file', $this->logLevel);
        $result = $json->getJSON();

        self::assertNull($result);
    }

    public function testGetJSONAsArrayFromLocalIsValidArray()
    {
        $json = new JSON(self::PATH_TO_LOCAL_JSON, $this->logLevel);
        $result = $json->getJSONAsArray();

        self::assertArrayHasKey('timestamp', $result);
        self::assertArrayHasKey('nodes', $result);


        if (file_exists(Constants::CACHED_NODE_FILE)) {
            unlink(Constants::CACHED_NODE_FILE);
        }
    }

}