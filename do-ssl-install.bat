@echo off
title Installing SSL Certificate
echo.
echo ========================================
echo   Installing Free SSL Certificate
echo   Domain: biggestlogs.com
echo ========================================
echo.

"C:\Program Files\PuTTY\plink.exe" -ssh -pw 61btnCsn5RUu1UBpJzXLhBmdd -batch root@75.119.139.18 "apt-get update && apt-get install -y certbot python3-certbot-apache && systemctl stop apache2 && certbot certonly --standalone -d biggestlogs.com -d www.biggestlogs.com --non-interactive --agree-tos --register-unsafely-without-email && echo 'SSL Certificate obtained!' && systemctl start apache2"

echo.
echo ========================================
echo   SSL Installation Complete!
echo ========================================
echo.
echo Your site is now secured!
echo Visit: https://biggestlogs.com
echo.
pause

