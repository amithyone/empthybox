#!/bin/bash
# Complete Laravel Deployment Script for Apache

set -e

echo "=========================="
echo "Deploying BiggestLogs..."
echo "=========================="

APP_DIR="/var/www/biggestlogs"
APP_URL="https://biggestlogs.com"

# 1. Create directory
echo "Creating app directory..."
mkdir -p $APP_DIR
cd $APP_DIR

# 2. Install additional dependencies if needed
echo "Installing additional dependencies..."
apt-get update
apt-get install -y php8.1-bcmath php8.1-tokenizer nodejs npm

# 3. Clone repo (if using git) OR upload files
echo "Files should be uploaded to: $APP_DIR"
echo "Continuing with setup..."

# 4. Install composer dependencies
echo "Installing Composer dependencies..."
cd $APP_DIR
composer install --no-dev --optimize-autoloader

# 5. Install npm dependencies and build
echo "Installing npm dependencies..."
npm install
npm run build

# 6. Create .env if not exists
if [ ! -f $APP_DIR/.env ]; then
    echo "Creating .env file..."
    cp $APP_DIR/.env.example $APP_DIR/.env
    php artisan key:generate
    
    # Update .env for production
    sed -i 's/APP_ENV=local/APP_ENV=production/' $APP_DIR/.env
    sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' $APP_DIR/.env
    sed -i "s|APP_URL=.*|APP_URL=$APP_URL|" $APP_DIR/.env
fi

# 7. Create database (if not exists)
echo "Setting up database..."
mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS biggestlogs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'biggestlogs'@'localhost' IDENTIFIED BY 'BiggestLogs2024!';
GRANT ALL PRIVILEGES ON biggestlogs.* TO 'biggestlogs'@'localhost';
FLUSH PRIVILEGES;
EXIT;
EOF

# Update .env with database credentials
sed -i 's/DB_DATABASE=biggestlogs/DB_DATABASE=biggestlogs/' $APP_DIR/.env
sed -i 's/DB_USERNAME=root/DB_USERNAME=biggestlogs/' $APP_DIR/.env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD='BiggestLogs2024!'/" $APP_DIR/.env

# 8. Run migrations
echo "Running migrations..."
php artisan migrate --force --seed

# 9. Create storage link
echo "Creating storage link..."
php artisan storage:link

# 10. Set permissions
echo "Setting permissions..."
chown -R www-data:www-data $APP_DIR
chmod -R 755 $APP_DIR
chmod -R 775 $APP_DIR/storage
chmod -R 775 $APP_DIR/bootstrap/cache

# 11. Create Apache Virtual Host
echo "Creating Apache Virtual Host..."
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

# 12. Enable site
echo "Enabling Apache site..."
a2ensite biggestlogs.conf
a2enmod rewrite
systemctl restart apache2

# 13. Optimize Laravel
echo "Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=========================="
echo "Deployment Complete!"
echo "=========================="
echo "Visit: $APP_URL"
echo "Admin: admin@biggestlogs.com / password"

