#!/bin/bash

cd /root

# Create app directory
mkdir -p /var/www/biggestlogs
cd /var/www/biggestlogs

# Install Node.js if not installed
if ! command -v node &> /dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    apt-get install -y nodejs
fi

# Install additional PHP extensions if needed
apt-get update
apt-get install -y php8.1-bcmath php8.1-tokenizer

# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# Install npm dependencies
npm install
npm run build

# Create .env
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Update .env for production
sed -i 's/APP_ENV=local/APP_ENV=production/' .env
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
sed -i 's|APP_URL=.*|APP_URL=https://biggestlogs.com|' .env

# Create database
mysql -u root <<MYSQL_SCRIPT
CREATE DATABASE IF NOT EXISTS biggestlogs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'biggestlogs'@'localhost' IDENTIFIED BY 'BiggestLogs2024!';
GRANT ALL PRIVILEGES ON biggestlogs.* TO 'biggestlogs'@'localhost';
FLUSH PRIVILEGES;
EXIT;
MYSQL_SCRIPT

# Update database credentials
sed -i 's/DB_USERNAME=root/DB_USERNAME=biggestlogs/' .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD='BiggestLogs2024!'/" .env

# Run migrations
php artisan migrate --force --seed

# Create storage link
php artisan storage:link

# Set permissions
chown -R www-data:www-data /var/www/biggestlogs
chmod -R 755 /var/www/biggestlogs
chmod -R 775 storage bootstrap/cache

# Create Apache virtual host for main domain
cat > /etc/apache2/sites-available/biggestlogs.conf <<'APACHE_EOF'
<VirtualHost *:80>
    ServerName biggestlogs.com
    ServerAlias www.biggestlogs.com
    DocumentRoot /var/www/biggestlogs/public

    <Directory /var/www/biggestlogs/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/biggestlogs_error.log
    CustomLog ${APACHE_LOG_DIR}/biggestlogs_access.log combined
</VirtualHost>

<VirtualHost *:443>
    ServerName biggestlogs.com
    ServerAlias www.biggestlogs.com
    DocumentRoot /var/www/biggestlogs/public

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/biggestlogs.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/biggestlogs.com/privkey.pem

    <Directory /var/www/biggestlogs/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/biggestlogs_error.log
    CustomLog ${APACHE_LOG_DIR}/biggestlogs_access.log combined
</VirtualHost>
APACHE_EOF

# Enable site
a2ensite biggestlogs.conf
a2enmod rewrite
systemctl restart apache2

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=============================================="
echo "  Deployment Complete!"
echo "=============================================="
echo "Visit: https://biggestlogs.com"
echo "Admin: admin@biggestlogs.com / password"
echo "=============================================="

