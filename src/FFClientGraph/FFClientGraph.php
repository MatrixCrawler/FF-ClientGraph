<?php

namespace FFClientGraph;


use Exception;
use FFClientGraph\Config\Config;
use FFClientGraph\Config\Constants;
use FFClientGraph\JSON\JSON;
use FFClientGraph\Util\DB;
use FFClientGraph\Util\Graph;
use InvalidArgumentException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class FFClientGraph
{
    /**
     * @var int
     */
    private $logLevel;

    /**
     * The monolog logger
     * @var Logger
     */
    protected $logger;

    /**
     * Constructor
     * @param int $logLevel
     */
    public function __construct($logLevel = Config::LOGLEVEL)
    {
        $this->logLevel = $logLevel;
        /**
         * Set up Logger
         */
        try {
            $this->logger = new Logger('FFClientLogger');
            $this->logger->pushHandler(new StreamHandler(Constants::LOGPATH, $this->logLevel));

        } catch (InvalidArgumentException $exception) {
            die('There was an invalid argument exception in ' . get_class() . '\n Please check your configuration.\n' . $exception->getMessage());
        } catch (Exception $exception) {
            die('There was an exception in ' . get_class() . '\n Please check your configuration.\n' . $exception->getMessage());
        }

    }

    /**
     * Refresh all data and create all graphs
     *
     * @param string $nodeFile URI to nodes.js
     */
    public function refresh($nodeFile = Constants::NODE_FILE)
    {
        $this->testDBVersion();
        /**
         * Get JSON Data from remote Server
         */
        $json = new JSON($nodeFile, $this->logLevel);
        $jsonDataArray = $json->getJSONAsArray();

        if ($jsonDataArray) {
            /**
             * Save JSON Data into the Database
             */
            $db = new DB($this->logLevel);
            $db->saveNodes($jsonDataArray);
        }
    }

    public function createAllGraphs()
    {
        /**
         * Create all Graphs
         */
        $graph = new Graph($this->logLevel);
        $graph->createAllGraphs();
    }

    private function testDBVersion()
    {
        $this->logger->addDebug('Checking db schema', [get_class()]);
        $version = '0.0.0';
        if (file_exists(Config::CACHE_FOLDER . '/dbVersion')) {
            $version = file_get_contents(Config::CACHE_FOLDER . '/dbVersion');
        }
        if (version_compare(Constants::DB_SCHEMA_VERSION, $version) > 0) {
            $this->logger->addDebug('Updating db schema', [get_class()]);
            $db = new DB($this->logLevel);
            $db->updateSchema($version === '0.0.0');
            file_put_contents(Config::CACHE_FOLDER . '/dbVersion', Constants::DB_SCHEMA_VERSION);
        }
    }

}