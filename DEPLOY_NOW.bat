@echo off
echo ================================================
echo   Deploying BiggestLogs to Production Server
echo ================================================
echo.

echo Uploading deployment script...
"C:\Program Files\PuTTY\pscp.exe" -pw 61btnCsn5RUu1UBpJzXLhBmdd final-deploy.sh root@75.119.139.18:/root/

echo.
echo Running deployment (this will take a few minutes)...
"C:\Program Files\PuTTY\plink.exe" -ssh -pw 61btnCsn5RUu1UBpJzXLhBmdd -batch root@75.119.139.18 "bash /root/final-deploy.sh"

echo.
echo ================================================
echo   Deployment Complete!
echo ================================================
echo Visit: https://biggestlogs.com
echo ================================================
pause

