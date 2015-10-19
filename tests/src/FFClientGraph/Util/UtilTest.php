<?php

namespace FFClientGraph\Util;

use DateTime;
use FFClientGraph\Config\Constants;
use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/../../../../vendor/autoload.php';

class UtilTest extends PHPUnit_Framework_TestCase
{

    public function testGetLastJSONUpdateIsNotNull() {

        if (!file_exists(Constants::LAST_UPDATE_FILE)) {
            file_put_contents(Constants::LAST_UPDATE_FILE, (new DateTime())->format('c'));
        }

        $result = Util::getLastJSONUpdate();
        self::assertNotNull($result);
        self::assertInstanceOf('DateTime', $result);

        if (file_exists(Constants::LAST_UPDATE_FILE)) {
            unlink(Constants::LAST_UPDATE_FILE);
        }
    }

    public function testGetLastRemoteJSONUpdate_Local() {
        $result = Util::getLastRemoteJSONUpdate(__DIR__.'/../../../resources/test.json');

        self::assertNotNull($result);
        self::assertInstanceOf('DateTime', $result);
    }

    public function testGetLastRemoteJSONUpdate_Remote() {
        $result = Util::getLastRemoteJSONUpdate('http://www.google.de/intl/de/about/');

        self::assertNotNull($result);
        self::assertInstanceOf('DateTime', $result);
    }

}