@echo off
echo Checking PHP installation...
"C:\Program Files\PuTTY\plink.exe" -ssh -pw 61btnCsn5RUu1UBpJzXLhBmdd -batch root@75.119.139.18 "which php && php -v && which php8.1 && php8.1 -v"
pause

