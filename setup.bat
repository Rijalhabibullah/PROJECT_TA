@echo off
REM Setup script untuk menjalankan API Classification system

echo.
echo ========================================
echo Rice Leaf Disease Classification Setup
echo ========================================
echo.

REM Check if Python is installed
python --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Python tidak terinstall!
    echo Silakan install Python dari https://www.python.org
    pause
    exit /b 1
)

echo [OK] Python terdeteksi

REM Check Laravel
if not exist "web_TA\artisan" (
    echo [ERROR] Laravel project tidak ditemukan di web_TA\
    pause
    exit /b 1
)

REM Check model files
echo.
echo Cek file model...
if exist "rice_leaf_disease_model.keras" (
    echo [OK] Model keras ditemukan
) else if exist "rice_leaf_disease_model.h5" (
    echo [OK] Model h5 ditemukan
) else (
    echo [WARNING] File model tidak ditemukan
    echo Pastikan file model ada di folder dataset
)

echo.
echo ========================================
echo Setup Selesai
echo ========================================
echo.
echo Langkah selanjutnya:
echo.
echo 1. Buka Terminal 1 dan jalankan Laravel:
echo    cd "web_TA"
echo    php artisan serve
echo.
echo 2. Test API:
echo    python test_api.py
echo.
echo 3. Pastikan Python environment memiliki dependency inferensi:
echo    pip install -r "rice leaf diseases dataset\requirements_api.txt"
echo.
pause
