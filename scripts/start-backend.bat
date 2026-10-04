@echo off
echo Starting Digital Library Backend Server...
echo.
SET PATH=C:\xampp\php;%PATH%
cd /d "%~dp0..\backend"
php -S localhost:8000 -t public
pause
