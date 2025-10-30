@echo off
echo Uploading project files to server...

cd C:\Users\LENOVO" "LEGION\Documents\Biggestlogs

"C:\Program Files\PuTTY\pscp.exe" -pw 61btnCsn5RUu1UBpJzXLhBmdd -r app/ artisan bootstrap/ config/ database/ public/ resources/ routes/ storage/ composer.json composer.lock package.json vite.config.js tailwind.config.js postcss.config.js .env.example root@75.119.139.18:/var/www/biggestlogs/

echo Upload complete!
pause

