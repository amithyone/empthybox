@echo off
echo Configuring Apache for SSL...

"C:\Program Files\PuTTY\plink.exe" -ssh -pw 61btnCsn5RUu1UBpJzXLhBmdd -batch root@75.119.139.18 bash /root/configure-apache-ssl.sh

echo Done!
pause

