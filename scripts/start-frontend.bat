@echo off
echo Starting Digital Library Frontend...
echo.
SET PATH=C:\Program Files\nodejs;%PATH%
cd /d "%~dp0..\frontend"
npm start
pause
