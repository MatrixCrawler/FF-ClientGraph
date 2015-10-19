<?php

namespace FFClientGraph\Util;

use DateTime;
use FFClientGraph\Config\Constants;

/**
 * Class Util
 *
 * Contains utility functions
 *
 * @package FFClientGraph\Util
 */
class Util
{

    /**
     * Returns the DateTime from the last successful JSON update
     *
     * @return DateTime|null
     */
    public static function getLastJSONUpdate()
    {
        if (file_exists(Constants::LAST_UPDATE_FILE)) {
            $content = file_get_contents(Constants::LAST_UPDATE_FILE);
            if ($content !== '') {
                return new DateTime($content);
            }
        }
        return null;
    }

    /**
     * Returns the DateTime from the last modification of the remote JSON
     *
     * @param $uri
     * @return DateTime|null
     */
    public static function getLastRemoteJSONUpdate($uri)
    {
        /**
         * Check if uri is a valid url.
         * if not try if it is a file path and return the file last-modified stamp
         */
        if (!self::isValidURL($uri)) {
            if (file_exists($uri)) {
                $fileStat = stat($uri);
                return new DateTime(date('c',$fileStat[9]));
            } else {
                return null;
            }
        }

        $fileHeaders = get_headers($uri, 1);
        if (array_key_exists('Last-Modified', $fileHeaders) && $fileHeaders['Last-Modified'] !== '') {
            return new DateTime($fileHeaders['Last-Modified']);
        }
        return null;
    }


    /**
     * Get the timestamp from the nodes JSON
     * Return null if there is no timestamp or an invalid resource JSON
     *
     * @param array|string $JSONData
     * @return DateTime|null
     */
    public static function getJSONTimestamp($JSONData)
    {
        if (!is_array($JSONData)) {
            $JSONData = json_decode($JSONData, true);
        }
        if ($JSONData && array_key_exists('timestamp', $JSONData)) {
            $dateTime = new DateTime($JSONData['timestamp']);
            return $dateTime;
        }
        return null;
    }

    /**
     * Check if an url is valid
     *
     * @param $url
     * @return bool
     */
    public static function isValidURL($url)
    {
        $url = @parse_url($url);

        if (!$url) {
            return false;
        }

        $url = array_map('trim', $url);
        $url['port'] = (!isset($url['port'])) ? 80 : (int)$url['port'];
        $path = (isset($url['path'])) ? $url['path'] : '';

        if ($path == '') {
            $path = '/';
        }

        $path .= (isset ($url['query'])) ? "?$url[query]" : '';

        if (isset ($url['host']) AND $url['host'] != gethostbyname($url['host'])) {
            if (PHP_VERSION >= 5) {
                $headers = get_headers("$url[scheme]://$url[host]:$url[port]$path");
            } else {
                $fp = fsockopen($url['host'], $url['port'], $errno, $errstr, 30);

                if (!$fp) {
                    return false;
                }
                fwrite($fp, "HEAD $path HTTP/1.1\r\nHost: $url[host]\r\n\r\n");
                $headers = fread($fp, 128);
                fclose($fp);
            }
            $headers = (is_array($headers)) ? implode("\n", $headers) : $headers;
            return ( bool )preg_match('#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers);
        }
        return false;
    }
}