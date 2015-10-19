<?php
/**
 * Created by IntelliJ IDEA.
 * User: Johannes
 * Date: 13.10.2015
 * Time: 18:15
 */

namespace FFClientGraph\Config;

use Monolog\Logger;

/**
 * Class Config
 *
 * Please configure your system settings here
 * After you finished configuration SAVE THIS FILE AS Config.php
 *
 * @package FFClientGraph\config
 */
class Config
{

    /**
     * Loglevel setting
     * please choose between Logger::DEBUG, Logger::INFO, Logger::WARNING, Logger::ERROR
     */
    const LOGLEVEL = Logger::DEBUG;

    /**
     * Path to the node JSON
     */
    const DATA_PATH = 'http://map.fichtenfunk.freifunk.ruhr/json';

    /**
     * Path where the DB is to be stored (if you are using sqlite)
     * Path is relative to this file location
     */
    const DB_PATH = __DIR__ . '/../../../db.sqlite';

    /**
     * The Database type you would like to use.
     * Use one of the following:
     *
     * Constants::DB_DRIVER_SQLITE
     * Constants::DB_DRIVER_MYSQL
     */
    const DB_DRIVER = Constants::DB_DRIVER_MYSQL;

    /**
     * Database name
     */
    const DB_NAME = '';

    /**
     * Database user
     */
    const DB_USER = '';

    /**
     * Database password
     */
    const DB_PASSWORD = '';

    /**
     * Database-Host
     */
    const DB_HOST = 'localhost';

    /**
     * The path to your cache folder
     * Must be writeable from the app
     */
    const CACHE_FOLDER = __DIR__ . '/../../../cache';

    /**
     * The maximum age of the cache file in minutes
     * The script will try to fetch a new nodes.JSON from remote when the cached file is older than that
     */
    const CACHETIME_NODE_LIST = 1;

    /**
     * The maximum age of the cached graph image.
     * If it is older than the given minutes it will be generated.
     */
    const CACHETIME_IMAGE = 5;
}