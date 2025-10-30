@echo off
echo Checking phpMyAdmin...
"C:\Program Files\PuTTY\plink.exe" -ssh -pw 61btnCsn5RUu1UBpJzXLhBmdd -batch root@75.119.139.18 "ls -la /var/www/html/ | grep phpmy"
echo.
pause

