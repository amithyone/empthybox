@echo off
echo ================================================
echo   Complete BiggestLogs Deployment
echo ================================================
echo.

echo Step 1: Creating directory on server...
"C:\Program Files\PuTTY\plink.exe" -ssh -pw 61btnCsn5RUu1UBpJzXLhBmdd -batch root@75.119.139.18 "mkdir -p /var/www/biggestlogs"

echo.
echo Step 2: Uploading deployment script...
"C:\Program Files\PuTTY\pscp.exe" -pw 61btnCsn5RUu1UBpJzXLhBmdd final-deploy.sh root@75.119.139.18:/root/

echo.
echo Step 3: Uploading project files (this may take a minute)...
"C:\Program Files\PuTTY\pscp.exe" -pw 61btnCsn5RUu1UBpJzXLhBmdd -r app\ root@75.119.139.18:/var/www/biggestlogs/app/
"C:\Program Files\PuTTY\pscp.exe" -pw 61btnCsn5RUu1UBpJzXLhBmdd artisan root@75.119.139.18:/var/www/biggestlogs/
"C:\Program Files\PuTTY\pscp.exe" -pw 61btnCsn5RUu1UBpJzXLhBmdd -r bootstrap\ root@75.119.139.18:/var/www/biggestlogs/bootstrap/
"C:\Program Files\PuTTY\pscp.exe" -pw 61btnCsn5RUu1UBpJzXLhBmdd -r config\ root@75.119.139.18:/var/www/biggestlogs/config/
"C:\Program Files\PuTTY\pscp.exe" -pw 61btnCsn5RUu1UBpJzXLhBmdd -r database\ root@75.119.139.18:/var/www/biggestlogs/database/
"C:\Program Files\PuTTY\pscp.exe" -pw 61btnCsn5RUu1UBpJzXLhBmdd -r public\ root@75.119.139.18:/var/www/biggestlogs/public/
"C:\Program Files\PuTTY\pscp.exe" -pw 61btnCsn5RUu1UBpJzXLhBmdd -r resources\ root@75.119.139.18:/var/www/biggestlogs/resources/
"C:\Program Files\PuTTY\pscp.exe" -pw 61btnCsn5RUu1UBpJzXLhBmdd -r routes\ root@75.119.139.18:/var/www/biggestlogs/routes/
"C:\Program Files\PuTTY\pscp.exe" -pw 61btnCsn5RUu1UBpJzXLhBmdd -r storage\ root@75.119.139.18:/var/www/biggestlogs/storage/

echo.
echo Step 4: Uploading configuration files...
"C:\Program Files\PuTTY\pscp.exe" -pw 61btnCsn5RUu1UBpJzXLhBmdd composer.json composer.lock package.json vite.config.js tailwind.config.js postcss.config.js .env.example root@75.119.139.18:/var/www/biggestlogs/

echo.
echo Step 5: Running deployment script (this will take 5-10 minutes)...
"C:\Program Files\PuTTY\plink.exe" -ssh -pw 61btnCsn5RUu1UBpJzXLhBmdd -batch root@75.119.139.18 "bash /root/final-deploy.sh"

echo.
echo ================================================
echo   Deployment Complete!
echo ================================================
echo Visit: https://biggestlogs.com
echo Admin: admin@biggestlogs.com / password
echo ================================================
pause

