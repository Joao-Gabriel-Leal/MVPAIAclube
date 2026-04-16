@echo off
set DATA_DIR=%LOCALAPPDATA%\ClubeAIA\PostgreSQL\data
"C:\Program Files\PostgreSQL\18\bin\pg_ctl.exe" -D "%DATA_DIR%" stop
