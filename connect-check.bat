@echo off
echo Connecting to server...
"C:\Program Files\PuTTY\plink.exe" -ssh -pw 61btnCsn5RUu1UBpJzXLhBmdd root@75.119.139.18 "php -v && echo '' && systemctl status apache2 | head -10"
pause

