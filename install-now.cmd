@echo off
title Installing Webmin on BiggestLogs Server
echo.
echo ========================================
echo   Installing Webmin - Please Wait...
echo ========================================
echo.

"C:\Program Files\PuTTY\plink.exe" -ssh -pw 61btnCsn5RUu1UBpJzXLhBmdd -batch root@75.119.139.18 ^
"apt-get update -y -qq && ^
apt-get upgrade -y -qq && ^
apt-get install -y wget perl libnet-ssleay-perl openssl libauthen-pam-perl libpam-runtime libio-pty-perl apt-show-versions python3 unzip && ^
cd /root && ^
wget -q https://prdownloads.sourceforge.net/webadmin/webmin_2.000_all.deb && ^
dpkg -i webmin_2.000_all.deb && ^
apt-get install -f -y -qq && ^
systemctl start webmin && ^
systemctl enable webmin && ^
echo '' && ^
echo '========================================' && ^
echo '  Webmin Installed Successfully!' && ^
echo '========================================' && ^
echo 'Access: https://75.119.139.18:10000' && ^
echo 'Login: root / 61btnCsn5RUu1UBpJzXLhBmdd' && ^
echo '========================================'"

echo.
echo ========================================
echo   Installation Complete!
echo ========================================
echo.
echo Next Steps:
echo 1. Open browser to: https://75.119.139.18:10000
echo 2. Accept SSL certificate warning
echo 3. Login with root credentials
echo.
pause

