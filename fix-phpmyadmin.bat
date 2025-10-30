@echo off
echo Installing phpMyAdmin dependencies...
"C:\Program Files\PuTTY\plink.exe" -ssh -pw 61btnCsn5RUu1UBpJzXLhBmdd -batch root@75.119.139.18 "cd /var/www/html/phpmyadmin && composer install --no-dev && echo 'Dependencies installed!'"
pause

