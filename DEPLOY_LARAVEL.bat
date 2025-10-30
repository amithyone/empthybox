@echo off
echo ================================================
echo   Deploying BiggestLogs Laravel App
echo ================================================
echo.

echo Step 1: Creating project archive...
tar -czf biggestlogs.tar.gz --exclude=node_modules --exclude=vendor --exclude=.git --exclude=.env app/ artisan bootstrap/ config/ database/ public/ resources/ routes/ storage/ composer.json composer.lock package.json vite.config.js tailwind.config.js postcss.config.js .env.example

echo.
echo Step 2: Uploading to server...
"C:\Program Files\PuTTY\pscp.exe" -pw 61btnCsn5RUu1UBpJzXLhBmdd biggestlogs.tar.gz root@75.119.139.18:/root/
"C:\Program Files\PuTTY\pscp.exe" -pw 61btnCsn5RUu1UBpJzXLhBmdd setup-laravel.sh root@75.119.139.18:/root/

echo.
echo Step 3: Extracting and setting up on server...
"C:\Program Files\PuTTY\plink.exe" -ssh -pw 61btnCsn5RUu1UBpJzXLhBmdd -batch root@75.119.139.18 "rm -rf /var/www/biggestlogs && mkdir -p /var/www/biggestlogs && cd /root && tar -xzf biggestlogs.tar.gz -C /var/www/biggestlogs && bash /root/setup-laravel.sh"

echo.
echo ================================================
echo   Deployment Complete!
echo ================================================
echo Visit: https://biggestlogs.com
echo Admin: admin@biggestlogs.com / password
echo ================================================
echo.
pause

