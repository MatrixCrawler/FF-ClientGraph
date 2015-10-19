<?php
/**
 * Created by IntelliJ IDEA.
 * User: Johannes Brunswicker
 * Date: 12.10.2015
 * Time: 16:05
 */

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use FFClientGraph\config\Config;
use FFClientGraph\Config\Constants;

require_once 'vendor/autoload.php';

$ORMConfig = Setup::createAnnotationMetadataConfiguration(array(Constants::ENTITY_PATH), Constants::DEVMODE);

$DBConnection = array(
    'driver' => Config::DB_DRIVER
);
switch (Config::DB_DRIVER) {
    case Constants::DB_DRIVER_SQLITE:
        $DBConnection['path'] = Config::DB_PATH;
        break;
    case Constants::DB_DRIVER_MYSQL:
        $mysqlConfig = array(
            'user' => Config::DB_USER,
            'password' => Config::DB_PASSWORD,
            'host' => Config::DB_HOST,
            'dbname' => Config::DB_NAME
        );
        $DBConnection = array_merge($DBConnection, $mysqlConfig);
        break;
}

$entityManager = EntityManager::create($DBConnection, $ORMConfig);
