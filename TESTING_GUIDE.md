# 🧪 Testing Guide - Classification System Integration

## 📋 Pre-Testing Checklist

### Server Status
- [ ] Laravel server running (`php artisan serve --port=8000`)
- [ ] Python API running (`python api_server.py` on port 5000)
- [ ] Database migrations done (`php artisan migrate`)
- [ ] Flutter pubspec packages updated (`flutter pub get`)

### Network Configuration
- [ ] Python API accessible from Laravel: `http://127.0.0.1:5000`
- [ ] Laravel API accessible from mobile: `http://10.0.2.2:8000` (emulator) or `http://192.168.x.x:8000` (device)
- [ ] Mobile device/emulator has internet access

### Environment
- [ ] `.env` configured with database credentials
- [ ] `storage/app/public` directory writable
- [ ] Model file loaded in Python API

---

## 🖥️ Backend Testing (Laravel + Python)

### Test 1: Connection Test

#### By Postman/curl
```bash
curl -X GET http://127.0.0.1:8000/api/classification/test
```

#### Expected Response
```json
{
  "success": true,
  "message": "Koneksi ke model API berhasil",
  "model_info": {
    "model": "TensorFlow CNN Model",
    "classes": ["Bacterialblight", "Brownspot", "Leafsmut"]
  }
}
```

#### What's Being Tested
- ✓ Laravel server responding
- ✓ Connection to Python API server
- ✓ Model loaded in Python

---

### Test 2: Single Classification (No Save)

#### By Postman
1. Open **Postman**
2. Create **POST** request to `http://127.0.0.1:8000/api/classification/classify`
3. Go to **Body** → **form-data**
4. Add key `image` → select rice leaf image
5. Click **Send**

#### By curl
```bash
curl -X POST http://127.0.0.1:8000/api/classification/classify \
  -F "image=@/path/to/rice_leaf.jpg"
```

#### Expected Response
```json
{
  "success": true,
  "data": {
    "predicted_class": "Bacterialblight",
    "confidence": "95.23%",
    "disease_info": {
      "name": "Bercak Bakteri",
      "severity": "Sedang hingga Tinggi",
      "symptoms": "...",
      "treatments": ["..."]
    },
    "timestamp": "2026-03-09T10:30:00"
  }
}
```

#### What's Being Tested
- ✓ Image upload functionality
- ✓ Image preprocessing in Python
- ✓ TensorFlow model prediction
- ✓ JSON response formatting
- ✗ Database save (intentionally not saved)

---

### Test 3: Classification with Save

#### By Postman
1. Create **POST** request to `http://127.0.0.1:8000/api/classification/classify-and-save`
2. Go to **Body** → **form-data**
3. Add key `image` → select rice leaf image
4. Add key `notes` → enter "Initial test classification"
5. Click **Send**

#### By curl
```bash
curl -X POST http://127.0.0.1:8000/api/classification/classify-and-save \
  -F "image=@/path/to/rice_leaf.jpg" \
  -F "notes=Initial test classification"
```

#### Expected Response
```json
{
  "success": true,
  "data": {
    "predicted_class": "Bacterialblight",
    "confidence": "95.23%",
    "disease_info": {...}
  }
}
```

#### Verify Database Save (Tinker)
```bash
php artisan tinker

>>> DB::table('classifications')->count()
1

>>> DB::table('classifications')->latest()->first()
{
  "id": 1,
  "image_path": "classifications/2026_03_09_...",
  "filename": "rice_leaf.jpg",
  "predicted_class": "Bacterialblight",
  "confidence": 0.9523,
  "all_predictions": "[...]",
  "disease_name": "Bercak Bakteri",
  "severity": "Sedang hingga Tinggi",
  "notes": "Initial test classification",
  "created_at": "2026-03-09 10:30:00",
  "updated_at": "2026-03-09 10:30:00"
}
```

#### What's Being Tested
- ✓ Image upload
- ✓ Model prediction
- ✓ **Database save** (new in Phase 5!)
- ✓ Image file storage
- ✓ JSON all_predictions storage

---

### Test 4: Get All Classifications

#### By curl
```bash
curl -X GET "http://127.0.0.1:8000/api/classifications?page=1"
```

#### By Browser
```
http://127.0.0.1:8000/api/classifications?page=1
```

#### Expected Response
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "image_path": "classifications/...",
        "filename": "rice_leaf.jpg",
        "predicted_class": "Bacterialblight",
        "confidence": 0.9523,
        "disease_name": "Bercak Bakteri",
        "severity": "Sedang hingga Tinggi",
        "notes": "Initial test classification",
        "created_at": "2026-03-09T10:30:00Z"
      }
    ],
    "current_page": 1,
    "total": 1,
    "per_page": 15,
    "last_page": 1
  }
}
```

#### What's Being Tested
- ✓ Pagination
- ✓ Data retrieval from database
- ✓ JSON response with all fields

---

### Test 5: Get Classification Detail

#### By curl
```bash
curl -X GET http://127.0.0.1:8000/api/classifications/1
```

#### Expected Response
```json
{
  "success": true,
  "data": {
    "id": 1,
    "image_path": "classifications/...",
    "filename": "rice_leaf.jpg",
    "predicted_class": "Bacterialblight",
    "confidence": 0.9523,
    "all_predictions": {
      "Bacterialblight": 0.9523,
      "Brownspot": 0.0350,
      "Leafsmut": 0.0127
    },
    "disease_name": "Bercak Bakteri",
    "severity": "Sedang hingga Tinggi",
    "notes": "Initial test classification",
    "created_at": "2026-03-09T10:30:00Z",
    "updated_at": "2026-03-09T10:30:00Z"
  }
}
```

#### What's Being Tested
- ✓ Detail retrieval by ID
- ✓ JSON array decoding for all_predictions
- ✓ Timestamp formatting

---

### Test 6: Get Statistics

#### By curl
```bash
curl -X GET http://127.0.0.1:8000/api/classifications/stats/summary
```

#### Expected Response
```json
{
  "success": true,
  "data": {
    "total_classifications": 1,
    "average_confidence": "95.23%",
    "by_disease": [
      {
        "disease_name": "Bercak Bakteri",
        "count": 1
      }
    ]
  }
}
```

#### What's Being Tested
- ✓ Aggregation queries
- ✓ Statistical calculations
- ✓ Grouping by disease

---

### Test 7: Delete Classification

#### By curl
```bash
curl -X DELETE http://127.0.0.1:8000/api/classifications/1
```

#### Expected Response
```json
{
  "success": true,
  "message": "Classification deleted successfully"
}
```

#### Verify Delete
```bash
php artisan tinker

>>> DB::table('classifications')->count()
0

>>> file_exists(storage_path('app/public/classifications/...'))
false  // Image should also be deleted
```

#### What's Being Tested
- ✓ Database record deletion
- ✓ Image file cleanup
- ✓ Cascade delete

---

## 📱 Mobile Testing (Flutter)

### Setup

1. **Update API URL in classification_service.dart**
   ```dart
   // For Android emulator
   static const String baseUrl = 'http://10.0.2.2:8000/api/classification';
   
   // For physical device (replace with your computer IP)
   static const String baseUrl = 'http://192.168.1.100:8000/api/classification';
   ```

2. **Ensure Flutter dependencies installed**
   ```bash
   cd d:\PROJECT TA\mobile_TA\padi_app
   flutter pub get
   ```

3. **Run Flutter app**
   ```bash
   flutter run
   ```

---

### Test 1: App Startup

#### Steps
1. Run `flutter run`
2. Wait for app to build and launch
3. Should see SplashScreen → MainNavigation

#### Expected
- ✓ App starts without crashes
- ✓ Navigation loads correctly
- ✓ Bottom tabs visible

#### What's Being Tested
- ✓ Flutter build success
- ✓ Navigation routing
- ✓ UI rendering

---

### Test 2: Image Picker

#### Steps
1. Navigate to Klasifikasi tab
2. Click on image container or camera button
3. Choose **Camera** or **Gallery**
4. Select/take a rice leaf photo

#### Expected
- ✓ Image picker opens
- ✓ Permission dialog appears (first time)
- ✓ Image selected shows in UI

#### What's Being Tested
- ✓ Image picker plugin
- ✓ Permission handling
- ✓ Image display in UI

---

### Test 3: Classification Request

#### Steps
1. Select a rice leaf image
2. Click "Klasifikasikan" button
3. Wait for loading spinner
4. Should receive results or error

#### Expected Success
```
Disease: Bercak Bakteri
Confidence: 95.23%
Severity: Sedang hingga Tinggi
Loading indicator disappears
Result screen shows data
```

#### Expected Errors (should handle gracefully)
- Network timeout → "Koneksi timeout"
- Connection refused → "Server tidak dihubungi"
- Image validation → "Gambar tidak valid"

#### What's Being Tested
- ✓ API request from mobile
- ✓ Error handling
- ✓ Loading state management
- ✓ Response parsing
- ✓ Navigation to result screen

---

### Test 4: Result Display

#### Steps
1. After classification completes
2. Verify result screen shows:
   - Disease name
   - Confidence percentage with bar
   - Severity badge
   - Symptoms list
   - Treatments list
   - Disease description

#### Expected
- ✓ All fields populated correctly
- ✓ Confidence bar displays correctly
- ✓ Info readable and formatted well

#### What's Being Tested
- ✓ UI rendering of API response
- ✓ Data parsing and formatting
- ✓ Conditional rendering

---

### Test 5: Error Handling

#### Scenario 1: No Internet
1. Turn off WiFi/mobile data
2. Try classification
3. Should see error message

#### Scenario 2: Server Down
1. Stop Python/Laravel server
2. Try classification
3. Should see timeout or connection error

#### Scenario 3: Invalid Image
1. Select a non-image file (if possible)
2. Try to classify
3. Should see validation error

#### What's Being Tested
- ✓ Network error handling
- ✓ Timeout handling
- ✓ Exception catching
- ✓ User feedback

---

## 🔄 Integration Test

### Full Workflow Test

1. **Start all servers**
   ```bash
   # Terminal 1: PHP
   cd d:\PROJECT TA\web_TA
   php artisan serve --port=8000
   
   # Terminal 2: Python (in dataset folder)
   python api_server.py
   
   # Terminal 3: Flutter
   cd d:\PROJECT TA\mobile_TA\padi_app
   flutter run
   ```

2. **Test flow**
   - Open app → Klasifikasi tab
   - Pick rice leaf image
   - Click "Klasifikasikan"
   - Wait for result
   - Verify result displays correctly

3. **Verify database**
   ```bash
   php artisan tinker
   >>> App\Models\Classification::latest()->first()
   ```

4. **Check stored files**
   ```bash
   dir d:\PROJECT TA\web_TA\storage\app\public\classifications
   ```

---

## ✅ All Tests Checklist

### Backend (Laravel + Python)
- [ ] Test 1: Connection test passes
- [ ] Test 2: Single classification works
- [ ] Test 3: Save to database works
- [ ] Test 4: List all classifications works
- [ ] Test 5: Get detail works
- [ ] Test 6: Statistics endpoint works
- [ ] Test 7: Delete + cleanup works

### Mobile (Flutter)
- [ ] Test 1: App starts successfully
- [ ] Test 2: Image picker opens
- [ ] Test 3: Classification request works
- [ ] Test 4: Results display correctly
- [ ] Test 5: Error handling works

### Integration
- [ ] [ ] Full workflow end-to-end
- [ ] [ ] Database saves when classifying from mobile
- [ ] [ ] Can retrieve history from mobile?
- [ ] [ ] Statistics reflect classified images

---

## 🐛 Troubleshooting

### Error: Connection Refused (Python API)
```
Error: Failed to connect to 127.0.0.1:5000
```
**Solution:**
1. Check Python API running: `python api_server.py`
2. Verify port 5000 not blocked
3. Check firewall settings

### Error: Connection Refused (Laravel API)
```
Error: Failed to connect to 127.0.0.1:8000
```
**Solution:**
1. Check Laravel running: `php artisan serve --port=8000`
2. Verify database connected: `php artisan migrate`
3. Check `.env` database credentials

### Error: CORS Error on Mobile
```
Error: CORS policy: No 'Access-Control-Allow-Origin' header
```
**Solution:**
1. Check python `api_server.py` has CORS enabled
2. Verify requests from correct origin IP (not localhost for emulator)
3. Update API URL in Flutter service

### Error: Database Migration Failed
```
Error: SQLSTATE[HY000]: General error
```
**Solution:**
1. Reset migrations: `php artisan migrate:rollback`
2. Drop and recreate database
3. Run migrations again: `php artisan migrate`

### Error: File Not Saved
```
Storage path not writable
```
**Solution:**
```bash
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

---

## 📊 Test Results Template

```markdown
## Test Session: [Date/Time]

### Environment
- Laravel Version: ___
- PHP Version: ___
- Python Version: ___
- Database: ___
- Flutter/Dart Version: ___

### Backend Tests
| Test | Result | Duration | Notes |
|------|--------|----------|-------|
| Connection Test | ✓/✗ | __ ms | ___ |
| Classify Only | ✓/✗ | __ ms | ___ |
| Classify & Save | ✓/✗ | __ ms | ___ |
| Get All | ✓/✗ | __ ms | ___ |
| Get Detail | ✓/✗ | __ ms | ___ |
| Get Stats | ✓/✗ | __ ms | ___ |
| Delete | ✓/✗ | __ ms | ___ |

### Mobile Tests
| Test | Result | Duration | Notes |
|------|--------|----------|-------|
| App Startup | ✓/✗ | __ sec | ___ |
| Image Picker | ✓/✗ | __ sec | ___ |
| Classification | ✓/✗ | __ sec | ___ |
| Result Display | ✓/✗ | __ sec | ___ |
| Error Handling | ✓/✗ | __ sec | ___ |

### Issues Found
1. [Issue] → Fix: [Solution]
2. [Issue] → Fix: [Solution]

### Performance Notes
- Average classification time: __ ms
- Average database save time: __ ms
- Mobile response time: __ sec

### Sign-off
Tester: ___ | Date: ___ | Status: ___
```

