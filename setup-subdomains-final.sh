#!/bin/bash
cat > /etc/apache2/sites-available/db.biggestlogs.com.conf << 'EOF'
<VirtualHost *:443>
    ServerName db.biggestlogs.com
    DocumentRoot /var/www/html/phpmyadmin
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/biggestlogs.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/biggestlogs.com/privkey.pem
    <Directory /var/www/html/phpmyadmin>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>

<VirtualHost *:80>
    ServerName db.biggestlogs.com
    RewriteEngine On
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]
</VirtualHost>
EOF

a2ensite db.biggestlogs.com.conf
apache2ctl configtest
systemctl restart apache2

echo ""
echo "============================================"
echo "  ALL SUBDOMAINS CONFIGURED!"
echo "============================================"
echo ""
echo "Webmin: https://server.biggestlogs.com"
echo "phpMyAdmin: https://db.biggestlogs.com"
echo ""
echo "Visit them now!"
echo "============================================"

