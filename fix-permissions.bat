@echo off
echo Fixing permissions on server...
"C:\Program Files\PuTTY\plink.exe" -ssh -pw 61btnCsn5RUu1UBpJzXLhBmdd -batch root@75.119.139.18 "chown -R www-data:www-data /var/www/biggestlogs && chmod -R 755 /var/www/biggestlogs && chmod -R 775 /var/www/biggestlogs/storage /var/www/biggestlogs/bootstrap/cache && systemctl restart apache2 && echo 'Permissions fixed!'"
pause


