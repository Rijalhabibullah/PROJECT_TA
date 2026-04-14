import 'package:http/http.dart' as http;
import 'dart:convert';
import 'dart:io';
import 'dart:async';

class ClassificationService {
  // ⚠️ UBAH URL INI SESUAI ENVIRONMENT
  // Emulator Android: http://10.0.2.2:8000/api/classification
  // Device Fisik (WiFi): http://192.168.x.x:8000/api/classification (ganti dengan IP komputer Anda)
  // Testing Lokal: http://127.0.0.1:8000/api/classification
  // Production (ngrok): https://gobony-wedgy-cathi.ngrok-free.dev/api/classification
  static const String _baseUrl = 'https://gobony-wedgy-cathi.ngrok-free.dev/api/classification';
  
  final http.Client _httpClient;
  
  ClassificationService({http.Client? httpClient})
      : _httpClient = httpClient ?? http.Client();
  
  /// Klasifikasi gambar dari file
  Future<ClassificationResult> classifyImage(File imageFile) async {
    try {
      final request = http.MultipartRequest('POST', Uri.parse('$_baseUrl/classify'));
      
      request.files.add(
        await http.MultipartFile.fromPath('image', imageFile.path),
      );
      
      final response = await request.send().timeout(
        const Duration(seconds: 30),
        onTimeout: () {
          throw TimeoutException('Request timeout - pastikan server berjalan');
        },
      );
      
      final responseBody = await response.stream.bytesToString();
      final jsonResponse = jsonDecode(responseBody);
      
      if (response.statusCode == 200 && jsonResponse['success'] == true) {
        return ClassificationResult.fromJson(jsonResponse['data']);
      } else {
        throw Exception(jsonResponse['message'] ?? 'Klasifikasi gagal');
      }
    } on TimeoutException catch (e) {
      throw Exception(e.message);
    } on SocketException {
      throw Exception('Network error - Check your connection');
    } catch (e) {
      throw Exception('Error: $e');
    }
  }
  
  /// Klasifikasi dan simpan gambar di server
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
          throw TimeoutException('Request timeout');
        },
      );
      
      final responseBody = await response.stream.bytesToString();
      final jsonResponse = jsonDecode(responseBody);
      
      if (response.statusCode == 200 && jsonResponse['success'] == true) {
        return ClassificationResult.fromJson(jsonResponse['data']);
      } else {
        throw Exception(jsonResponse['message'] ?? 'Klasifikasi gagal');
      }
    } on TimeoutException catch (e) {
      throw Exception(e.message);
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
