@echo off
echo Checking installation status...
"C:\Program Files\PuTTY\plink.exe" -ssh -pw 61btnCsn5RUu1UBpJzXLhBmdd -batch root@75.119.139.18 "php --version 2>&1 | head -3 && echo '---' && composer --version 2>&1 | head -1 && echo '---' && ls -la /var/www/html/phpmyadmin/vendor 2>&1 | head -5"
pause

