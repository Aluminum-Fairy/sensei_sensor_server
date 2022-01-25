#!/bin/bash
echo "{" >>  ~/SyncMessageLog.log
date >> ~/SyncMessageLog.log
php /var/www/html/sensei-sensor-php/SyncSystem/syncUserList.log >> ~/SyncMessageLog.log
php /var/www/html/sensei-sensor-php/SyncSystem/syncTagList.log >> ~/SyncMessageLog.log
php /var/www/html/sensei-sensor-php/SyncSystem/syncSensorList.log >> ~/SyncMessageLog.log
php /var/www/html/sensei-sensor-php/SyncSystem/syncDiscvLog.log >> ~/SyncMessageLog.log
echo "}," >> ~/SyncMessageLog.log
