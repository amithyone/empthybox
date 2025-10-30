@echo off
echo Completing subdomain setup automatically...
echo.

"C:\Program Files\PuTTY\plink.exe" -ssh -pw 61btnCsn5RUu1UBpJzXLhBmdd -batch root@75.119.139.18 bash /root/setup-subdomains-final.sh

echo.
echo Done!
pause

