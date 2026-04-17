@echo off
setlocal

for %%I in ("%~dp0.") do set "WORKDIR=%%~fI"
for /f "usebackq delims=" %%I in (`powershell -NoProfile -Command "$ip = Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.IPAddress -like '192.168.*' -or $_.IPAddress -like '10.*' -or $_.IPAddress -like '172.1[6-9].*' -or $_.IPAddress -like '172.2?.*' -or $_.IPAddress -like '172.3[0-1].*' } | Sort-Object SkipAsSource,InterfaceMetric | Select-Object -First 1 -ExpandProperty IPAddress; if ($ip) { $ip } else { '127.0.0.1' }"`) do set "LAN_IP=%%I"

cd /d "%WORKDIR%"
set "PHP_INI_SCAN_DIR=%WORKDIR%\.php-runtime"

echo.
echo Clube Hub em rede local
echo PC:      http://127.0.0.1:8000
echo Celular: http://%LAN_IP%:8000
echo.

php artisan serve --host=0.0.0.0 --port=8000
