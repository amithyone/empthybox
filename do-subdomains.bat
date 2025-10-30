@echo off
title Setting up Subdomains
echo.
echo Configuring subdomains...
echo.

"C:\Program Files\PuTTY\plink.exe" -ssh -pw 61btnCsn5RUu1UBpJzXLhBmdd -batch root@75.119.139.18 "cat > /etc/apache2/sites-available/server.biggestlogs.com.conf << 'EOFWB'
<VirtualHost *:443>
    ServerName server.biggestlogs.com
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/biggestlogs.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/biggestlogs.com/privkey.pem
    ProxyPreserveHost On
    ProxyPass / https://localhost:10000/
    ProxyPassReverse / https://localhost:10000/
    SSLProxyEngine on
    SSLProxyVerify none
</VirtualHost>
<VirtualHost *:80>
    ServerName server.biggestlogs.com
    RewriteEngine On
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]
</VirtualHost>
EOFWB
cat > /etc/apache2/sites-available/db.biggestlogs.com.conf << 'EOFDB'
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
EOFDB
a2enmod proxy proxy_http ssl rewrite && a2ensite server.biggestlogs.com.conf && a2ensite db.biggestlogs.com.conf && apache2ctl configtest && systemctl restart apache2 && echo 'SUBDOMAINS CONFIGURED! Webmin: https://server.biggestlogs.com | phpMyAdmin: https://db.biggestlogs.com'"

echo.
echo Done!
pause

