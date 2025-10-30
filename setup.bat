@echo off
echo Setting up BiggestLogs...
echo.

echo Step 1: Installing Composer dependencies...
composer install --no-interaction

echo.
echo Step 2: Creating .env file...
if not exist .env copy .env.example .env

echo.
echo Step 3: Generating application key...
php artisan key:generate

echo.
echo Step 4: Running migrations...
php artisan migrate --seed

echo.
echo Step 5: Installing npm dependencies...
call npm install

echo.
echo Step 6: Building assets...
call npm run build

echo.
echo Setup complete! Run 'php artisan serve' to start the server.
pause






