# 📊 Laravel Model & Database Setup - Classification System

## ✅ Apa yang Sudah Dibuat

### 1. **Model** (`Classification.php`)
- Represents `classifications` table
- Fillable properties untuk menyimpan hasil klasifikasi
- JSON casting untuk `all_predictions` field

### 2. **Migration** (`create_classifications_table.php`)
- Table schema dengan columns:
  - `id` - Primary key
  - `image_path` - Path gambar yang disimpan
  - `filename` - Original filename
  - `predicted_class` - Hasil prediksi (Bacterialblight, Brownspot, Leafsmut)
  - `confidence` - Score confidence (0-1)
  - `all_predictions` - JSON semua prediksi
  - `disease_name` - Nama penyakit dari disease info
  - `severity` - Tingkat severity penyakit
  - `notes` - Catatan tambahan
  - `created_at`, `updated_at` - Timestamps

### 3. **Controller** (`ClassificationHistoryController.php`)
- Index: Get semua classification dengan pagination
- Show: Get detail classification tertentu
- Destroy: Delete classification + image
- Stats: Get statistik klasifikasi

### 4. **Updated Files**
- `ClassificationController.php` - Save data ke model
- `routes/api.php` - Add history endpoints
- `database/migrations/2026_03_08_182722_create_classifications_table.php` - Table schema

---

## 🔌 API Endpoints

### Classification Endpoints

#### 1. Test Connection
```http
GET /api/classification/test
```
**Response:**
```json
{
  "success": true,
  "message": "Koneksi ke model API berhasil",
  "model_info": { ... }
}
```

#### 2. Classify Image (Analyze Only)
```http
POST /api/classification/classify
Content-Type: multipart/form-data

Body:
  image: <file>
```
**Response:**
```json
{
  "success": true,
  "data": {
    "predicted_class": "Bacterialblight",
    "confidence": "95.23%",
    "disease_info": {...},
    "timestamp": "2026-03-09T..."
  }
}
```

#### 3. Classify & Save Image
```http
POST /api/classification/classify-and-save
Content-Type: multipart/form-data

Body:
  image: <file>
  notes: "Optional notes"
```
**Response:** Same as above + saves to database

---

### History Endpoints

#### 4. Get All Classifications (Paginated)
```http
GET /api/classifications?page=1
```
**Response:**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "image_path": "classifications/...",
        "filename": "image.jpg",
        "predicted_class": "Bacterialblight",
        "confidence": 0.95,
        "disease_name": "Bercak Bakteri",
        "severity": "Sedang hingga Tinggi",
        "notes": "...",
        "created_at": "2026-03-09T10:30:00Z"
      }
    ],
    "current_page": 1,
    "total": 50,
    "per_page": 15
  }
}
```

#### 5. Get Classification Detail
```http
GET /api/classifications/{id}
```
**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "image_path": "...",
    "filename": "...",
    "predicted_class": "...",
    "confidence": 0.95,
    "all_predictions": {
      "Bacterialblight": 0.95,
      "Brownspot": 0.04,
      "Leafsmut": 0.01
    },
    "disease_name": "...",
    "severity": "...",
    "notes": "...",
    "created_at": "...",
    "updated_at": "..."
  }
}
```

#### 6. Delete Classification
```http
DELETE /api/classifications/{id}
```
**Response:**
```json
{
  "success": true,
  "message": "Classification deleted successfully"
}
```

#### 7. Get Statistics
```http
GET /api/classifications/stats/summary
```
**Response:**
```json
{
  "success": true,
  "data": {
    "total_classifications": 50,
    "average_confidence": "93.45%",
    "by_disease": [
      {
        "disease_name": "Bacterialblight",
        "count": 30
      },
      {
        "disease_name": "Brownspot",
        "count": 15
      },
      {
        "disease_name": "Leafsmut",
        "count": 5
      }
    ]
  }
}
```

---

## 📦 Database Schema

```sql
CREATE TABLE classifications (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  image_path VARCHAR(255) NULLABLE,
  filename VARCHAR(255) NOT NULL,
  predicted_class VARCHAR(255) NOT NULL,
  confidence FLOAT NOT NULL,
  all_predictions JSON NOT NULL,
  disease_name VARCHAR(255) NULLABLE,
  severity VARCHAR(255) NULLABLE,
  notes TEXT NULLABLE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

---

## 📁 File Structure

```
app/
├── Http/
│   └── Controllers/
│       ├── ClassificationController.php       (✏️ Updated)
│       └── ClassificationHistoryController.php (✨ NEW)
└── Models/
    └── Classification.php                     (✨ NEW)

database/
└── migrations/
    └── 2026_03_08_182722_create_classifications_table.php (✨ NEW)

routes/
└── api.php                                    (✏️ Updated)
```

---

## 🚀 Usage Examples

### Save Classification from Controller
```php
use App\Models\Classification;

// Automatic save in ClassificationController
Classification::create([
    'image_path' => 'classifications/image.jpg',
    'filename' => 'rice_leaf.jpg',
    'predicted_class' => 'Bacterialblight',
    'confidence' => 0.95,
    'all_predictions' => ['Bacterialblight' => 0.95, ...],
    'disease_name' => 'Bercak Bakteri',
    'severity' => 'Sedang hingga Tinggi',
    'notes' => 'Classification from mobile app',
]);
```

### Query Classifications
```php
// Get latest 10 classifications
$classifications = Classification::orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

// Get by disease
$bacterialblight = Classification::where('disease_name', 'Bercak Bakteri')->get();

// Get high confidence classifications
$highConfidence = Classification::where('confidence', '>=', 0.9)->get();
```

### Delete Classification with Image
```php
$classification = Classification::find(1);

// Delete image
if ($classification->image_path) {
    Storage::disk('public')->delete($classification->image_path);
}

// Delete record
$classification->delete();
```

---

## ✅ Migration Status

```bash
$ php artisan migrate

   INFO  Running migrations.  
  2026_03_08_182722_create_classifications_table ............... 347.26ms DONE
```

✅ Table `classifications` berhasil dibuat!

---

## 🔍 Verification

### Check Table Structure
```bash
php artisan tinker

>>> DB::table('classifications')->getColumns()
// Returns table structure

>>> App\Models\Classification::count()
// Returns number of classifications
```

### Check Fillable Properties
```bash
php artisan tinker

>>> (new App\Models\Classification())->getFillable()
// Returns: ['image_path', 'filename', 'predicted_class', ...]
```

---

## 📊 Data Flow

```
Mobile App
    ↓
POST /classify-and-save (with image)
    ↓
ClassificationController
    ↓ Call Python API
    ↓ Get result
    ↓
Classification::create([...])  ← Save to DB
    ↓
Return JSON response
    ↓
Mobile App displays result
```

---

## 🎯 Next Steps

1. ✅ Model & Migration created
2. ✅ Database table created (`php artisan migrate`)
3. ✅ History endpoints added
4. **→ Test API endpoints** dengan Postman/curl
5. **→ Update mobile app** untuk handle response
6. **→ Create dashboard** untuk view classifications

---

## 🧪 Test API Endpoints

### Test Save Classification
```bash
curl -X POST http://127.0.0.1:8000/api/classification/classify-and-save \
  -F "image=@rice_leaf.jpg" \
  -F "notes=Test classification"
```

### Test Get History
```bash
curl http://127.0.0.1:8000/api/classifications
```

### Test Get Stats
```bash
curl http://127.0.0.1:8000/api/classifications/stats/summary
```

---

## 📝 Notes

- ✅ All classifications auto-save to database
- ✅ Images stored in `storage/app/public/classifications/`
- ✅ JSON data properly casted in model
- ✅ Pagination on history endpoint (15 per page)
- ✅ Delete endpoint includes image cleanup
- ✅ Statistics endpoint aggregates data

---

**Status:** ✅ **COMPLETE**  
**Tables Created:** 1 (classifications)  
**Controllers:** 2 (ClassificationController, ClassificationHistoryController)  
**API Endpoints:** 7 total  
**Last Updated:** March 9, 2026
