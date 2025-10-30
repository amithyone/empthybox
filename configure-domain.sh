#!/bin/bash
# Configure Apache for biggestlogs.com domain

echo "🌐 Configuring Apache for biggestlogs.com..."

# Create virtual host
cat > /etc/apache2/sites-available/biggestlogs.conf << 'EOF'
<VirtualHost *:80>
    ServerName biggestlogs.com
    ServerAlias www.biggestlogs.com *.biggestlogs.com
    DocumentRoot /var/www/biggestlogs/public
    
    <Directory /var/www/biggestlogs/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/biggestlogs-error.log
    CustomLog ${APACHE_LOG_DIR}/biggestlogs-access.log combined
    
    # Increase upload limits
    LimitRequestBody 104857600
</VirtualHost>
EOF

# Enable Apache modules
a2enmod rewrite
a2enmod ssl
a2enmod headers

# Enable site
a2ensite biggestlogs.conf
a2dissite 000-default.conf

# Create Laravel directories
mkdir -p /var/www/biggestlogs/public
chown -R www-data:www-data /var/www/biggestlogs

# Test Apache config
apache2ctl configtest

# Restart Apache
systemctl restart apache2

echo ""
echo "✅ Domain configuration complete!"
echo ""
echo "🌐 Your domain is now configured:"
echo "   - biggestlogs.com → /var/www/biggestlogs/public"
echo "   - www.biggestlogs.com → same directory"
echo "   - *.biggestlogs.com → same directory (all subdomains)"
echo ""
echo "📝 Next steps:"
echo "   1. Add DNS records at your registrar"
echo "   2. Wait 5-15 minutes for DNS propagation"
echo "   3. Install SSL: certbot --apache -d biggestlogs.com -d www.biggestlogs.com"
echo ""

