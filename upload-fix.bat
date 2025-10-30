@echo off
echo Uploading fix script...
"C:\Program Files\PuTTY\pscp.exe" -pw 61btnCsn5RUu1UBpJzXLhBmdd FIX_PHPMYADMIN_NOW.sh root@75.119.139.18:/root/
echo Script uploaded! Run RUN_FIX.bat next
pause

