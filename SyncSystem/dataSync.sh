#!/bin/bash
echo "{" >>  ~/SyncMessage.log
date >> ~/SyncMessage.log
php /var/www/html/sensei-sensor-php/SyncSystem/syncUserList.php 1>> ~/SyncMessage.log 2>&1
php /var/www/html/sensei-sensor-php/SyncSystem/syncTagList.php 1>> ~/SyncMessage.log 2>&1
php /var/www/html/sensei-sensor-php/SyncSystem/syncSensorList.php 1>> ~/SyncMessage.log 2>&1
php /var/www/html/sensei-sensor-php/SyncSystem/syncDiscvLog.php 1>> ~/SyncMessage.log 2>&1
echo "}," >> ~/SyncMessage.log
