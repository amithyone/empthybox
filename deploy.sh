#!/bin/bash
# BiggestLogs Deployment Script for Contabo Server
# Server: 75.119.139.18

set -e

echo "🚀 Starting BiggestLogs Deployment..."

# Check if we're on the server
if [ ! -d "/var/www" ]; then
    echo "This script must be run on the target server"
    exit 1
fi

# Update system
echo "📦 Updating system packages..."
apt-get update
apt-get upgrade -y

# Install required software
echo "🔧 Installing required software..."
apt-get install -y \
    nginx \
    mysql-server \
    php8.1-fpm \
    php8.1-mysql \
    php8.1-xml \
    php8.1-mbstring \
    php8.1-curl \
    php8.1-zip \
    php8.1-gd \
    php8.1-bcmath \
    php8.1-tokenizer \
    unzip \
    git \
    curl \
    supervisor

# Install Composer
if ! command -v composer &> /dev/null; then
    echo "📥 Installing Composer..."
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
fi

# Install Node.js and npm
if ! command -v node &> /dev/null; then
    echo "📥 Installing Node.js..."
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    apt-get install -y nodejs
fi

# Create application directory
echo "📁 Creating application directory..."
mkdir -p /var/www/biggestlogs
cd /var/www/biggestlogs

# Get repository URL from user
read -p "Enter your Git repository URL: " REPO_URL

if [ -z "$REPO_URL" ]; then
    echo "❌ Git repository URL is required"
    exit 1
fi

# Clone or pull the repository
if [ -d ".git" ]; then
    echo "📥 Pulling latest changes..."
    git pull
else
    echo "📥 Cloning repository..."
    git clone $REPO_URL .
fi

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# Install npm dependencies
echo "📦 Installing npm dependencies..."
npm install

# Build assets
echo "🎨 Building assets..."
npm run build

# Configure environment
echo "⚙️ Configuring environment..."
if [ ! -f ".env" ]; then
    cp .env.example .env
    
    # Generate app key
    php artisan key:generate
    
    echo ""
    echo "🔐 Please configure your .env file:"
    echo "Edit: /var/www/biggestlogs/.env"
    echo ""
    echo "Required settings:"
    echo "- APP_URL=http://your-domain.com"
    echo "- DB_CONNECTION=mysql"
    echo "- DB_HOST=127.0.0.1"
    echo "- DB_DATABASE=biggestlogs"
    echo "- DB_USERNAME=biggestlogs"
    echo "- DB_PASSWORD=your_secure_password"
    echo ""
    read -p "Press Enter after configuring .env..."
fi

# Create database
echo "🗄️ Setting up database..."
MYSQL_PASSWORD=$(openssl rand -base64 32)
mysql -e "CREATE DATABASE IF NOT EXISTS biggestlogs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS 'biggestlogs'@'localhost' IDENTIFIED BY '$MYSQL_PASSWORD';"
mysql -e "GRANT ALL PRIVILEGES ON biggestlogs.* TO 'biggestlogs'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

# Update .env with database credentials
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$MYSQL_PASSWORD/" .env

# Run migrations
echo "🔄 Running database migrations..."
php artisan migrate --force --seed

# Set permissions
echo "🔒 Setting permissions..."
chown -R www-data:www-data /var/www/biggestlogs
chmod -R 755 /var/www/biggestlogs
chmod -R 775 /var/www/biggestlogs/storage
chmod -R 775 /var/www/biggestlogs/bootstrap/cache

# Configure Nginx
echo "🌐 Configuring Nginx..."
cat > /etc/nginx/sites-available/biggestlogs << 'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name _;
    root /var/www/biggestlogs/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# Enable site
ln -sf /etc/nginx/sites-available/biggestlogs /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Test Nginx configuration
nginx -t

# Restart services
echo "🔄 Restarting services..."
systemctl restart nginx
systemctl restart php8.1-fpm
systemctl enable nginx
systemctl enable php8.1-fpm

# Setup supervisor for queue workers (optional)
echo "⚙️ Configuring supervisor for queue workers..."
cat > /etc/supervisor/conf.d/biggestlogs-worker.conf << 'EOF'
[program:biggestlogs-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/biggestlogs/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/biggestlogs/storage/logs/worker.log
stopwaitsecs=3600
EOF

supervisorctl reread
supervisorctl update
supervisorctl start biggestlogs-worker:*

echo ""
echo "✅ Deployment completed successfully!"
echo ""
echo "🌐 Your BiggestLogs application should now be accessible at:"
echo "   http://$(hostname -I | awk '{print $1}')"
echo ""
echo "📝 Database credentials saved in: /var/www/biggestlogs/.env"
echo "🔐 Admin login: admin@biggestlogs.com / password"
echo ""
echo "📋 Next steps:"
echo "1. Configure DNS to point to this server's IP"
echo "2. Set up SSL certificate (Let's Encrypt recommended)"
echo "3. Update .env with your domain name and configure payment gateways"
echo ""

