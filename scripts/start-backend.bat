@echo off
echo Starting Digital Library Backend Server...
echo.
cd backend
php -S localhost:8000 -t public
