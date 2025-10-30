@echo off
echo Creating db.biggestlogs.com configuration...
echo.

"C:\Program Files\PuTTY\plink.exe" -ssh -pw 61btnCsn5RUu1UBpJzXLhBmdd -batch root@75.119.139.18 "printf '<VirtualHost *:443>\n    ServerName db.biggestlogs.com\n    DocumentRoot /var/www/html/phpmyadmin\n    SSLEngine on\n    SSLCertificateFile /etc/letsencrypt/live/biggestlogs.com/fullchain.pem\n    SSLCertificateKeyFile /etc/letsencrypt/live/biggestlogs.com/privkey.pem\n    <Directory /var/www/html/phpmyadmin>\n        Options -Indexes +FollowSymLinks\n        AllowOverride All\n        Require all granted\n    </Directory>\n</VirtualHost>\n<VirtualHost *:80>\n    ServerName db.biggestlogs.com\n    RewriteEngine On\n    RewriteRule ^(.*)$ https://%%{HTTP_HOST}%%{REQUEST_URI} [R=301,L]\n</VirtualHost>\n' > /etc/apache2/sites-available/db.biggestlogs.com.conf && a2ensite db.biggestlogs.com.conf && apache2ctl configtest && systemctl restart apache2 && echo 'SUBDOMAINS CONFIGURED! Visit https://db.biggestlogs.com'"

echo.
echo Done!
pause

