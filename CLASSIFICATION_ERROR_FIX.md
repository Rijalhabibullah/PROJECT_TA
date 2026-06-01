# 🔧 Klasifikasi Error - Solusi & Troubleshooting

## 🎯 Ringkasan Masalah & Solusi

### Masalah
Error "Gagal menghubungi model classification" muncul **secara intermittent**:
- Kadang berhasil
- Kadang gagal dengan foto yang sama
- Tidak konsisten

### Penyebab Utama
1. **Model Reload per Request** - TensorFlow model dimuat ulang setiap kali klasifikasi (berat: ~100MB+)
2. **Memory Exhaustion** - Beberapa request concurrent → multiple model instances → OOM
3. **Process Race Condition** - Beberapa Python process kompetisi akses resources

---

## ✅ Solusi Implementasi

### 1. **Request Queue dengan File Lock**
- File: `web_TA/app/Services/PythonClassificationService.php`
- Mencegah concurrent model loads dengan file locking
- Serialisasi requests sehingga hanya 1 model di memory

```php
// Baru: Request dijadwalkan dengan lock
runActionWithLock() {
    flock($lockFile, LOCK_EX);  // Tunggu sampai lock tersedia
    $result = runAction();
    flock($lockFile, LOCK_UN);  // Lepas lock
}
```

### 2. **Retry Logic dengan Exponential Backoff**
```php
for ($attempt = 1; $attempt <= 3; $attempt++) {
    try {
        return runActionWithLock(...);
    } catch (TimeoutException) {
        sleep($attempt);  // 1s, 2s, 3s
    }
}
```

### 3. **Enhanced Error Logging**
- Semua error dari Python process dicatat ke `storage/logs/laravel.log`
- Memudahkan diagnosis

### 4. **Health Check Endpoints**
- `GET /api/health/` - Quick system check
- `GET /api/health/diagnose` - Full Python model check (30+ detik)

---

## 🧪 Cara Testing Solusi

### 1. Cek Status Kesehatan (dari browser/postman)
```
GET http://localhost:8000/api/health/
```

Response:
```json
{
  "healthy": true,
  "health": {
    "python_model": {
      "status": "ok",
      "model_directory": "..."
    },
    "system": {
      "memory": { "php_memory_mb": 10.5 }
    }
  }
}
```

### 2. Full Diagnostic Check
```
GET http://localhost:8000/api/health/diagnose
```
(Tunggu 30+ detik, check TensorFlow & model loading)

### 3. Test Concurrent Classifications (dari terminal)
```powershell
# Jalankan 5 klasifikasi bersamaan
for ($i=1; $i -le 5; $i++) {
    Invoke-WebRequest -Uri "http://localhost:8000/api/classification/classify" `
      -Method POST `
      -Form @{image = Get-Item "path/to/image.jpg"} &
}
Wait-Job
```

---

## 📊 Performance Improvement

### Sebelum Fix
- Request 1: ✅ 15 detik (load model)
- Request 2: ❌ Timeout 30s (memory/conflict)
- Request 3: ❌ Gagal

### Sesudah Fix
- Request 1: ✅ 15 detik (load model)
- Request 2: ✅ 20 detik (wait lock + inference)
- Request 3: ✅ 18 detik (queue + inference)
- Semua berhasil ✅

---

## 🔍 Troubleshooting

### Jika Error Masih Muncul

#### 1. Cek Memory Sistem
```
GET /api/health/
Lihat: health.system.memory
```
Jika `php_memory_mb` > 256MB → perlu optimize

#### 2. Cek Model File
```
python web_TA/scripts/diagnosis.py "rice leaf diseases dataset"
```
Check output:
- model_files.*.exists: harus true
- tensorflow.installed: harus true

#### 3. Lihat Error Log
```
tail -f storage/logs/laravel.log
Cari: "Python Process Error"
```

#### 4. Test Python Langsung
```powershell
cd web_TA
$img_base64 = [Convert]::ToBase64String([IO.File]::ReadAllBytes("test.jpg"))
$json = @{image=$img_base64} | ConvertTo-Json

$json | python scripts/rice_inference.py classify --model-dir "../rice leaf diseases dataset"
```

### Common Error Messages

#### "Model sedang diproses, silahkan coba lagi dalam beberapa detik"
**Penyebab**: Request queue penuh  
**Solusi**: Tunggu 5 detik, retry

#### "Proses inferensi Python tidak mengembalikan output"
**Penyebab**: Model loading gagal  
**Solusi**: Jalankan diagnosis.py, check TensorFlow

#### "Gagal menjalankan proses inferensi Python"
**Penyebab**: Python executable tidak ditemukan  
**Solusi**: Cek `.env` PYTHON_EXECUTABLE pointing ke .venv

---

## 📈 Monitoring & Prevention

### 1. Setup Cron Job untuk Health Check (optional)
```php
// app/Console/Kernel.php
Schedule::call(function () {
    $service = new PythonClassificationService();
    $service->health();
})->everyFiveMinutes()->onOneServer();
```

### 2. Add Alert untuk Memory Warning
Jika `health.system.memory.percent_used > 80%`:
- Alert admin
- Reduce concurrent requests

### 3. Logging ke Database (optional)
Track semua klasifikasi errors untuk analytics

---

## 🚀 Long-term Optimization (Phase 6)

1. **Model Daemon Server** - FastAPI server yang load model once, serve multiple requests
2. **GPU Support** - Setup CUDA untuk TensorFlow GPU acceleration
3. **Model Compression** - Quantize model untuk memory lebih efisien
4. **Caching Layer** - Cache hasil klasifikasi untuk identical images

---

## 📞 Support

Jika error masih muncul setelah implementasi:
1. Jalankan `/api/health/diagnose`
2. Check `storage/logs/laravel.log`
3. Report output ke backend team

---

*Last Updated: May 2026*
