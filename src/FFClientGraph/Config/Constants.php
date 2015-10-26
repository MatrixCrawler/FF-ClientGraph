<?php
/**
 * Created by IntelliJ IDEA.
 * User: Johannes Brunswicker
 * Date: 12.10.2015
 * Time: 15:52
 */

namespace FFClientGraph\Config;


/**
 * Class Constants
 *
 * Please do not modify this file unless you know what you are doing
 *
 * @package FFClientGraph\Config
 */
class Constants
{
    const DEVMODE = true;

    const GRAPH_WIDTH = 885;
    const GRAPH_HEIGHT = 470;
    const GRAPH_LEFT_OFFSET = 50;
    const GRAPH_RIGHT_OFFSET = 40;
    const GRAPH_BOTTOM_OFFSET = 50;
    const GRAPH_TOP_OFFSET = 10;

    const CACHED_NODE_FILE = Config::CACHE_FOLDER . '/nodes.json';
    const LAST_UPDATE_FILE = Config::CACHE_FOLDER . '/last_download';

    const NODE_FILE = Config::DATA_PATH.'/nodes.json';

    const RESOURCE_PATH = __DIR__ . '/../../../resources';
    const LOGPATH = __DIR__ . '/../../../log/FFClientGraph.log';


    /**
     * Path to the entity definitions
     */
    const ENTITY_PATH = __DIR__ . '/../Entities';


    /**
     * Constants for the DB Drivers
     */
    const DB_DRIVER_SQLITE = 'pdo_sqlite';
    const DB_DRIVER_MYSQL = 'pdo_mysql';

    const DB_SCHEMA_VERSION = '1.1.3';
}