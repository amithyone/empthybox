apt-get update && apt-get install -y php8.1 php8.1-cli php8.1-fpm php8.1-mysql php8.1-xml php8.1-mbstring php8.1-curl php8.1-zip php8.1-gd php8.1-bcmath unzip && cd /root && curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer && chmod +x /usr/local/bin/composer && cd /var/www/html/phpmyadmin && composer install --no-dev && echo "DONE! phpMyAdmin ready!"

