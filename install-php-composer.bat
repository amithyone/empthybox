@echo off
echo Installing PHP and Composer on server...
"C:\Program Files\PuTTY\plink.exe" -ssh -pw 61btnCsn5RUu1UBpJzXLhBmdd -batch root@75.119.139.18 "apt-get update && apt-get install -y php8.1 php8.1-cli php8.1-fpm php8.1-mysql php8.1-xml php8.1-mbstring php8.1-curl php8.1-zip php8.1-gd && cd /root && curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer && chmod +x /usr/local/bin/composer && cd /var/www/html/phpmyadmin && composer install --no-dev && systemctl restart apache2 && echo 'COMPLETE! Check https://db.biggestlogs.com'"
pause

