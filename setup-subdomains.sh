#!/bin/bash
# Configure subdomains for Webmin and phpMyAdmin

echo "Configuring subdomains..."

# Create Webmin subdomain configuration (Reverse proxy)
cat > /etc/apache2/sites-available/server.biggestlogs.com.conf << 'EOF'
<VirtualHost *:443>
    ServerName server.biggestlogs.com
    
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/biggestlogs.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/biggestlogs.com/privkey.pem
    
    # Reverse proxy to Webmin (port 10000)
    ProxyPreserveHost On
    ProxyPass / https://localhost:10000/
    ProxyPassReverse / https://localhost:10000/
    
    # SSL Configuration for proxying to Webmin
    SSLProxyEngine on
    SSLProxyVerify none
    SSLProxyCheckPeerCN off
    SSLProxyCheckPeerName off
    
    ErrorLog ${APACHE_LOG_DIR}/webmin-subdomain-error.log
    CustomLog ${APACHE_LOG_DIR}/webmin-subdomain-access.log combined
</VirtualHost>

<VirtualHost *:80>
    ServerName server.biggestlogs.com
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]
</VirtualHost>
EOF

# Create phpMyAdmin subdomain configuration
cat > /etc/apache2/sites-available/db.biggestlogs.com.conf << 'EOF'
<VirtualHost *:443>
    ServerName db.biggestlogs.com
    DocumentRoot /var/www/html/phpmyadmin
    
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/biggestlogs.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/biggestlogs.com/privkey.pem
    
    # Optional: Add basic auth for extra security
    # <Location />
    #     AuthType Basic
    #     AuthName "phpMyAdmin Access"
    #     AuthUserFile /etc/apache2/.htpasswd
    #     Require valid-user
    # </Location>
    
    <Directory /var/www/html/phpmyadmin>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/phpmyadmin-subdomain-error.log
    CustomLog ${APACHE_LOG_DIR}/phpmyadmin-subdomain-access.log combined
</VirtualHost>

<VirtualHost *:80>
    ServerName db.biggestlogs.com
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]
</VirtualHost>
EOF

# Install and enable proxy modules for Webmin
a2enmod proxy
a2enmod proxy_http
a2enmod proxy_wstunnel
a2enmod ssl
a2enmod rewrite

# Enable sites
a2ensite server.biggestlogs.com.conf
a2ensite db.biggestlogs.com.conf

# Create .htpasswd for phpMyAdmin security (optional)
if [ ! -f /etc/apache2/.htpasswd ]; then
    echo "Creating phpMyAdmin security password..."
    htpasswd -cb /etc/apache2/.htpasswd admin admin123
    echo "Default login: admin / admin123 (change this!)"
fi

# Test configuration
apache2ctl configtest

# Restart Apache
systemctl restart apache2

echo ""
echo "Subdomains configured!"
echo ""
echo "Access points:"
echo "- Webmin: https://server.biggestlogs.com"
echo "- phpMyAdmin: https://db.biggestlogs.com"
echo ""
echo "Main site: https://biggestlogs.com"
echo ""

