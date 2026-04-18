#!/bin/bash
# Setup script untuk menjalankan API Classification system (Linux/Mac)

echo ""
echo "========================================"
echo "Rice Leaf Disease Classification Setup"
echo "========================================"
echo ""

# Check if Python is installed
if ! command -v python3 &> /dev/null; then
    echo "[ERROR] Python tidak terinstall!"
    echo "Silakan install Python"
    exit 1
fi

echo "[OK] Python terdeteksi"

# Check Laravel
if [ ! -f "web_TA/artisan" ]; then
    echo "[ERROR] Laravel project tidak ditemukan di web_TA/"
    exit 1
fi

# Check model files
echo ""
echo "Cek file model..."
if [ -f "rice leaf diseases dataset/rice_leaf_disease_model.keras" ]; then
    echo "[OK] Model keras ditemukan"
elif [ -f "rice leaf diseases dataset/rice_leaf_disease_model.h5" ]; then
    echo "[OK] Model h5 ditemukan"
else
    echo "[WARNING] File model tidak ditemukan"
    echo "Pastikan file model ada di folder dataset"
fi

echo ""
echo "========================================"
echo "Setup Selesai"
echo "========================================"
echo ""
echo "Langkah selanjutnya:"
echo ""
echo "1. Buka Terminal 1 dan jalankan Laravel:"
echo "   cd web_TA"
echo "   php artisan serve"
echo ""
echo "2. Test API:"
echo "   python3 test_api.py"
echo ""
echo "3. Pastikan dependency inferensi Python sudah terpasang:"
echo "   pip3 install -r \"rice leaf diseases dataset/requirements_api.txt\""
echo ""
