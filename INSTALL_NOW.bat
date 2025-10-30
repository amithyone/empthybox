@echo off
"C:\Program Files\PuTTY\plink.exe" -ssh -pw 61btnCsn5RUu1UBpJzXLhBmdd -batch root@75.119.139.18 "apt-get update && apt-get install -y php8.1 php8.1-cli php8.1-mysql php8.1-xml php8.1-mbstring php8.1-curl php8.1-zip php8.1-gd unzip && cd /root && curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer && chmod +x /usr/local/bin/composer && cd /var/www/html/phpmyadmin && /usr/local/bin/composer install --no-dev && echo 'COMPLETE!'"
pause

