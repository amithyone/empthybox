@echo off
echo Verifying subdomain configuration...
echo.

"C:\Program Files\PuTTY\plink.exe" -ssh -pw 61btnCsn5RUu1UBpJzXLhBmdd -batch root@75.119.139.18 "ls -la /etc/apache2/sites-enabled/ | grep biggestlogs && echo '' && apache2ctl -S | grep biggestlogs"

echo.
pause

