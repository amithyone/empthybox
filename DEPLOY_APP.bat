@echo off
echo ========================================
echo   Deploying BiggestLogs to Server
echo ========================================
echo.

echo Step 1: Creating archive...
powershell -Command "Compress-Archive -Path '.' -DestinationPath 'biggestlogs_app.zip' -Force"
echo Archive created!
echo.

echo Step 2: Uploading to server...
"C:\Program Files\PuTTY\pscp.exe" -pw 61btnCsn5RUu1UBpJzXLhBmdd biggestlogs_app.zip root@75.119.139.18:/root/
echo Upload complete!
echo.

echo Step 3: Deploying on server...
"C:\Program Files\PuTTY\plink.exe" -ssh -pw 61btnCsn5RUu1UBpJzXLhBmdd -batch root@75.119.139.18 "cd /root && unzip -q biggestlogs_app.zip -d /var/www/biggestlogs && cd /var/www/biggestlogs && composer install --no-dev && npm install && npm run build && cp .env.example .env && php artisan key:generate && php artisan migrate --force --seed && chown -R www-data:www-data /var/www/biggestlogs && chmod -R 775 storage bootstrap/cache && systemctl restart apache2"

echo.
echo ========================================
echo   Deployment Complete!
echo ========================================
echo Visit: https://biggestlogs.com
pause

