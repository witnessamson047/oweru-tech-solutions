' Oweru Scanner Engine - hidden autostart launcher
' Runs the watchdog (which keeps the Flask scanner alive and restarts it on crash).
' No visible window; output appends to scanner-service.log in the project root.
' NOTE: paths are absolute because the scheduled task may start in any working directory.
'       If you move this repo, update the path below (the bat handles the rest).
Dim shell
Set shell = CreateObject("WScript.Shell")

' 0 = hidden window (no console popup at login)
shell.Run "cmd /c ""D:\Desktop\dmo03\my-new-app\oweru-tech-solutions\scripts\scanner-watchdog.bat""", 0, False
