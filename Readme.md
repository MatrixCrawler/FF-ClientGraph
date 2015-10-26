[![Build Status](https://travis-ci.org/MatrixCrawler/FF-ClientGraph.svg?branch=master)](https://travis-ci.org/MatrixCrawler/FF-ClientGraph)

# Freifunk - ClientGraph

Dies ist ein Tool zum Tracken und grafischer Darstellung der Auslastung von Freifunkknoten.

##Systemvoraussetzungen

* PHP5.6+ (mit GD & Freetype-Support)
* PDO Treiber (SQLite oder MySQL)


## Installation

1. Lade die benötigten Composer-Dependencies mit dem Befehl ```PATH/TO/YOUR/php.exe PATH/TO/YOUR/composer.phar update```. Composer kann hier herunter geladen werden: http://www.getcomposer.org
2. Bearbeite die Konfiguration nach deinen Bedürfnissen ```src/FFClientGraph/config/Config.php```
3. Die Datenbank-Schemata werden erzeugt, wenn das erste mal der cron-script aufgerufen wird. Hier wird auch überprüft, ob das Datenbank-Schema noch aktuell ist. Bitte benutze eine eigene Datenbank für dieses Tool, da beim überprüfen der Schemata andere Tabellen entfernt werden könnten.

### Generierung über Cronjobs
Der Cronjob ```cron.php``` sollte jede n Minuten laufen, je nachdem wie akurat die Datensammlung sein soll.
Der Cronjob holt die Daten von der remote nodes.json (siehe Konfiguration) und speichert die Werte in der Datenbank.

### Generierung der Grafik "on Demand"
Um einen Graphen zu erzeugen, rufe den Script ```YOURURI/graph.php?client=CLIENT_ID``` auf.
dadurch wird ein Graph erzeugt, wenn es keine gecachte Version davon gibt. Ansonsten wird die gecachte Version ausgeliefert.
Die Cachingzeit kann über folgegenden Wert in der Konfiguration angepasst werden: ```Config::CACHE_AGE```

### Generierung aller Grafiken
Es ist möglich alle Grafiken auf einmal zu generieren. Dazu muss die Funktion FFClientGraph::createAllGraphs aufgerufen werden.
ACHTUNG! Dieser Prozess kann dann mehrere Minuten in Anspruch nehmen. Die bessere Variante ist das generieren "on demand".
 
