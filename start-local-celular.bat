@echo off
setlocal

set "WORKDIR=C:\Users\joaog\MVPclube2"
set "PHP_EXE=%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"

cd /d "%WORKDIR%"

echo.
echo Clube Hub em rede local
echo PC:      http://127.0.0.1:8000
echo Celular: http://192.168.1.7:8000
echo.

"%PHP_EXE%" artisan serve --host=0.0.0.0 --port=8000
