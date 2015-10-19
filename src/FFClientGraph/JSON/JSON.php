<?php
/**
 * Created by IntelliJ IDEA.
 * User: Johannes
 * Date: 13.10.2015
 * Time: 17:58
 */

namespace FFClientGraph\JSON;

use DateInterval;
use DateTime;
use FFClientGraph\Config\Config;
use FFClientGraph\Config\Constants;
use FFClientGraph\Util\Util;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class JSON
 * This class contains functions to provide JSON Node files
 *
 * @package FFClientGraph\JSON
 */
class JSON
{

    protected $jsonSource = null;

    /**
     * @var Logger
     */
    private $logger = null;

    /**
     * @param string $jsonSource
     * @param int $logLevel
     */
    public function __construct($jsonSource = Constants::NODE_FILE, $logLevel = Config::LOGLEVEL)
    {
        $this->jsonSource = $jsonSource;

        $this->logger = new Logger('FFClientLogger');
        $this->logger->pushHandler(new StreamHandler(Constants::LOGPATH, $logLevel));
    }

    /**
     * Function to return the JSON String
     * @return String|null
     */
    public function getJSON()
    {
        if ($this->isCacheValid()) {
            return $this->getJSONFromCache();
        }
        return $this->getJSONFromRemote();
    }

    /**
     * Function to return JSON data as associative array
     * @return Array|null
     */
    public function getJSONAsArray()
    {
        return json_decode($this->getJSON(), true);
    }


    /**
     * Function to check whether the cached file is still valid or not
     *
     * @return bool
     */
    private function isCacheValid()
    {
        $this->logger->addDebug('Checking if cached file is still valid', [get_class()]);

        /**
         * Get DateTime of last successful refresh
         */
        $lastRefresh = Util::getLastJSONUpdate();

        $this->logger->addDebug('Get last remote modified from: ' . $this->jsonSource, [get_class()]);
        $lastRemoteRefresh = Util::getLastRemoteJSONUpdate($this->jsonSource);

        if (!$lastRefresh || !file_exists(Constants::CACHED_NODE_FILE)) {
            $this->logger->addDebug('There was no cached file. Cache is invalid', [get_class()]);
            return false;
        } else if (!$lastRemoteRefresh) {
            $this->logger->addDebug('Local file is present. The remote file has no modified date. Cache is valid', [get_class()]);
            return true;
        }

        $this->logger->addDebug('Adding ' . Config::CACHETIME_NODE_LIST . ' Minutes to the lastRefresh DateTime.', [get_class()]);
        $lastRefreshPlusNMinutes = $lastRefresh->add(new DateInterval('PT' . Config::CACHETIME_NODE_LIST . 'M'));
        $this->logger->addDebug('Result: ' . $lastRefreshPlusNMinutes->format('c'), [get_class()]);
        $this->logger->addDebug('Result Remote: ' . $lastRemoteRefresh->format('c'), [get_class()]);

        $isToOld = $lastRefreshPlusNMinutes > $lastRemoteRefresh;
        $this->logger->addDebug('Is to old? ' . $isToOld, [get_class()]);
        return ($isToOld);
    }

    /**
     * Function to fetch JSON data from the cache
     *
     * @return String|null
     */
    private function getJSONFromCache()
    {
        if (file_exists(Constants::CACHED_NODE_FILE)) {
            return file_get_contents(Constants::CACHED_NODE_FILE);
        }
        return null;
    }

    /**
     * Function to fetch JSON Data from a remote server
     *
     * @param bool $retry Whether this is the second try or not
     * @return null|String
     */
    private function getJSONFromRemote($retry = false)
    {
        $this->logger->addDebug('Trying to get JSON from remote. ' . $this->jsonSource, [get_class()]);
        $json = null;
        if (Util::isValidURL($this->jsonSource) || file_exists($this->jsonSource)) {
            $json = file_get_contents($this->jsonSource);
        }

        if (!$json) {
            $this->logger->addError('There was an error fetching the JSON source from ' . $this->jsonSource, [get_class()]);
            if (!$retry) {
                $this->logger->addError('Retrying once in 10 seconds', [get_class()]);
                sleep(10);
                return $this->getJSONFromRemote(true);
            } else {
                $this->logger->addError('Giving up', [get_class()]);
            }
        } else {
            $decodedJSON = json_decode($json, true);
            if (!$decodedJSON) {
                $this->logger->addError('The fetched JSON seems to be invalid.', [get_class()]);
                if (!$retry) {
                    $this->logger->addError('Retrying once in 10 seconds', [get_class()]);
                    sleep(10);
                    return $this->getJSONFromRemote(true);
                } else {
                    $this->logger->addError('Giving up', [get_class()]);
                }
            }
            /**
             * We got the file and it is valid.
             * Store it in cache
             */

            if (!file_put_contents(Constants::CACHED_NODE_FILE, $json)) {
                $this->logger->addError('There was an error saving the nodes file to the cache', [get_class()]);
            } else {
                $lastUpdateTimestamp = new DateTime();
                file_put_contents(Constants::LAST_UPDATE_FILE, $lastUpdateTimestamp->format('c'));
            }

            return $json;
        }
        return null;
    }
}