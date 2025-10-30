@echo off
echo Uploading and running fix script...
"C:\Program Files\PuTTY\plink.exe" -ssh -pw 61btnCsn5RUu1UBpJzXLhBmdd root@75.119.139.18 "bash /root/FIX_PHPMYADMIN_NOW.sh"
pause

