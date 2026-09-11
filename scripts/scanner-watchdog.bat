@echo off
rem Oweru scanner watchdog - keeps the Flask scanner engine running.
rem Restarts it automatically if it crashes. Stop with scripts\stop-scanner.bat.
rem Logs append to scanner-service.log in the project root.
setlocal
cd /d "%~dp0.."

:loop
if exist scanner-stop.flag goto stopped

rem Skip starting if something is already serving port 5000
netstat -ano | findstr ":5000" | findstr "LISTENING" >nul 2>nul
if not errorlevel 1 goto already_running

echo [%date% %time%] Starting scanner engine... >> scanner-service.log
C:\python312\python.exe scanner\scanner.py >> scanner-service.log 2>&1
echo [%date% %time%] Scanner exited - restarting in 5 seconds >> scanner-service.log
ping -n 6 127.0.0.1 >nul
goto loop

:already_running
echo [%date% %time%] Port 5000 already serving - watchdog standing by >> scanner-service.log
ping -n 16 127.0.0.1 >nul
goto loop

:stopped
del scanner-stop.flag >nul 2>nul
echo [%date% %time%] Watchdog stopped by stop flag >> scanner-service.log
exit /b 0
