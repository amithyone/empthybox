#!/bin/bash
# Install Apache, PHP, and phpMyAdmin on BiggestLogs Server

echo "🚀 Installing Apache, PHP, and phpMyAdmin..."

# Install Apache and required PHP packages
apt-get update -y
apt-get install -y \
    apache2 \
    php8.1 \
    php8.1-cli \
    php8.1-common \
    php8.1-fpm \
    php8.1-mysql \
    php8.1-zip \
    php8.1-gd \
    php8.1-mbstring \
    php8.1-curl \
    php8.1-xml \
    php8.1-bcmath \
    php8.1-intl \
    php8.1-soap \
    libapache2-mod-php8.1

# Enable Apache modules
echo "⚙️ Configuring Apache..."
a2enmod rewrite
a2enmod ssl
a2enmod php8.1

# Install phpMyAdmin dependencies
echo "📦 Installing phpMyAdmin..."
apt-get install -y \
    php8.1-tidy \
    php8.1-imagick \
    unzip \
    wget

# Download and install phpMyAdmin
cd /var/www/html
wget https://files.phpmyadmin.net/phpMyAdmin/5.2.1/phpMyAdmin-5.2.1-all-languages.zip
unzip phpMyAdmin-5.2.1-all-languages.zip
mv phpMyAdmin-5.2.1-all-languages phpmyadmin
chown -R www-data:www-data phpmyadmin

# Configure phpMyAdmin
echo "🔧 Configuring phpMyAdmin..."
cp /var/www/html/phpmyadmin/config.sample.inc.php /var/www/html/phpmyadmin/config.inc.php

# Generate blowfish secret for phpMyAdmin
BLOWFISH=$(openssl rand -hex 16)
sed -i "s/\$cfg\['blowfish_secret'\] = '';/\$cfg['blowfish_secret'] = '$BLOWFISH';/" /var/www/html/phpmyadmin/config.inc.php

# Create phpMyAdmin configuration for Apache
cat > /etc/apache2/conf-available/phpmyadmin.conf << 'EOF'
Alias /phpmyadmin /var/www/html/phpmyadmin

<Directory /var/www/html/phpmyadmin>
    Options SymLinksIfOwnerMatch
    DirectoryIndex index.php
    
    <IfModule mod_php.c>
        <IfModule mod_mime.c>
            AddType application/x-httpd-php .php
        </IfModule>
        <FilesMatch "\.php$">
            SetHandler application/x-httpd-php
        </FilesMatch>
        php_value upload_max_filesize 128M
        php_value post_max_size 128M
    </IfModule>
    
    Require all granted
</Directory>
EOF

# Enable phpMyAdmin configuration
a2enconf phpmyadmin

# Set proper permissions
chmod -R 755 /var/www/html/phpmyadmin

# Configure MySQL for phpMyAdmin (if MySQL is installed)
if command -v mysql &> /dev/null; then
    echo "🔐 Configuring MySQL access for phpMyAdmin..."
    mysql -e "CREATE USER IF NOT EXISTS 'pma'@'localhost' IDENTIFIED BY 'pmapass123';" 2>/dev/null || echo "MySQL user already exists or MySQL not accessible"
    mysql -e "GRANT ALL PRIVILEGES ON *.* TO 'pma'@'localhost' WITH GRANT OPTION;" 2>/dev/null || true
fi

# Create .htaccess for security
cat > /var/www/html/phpmyadmin/.htaccess << 'EOF'
AuthType Basic
AuthName "phpMyAdmin Login"
AuthUserFile /etc/apache2/.htpasswd
Require valid-user
EOF

# Create basic auth user (optional - you can remove this or change password)
echo "admin:$(openssl passwd -apr1 'admin123')" > /etc/apache2/.htpasswd
chmod 644 /etc/apache2/.htpasswd

# Restart Apache
echo "🔄 Restarting Apache..."
systemctl restart apache2
systemctl enable apache2

# Clean up
rm -f phpMyAdmin-5.2.1-all-languages.zip

echo ""
echo "✅ Installation Complete!"
echo ""
echo "🌐 phpMyAdmin URL:"
echo "   http://75.119.139.18/phpmyadmin"
echo ""
echo "🔐 Login Information:"
echo "   Apache Auth:"
echo "   - Username: admin"
echo "   - Password: admin123"
echo ""
echo "   phpMyAdmin Database:"
echo "   - Username: root"
echo "   - Password: [your MySQL root password]"
echo ""
echo "📝 Security Note:"
echo "   Change the Apache auth password immediately!"
echo "   Run: htpasswd /etc/apache2/.htpasswd admin"
echo ""
echo "🎉 Apache and phpMyAdmin are now installed!"
echo ""

