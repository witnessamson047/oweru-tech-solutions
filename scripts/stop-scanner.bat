@echo off
rem Stops the scanner engine cleanly:
rem  1. Creates a stop flag so the watchdog shuts itself down
rem  2. Kills whatever is listening on port 5000
echo scanner-stop > "%~dp0..\scanner-stop.flag"
for /f "tokens=5" %%P in ('netstat -ano ^| findstr ":5000" ^| findstr "LISTENING"') do taskkill /F /PID %%P >nul 2>nul
echo Scanner engine stopped. The watchdog will shut itself down.
pause
