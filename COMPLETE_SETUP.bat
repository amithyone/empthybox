@echo off
echo ==========================================
echo   BiggestLogs - Complete Setup
echo ==========================================
echo.

if not exist vendor\autoload.php (
    echo Installing Composer dependencies...
    php composer.phar install --no-interaction
    echo.
)

if not exist .env (
    echo Creating .env file...
    copy .env.example .env
    echo.
)

echo Generating application key...
php artisan key:generate
echo.

echo Running database migrations...
php artisan migrate --seed --force
echo.

echo Building assets...
call npm run build
echo.

echo.
echo ==========================================
echo   Setup Complete!
echo ==========================================
echo.
echo To start the server, run:
echo   php artisan serve
echo.
echo Or double-click: START_SERVER.bat
echo.
echo Server will be available at:
echo   http://localhost:8000
echo.
echo Default Login:
echo   Admin: admin@biggestlogs.com / password
echo   User:  user@test.com / password
echo.
pause





