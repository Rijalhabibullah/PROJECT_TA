# 🚀 Quick Start Guide - API Classification

Panduan cepat untuk menjalankan sistem API klasifikasi penyakit padi.

---

## ⚡ 5 Menit Setup

### Terminal 1: Jalankan Python API Server

```bash
cd "YOUR_PROJECT_PATH\rice leaf diseases dataset"

# Install dependencies (hanya sekali)
pip install -r requirements_api.txt

# Jalankan server
python api_server.py
```

**Expected Output:**
```
============================================================
Rice Leaf Disease Classification API Server
============================================================
✓ Model ditemukan: rice_leaf_disease_model.keras
✓ Model berhasil dimuat!
Server berjalan di http://127.0.0.1:5000/
```

---

### Terminal 2: Jalankan Laravel Server

```bash
cd "YOUR_PROJECT_PATH\web_TA"

# Install dependencies (hanya sekali)
composer install

# Setup environment
copy .env.example .env
php artisan key:generate

# Jalankan server
php artisan serve
```

**Expected Output:**
```
INFO  Server running on [http://127.0.0.1:8000].
```

---

### Terminal 3: Test API

```bash
cd "YOUR_PROJECT_PATH"

# Test connections
python test_api.py
```

---

## 📡 API Endpoints Quick Reference

### Test Connection
```bash
GET http://127.0.0.1:8000/api/classification/test
```

### Classify Image
```bash
POST http://127.0.0.1:8000/api/classification/classify
Content: image file (multipart/form-data)

Response:
{
  "success": true,
  "data": {
    "predicted_class": "Bacterialblight",
    "confidence": "95.23%",
    "disease_info": {...}
  }
}
```

### Classify & Save
```bash
POST http://127.0.0.1:8000/api/classification/classify-and-save
Content: image file + optional notes

Response: Same as above + image_path
```

---

## 🧪 Test dengan cURL

```bash
# Test koneksi
curl -X GET http://127.0.0.1:8000/api/classification/test

# Classify image
curl -X POST http://127.0.0.1:8000/api/classification/classify \
  -F "image=@C:/path/to/image.jpg"
```

---

## 🐛 Troubleshooting

| Problem | Solution |
|---------|----------|
| **"Tidak dapat menghubungi model API"** | Pastikan Terminal 1 (Python API) berjalan |
| **"Model tidak ditemukan"** | Cek file `.keras` atau `.h5` ada di folder dataset |
| **"Connection refused"** | Pastikan port 5000 & 8000 tidak digunakan program lain |
| **"CORS Error"** | Flask API sudah dikonfigurasi CORS, tapi jika masih error reload page |

---

## 📁 File Structure

```
PROJECT TA/
├── SETUP_GUIDE.md                    ← Panduan lengkap
├── FLUTTER_INTEGRATION.md            ← Integrasi Mobile
├── setup.bat                         ← Setup otomatis Windows
├── test_api.py                       ← Test script
│
├── rice leaf diseases dataset/
│   ├── api_server.py                 ← Flask API Server ⭐
│   ├── requirements_api.txt          ← Python dependencies
│   ├── rice_leaf_disease_model.keras ← Model CNN
│   └── (gambar dataset)
│
└── web_TA/
    ├── app/Http/Controllers/
    │   └── ClassificationController.php  ← API Controller ⭐
    └── routes/
        └── api.php                       ← API Routes ⭐
```

---

## 🎯 Next Steps

1. ✅ Run Python API
2. ✅ Run Laravel Server
3. ✅ Test API
4. ➡️ **Create UI** - Buat halaman upload
5. ➡️ **Integrate Mobile** - Hubungkan Flutter app
6. ➡️ **Deploy** - Siapkan untuk production

---

## 📚 Full Documentation

- [SETUP_GUIDE.md](SETUP_GUIDE.md) - Panduan lengkap setup
- [FLUTTER_INTEGRATION.md](FLUTTER_INTEGRATION.md) - Integrasi Flutter
- API Code: [ClassificationController.php](web_TA/app/Http/Controllers/ClassificationController.php)
- Python API: [api_server.py](rice%20leaf%20diseases%20dataset/api_server.py)

---

**Created:** March 9, 2026  
**Status:** Ready to Use ✅
