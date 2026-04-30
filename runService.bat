@echo off
setlocal enabledelayedexpansion

echo.
echo ========================================
echo   Starting Magang-RPA Services
echo ========================================
echo.

REM Get current directory
set BASEDIR=%cd%

REM Check if we're in the right directory
if not exist "ektrasi data identitas\main.py" (
    echo ERROR: ektrasi data identitas\main.py not found!
    echo Make sure you run this from project root directory
    pause
    exit /b 1
)

REM Start OCR Service
echo [1/3] Starting OCR Service...
start "OCR Service" cmd /k "cd /d %BASEDIR%\ektrasi data identitas && python main.py"
cd /d %BASEDIR%

REM Start Laravel Server
echo [2/3] Starting Laravel Server on port 8000...
start "Laravel Server - Port 8000" cmd /k "cd /d %BASEDIR% && php artisan serve"

REM Wait for Laravel
timeout /t 2 /nobreak

REM Start Queue Worker
echo [3/3] Starting Queue Worker...
start "Queue Worker" cmd /k "cd /d %BASEDIR% && php artisan queue:work"

echo.
echo ========================================
echo    All 3 Services Started!
echo ========================================
echo.
echo   [1] OCR Service:      http://127.0.0.1:8500
echo   [2] Laravel:          http://127.0.0.1:8000
echo   [3] Queue Worker:     Running in background
echo.
echo   All 3 windows appear in taskbar
echo.
echo ========================================
echo.
pause
