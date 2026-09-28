@echo off
echo ============================================
echo  WebGIS Banjir - Flask API Server
echo  Tesis Alpin - Universitas Halu Oleo
echo ============================================
echo.
echo [*] Memulai Flask API di http://localhost:5000 ...
echo [*] Tekan Ctrl+C untuk menghentikan server
echo.
cd /d "%~dp0python-api"
python app.py
pause
