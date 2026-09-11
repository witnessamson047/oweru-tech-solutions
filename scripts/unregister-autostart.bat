@echo off
rem Removes the scanner engine auto-start (scheduled task and/or registry Run key).
schtasks /End /TN OweruScannerEngine >nul 2>nul
schtasks /Delete /TN OweruScannerEngine /F >nul 2>nul
reg delete "HKCU\Software\Microsoft\Windows\CurrentVersion\Run" /v OweruScannerEngine /f >nul 2>nul
echo Autostart removed (task and/or registry entry, if present).
pause
