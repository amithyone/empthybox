@echo off
echo Finalizing configuration...
"C:\Program Files\PuTTY\plink.exe" -ssh -pw 61btnCsn5RUu1UBpJzXLhBmdd -batch root@75.119.139.18 "systemctl restart apache2 && echo 'DONE!' && echo '' && echo 'Subdomains ready:' && echo 'https://server.biggestlogs.com' && echo 'https://db.biggestlogs.com'"
echo.
pause

