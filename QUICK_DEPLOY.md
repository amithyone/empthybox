# Quick Deployment to Contabo Server

## Server Details
- IP: 75.119.139.18
- User: root
- Password: 61btnCsn5RUu1UBpJzXLhBmdd

## Method 1: Manual File Transfer

### Step 1: Upload Files to Server

Use one of these methods:

**A. Using WinSCP (Windows)**
1. Download WinSCP: https://winscp.net
2. Connect to: `root@75.119.139.18`
3. Password: `61btnCsn5RUu1UBpJzXLhBmdd`
4. Upload your entire Biggestlogs folder to `/root/biggestlogs`

**B. Using SFTP from PowerShell**
```powershell
# Compress first
cd C:\Users\LENOVO` LEGION\Documents\Biggestlogs
tar -czf biggestlogs.tar.gz .

# Then SFTP
sftp root@75.119.139.18
put biggestlogs.tar.gz /root/
exit
```

**C. Using Git (If you have a repository)**
```bash
ssh root@75.119.139.18
cd /root
git clone YOUR_REPO_URL biggestlogs
```

### Step 2: Initial Server Setup

SSH into your server:
```bash
ssh root@75.119.139.18
```

Run these commands:
```bash
# Update system
apt-get update && apt-get upgrade -y

# Install required packages
apt-get install -y nginx mysql-server php8.1-fpm php8.1-mysql \
    php8.1-xml php8.1-mbstring php8.1-curl php8.1-zip php8.1-gd \
    php8.1-bcmath php8.1-tokenizer unzip git curl

# Install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt-get install -y nodejs
```

### Step 3: Setup Application

```bash
# If you uploaded tar.gz, extract it:
cd /root
tar -xzf biggestlogs.tar.gz -C /var/www/biggestlogs
cd /var/www/biggestlogs

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install npm dependencies
npm install
npm run build

# Configure environment
cp .env.example .env
nano .env  # Edit database and other settings

# Generate key
php artisan key:generate
```

### Step 4: Configure Database

```bash
mysql -u root -p
```

In MySQL:
```sql
CREATE DATABASE biggestlogs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'biggestlogs'@'localhost' IDENTIFIED BY 'YOUR_SECURE_PASSWORD';
GRANT ALL PRIVILEGES ON biggestlogs.* TO 'biggestlogs'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Update your `.env` file with database credentials.

### Step 5: Run Migrations & Set Permissions

```bash
cd /var/www/biggestlogs

# Run migrations
php artisan migrate --force --seed

# Set permissions
chown -R www-data:www-data /var/www/biggestlogs
chmod -R 755 /var/www/biggestlogs
chmod -R 775 storage bootstrap/cache
```

### Step 6: Configure Nginx

Create Nginx config:
```bash
nano /etc/nginx/sites-available/biggestlogs
```

Paste this configuration:
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name 75.119.139.18;  # Or your domain
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

    client_max_body_size 100M;
}
```

Enable and restart:
```bash
ln -s /etc/nginx/sites-available/biggestlogs /etc/nginx/sites-enabled/
rm /etc/nginx/sites-enabled/default
nginx -t
systemctl restart nginx
systemctl restart php8.1-fpm
```

### Step 7: Access Your Site

Open browser and go to: **http://75.119.139.18**

**Default Logins:**
- Admin: admin@biggestlogs.com / password
- User: user@test.com / password

## Need Help?

See **DEPLOYMENT_SERVER.md** for detailed troubleshooting and advanced configuration.

## Important Security Notes

1. **Change default passwords immediately**
2. **Configure firewall**: `ufw allow 22 && ufw allow 80 && ufw allow 443 && ufw enable`
3. **Setup SSL certificate**: Use Let's Encrypt
4. **Regular backups**: Configure automated database backups

Good luck! 🔥

