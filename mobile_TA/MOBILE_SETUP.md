# 📱 Mobile App Integration - Setup Guide

Panduan lengkap mengintegrasikan API Classification ke aplikasi Flutter Anda.

---

## ✅ File yang Dibuat/Diupdate

### File Baru:
1. **lib/services/classification_service.dart** - Service untuk komunikasi dengan API
2. **MOBILE_SETUP.md** - Guide ini

### File yang Diupdate:
1. **lib/dashboard_klasifikasi.dart** - Integrasi API + error handling
2. **lib/screen/result_screen.dart** - Display hasil API termasuk disease info
3. **pubspec.yaml** - Tambah permission_handler

---

## 🔧 Konfigurasi Server URL

**PENTING:** Update URL server sesuai environment Anda!

### File: `lib/services/classification_service.dart`

Edit line 7:
```dart
static const String _baseUrl = 'http://10.0.2.2:8000/api/classification';
```

Pilih satu sesuai kebutuhan:

| Environment | URL | Keterangan |
|---|---|---|
| **Emulator Android** | `http://10.0.2.2:8000/api/classification` | Default (Android emulator akses host via 10.0.2.2) |
| **Emulator iOS** | `http://127.0.0.1:8000/api/classification` | iOS emulator akses localhost langsung |
| **Device Fisik (WiFi)** | `http://192.168.x.x:8000/api/classification` | Ganti x.x dengan IP komputer Anda |
| **Testing Lokal** | `http://127.0.0.1:8000/api/classification` | Jika Flutter di desktop |

### Cara Cari IP Komputer Anda:

**Windows:**
```bash
ipconfig
# Cari "IPv4 Address" misalnya: 192.168.1.100
```

**Mac/Linux:**
```bash
ifconfig
# Cari "inet" misalnya: 192.168.1.100
```

---

## 📦 Install Dependencies

Buka terminal di folder `mobile_TA/padi_app/`:

```bash
flutter pub get
```

Atau jika menggunakan Android Studio:
```
Tools → Flutter → Flutter Pub Get
```

---

## 🚀 Menjalankan Aplikasi

### Terminal 1: Python API Server
```bash
cd "d:\PROJECT TA\rice leaf diseases dataset"
python api_server.py
```

### Terminal 2: Laravel Web Server
```bash
cd "d:\PROJECT TA\web_TA"
php artisan serve
```

### Terminal 3: Flutter App

**Emulator Android:**
```bash
cd "d:\PROJECT TA\mobile_TA\padi_app"
flutter run
```

**Device Fisik:**
```bash
flutter run -d <device_id>
# Dapatkan device_id dengan: flutter devices
```

---

## 🎯 Fitur yang Sudah Terintegrasi

✅ **Image Picker**
- Upload dari galeri
- Upload dari kamera
- Preview gambar sebelum klasifikasi

✅ **API Integration**
- Kirim gambar ke server Laravel
- Dapatkan hasil prediksi dengan confidence score
- Dapatkan informasi disease (gejala, penanganan, severity)

✅ **Error Handling**
- Network error messages
- Timeout handling
- Validation feedback

✅ **Result Display**
- Confidence score dengan progress bar
- Severity badge dengan warna
- Analisis semua kelas
- Detail gejala dan penanganan

✅ **Loading Indicator**
- Menunjukkan proses klasifikasi sedang berlangsung

---

## 📱 Screen Flow

```
Home Screen
  ↓ [Pilih/ambil foto]
  ↓
Dashboard Klasifikasi
  ↓ [Klasifikasi Sekarang]
  ↓ [Loading...]
  ↓
Result Screen
  ├─ Disease Info
  ├─ Confidence Score
  ├─ Severity Level
  ├─ Symptoms
  ├─ Treatment
  ├─ All Predictions
  └─ Product Recommendations
```

---

## 🔌 API Endpoints Used

Aplikasi menggunakan 2 endpoint:

### 1. Test Connection (Opsional)
```
GET /api/classification/test
```

### 2. Classify & Save Image
```
POST /api/classification/classify-and-save
Body:
  - image: File
  - notes: String (optional)

Response:
{
  "success": true,
  "data": {
    "predicted_class": "Bacterialblight",
    "confidence": "95.23%",
    "disease_info": {
      "name": "...",
      "symptoms": [...],
      "treatment": [...]
    }
  }
}
```

---

## 🧪 Testing

### Test dengan Gambar Manual:
1. Buka app
2. Tap tombol upload
3. Pilih/ambil foto daun padi
4. Tap "Klasifikasi Sekarang"
5. Tunggu hasil

### Expected Behavior:
- Loading spinner muncul
- Setelah 1-2 detik, hasil ditampilkan
- Detail penyakit terlihat lengkap
- Confidence score di atas 80% untuk prediksi yang baik

---

## ⚠️ Troubleshooting

### Error: "Network error - Check your connection"
**Solusi:**
1. Pastikan tanto Python API server maupun Laravel server berjalan
2. Update URL di `classification_service.dart` sesuai environment
3. Pastikan device/emulator bisa akses server:
   ```bash
   curl http://10.0.2.2:8000/api/classification/test
   ```

### Error: "Request timeout"
**Solusi:**
1. Cek kecepatan koneksi
2. Model CNN memang butuh 1-2 detik untuk prediksi
3. Pastikan model file (.keras/.h5) ada di server

### Error: "Model API is not loaded"
**Solusi:**
1. Lihat output Python API server
2. Pastikan file model ada: `rice_leaf_disease_model.keras`
3. Restart Python API server

### Image tidak bisa dipilih
**Solusi:**
1. Check permissions di `AndroidManifest.xml` atau `Info.plist`
2. Pastikan `permission_handler` package installed
3. Grant camera & storage permissions di app

---

## 📂 Struktur File

```
lib/
├── main.dart                    # Main app
├── dashboard_klasifikasi.dart   # 🔥 Updated - Image picker + API call
├── screen/
│   ├── result_screen.dart      # 🔥 Updated - Display API results
│   ├── login.dart
│   ├── home_screen.dart
│   └── ... (screens lainnya)
├── services/
│   └── classification_service.dart  # ✨ NEW - API service
└── ... (files lainnya)
```

---

## 🎓 Customization

### 1. Ubah UI Warna
Edit di `dashboard_klasifikasi.dart` dan `result_screen.dart`:
```dart
backgroundColor: const Color(0xFF0F703A),  // Warna hijau
```

### 2. Ubah Max Upload Size
Di server Laravel, edit `ClassificationController.php`:
```php
'image' => 'image|mimes:jpeg,png,jpg|max:10240' // 10MB
```

### 3. Tambah Loading Spinner Custom
Di `dashboard_klasifikasi.dart`, replace:
```dart
CircularProgressIndicator(...)
```
dengan spinner pilihan Anda

### 4. Persist Classification History
Tambah SharedPreferences untuk menyimpan history:
```dart
// Di classification_service.dart
final prefs = await SharedPreferences.getInstance();
await prefs.setString('last_classification', jsonEncode(result.toJson()));
```

---

## 🔐 Security Notes

1. **API URL jangan hardcode untuk production** - Gunakan env files
2. **Validate image size** sebelum upload - Sudah ada di service
3. **Handle sensitive data** - Jangan log classification results
4. **Use HTTPS** di production - Ubah `http://` ke `https://`

---

## 📞 Quick Reference

### Common Commands
```bash
# Get dependencies
flutter pub get

# Run app
flutter run

# Run di device tertentu
flutter run -d <device_id>

# List devices
flutter devices

# Clean build
flutter clean
flutter pub get
```

### API Call Example
```dart
final service = ClassificationService();
final result = await service.classifyAndSave(imageFile);
print(result.diseaseInfo.name);
```

---

## ✨ Features Added

| Feature | Location | Status |
|---------|----------|--------|
| Image Picker | dashboard_klasifikasi.dart | ✅ |
| API Classification | services/classification_service.dart | ✅ |
| Error Handling | dashboard_klasifikasi.dart | ✅ |
| Loading State | dashboard_klasifikasi.dart | ✅ |
| Disease Display | result_screen.dart | ✅ |
| Confidence Score | result_screen.dart | ✅ |
| Symptoms/Treatment | result_screen.dart | ✅ |
| All Predictions | result_screen.dart | ✅ |
| History Tracking | Not implemented | ⏳ |
| Offline Mode | Not implemented | ⏳ |

---

## 🚀 Next Steps

1. ✅ Setup Flutter project
2. ✅ Install dependencies
3. ✅ Create ClassificationService
4. ✅ Integrate with HomeScreen
5. **→ Configure server URL for your environment**
6. **→ Run Python API + Laravel servers**
7. **→ Test with real images**
8. **→ Deploy to Play Store / App Store** (future)

---

**Status:** ✅ Ready to Use
**Last Updated:** March 9, 2026
**Version:** 1.0
