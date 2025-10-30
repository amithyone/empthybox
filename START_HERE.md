# 🚀 Start Here - Deploy BiggestLogs to Contabo

## Server Information
- **IP Address**: 75.119.139.18
- **Username**: root
- **Password**: 61btnCsn5RUu1UBpJzXLhBmdd

---

## Quick Start - Choose Your Method

### Method 1: Visual File Transfer (Easiest - Recommended)

1. **Download WinSCP**: https://winscp.net/eng/download.php

2. **Connect to Server**:
   - Open WinSCP
   - Host: `75.119.139.18`
   - Username: `root`
   - Password: `61btnCsn5RUu1UBpJzXLhBmdd`
   - Click "Login"

3. **Upload Your Project**:
   - Navigate to `C:\Users\LENOVO LEGION\Documents\Biggestlogs` on the left
   - Navigate to `/root` on the right side
   - Drag and drop your **entire Biggestlogs folder** to the server
   - Wait for upload to complete

4. **SSH into Server**:
   - Click the "Open Terminal" button in WinSCP (or use PuTTY)
   - Run the commands from **QUICK_DEPLOY.md** starting from Step 2

---

### Method 2: Command Line (Advanced)

**Install PuTTY first**: `winget install PuTTY.PuTTY`

Then double-click: `auto-connect.bat`

Or manually:
```cmd
plink -ssh -pw 61btnCsn5RUu1UBpJzXLhBmdd root@75.119.139.18
```

---

### Method 3: VS Code Remote SSH

1. Install "Remote - SSH" extension in VS Code
2. Press `Ctrl+Shift+P`
3. Type "Remote-SSH: Connect to Host"
4. Enter: `root@75.119.139.18`
5. Password: `61btnCsn5RUu1UBpJzXLhBmdd`
6. Open folder: `/root`

---

## What to Do After Connecting

Once you're on the server, follow these steps:

### 1. Basic Server Setup
```bash
# Update system
apt-get update && apt-get upgrade -y

# Install required software
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

### 2. Move Your Uploaded Files
```bash
# If you uploaded via WinSCP to /root
mv /root/Biggestlogs/* /var/www/biggestlogs/

# Or create and extract
mkdir -p /var/www/biggestlogs
cd /var/www/biggestlogs
```

### 3. Install Dependencies
```bash
cd /var/www/biggestlogs

# Install PHP packages
composer install --no-dev --optimize-autoloader

# Install npm packages
npm install
npm run build
```

### 4. Configure Environment
```bash
cp .env.example .env
nano .env
```

**Essential settings in .env**:
```env
APP_NAME=BiggestLogs
APP_ENV=production
APP_DEBUG=false
APP_URL=http://75.119.139.18

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=biggestlogs
DB_USERNAME=biggestlogs
DB_PASSWORD=your_secure_password
```

```bash
# Generate app key
php artisan key:generate
```

### 5. Setup Database
```bash
mysql -u root -p
```

In MySQL:
```sql
CREATE DATABASE biggestlogs;
CREATE USER 'biggestlogs'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON biggestlogs.* TO 'biggestlogs'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 6. Final Steps
```bash
# Run migrations
php artisan migrate --seed

# Set permissions
chown -R www-data:www-data /var/www/biggestlogs
chmod -R 755 /var/www/biggestlogs
chmod -R 775 storage bootstrap/cache
```

### 7. Configure Nginx
```bash
nano /etc/nginx/sites-available/biggestlogs
```

Paste:
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name 75.119.139.18;
    root /var/www/biggestlogs/public;

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

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

```bash
# Enable site
ln -s /etc/nginx/sites-available/biggestlogs /etc/nginx/sites-enabled/
rm /etc/nginx/sites-enabled/default
nginx -t
systemctl restart nginx
```

---

## 🎉 Done!

Visit: **http://75.119.139.18**

**Login Details:**
- **Admin**: admin@biggestlogs.com / password
- **User**: user@test.com / password

---

## 📞 Need Help?

- See **QUICK_DEPLOY.md** for troubleshooting
- See **DEPLOYMENT_SERVER.md** for advanced configuration
- Check server logs: `tail -f /var/www/biggestlogs/storage/logs/laravel.log`

---

**Good luck with your deployment! 🔥**

