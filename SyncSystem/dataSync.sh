#!/bin/bash
echo "{" >>  ~/SyncMessageLog.log
date >> ~/SyncMessageLog.log
php /var/www/html/sensei-sensor-php/SyncSystem/syncUserList.php >> ~/SyncMessageLog.log
php /var/www/html/sensei-sensor-php/SyncSystem/syncTagList.php >> ~/SyncMessageLog.log
php /var/www/html/sensei-sensor-php/SyncSystem/syncSensorList.php >> ~/SyncMessageLog.log
php /var/www/html/sensei-sensor-php/SyncSystem/syncDiscvLog.php >> ~/SyncMessageLog.log
echo "}," >> ~/SyncMessageLog.log
