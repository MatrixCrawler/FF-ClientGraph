<?php
/**
 * Created by IntelliJ IDEA.
 * User: Johannes Brunswicker
 * Date: 13.10.2015
 * Time: 14:37
 */

use FFClientGraph\FFClientGraph;

require_once 'vendor/autoload.php';

$client = new FFClientGraph();
$client->refresh();