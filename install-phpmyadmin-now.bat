@echo off
echo Installing phpMyAdmin...
"C:\Program Files\PuTTY\plink.exe" -ssh -pw 61btnCsn5RUu1UBpJzXLhBmdd -batch root@75.119.139.18 "cd /var/www/html && wget https://files.phpmyadmin.net/phpMyAdmin/5.2.1/phpMyAdmin-5.2.1-all-languages.zip && unzip phpMyAdmin-5.2.1-all-languages.zip && mv phpMyAdmin-5.2.1-all-languages phpmyadmin && chown -R www-data:www-data phpmyadmin && chmod -R 755 phpmyadmin && rm phpMyAdmin-5.2.1-all-languages.zip && echo 'phpMyAdmin installed!'"
echo Done!
pause

