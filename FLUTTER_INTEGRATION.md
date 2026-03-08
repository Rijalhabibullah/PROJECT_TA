# Mobile App Integration Guide

Panduan integrasi API Classification dengan aplikasi mobile Flutter.

## 📱 Flutter Integration

### 1. Dependencies

Tambahkan ke `pubspec.yaml` di `mobile_TA/padi_app/`:

```yaml
dependencies:
  flutter:
    sdk: flutter
  http: ^1.1.0
  image_picker: ^1.0.0
  permission_handler: ^11.4.0
  image: ^4.0.0
  cached_network_image: ^3.3.0
```

### 2. Service Class

Buat file `lib/services/classification_service.dart`:

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'dart:io';
import 'package:image_picker/image_picker.dart';

class ClassificationService {
  static const String _baseUrl = 'http://YOUR_LARAVEL_IP:8000/api/classification';
  
  // Get IP lokal Laravel server
  // Jika testing di device lokal: http://127.0.0.1:8000
  // Jika testing di emulator: http://10.0.2.2:8000
  // Jika testing di device fisik: http://192.168.x.x:8000
  
  final http.Client _httpClient;
  
  ClassificationService({http.Client? httpClient})
      : _httpClient = httpClient ?? http.Client();
  
  /// Klasifikasi dari file gambar
  Future<ClassificationResult> classifyImage(File imageFile) async {
    try {
      final request = http.MultipartRequest('POST', Uri.parse('$_baseUrl/classify'));
      
      request.files.add(
        await http.MultipartFile.fromPath('image', imageFile.path),
      );
      
      final response = await request.send().timeout(
        const Duration(seconds: 30),
        onTimeout: () {
          throw Exception('Request timeout');
        },
      );
      
      final responseBody = await response.stream.bytesToString();
      final jsonResponse = jsonDecode(responseBody);
      
      if (response.statusCode == 200 && jsonResponse['success'] == true) {
        return ClassificationResult.fromJson(jsonResponse['data']);
      } else {
        throw Exception(jsonResponse['message'] ?? 'Classification failed');
      }
    } on SocketException {
      throw Exception('Network error - pastikan server berjalan');
    } on TimeoutException {
      throw Exception('Request timeout');
    } catch (e) {
      throw Exception('Error: $e');
    }
  }
  
  /// Klasifikasi dan simpan
  Future<ClassificationResult> classifyAndSave(
    File imageFile, {
    String? notes,
  }) async {
    try {
      final request = http.MultipartRequest(
        'POST',
        Uri.parse('$_baseUrl/classify-and-save'),
      );
      
      request.files.add(
        await http.MultipartFile.fromPath('image', imageFile.path),
      );
      
      if (notes != null && notes.isNotEmpty) {
        request.fields['notes'] = notes;
      }
      
      final response = await request.send().timeout(
        const Duration(seconds: 30),
        onTimeout: () {
          throw Exception('Request timeout');
        },
      );
      
      final responseBody = await response.stream.bytesToString();
      final jsonResponse = jsonDecode(responseBody);
      
      if (response.statusCode == 200 && jsonResponse['success'] == true) {
        return ClassificationResult.fromJson(jsonResponse['data']);
      } else {
        throw Exception(jsonResponse['message'] ?? 'Classification failed');
      }
    } on SocketException {
      throw Exception('Network error');
    } catch (e) {
      throw Exception('Error: $e');
    }
  }
  
  /// Test koneksi ke API
  Future<bool> testConnection() async {
    try {
      final response = await _httpClient
          .get(Uri.parse('$_baseUrl/test'))
          .timeout(const Duration(seconds: 10));
      
      if (response.statusCode == 200) {
        final jsonResponse = jsonDecode(response.body);
        return jsonResponse['success'] == true;
      }
      return false;
    } catch (e) {
      return false;
    }
  }
}

class ClassificationResult {
  final String predictedClass;
  final String confidence;
  final double confidenceValue;
  final Map<String, dynamic> allPredictions;
  final DiseaseInfo diseaseInfo;
  final String? imagePath;
  final String? notes;
  final DateTime timestamp;
  
  ClassificationResult({
    required this.predictedClass,
    required this.confidence,
    required this.confidenceValue,
    required this.allPredictions,
    required this.diseaseInfo,
    required this.timestamp,
    this.imagePath,
    this.notes,
  });
  
  factory ClassificationResult.fromJson(Map<String, dynamic> json) {
    return ClassificationResult(
      predictedClass: json['predicted_class'] ?? '',
      confidence: json['confidence'] ?? '0%',
      confidenceValue: json['confidence_value'] ?? 0.0,
      allPredictions: Map<String, dynamic>.from(
        json['all_predictions'] ?? {},
      ),
      diseaseInfo: DiseaseInfo.fromJson(
        Map<String, dynamic>.from(json['disease_info'] ?? {}),
      ),
      imagePath: json['image_path'],
      notes: json['notes'],
      timestamp: DateTime.parse(
        json['timestamp'] ?? DateTime.now().toIso8601String(),
      ),
    );
  }
}

class DiseaseInfo {
  final String name;
  final String description;
  final List<String> symptoms;
  final List<String> treatment;
  final String severity;
  
  DiseaseInfo({
    required this.name,
    required this.description,
    required this.symptoms,
    required this.treatment,
    required this.severity,
  });
  
  factory DiseaseInfo.fromJson(Map<String, dynamic> json) {
    return DiseaseInfo(
      name: json['name'] ?? 'Unknown',
      description: json['description'] ?? '',
      symptoms: List<String>.from(json['symptoms'] ?? []),
      treatment: List<String>.from(json['treatment'] ?? []),
      severity: json['severity'] ?? 'Unknown',
    );
  }
}
```

### 3. UI Screen

Buat file `lib/screens/classification_screen.dart`:

```dart
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'dart:io';
import '../services/classification_service.dart';

class ClassificationScreen extends StatefulWidget {
  const ClassificationScreen({Key? key}) : super(key: key);

  @override
  State<ClassificationScreen> createState() => _ClassificationScreenState();
}

class _ClassificationScreenState extends State<ClassificationScreen> {
  final ClassificationService _service = ClassificationService();
  final ImagePicker _imagePicker = ImagePicker();
  
  File? _selectedImage;
  ClassificationResult? _result;
  bool _isLoading = false;
  String? _errorMessage;
  
  Future<void> _pickImage() async {
    try {
      final pickedFile = await _imagePicker.pickImage(
        source: ImageSource.camera,
      );
      
      if (pickedFile != null) {
        setState(() {
          _selectedImage = File(pickedFile.path);
          _result = null;
          _errorMessage = null;
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'Error picking image: $e';
      });
    }
  }
  
  Future<void> _classify() async {
    if (_selectedImage == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Pilih gambar terlebih dahulu')),
      );
      return;
    }
    
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });
    
    try {
      final result = await _service.classifyAndSave(_selectedImage!);
      setState(() {
        _result = result;
      });
    } catch (e) {
      setState(() {
        _errorMessage = e.toString();
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Klasifikasi Penyakit Padi')),
      body: SingleChildScrollView(
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            children: [
              // Image Preview
              Container(
                width: double.infinity,
                height: 300,
                decoration: BoxDecoration(
                  border: Border.all(color: Colors.grey),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: _selectedImage != null
                    ? Image.file(_selectedImage!, fit: BoxFit.cover)
                    : const Center(child: Text('Tidak ada gambar dipilih')),
              ),
              const SizedBox(height: 16),
              
              // Buttons
              Row(
                children: [
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: _pickImage,
                      icon: const Icon(Icons.camera_alt),
                      label: const Text('Ambil Foto'),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: _isLoading ? null : _classify,
                      icon: const Icon(Icons.check),
                      label: _isLoading
                          ? const SizedBox(
                              width: 20,
                              height: 20,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                              ),
                            )
                          : const Text('Klasifikasi'),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 24),
              
              // Error Message
              if (_errorMessage != null)
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.red[100],
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    _errorMessage!,
                    style: const TextStyle(color: Colors.red),
                  ),
                ),
              
              // Results
              if (_result != null)
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Card(
                      child: Padding(
                        padding: const EdgeInsets.all(16.0),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Hasil Klasifikasi',
                              style: Theme.of(context).textTheme.titleLarge,
                            ),
                            const SizedBox(height: 16),
                            Text('Diagnosis: ${_result!.diseaseInfo.name}',
                                style: const TextStyle(
                                    fontSize: 18, fontWeight: FontWeight.bold)),
                            const SizedBox(height: 8),
                            Text('Confidence: ${_result!.confidence}'),
                            Text('Severity: ${_result!.diseaseInfo.severity}'),
                            const SizedBox(height: 16),
                            Text(
                              'Gejala:',
                              style: Theme.of(context).textTheme.titleMedium,
                            ),
                            ..._result!.diseaseInfo.symptoms
                                .map((s) => Padding(
                                      padding: const EdgeInsets.only(
                                          left: 8.0, top: 4.0),
                                      child: Text('• $s'),
                                    )),
                            const SizedBox(height: 16),
                            Text(
                              'Penanganan:',
                              style: Theme.of(context).textTheme.titleMedium,
                            ),
                            ..._result!.diseaseInfo.treatment
                                .map((t) => Padding(
                                      padding: const EdgeInsets.only(
                                          left: 8.0, top: 4.0),
                                      child: Text('• $t'),
                                    )),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
            ],
          ),
        ),
      ),
    );
  }
}
```

### 4. Update main.dart

Edit `lib/main.dart`:

```dart
import 'package:flutter/material.dart';
import 'screens/classification_screen.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Padi Disease Classification',
      theme: ThemeData(
        primarySwatch: Colors.green,
        useMaterial3: true,
      ),
      home: const ClassificationScreen(),
    );
  }
}
```

## 🌐 Konfigurasi IP Server

Tergantung di mana aplikasi dijalankan:

| Environment | URL |
|---|---|
| **Emulator Android** | `http://10.0.2.2:8000` |
| **Device Fisik (WiFi)** | `http://192.168.x.x:8000` |
| **Testing Lokal** | `http://127.0.0.1:8000` |
| **Production** | `http://production-domain.com/api` |

Edit di `classification_service.dart`:

```dart
static const String _baseUrl = 'http://10.0.2.2:8000/api/classification';
```

## 🔧 Testing

```bash
cd mobile_TA/padi_app

# Install dependencies
flutter pub get

# Run app
flutter run

# Atau dengan specific device
flutter run -d emulator-5554
```

## 📝 Contoh Response

```json
{
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
  "image_path": "/storage/classifications/...",
  "timestamp": "2024-03-09T10:30:45.000000Z"
}
```

---

**Next:** Integrasikan dengan dashboard aplikasi Anda.
