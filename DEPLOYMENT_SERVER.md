# BiggestLogs Server Deployment Guide
## Contabo Server: 75.119.139.18

### Quick Setup Steps

#### 1. Connect to Server
```bash
ssh root@75.119.139.18
# Password: 61btnCsn5RUu1UBpJzXLhBmdd
```

#### 2. Initial Server Setup
```bash
# Update system
apt-get update && apt-get upgrade -y

# Install required packages
apt-get install -y nginx mysql-server php8.1-fpm php8.1-mysql php8.1-xml \
    php8.1-mbstring php8.1-curl php8.1-zip php8.1-gd php8.1-bcmath \
    php8.1-tokenizer unzip git curl

# Install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt-get install -y nodejs
```

#### 3. Prepare Application Directory
```bash
mkdir -p /var/www/biggestlogs
cd /var/www/biggestlogs
```

#### 4. Transfer Your Code

**Option A: Using Git (Recommended)**
```bash
# If you have a Git repository
git clone YOUR_REPO_URL /var/www/biggestlogs

# Or initialize and pull
git init
git remote add origin YOUR_REPO_URL
git pull origin main
```

**Option B: Using SCP from your local machine**
```bash
# From your local Windows machine, in PowerShell:
# First install WinSCP or use this command:
scp -r C:\Users\LENOVO\Documents\Biggestlogs\* root@75.119.139.18:/var/www/biggestlogs/
```

**Option C: Manual Upload**
1. Compress your project: `tar -czf biggestlogs.tar.gz .`
2. Use FTP/SFTP to upload
3. Extract on server: `tar -xzf biggestlogs.tar.gz`

#### 5. Install Dependencies
```bash
cd /var/www/biggestlogs

# PHP dependencies
composer install --no-dev --optimize-autoloader

# Node dependencies
npm install
npm run build
```

#### 6. Configure Environment
```bash
cp .env.example .env
nano .env  # Or use vi/vim
```

**Essential .env settings:**
```env
APP_NAME=BiggestLogs
APP_ENV=production
APP_KEY=  # Will be generated
APP_DEBUG=false
APP_URL=http://75.119.139.18

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=biggestlogs
DB_USERNAME=biggestlogs
DB_PASSWORD=YOUR_SECURE_PASSWORD

# Add payment gateways, SMS, etc.
```

**Generate app key:**
```bash
php artisan key:generate
```

#### 7. Setup Database
```bash
mysql -u root -p
```

In MySQL console:
```sql
CREATE DATABASE biggestlogs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'biggestlogs'@'localhost' IDENTIFIED BY 'YOUR_SECURE_PASSWORD';
GRANT ALL PRIVILEGES ON biggestlogs.* TO 'biggestlogs'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Update your .env with the database credentials.

#### 8. Run Migrations
```bash
php artisan migrate --force --seed
```

#### 9. Set Permissions
```bash
chown -R www-data:www-data /var/www/biggestlogs
chmod -R 755 /var/www/biggestlogs
chmod -R 775 /var/www/biggestlogs/storage
chmod -R 775 /var/www/biggestlogs/bootstrap/cache
```

#### 10. Configure Nginx
```bash
nano /etc/nginx/sites-available/biggestlogs
```

Add this configuration:
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name _;  # Replace with your domain later
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

    # Increase upload limits
    client_max_body_size 100M;
}
```

Enable the site:
```bash
ln -s /etc/nginx/sites-available/biggestlogs /etc/nginx/sites-enabled/
rm /etc/nginx/sites-enabled/default
nginx -t
systemctl restart nginx
systemctl restart php8.1-fpm
```

#### 11. Setup Queue Workers (Optional but Recommended)
```bash
apt-get install -y supervisor

nano /etc/supervisor/conf.d/biggestlogs-worker.conf
```

Add:
```ini
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
```

Enable supervisor:
```bash
supervisorctl reread
supervisorctl update
supervisorctl start biggestlogs-worker:*
```

#### 12. Setup Firewall (Recommended)
```bash
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw enable
```

#### 13. Setup SSL Certificate (Optional but Recommended)
```bash
apt-get install -y certbot python3-certbot-nginx
certbot --nginx -d your-domain.com
```

#### 14. Access Your Application
Open browser and navigate to:
```
http://75.119.139.18
```

**Default Admin Login:**
- Email: admin@biggestlogs.com
- Password: password

**Default User Login:**
- Email: user@test.com
- Password: password

### Useful Commands

```bash
# View logs
tail -f /var/www/biggestlogs/storage/logs/laravel.log

# Restart services
systemctl restart nginx
systemctl restart php8.1-fpm

# Check Nginx config
nginx -t

# Check PHP-FPM status
systemctl status php8.1-fpm

# Update code from Git
cd /var/www/biggestlogs
git pull origin main
composer install --no-dev
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Security Checklist
- [ ] Change default admin password
- [ ] Configure firewall (ufw)
- [ ] Setup SSL certificate
- [ ] Review .env permissions
- [ ] Enable 2FA if possible
- [ ] Setup automated backups
- [ ] Configure log rotation

### Troubleshooting

**502 Bad Gateway:**
```bash
systemctl status php8.1-fpm
systemctl restart php8.1-fpm
```

**Permission denied:**
```bash
chown -R www-data:www-data /var/www/biggestlogs
chmod -R 755 /var/www/biggestlogs
chmod -R 775 storage bootstrap/cache
```

**Database connection error:**
- Check .env database credentials
- Verify MySQL is running: `systemctl status mysql`
- Test connection: `mysql -u biggestlogs -p`

**Clear all caches:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

Good luck with your deployment! 🔥

