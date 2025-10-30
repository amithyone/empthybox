@echo off
"C:\Program Files\PuTTY\plink.exe" -ssh -pw 61btnCsn5RUu1UBpJzXLhBmdd root@75.119.139.18 "echo '=== PHP Version ===' && php -v 2>&1 | head -1 && echo '' && echo '=== Composer ===' && /usr/local/bin/composer -V 2>&1 | head -1 && echo '' && echo '=== PHPMyAdmin Vendor ===' && ls -la /var/www/html/phpmyadmin/vendor 2>&1 | head -3 && echo '' && echo '=== Apache Config ===' && systemctl status apache2 | head -5"
pause

