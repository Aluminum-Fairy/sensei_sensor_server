#!/bin/sh

## My SQL or Maria DB##

<< COMMENTOUT

sudo mysql_secure_installation
sudo mysql -u root -p
MariaDB [(none)] > ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password;
MariaDB [(none)] > ALTER USER 'root'@'localhost' IDENTIFIED BY 'YOUR PASSWORD';
---## If you can't run this command. ##---
    MariaDB [(none)] > UPDATE mysql.user SET plugin = 'mysql_native_password' WHERE user = 'root';

MariaDB [(none)] > FLUSH PRIVILEGES;
MariaDB [(none)] > exit;
sudo systemctl restart mysqld

COMMENTOUT
## phpMyAdmin URL : https://www.phpmyadmin.net/
## phpMyAdmin Config

<< COMMENTOUT

$cfg['Servers'][$i]['host'] = 'localhost';
$cfg['Servers'][$i]['compress'] = false;
$cfg['Servers'][$i]['AllowNoPassword'] = true;
$cfg['Servers'][$i]['user']          = 'root';     // MySQL user
$cfg['Servers'][$i]['password']      = 'rootPasswd';

COMMENTOUT

sudo apt update
sudo apt upgrade
sudo apt install -y git  python3-pip apache2 unzip php libapache2-mod-php php-mysql php-curl mariadb-server mariadb-client php-xml php-mbstring vim
sudo a2enmod rewrite
sudo a2enmod headers

#apache2.conf
<< COMMENTOUT

## Edit Apache2 Config

<Directory "/var/www/html">
    Options Indexes FollowSymLinks
-   AllowOverride None
+   AllowOverride ALL
    Require all granted
</Directory>

COMMENTOUT

sudo systemctl enable apache2
sudo systemctl enable mysqld
# Python 3.x

pip3 install pyserial bitarray pymysql bluepy

#Repository Clone
cd ~/
git clone https://github.com/sensei-sensor/sensei-sensor-device.git


