#!/bin/bash
# Install Let's Encrypt SSL Certificate for biggestlogs.com
# Free SSL that auto-renews - valid for 90 days, renews automatically

echo "🔒 Installing Free SSL Certificate (Let's Encrypt)..."

# Update system
apt-get update -y

# Install Certbot
echo "📦 Installing Certbot..."
apt-get install -y certbot python3-certbot-apache

# Stop Apache temporarily to free port 80
echo "⏸️ Temporarily stopping Apache..."
systemctl stop apache2

# Get SSL certificate for domain and www
echo "🔐 Obtaining SSL certificate..."
certbot certonly --standalone -d biggestlogs.com -d www.biggestlogs.com --non-interactive --agree-tos --register-unsafely-without-email

# Check if certificate was issued
if [ -f "/etc/letsencrypt/live/biggestlogs.com/fullchain.pem" ]; then
    echo "✅ SSL certificate obtained successfully!"
    
    # Configure Apache for SSL
    echo "⚙️ Configuring Apache for SSL..."
    
    # Create Apache SSL configuration
    cat > /etc/apache2/sites-available/biggestlogs-ssl.conf << 'EOF'
<VirtualHost *:443>
    ServerName biggestlogs.com
    ServerAlias www.biggestlogs.com *.biggestlogs.com
    
    DocumentRoot /var/www/biggestlogs/public
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/biggestlogs.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/biggestlogs.com/privkey.pem
    
    # Security headers
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

    # Create HTTP redirect to HTTPS
    cat > /etc/apache2/sites-available/biggestlogs.conf << 'EOF'
<VirtualHost *:80>
    ServerName biggestlogs.com
    ServerAlias www.biggestlogs.com *.biggestlogs.com
    
    # Redirect all HTTP to HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]
    
    ErrorLog ${APACHE_LOG_DIR}/biggestlogs-error.log
    CustomLog ${APACHE_LOG_DIR}/biggestlogs-access.log combined
</VirtualHost>
EOF

    # Enable sites
    a2ensite biggestlogs.conf
    a2ensite biggestlogs-ssl.conf
    a2dissite 000-default.conf
    
    # Enable SSL module
    a2enmod ssl
    a2enmod rewrite
    a2enmod headers
    
    # Test configuration
    apache2ctl configtest
    
    # Start Apache
    systemctl start apache2
    systemctl enable apache2
    
    # Setup auto-renewal
    echo "🔄 Setting up automatic renewal..."
    cat > /etc/cron.monthly/certbot-renew << 'EOF'
#!/bin/bash
certbot renew --quiet --post-hook "systemctl reload apache2"
EOF
    chmod +x /etc/cron.monthly/certbot-renew
    
    # Test renewal
    certbot renew --dry-run
    
    echo ""
    echo "✅ SSL CERTIFICATE INSTALLED SUCCESSFULLY!"
    echo ""
    echo "🌐 Your site is now secured:"
    echo "   https://biggestlogs.com"
    echo "   https://www.biggestlogs.com"
    echo ""
    echo "🔒 Certificate Details:"
    echo "   - Issued by: Let's Encrypt"
    echo "   - Valid for: 90 days"
    echo "   - Auto-renewal: Enabled (renews every month)"
    echo "   - All HTTP traffic: Redirected to HTTPS"
    echo ""
    echo "🎉 SSL is FREE and will auto-renew forever!"
    echo ""
    
else
    echo "❌ SSL certificate installation failed!"
    echo ""
    echo "Possible reasons:"
    echo "1. DNS not propagated yet (wait 15-30 minutes)"
    echo "2. Domain not pointing to this server"
    echo "3. Port 80 blocked"
    echo ""
    echo "To retry later: bash install-ssl.sh"
    exit 1
fi

