@echo off
echo Verifying PHP installation and Apache config...
"C:\Program Files\PuTTY\plink.exe" -ssh -pw 61btnCsn5RUu1UBpJzXLhBmdd -batch root@75.119.139.18 "php -v && echo '---' && which php && echo '---' && systemctl status php8.1-fpm | head -5"
pause

