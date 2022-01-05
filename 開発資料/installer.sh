#!/bin/sh

## My SQL or Maria DB##

<< COMMENTOUT

mysql_secure_installation
sudo mysql - u root -p
MariaDB [(none)] > ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'enter_password_here';

---## If you can't run this command. ##---
    MariaDB [(none)] > UPDATE mysql.user SET plugin = 'mysql_native_password' WHERE user = 'root';

MariaDB [(none)] > FLUSH PRIVILEGES;
MariaDB [(none)] > exit;
sudo systemctl restart mysqld

COMMENTOUT

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
sudo apt install -y git  python3-pip apache2 php libapache2-mod-php php-mysql php-curl mariadb-server mariadb-client php-xml php-mbstring vim

sudo systemctl enable apache2
sudo systemctl enable mysqld
# Python 3.x

pip3 install pyserial bitarray pymysql bluepy

#Repository Clone
cd ~/
git clone https://github.com/sensei-sensor/sensei-sensor-device.git