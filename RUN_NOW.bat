@echo off
title BiggestLogs Server
color 0A
echo.
echo =========================================
echo    🔥 BiggestLogs Marketplace 🔥
echo =========================================
echo.
echo Starting server at http://localhost:8000
echo.
echo Default Login:
echo   Admin: admin@biggestlogs.com / password
echo   User:  user@test.com / password
echo.
echo Press Ctrl+C to stop
echo.
echo =========================================
echo.
php artisan serve
pause





