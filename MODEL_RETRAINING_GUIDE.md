# 🚀 Model Retraining Guide - 4 Classes (Healthy Added)

## 📋 Perubahan Terbaru

Sistem klasifikasi telah diupdate untuk support **4 kelas** daripada 3:
- ✅ Bacterialblight (Bercak Bakteri)
- ✅ Brownspot (Bercak Coklat) 
- ✅ **Healthy (BARU - Daun Sehat)**
- ✅ Leafsmut (Jamur Daun)

### File yang Sudah Diupdate
1. **Backend**: `web_TA/scripts/rice_inference.py` - CLASS_NAMES updated
2. **Laravel**: `web_TA/app/Http/Controllers/ClassificationController.php` - Added Healthy disease info
3. **Mobile**: `mobile_TA/padi_app/lib/screen/result_screen.dart` - UI support untuk Healthy class

---

## 🔧 Langkah Retrain Model

### Step 1: Siapkan Dataset

**Pastikan folder struktur ada:**
```
rice leaf diseases dataset/
├── Bacterialblight/     (foto daun bercak bakteri)
├── Brownspot/           (foto daun bercak coklat)
├── Healthy/             (BARU - foto daun sehat)
└── Leafsmut/            (foto daun jamur)
```

**Dataset Healthy** harus contain:
- Daun padi yang SEHAT (tanpa penyakit)
- Minimal 100-200 foto berkualitas tinggi
- Beragam kondisi pencahayaan & angle
- Resolusi minimal 224x224 pixel

### Step 2: Bersihkan Dataset (Optional tapi Recommended)

Hapus gambar duplikat/corrupted dari Jupyter notebook:
```python
# Jalankan cell "Menghapus Gambar Duplikat" di notebook
PATH_DATASET = r"d:\PROJECT TA\rice leaf diseases dataset"
hapus_gambar_duplikat(PATH_DATASET)
```

### Step 3: Training di Jupyter Notebook

**File**: `rice leaf diseases dataset/rice_leaf_cnn_classification.ipynb`

#### 3a. Buka Notebook
```bash
cd "d:\PROJECT TA\rice leaf diseases dataset"
jupyter notebook rice_leaf_cnn_classification.ipynb
```

#### 3b. Jalankan Cell Berturut-turut (PENTING - ikuti order!)

**Cell 1: Setup & Imports**
```python
# Imports yang diperlukan
import os
import warnings
warnings.filterwarnings('ignore')
```

**Cell 2: Define Classes**
- **HARUS** sesuai dengan folder di dataset:
```python
classes = ['Bacterialblight', 'Brownspot', 'Healthy', 'Leafsmut']
IMG_SIZE = 224
```

**Cell 3: Load Dataset**
- Scan folder dan count gambar per class
- Output harus menunjukkan:
  - Bacterialblight: N images
  - Brownspot: N images
  - **Healthy: N images** (BARU)
  - Leafsmut: N images

**Cell 4: Data Augmentation**
- Augment gambar untuk variance yang lebih baik

**Cell 5: Model Building**
- Build MobileNetV2 transfer learning model
- 4 output classes (index 0-3)

**Cell 6: Training**
- Train untuk 50-100 epochs
- Monitor accuracy & loss
- Tunggu sampai selesai (~30 menit - 2 jam)

**Cell 7: Evaluation**
- Test model accuracy
- Check confusion matrix

**Cell 8: Save Model**
```python
model.save('rice_leaf_disease_model.keras')
# atau .h5 jika ingin format lama
```

---

## ✅ Validasi Model Baru

### 1. Check Model File Exists
```bash
cd "rice leaf diseases dataset"
ls -la rice_leaf_disease_model.keras
# File harus ada & > 30MB
```

### 2. Test dengan Python Script
```bash
cd web_TA
$json = @{image="[base64-image-here]"} | ConvertTo-Json

# Test classify
$json | python scripts/rice_inference.py classify --model-dir "../rice leaf diseases dataset"
```

**Output harus format:**
```json
{
  "success": true,
  "predicted_class": "Healthy",
  "confidence": 0.95,
  "all_predictions": {
    "Bacterialblight": 0.01,
    "Brownspot": 0.02,
    "Healthy": 0.95,
    "Leafsmut": 0.02
  },
  "leafiness": 0.45
}
```

### 3. Health Check Endpoint
```bash
curl http://localhost:8000/api/health/
# Response:
# {
#   "healthy": true,
#   "health": {
#     "python_model": {
#       "status": "ok",
#       "classes": ["Bacterialblight", "Brownspot", "Healthy", "Leafsmut"]
#     }
#   }
# }
```

---

## 🧪 Test Mobile App

### 1. Upload Foto Daun Sehat
- Open app
- Click "Ubah Foto"
- Upload foto daun sehat
- Harus detect sebagai **"Daun Sehat"** (hijau color)
- Severity: **"Tidak Ada"** (green badge)

### 2. Upload Foto Penyakit
- Upload foto daun sakit
- Harus detect penyakit specifik
- Severity: Normal (red/orange/yellow)

### 3. Test Concurrent Requests
- Multiple foto sekaligus
- Tidak boleh ada error "gagal menghubungi model"

---

## 📊 Training Performance Tips

### Meningkatkan Accuracy
1. **Dataset Quality** - Lebih penting dari quantity
   - Foto clear, bien-lit
   - Tidak ada gambar upside-down/rotated
   - Consistent background (daun only, no tools)

2. **Data Augmentation** - Notebook sudah punya
   - Rotation, Flip, Zoom
   - Brightness/Contrast adjustment

3. **Class Balance** - Pastikan semua class balanced
   - Ideal: ~200-300 per class
   - Min: 50 per class

4. **Training Parameters**
```python
# Already optimized di notebook:
IMG_SIZE = 224  # MobileNetV2 optimized size
learning_rate = 0.0001  # Small LR = stable learning
epochs = 50-100
batch_size = 32
```

### Jika Accuracy Rendah
1. Tambah dataset (lebih banyak foto)
2. Improve data quality
3. Increase epochs (sampai loss plateau)
4. Check class imbalance (use confusion matrix)

---

## 🚨 Common Issues & Fixes

### Issue: "Classes mismatch"
**Error**: `IndexError: list index out of range` saat inference

**Solusi**: 
```python
# Pastikan di notebook CLASS_NAMES updated:
classes = ['Bacterialblight', 'Brownspot', 'Healthy', 'Leafsmut']  # 4 classes
```

### Issue: Model Predicts Wrong Class
**Penyebab**: 
- Dataset Healthy tidak representative
- Too little training data
- Model overfit

**Solusi**:
1. Add more Healthy images
2. Increase epochs
3. Check data quality

### Issue: Timeout saat Classification
**Penyebab**: Model loading memakan waktu lama

**Solusi**: Sudah fixed dengan file locking!
- Requests di-queue
- Model load once → reuse untuk requests berikutnya

---

## 📈 After Training Checklist

- [ ] Model file exists: `rice_leaf_disease_model.keras`
- [ ] File size > 30MB
- [ ] `rice_inference.py` has 4 classes
- [ ] `ClassificationController.php` has Healthy info
- [ ] Mobile app UI updated (green color untuk Healthy)
- [ ] Test health endpoint: `/api/health/`
- [ ] Test mobile app dengan foto sehat
- [ ] Test mobile app dengan foto sakit
- [ ] Check no "gagal menghubungi model" errors

---

## 🎯 Expected Results

Setelah training dengan Healthy class:
- ✅ Daun sehat ter-classify dengan akurat
- ✅ Penyakit tetap ter-classify dengan akurat
- ✅ Mobile app display correctly (green untuk sehat, red untuk sakit)
- ✅ No more intermittent classification errors (sudah fixed)
- ✅ Confidence score consistent & reliable

---

## 📞 Troubleshooting Commands

### Clear Model Cache (jika ada issue)
```bash
# Delete old model files
rm "rice leaf diseases dataset\rice_leaf_disease_model.h5"
rm "rice leaf diseases dataset\rice_leaf_disease_model.json"

# Keep only .keras version
```

### Test Model Directly
```bash
cd web_TA
python scripts/diagnosis.py "../rice leaf diseases dataset"
```

Output akan show:
- TensorFlow status
- Model file status  
- Model loading test result
- Memory usage

---

*Last Updated: May 2026*
*Version: 2.0 - 4 Classes with Healthy Support*
