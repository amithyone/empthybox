@echo off
echo Installing SSL Certificate for biggestlogs.com...
echo.

"C:\Program Files\PuTTY\plink.exe" -ssh -pw 61btnCsn5RUu1UBpJzXLhBmdd -batch root@75.119.139.18 bash /root/install-ssl.sh

echo.
echo SSL installation completed!
echo Visit: https://biggestlogs.com
pause

