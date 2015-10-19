<?php
error_reporting(0);
ob_start();

use FFClientGraph\Config\Config;
use FFClientGraph\Util\Graph;

require_once 'vendor/autoload.php';
if (isset($_GET['client'])) {
    $filename = Config::CACHE_FOLDER . '/' . $_GET['client'] . '-clients.png';
    if (file_exists($filename)) {
        /**
         * Check if the generated file is older than 5 Minutes
         * If this is the case: generate new image
         */
        $fileStats = stat($filename);
        $currentUnixTime = time();
        if ($fileStats[9] < ($currentUnixTime - (Config::CACHETIME_IMAGE * 60))) {
            $graph = new Graph();
            $graph->createGraph($_GET['client']);
        }
    } else {
        /**
         * There is no cached file
         * Generate image
         */
        $graph = new Graph();
        $graph->createGraph($_GET['client']);
    }
    /**
     * Print image from cache
     */
    header('Content-type: image/png');
    echo file_get_contents($filename);
} else {
    die('Invalid client id');
}
ob_end_flush();