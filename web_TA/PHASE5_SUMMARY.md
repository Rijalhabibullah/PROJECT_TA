# 📋 RINGKASAN LENGKAP - Phase 5: Laravel Model & Database

## 🎯 Objektif Tercapai

Membuat **database layer** untuk menyimpan hasil klasifikasi gambar daun padi ke dalam Laravel menggunakan Eloquent Model.

---

## ✅ FILE YANG DIBUAT / DIUBAH

### 📝 **File Baru (NEW)**

#### 1. `app/Models/Classification.php`
```php
class Classification extends Model
{
    protected $fillable = [
        'image_path',
        'filename',
        'predicted_class',
        'confidence',
        'all_predictions',
        'disease_name',
        'severity',
        'notes',
    ];

    protected $casts = [
        'all_predictions' => 'array',
        'confidence' => 'float',
    ];
}
```
- Model untuk table `classifications`
- Supports JSON casting untuk `all_predictions`
- Timestamps otomatis (created_at, updated_at)

#### 2. `database/migrations/2026_03_08_182722_create_classifications_table.php`
```php
Schema::create('classifications', function (Blueprint $table) {
    $table->id();
    $table->string('image_path')->nullable();
    $table->string('filename');
    $table->string('predicted_class');
    $table->float('confidence');
    $table->json('all_predictions');
    $table->string('disease_name')->nullable();
    $table->string('severity')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
});
```
- 10 columns untuk store data lengkap
- JSON column untuk all_predictions
- Nullable fields untuk optional data
- **Status:** ✅ Executed successfully (347.26ms)

#### 3. `app/Http/Controllers/ClassificationHistoryController.php` ⭐ NEW
```php
class ClassificationHistoryController extends Controller
{
    public function index()              // GET /api/classifications
    public function show($id)            // GET /api/classifications/{id}
    public function destroy($id)         // DELETE /api/classifications/{id}
    public function stats()              // GET /api/classifications/stats/summary
}
```
- **index()**: List semua dengan pagination (15/page)
- **show()**: Detail single record dengan all_predictions JSON
- **destroy()**: Delete record + cleanup image file
- **stats()**: Aggregate stats (total, average confidence, breakdown by disease)

---

### ✏️ **File yang Diubah (UPDATED)**

#### 1. `app/Http/Controllers/ClassificationController.php`
**Perubahan:** Tambah `Classification::create()` untuk save data

Sebelum (hanya return response):
```php
return response()->json(['success' => true, 'data' => $result]);
```

Sesudah (save to database):
```php
// Get disease info
$diseaseInfo = getDiseaseInfo($result['predicted_class']);

// Save to database
Classification::create([
    'image_path' => $path,
    'filename' => $file->getClientOriginalName(),
    'predicted_class' => $result['predicted_class'],
    'confidence' => $result['confidence'],
    'all_predictions' => $result['all_predictions'],
    'disease_name' => $diseaseInfo['name'] ?? null,
    'severity' => $diseaseInfo['severity'] ?? null,
    'notes' => null,
]);

return response()->json(['success' => true, 'data' => $result]);
```

#### 2. `routes/api.php`
**Perubahan:** Tambah classification history routes

```php
// Classification endpoints
Route::post('/classify', [ClassificationController::class, 'classify']);
Route::post('/classify-and-save', [ClassificationController::class, 'classifyAndSave']);
Route::get('/test', [ClassificationController::class, 'testConnection']);

// History endpoints (NEW)
Route::get('/', [ClassificationHistoryController::class, 'index']);
Route::get('/{id}', [ClassificationHistoryController::class, 'show']);
Route::delete('/{id}', [ClassificationHistoryController::class, 'destroy']);
Route::get('/stats/summary', [ClassificationHistoryController::class, 'stats']);
```

Total: **7 endpoints** untuk classification & history

---

## 📊 Database Schema

### Table: `classifications`

```
Column              | Type      | Nullable | Default
--------------------|-----------|----------|----------
id                  | BIGINT    | ✗        | PRIMARY KEY
image_path          | VARCHAR   | ✓        | NULL
filename            | VARCHAR   | ✗        | 
predicted_class     | VARCHAR   | ✗        | 
confidence          | FLOAT     | ✗        | 
all_predictions     | JSON      | ✗        | 
disease_name        | VARCHAR   | ✓        | NULL
severity            | VARCHAR   | ✓        | NULL
notes               | TEXT      | ✓        | NULL
created_at          | TIMESTAMP | ✗        | CURRENT_TIMESTAMP
updated_at          | TIMESTAMP | ✗        | CURRENT_TIMESTAMP ON UPDATE
```

---

## 🔌 API Endpoints

### Classification (Existing)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/classification/test` | Test API connection |
| POST | `/api/classification/classify` | Analyze image only |
| POST | `/api/classification/classify-and-save` | Analyze + save to DB |

### Classifications History (NEW)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/classifications` | List all (paginated) |
| GET | `/api/classifications/{id}` | Get detail by ID |
| DELETE | `/api/classifications/{id}` | Delete record + image |
| GET | `/api/classifications/stats/summary` | Get statistics |

---

## 🔄 Data Flow

```
┌─────────────────────────────────────────────────────┐
│ Mobile App (Flutter)                                │
│ - Pick image from camera/gallery                    │
│ - Call: POST /classify-and-save                     │
└──────────────────┬──────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────┐
│ Laravel API (Port 8000)                             │
│ ClassificationController::classifyAndSave()         │
│ - Receive image                                     │
│ - Forward to Python API                            │
└──────────────────┬──────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────┐
│ Python API (Port 5000)                              │
│ api_server.py                                       │
│ - Load TensorFlow model                            │
│ - Preprocess image                                 │
│ - Predict disease                                  │
│ - Return JSON result                               │
└──────────────────┬──────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────┐
│ Laravel API (Port 8000)                             │
│ ClassificationController::classifyAndSave()         │
│ - Save image to storage/app/classifications/       │
│ - Classification::create([...])  ◄─── SAVE TO DB   │
│ - Return response to mobile                        │
└──────────────────┬──────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────┐
│ Mobile App (Flutter)                                │
│ - Display classification result                    │
│ - Show disease info, severity, treatments          │
└─────────────────────────────────────────────────────┘
```

---

## 📦 Struktur Direktori

```
web_TA/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── ClassificationController.php          (✏️ Updated)
│   │       └── ClassificationHistoryController.php   (✨ NEW)
│   └── Models/
│       └── Classification.php                        (✨ NEW)
│
├── database/
│   └── migrations/
│       └── 2026_03_08_182722_create_classifications_table.php (✨ NEW)
│
├── routes/
│   └── api.php                                       (✏️ Updated)
│
└── storage/
    └── app/
        └── public/
            └── classifications/                      (stores images)
```

---

## 🧪 Testing Endpoints

### 1. Test Save Classification
```bash
curl -X POST http://127.0.0.1:8000/api/classification/classify-and-save \
  -F "image=@rice_leaf.jpg"
```
**Expected Response:**
```json
{
  "success": true,
  "data": {
    "predicted_class": "Bacterialblight",
    "confidence": "95.23%",
    "disease_info": "Bercak Bakteri..."
  }
}
```
✓ Record akan tersimpan di table `classifications`

### 2. Get All Classifications
```bash
curl http://127.0.0.1:8000/api/classifications?page=1
```
**Expected:** List dengan pagination

### 3. Get Statistics
```bash
curl http://127.0.0.1:8000/api/classifications/stats/summary
```
**Expected:** Total count, average confidence, breakdown by disease

### 4. Delete Classification
```bash
curl -X DELETE http://127.0.0.1:8000/api/classifications/1
```
**Expected:** Record + image deleted

---

## 📈 Statistics Query

```php
// Get statistics
$total = Classification::count();
$avgConfidence = Classification::avg('confidence');
$byDisease = Classification::select('disease_name')
    ->groupBy('disease_name')
    ->selectRaw('count(*) as count')
    ->get();
```

---

## ✅ Verification Checklist

- ✅ Model `Classification` dibuat
- ✅ Migration dibuat dengan schema lengkap
- ✅ Table `classifications` berhasil dijalankan (`php artisan migrate`)
- ✅ ClassificationController save data ke model
- ✅ ClassificationHistoryController dengan CRUD + stats
- ✅ Routes ditambahkan untuk semua endpoints
- ✅ JSON casting untuk `all_predictions`
- ✅ Float casting untuk `confidence`
- ✅ Timestamps otomatis (created_at, updated_at)
- ✅ Image cleanup saat delete

---

## 🚀 Next Steps

1. **Start Laravel Server**: `php artisan serve --port=8000`
2. **Start Python API**: `python api_server.py` (port 5000)
3. **Run Flutter**: `flutter run` (emulator/device)
4. **Test Full Integration**:
   - Upload gambar dari mobile
   - Verify record di database
   - Test history endpoints
   - Test statistics
5. **Create Dashboard** (optional):
   - Display classification history
   - Add filters by disease
   - Add date range search

---

## 📞 Summary Endpoints

```
Classification:
├── GET    /api/classification/test
├── POST   /api/classification/classify
└── POST   /api/classification/classify-and-save

Classifications History:
├── GET    /api/classifications
├── GET    /api/classifications/{id}
├── DELETE /api/classifications/{id}
└── GET    /api/classifications/stats/summary
```

---

## 🎓 Key Features

| Feature | Status |
|---------|--------|
| Model dengan Eloquent | ✅ |
| Migration dengan schema | ✅ |
| Auto save saat classify | ✅ |
| Pagination di history | ✅ |
| JSON storage | ✅ |
| Image cleanup | ✅ |
| Statistics endpoint | ✅ |
| Timestamps | ✅ |

---

**Phase Status:** ✅ **COMPLETE**  
**Files Modified:** 2  
**Files Created:** 3  
**Database Tables:** 1 (classifications)  
**API Endpoints:** 7 total  
**Last Execution:** `php artisan migrate` - 347.26ms DONE

---

## 📄 Documentation Files

- [MODEL_API_SETUP.md](./MODEL_API_SETUP.md) - Dokumentasi lengkap
- [INTEGRATION_SUMMARY.md](./INTEGRATION_SUMMARY.md) - Summary integrasi full system
- [SETUP_GUIDE.md](./SETUP_GUIDE.md) - Panduan setup awal
