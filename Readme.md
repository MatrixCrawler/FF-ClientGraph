[![Build Status](https://travis-ci.org/MatrixCrawler/FF-ClientGraph.svg?branch=master)](https://travis-ci.org/MatrixCrawler/FF-ClientGraph)

# Freifunk - ClientGraph

Dies ist ein Tool zum Tracken und grafischer Darstellung der Auslastung von Freifunkknoten.

##Systemvoraussetzungen

* PHP5.5+ (mit GD & Freetype-Support)
* PDO Treiber (SQLite oder MySQL)


## Installation

1. Load the Composer requirements by calling ```PATH/TO/YOUR/php.exe PATH/TO/YOUR/composer.phar update```
2. Edit the config file ```src/FFClientGraph/config/Config.php```
3. Create the database schema by calling ```vendor\bin\doctrine orm:schema-tool:create``` in the root of the project

### Generierung über Cronjobs
The cronjob ```cron.php``` should run every n minutes. I would suggest every 5 Minutes.
It fetches data from the remote node.json and stores it into the configured database.


### Generierung der Grafik "on Demand"
To generate a graph call ```YOURURI/graph.php?client=CLIENT_ID```
The will generate the graph image if there is no cached version for it.
The caching time can be configured at ```Config::CACHE_AGE```

### Generierung aller Grafiken
Es ist möglich alle Grafiken auf einmal zu generieren. Dazu muss die Funktion FFClientGraph::createAllGraphs aufgerufen werden.
ACHTUNG! Dieser Prozess kann dann mehrere Minuten in Anspruch nehmen. Die bessere Variante ist das generieren "on demand".
 
