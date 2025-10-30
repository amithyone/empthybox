#!/bin/bash
# Complete phpMyAdmin fix
echo "Installing PHP and configuring Apache..."

# Install PHP
apt-get update
apt-get install -y php8.1 php8.1-cli php8.1-fpm php8.1-mysql php8.1-xml php8.1-mbstring php8.1-curl php8.1-zip php8.1-gd libapache2-mod-php8.1

# Enable PHP module
a2enmod php8.1

# Install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Install phpMyAdmin dependencies
cd /var/www/html/phpmyadmin
composer install --no-dev

# Configure phpMyAdmin
if [ ! -f /var/www/html/phpmyadmin/config.inc.php ]; then
    cp /var/www/html/phpmyadmin/config.sample.inc.php /var/www/html/phpmyadmin/config.inc.php
    echo "\$cfg['blowfish_secret'] = '$(openssl rand -base64 32)';" >> /var/www/html/phpmyadmin/config.inc.php
fi

# Set correct permissions
chown -R www-data:www-data /var/www/html/phpmyadmin
chmod -R 755 /var/www/html/phpmyadmin

# Restart Apache
systemctl restart apache2

echo "Done! phpMyAdmin should work now at https://db.biggestlogs.com"

