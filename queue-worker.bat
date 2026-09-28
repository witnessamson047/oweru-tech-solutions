@echo off
rem ============================================================
rem  Oweru Queue Worker - auto-restarting queue:work wrapper.
rem  Started automatically by start.bat. Run manually with:
rem      queue-worker.bat
rem  Close the window to stop the worker.
rem
rem  Why the loop: if the worker dies (crash, fatal error, or the
rem  hourly --max-time restart), it comes back in 3 seconds instead
rem  of silently piling up stalled jobs (scans, emails, auto-scan).
rem ============================================================
title Oweru Queue Worker
cd /d "%~dp0"

rem --- Resolve PHP: PATH first, XAMPP fallback (same as start.bat) ---
set "PHP=php"
where php >nul 2>nul || set "PHP=C:\xampp\php\php.exe"

:worker
%PHP% artisan queue:work --tries=1 --timeout=300 --sleep=3 --max-time=3600
echo.
echo [%date% %time%] Queue worker stopped (crash, fatal error, or hourly
echo max-time restart) - restarting in 3 seconds. Close this window to stop.
timeout /t 3 /nobreak >nul
goto worker
