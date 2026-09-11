@echo off
rem Registers the scanner engine to auto-start hidden at login.
rem Re-run this script if you move the project folder.
set "VBS=%~dp0autostart-scanner.vbs"
set "CMDLINE=wscript.exe \"%VBS%\""

rem --- Attempt 1: scheduled task at logon - no admin needed when limited to current user ---
schtasks /Delete /TN OweruScannerEngine /F >nul 2>nul
schtasks /Create /TN OweruScannerEngine /TR "%CMDLINE%" /SC ONLOGON /RU "%USERNAME%" /F >nul 2>nul

if not %errorlevel%==0 goto registry

echo [OK] Scheduled task created: scanner engine auto-starts at login.
echo Remove anytime with: scripts\unregister-autostart.bat
pause
exit /b 0

:registry
rem --- Attempt 2: per-user registry Run key - no admin needed ---
reg add "HKCU\Software\Microsoft\Windows\CurrentVersion\Run" /v OweruScannerEngine /t REG_SZ /d "%CMDLINE%" /f >nul

if %errorlevel%==0 goto done

echo [FAILED] Could not register autostart. Run this script as Administrator and retry.
pause
exit /b 1

:done
echo [OK] Scanner engine registered via registry Run key - auto-starts at login, hidden.
echo Remove anytime with: scripts\unregister-autostart.bat
pause
