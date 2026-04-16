@echo off
set DATA_DIR=%LOCALAPPDATA%\ClubeAIA\PostgreSQL\data
set LOG_FILE=%LOCALAPPDATA%\ClubeAIA\PostgreSQL\postgres.log
"C:\Program Files\PostgreSQL\18\bin\pg_ctl.exe" -D "%DATA_DIR%" -l "%LOG_FILE%" -o "-p 5433 -h 127.0.0.1" start
