@echo off
rem ============================================================
rem  Oweru Tech Solutions - local dev stack launcher
rem  Starts: Laravel app (:8000), Scanner engine (:5000),
rem          Queue worker (batch scans)
rem  Close a service's window to stop that service.
rem ============================================================
title Oweru Dev Launcher
cd /d "%~dp0"

rem --- Resolve PHP: PATH first, XAMPP fallback ---
set "PHP=php"
where php >nul 2>nul || set "PHP=C:\xampp\php\php.exe"

if not exist "scanner\scanner.py" (
    echo [ERROR] Run this from the project root - scanner\scanner.py not found.
    pause
    exit /b 1
)

echo Starting Oweru Tech Solutions dev stack...
echo.

rem --- 1) Scanner engine (Flask, port 5000) ---
start "Oweru Scanner (:5000)" cmd /k "C:\python312\python.exe scanner\scanner.py"

rem --- 2) Queue worker (background batch scans) ---
start "Oweru Queue Worker" cmd /k "%PHP% artisan queue:work --tries=1 --timeout=300"

rem --- 3) Laravel app (port 8000) ---
start "Oweru Laravel (:8000)" cmd /k "%PHP% artisan serve"

rem To capture emails in storage/logs/laravel.log instead, use this for #3:
rem start "Oweru Laravel (:8000)" cmd /k "set MAIL_MAILER=log&& set MAIL_HOST=localhost&& %PHP% artisan serve"

echo   [1/3] Scanner engine  - port 5000
echo   [2/3] Queue worker    - background jobs
echo   [3/3] Laravel app     - http://127.0.0.1:8000
echo.
echo Opening browser in 5 seconds... close a service window to stop that service.
timeout /t 5 /nobreak >nul
start "" http://127.0.0.1:8000
