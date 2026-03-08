# Setup Guide - Rice Leaf Disease Classification API

Panduan lengkap untuk mengintegrasikan model CNN klasifikasi penyakit daun padi ke aplikasi web Laravel.

---

## 📋 Persyaratan

### Backend (Python Flask API)
- Python 3.8 atau lebih baru
- TensorFlow/Keras 2.10+
- OpenCV
- Flask

### Frontend (Laravel Web)
- Laravel 10+
- PHP 8.0+
- Composer

---

## 🚀 Setup Langkah demi Langkah

### **Bagian 1: Setup Python API Server**

#### 1.1 Install Dependencies Python

Buka terminal/PowerShell di folder `rice leaf diseases dataset/`:

```bash
# Navigasi ke folder dataset
cd "d:\PROJECT TA\rice leaf diseases dataset"

# Buat virtual environment (opsional tapi recommended)
python -m venv venv

# Aktifkan virtual environment
# Windows:
venv\Scripts\activate

# Install dependencies
pip install -r requirements_api.txt
```

#### 1.2 Pastikan Model File Ada

Pastikan salah satu dari file model ini ada di folder `rice leaf diseases dataset/`:
- `rice_leaf_disease_model.keras` ✅ (Preferred)
- `rice_leaf_disease_model.h5`
- `rice_leaf_disease_model.json` (+ weights file)

File-file ini seharusnya sudah ada dari training notebook.

#### 1.3 Jalankan API Server

```bash
# Letakkan di folder yang sama dengan model file
python api_server.py
```

**Output yang diharapkan:**
```
============================================================
Rice Leaf Disease Classification API Server
============================================================

Working directory: d:\PROJECT TA\rice leaf diseases dataset

Loading model...
✓ Model ditemukan: rice_leaf_disease_model.keras
✓ Model berhasil dimuat!
  Model input shape: (None, 224, 224, 3)
  Number of layers: 25

============================================================
Starting Flask API Server...
============================================================
Server berjalan di http://127.0.0.1:5000/
Tekan CTRL+C untuk menghentikan.
```

> 🔴 **PENTING:** Server Python harus tetap berjalan saat menggunakan API dari Laravel!

---

### **Bagian 2: Setup Laravel Web API**

#### 2.1 Register API Routes

Edit file `web_TA/bootstrap/app.php` atau pastikan menggunakan API routes:

```php
// Di dalam bootstrap/app.php atau di app structure Anda
// Pastikan routes/api.php dimuat:

// Jika menggunakan Laravel 11, routes sudah auto-loaded
// Jika menggunakan Laravel 10, tambahkan di RouteServiceProvider
```

File `routes/api.php` sudah dibuat dan berisi endpoint classification.

#### 2.2 Konfigurasi Laravel

Pastikan file `.env` sudah dikonfigurasi:

```env
APP_NAME="Padi Disease Classification"
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:... # Jalankan php artisan key:generate jika belum

# Database configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=padi_db
DB_USERNAME=root
DB_PASSWORD=
```

#### 2.3 Run Laravel

```bash
cd "d:\PROJECT TA\web_TA"

# Install dependencies jika belum
composer install

# Generate key
php artisan key:generate

# Run development server
php artisan serve
```

Laravel akan berjalan di `http://127.0.0.1:8000`

---

## 🔌 API Endpoints

### Test Koneksi

```bash
curl -X GET http://127.0.0.1:8000/api/classification/test
```

**Response:**
```json
{
  "success": true,
  "message": "Koneksi ke model API berhasil",
  "model_info": {
    "status": "ok",
    "model_loaded": true,
    "classes": ["Bacterialblight", "Brownspot", "Leafsmut"]
  }
}
```

### Klasifikasi Gambar (Analyze Only)

**Endpoint:** `POST /api/classification/classify`

```bash
curl -X POST http://127.0.0.1:8000/api/classification/classify \
  -F "image=@path/to/image.jpg"
```

**Response:**
```json
{
  "success": true,
  "message": "Klasifikasi berhasil",
  "data": {
    "predicted_class": "Bacterialblight",
    "confidence": "95.23%",
    "confidence_value": 0.9523,
    "all_predictions": {
      "Bacterialblight": 0.9523,
      "Brownspot": 0.0380,
      "Leafsmut": 0.0097
    },
    "disease_info": {
      "name": "Bercak Bakteri (Bacterial Blight)",
      "description": "...",
      "symptoms": [...],
      "treatment": [...],
      "severity": "Sedang hingga Tinggi"
    },
    "timestamp": "2024-03-09T10:30:45Z"
  }
}
```

### Klasifikasi & Simpan Gambar

**Endpoint:** `POST /api/classification/classify-and-save`

```bash
curl -X POST http://127.0.0.1:8000/api/classification/classify-and-save \
  -F "image=@path/to/image.jpg" \
  -F "notes=Dari lapangan area A"
```

**Response:** Sama seperti di atas + `image_path` untuk akses gambar yang disimpan

---

## 💻 Contoh Penggunaan di Frontend

### HTML Form Upload

```html
<form id="classificationForm" enctype="multipart/form-data">
    <div>
        <label for="image">Pilih Gambar Daun:</label>
        <input type="file" id="image" name="image" accept="image/*" required>
    </div>
    
    <div>
        <label for="notes">Catatan (opsional):</label>
        <textarea id="notes" name="notes" rows="3"></textarea>
    </div>
    
    <button type="submit">Klasifikasi</button>
    <div id="result"></div>
</form>

<script>
document.getElementById('classificationForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('/api/classification/classify-and-save', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            displayResults(data.data);
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat klasifikasi');
    }
});

function displayResults(data) {
    const resultDiv = document.getElementById('result');
    resultDiv.innerHTML = `
        <h3>Hasil Klasifikasi</h3>
        <p><strong>Diagnosis:</strong> ${data.disease_info.name}</p>
        <p><strong>Confidence:</strong> ${data.confidence}</p>
        <p><strong>Severity:</strong> ${data.disease_info.severity}</p>
        
        <h4>Gejala:</h4>
        <ul>
            ${data.disease_info.symptoms.map(s => `<li>${s}</li>`).join('')}
        </ul>
        
        <h4>Penanganan:</h4>
        <ul>
            ${data.disease_info.treatment.map(t => `<li>${t}</li>`).join('')}
        </ul>
        
        <img src="${data.image_path}" style="max-width: 300px; margin-top: 20px;">
    `;
}
</script>
```

### JavaScript Fetch (Modern)

```javascript
async function classifyImage(imageFile) {
    const formData = new FormData();
    formData.append('image', imageFile);
    formData.append('notes', 'Upload manual');
    
    const response = await fetch('/api/classification/classify-and-save', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });
    
    return await response.json();
}

// Penggunaan
const fileInput = document.getElementById('imageInput');
fileInput.addEventListener('change', async (e) => {
    const file = e.target.files[0];
    const result = await classifyImage(file);
    console.log(result);
});
```

---

## 🔧 Troubleshooting

### Problem: "Tidak dapat menghubungi model API"

**Solusi:**
1. Pastikan Python API server berjalan
   ```bash
   # Cek apakah port 5000 aktif
   netstat -ano | findstr :5000  # Windows
   ```

2. Test API Python secara langsung:
   ```bash
   curl http://127.0.0.1:5000/health
   ```

3. Jika perlu ganti port, edit di:
   - `api_server.py`: ubah `port=5000`
   - `ClassificationController.php`: ubah `$pythonApiUrl`

### Problem: "Model tidak ditemukan"

**Solusi:**
1. Pastikan file model ada di folder `rice leaf diseases dataset/`
2. Nama file harus tepat:
   - `rice_leaf_disease_model.keras` ✅
   - `rice_leaf_disease_model.h5`
   - `rice_leaf_disease_model.json`

3. Cek output API server:
   ```bash
   # Lihat log saat startup untuk tahu file mana yang digunakan
   python api_server.py
   ```

### Problem: "Memory Error" saat prediksi

**Solusi:**
- Model cukup besar, pastikan sistem memiliki cukup RAM
- Jika Windows, tutup aplikasi lain yang menggunakan banyak memory
- Atau gunakan `model.quantize()` untuk kompresi model

### Problem: CORS Error di Frontend

**Solusi:**
- Python API sudah dikonfigurasi dengan CORS
- Pastikan request header benar dari Laravel

---

## 📊 Database (Opsional)

Jika ingin menyimpan riwayat klasifikasi:

```bash
cd "d:\PROJECT TA\web_TA"

# Create migration
php artisan make:migration create_classifications_table

# Run migrations
php artisan migrate
```

**Migration File** (`database/migrations/XXXX_create_classifications_table.php`):

```php
Schema::create('classifications', function (Blueprint $table) {
    $table->id();
    $table->string('image_path');
    $table->string('predicted_class');
    $table->float('confidence');
    $table->json('all_predictions');
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

---

## 📝 Catatan Penting

1. **API Server Harus Berjalan**: Pastikan `api_server.py` tetap berjalan saat menggunakan API
2. **CSRF Token**: Jika ada error CSRF, pastikan `X-CSRF-TOKEN` dikirim di request header
3. **File Upload Size**: Default batas 5MB, bisa diubah di `ClassificationController.php`
4. **Model Update**: Jika model diupdate, hanya perlu restart API server

---

## 🎯 Next Steps

1. ✅ Setup Python API
2. ✅ Setup Laravel
3. ✅ Test endpoints
4. **Create UI** - Buat halaman upload gambar
5. **Add Database** - Simpan riwayat klasifikasi
6. **Mobile App** - Integrasi dengan Flutter app

---

**Created**: March 9, 2026  
**Author**: AI Assistant  
**Version**: 1.0
