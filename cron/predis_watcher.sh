#!/bin/bash

echo Go Logger Watcher!

cd /data/www/common/cron/

/usr/local/bin/php ./predis_watcher.php> /dev/null 2>&1 &

#sudo -u www-data /usr/local/bin/php ./predis_watcher.php > /dev/null 2>&1 &
#/usr/local/bin/php ./predis_watcher.php