@echo off
echo Setting APP_KEY manually...
php -r "file_put_contents('.env', str_replace('APP_KEY=', 'APP_KEY=base64:' . base64_encode(random_bytes(32)), file_get_contents('.env')));"
echo APP_KEY generated!
echo.
echo You can now run: php artisan serve
echo Or double-click: RUN_NOW.bat
pause





