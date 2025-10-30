#!/bin/bash
# Configure Apache to use the SSL certificate

echo "Configuring Apache for SSL..."

# Create SSL virtual host
cat > /etc/apache2/sites-available/biggestlogs-ssl.conf << 'EOF'
<VirtualHost *:443>
    ServerName biggestlogs.com
    ServerAlias www.biggestlogs.com *.biggestlogs.com
    DocumentRoot /var/www/biggestlogs/public
    
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/biggestlogs.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/biggestlogs.com/privkey.pem
    
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    
    <Directory /var/www/biggestlogs/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/biggestlogs-ssl-error.log
    CustomLog ${APACHE_LOG_DIR}/biggestlogs-ssl-access.log combined
</VirtualHost>
EOF

# Update HTTP to redirect to HTTPS
cat > /etc/apache2/sites-available/000-default.conf << 'EOF'
<VirtualHost *:80>
    ServerName biggestlogs.com
    ServerAlias www.biggestlogs.com *.biggestlogs.com
    
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]
    
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

# Enable modules and site
a2enmod ssl rewrite headers
a2ensite biggestlogs-ssl.conf

# Create Laravel directory
mkdir -p /var/www/biggestlogs/public
echo "<h1>BiggestLogs - SSL Configured!</h1>" > /var/www/biggestlogs/public/index.html
chown -R www-data:www-data /var/www/biggestlogs

# Test and restart
apache2ctl configtest
systemctl restart apache2

echo ""
echo "SSL fully configured!"
echo "Visit: https://biggestlogs.com"

