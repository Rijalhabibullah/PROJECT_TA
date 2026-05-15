import 'package:http/http.dart' as http;
import 'dart:convert';
import 'dart:io';
import 'dart:async';
import 'package:shared_preferences/shared_preferences.dart';

class ClassificationService {
  // ⚠️ UBAH URL INI SESUAI ENVIRONMENT
  // Emulator Android: http://10.0.2.2:8000/api/classification
  // Device Fisik (WiFi): http://192.168.x.x:8000/api/classification (ganti dengan IP komputer Anda)
  // Testing Lokal: http://127.0.0.1:8000/api/classification
  // Production (ngrok): https://gobony-wedgy-cathi.ngrok-free.dev/api/classification
  static const String _apiRoot = 'https://gobony-wedgy-cathi.ngrok-free.dev/api';
  static const String _baseUrl = '$_apiRoot/classification';
  static const String _historyUrl = '$_apiRoot/classifications';
  
  final http.Client _httpClient;
  
  ClassificationService({http.Client? httpClient})
      : _httpClient = httpClient ?? http.Client();
  
  /// Klasifikasi gambar dari file
  Future<ClassificationResult> classifyImage(
    File imageFile, {
    String? locationAddress,
    double? locationLat,
    double? locationLng,
    String? kabupaten,
    String? kecamatan,
    String? kelurahan,
  }) async {
    try {
      final request = http.MultipartRequest('POST', Uri.parse('$_baseUrl/classify'));
      request.headers['Accept'] = 'application/json';

      final prefs = await SharedPreferences.getInstance();
      final userId = prefs.getInt('user_id');
      final userName = prefs.getString('user_name');
      if (userId != null && userId > 0) {
        request.fields['user_id'] = userId.toString();
      }
      if (userName != null && userName.trim().isNotEmpty) {
        request.fields['user_name'] = userName.trim();
      }
      
      request.files.add(
        await http.MultipartFile.fromPath('image', imageFile.path),
      );
      
      if (locationAddress != null && locationAddress.isNotEmpty) {
        request.fields['location_address'] = locationAddress;
      }
      if (locationLat != null) {
        request.fields['location_lat'] = locationLat.toString();
      }
      if (locationLng != null) {
        request.fields['location_lng'] = locationLng.toString();
      }
      if (kabupaten != null && kabupaten.isNotEmpty) {
        request.fields['kabupaten'] = kabupaten;
      }
      if (kecamatan != null && kecamatan.isNotEmpty) {
        request.fields['kecamatan'] = kecamatan;
      }
      if (kelurahan != null && kelurahan.isNotEmpty) {
        request.fields['kelurahan'] = kelurahan;
      }
      
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
      } else if (response.statusCode == 422 && jsonResponse['errors'] is Map) {
        final errors = Map<String, dynamic>.from(jsonResponse['errors']);
        final errorMessage = errors.entries
            .expand((entry) {
              final value = entry.value;
              if (value is List) {
                return value.map((item) => '$item');
              }
              return ['${entry.key}: $value'];
            })
            .join('\n');

        throw Exception(
          jsonResponse['message'] != null
              ? '${jsonResponse['message']}\n$errorMessage'
              : errorMessage,
        );
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
    String? locationAddress,
    double? locationLat,
    double? locationLng,
    String? kabupaten,
    String? kecamatan,
    String? kelurahan,
  }) async {
    try {
      final request = http.MultipartRequest(
        'POST',
        Uri.parse('$_baseUrl/classify-and-save'),
      );
      request.headers['Accept'] = 'application/json';

      final prefs = await SharedPreferences.getInstance();
      final userId = prefs.getInt('user_id');
      if (userId != null && userId > 0) {
        request.fields['user_id'] = userId.toString();
      }
      
      request.files.add(
        await http.MultipartFile.fromPath('image', imageFile.path),
      );
      
      if (notes != null && notes.isNotEmpty) {
        request.fields['notes'] = notes;
      }

      if (locationAddress != null && locationAddress.isNotEmpty) {
        request.fields['location_address'] = locationAddress;
      }
      if (locationLat != null) {
        request.fields['location_lat'] = locationLat.toString();
      }
      if (locationLng != null) {
        request.fields['location_lng'] = locationLng.toString();
      }
      if (kabupaten != null && kabupaten.isNotEmpty) {
        request.fields['kabupaten'] = kabupaten;
      }
      if (kecamatan != null && kecamatan.isNotEmpty) {
        request.fields['kecamatan'] = kecamatan;
      }
      if (kelurahan != null && kelurahan.isNotEmpty) {
        request.fields['kelurahan'] = kelurahan;
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

  /// Ambil riwayat klasifikasi
  Future<List<ClassificationHistoryItem>> fetchHistory({int page = 1}) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final userId = prefs.getInt('user_id');
      final userParam = (userId != null && userId > 0)
          ? '&user_id=${userId.toString()}'
          : '';

      final response = await _httpClient
          .get(
            Uri.parse('$_historyUrl?page=$page&per_page=1000$userParam'),
            headers: {'Accept': 'application/json'},
          )
          .timeout(const Duration(seconds: 15));

      if (response.statusCode != 200) {
        throw Exception('Gagal mengambil riwayat');
      }

      final jsonResponse = jsonDecode(response.body);
      if (jsonResponse['success'] != true) {
        throw Exception(jsonResponse['message'] ?? 'Gagal mengambil riwayat');
      }

      final data = jsonResponse['data'];
      final items = (data is Map<String, dynamic>) ? data['data'] : null;
      if (items is! List) {
        return [];
      }

      return items
          .map((item) => ClassificationHistoryItem.fromJson(
                Map<String, dynamic>.from(item),
              ))
          .toList();
    } on TimeoutException catch (e) {
      throw Exception(e.message);
    } on SocketException {
      throw Exception('Network error');
    } catch (e) {
      throw Exception('Error: $e');
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
  final String? locationAddress;
  final String? kabupaten;
  final String? kecamatan;
  final String? kelurahan;
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
    this.locationAddress,
    this.kabupaten,
    this.kecamatan,
    this.kelurahan,
  });
  
  factory ClassificationResult.fromJson(Map<String, dynamic> json) {
    return ClassificationResult(
      predictedClass: json['predicted_class'] ?? '',
      confidence: json['confidence'] ?? '0%',
        confidenceValue: (json['confidence_value'] is num)
          ? (json['confidence_value'] as num).toDouble()
          : 0.0,
      allPredictions: Map<String, dynamic>.from(
        json['all_predictions'] ?? {},
      ),
      diseaseInfo: DiseaseInfo.fromJson(
        Map<String, dynamic>.from(json['disease_info'] ?? {}),
      ),
      imagePath: json['image_path'],
      notes: json['notes'],
      locationAddress: json['location_address'],
      kabupaten: json['kabupaten'],
      kecamatan: json['kecamatan'],
      kelurahan: json['kelurahan'],
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

class ClassificationHistoryItem {
  final int id;
  final String diseaseName;
  final String predictedClass;
  final double confidenceValue;
  final String confidence;
  final String? imagePath;
  final String? locationAddress;
  final double? locationLat;
  final double? locationLng;
  final String? kabupaten;
  final String? kecamatan;
  final String? kelurahan;
  final DateTime createdAt;

  ClassificationHistoryItem({
    required this.id,
    required this.diseaseName,
    required this.predictedClass,
    required this.confidenceValue,
    required this.confidence,
    required this.createdAt,
    this.imagePath,
    this.locationAddress,
    this.locationLat,
    this.locationLng,
    this.kabupaten,
    this.kecamatan,
    this.kelurahan,
  });

  factory ClassificationHistoryItem.fromJson(Map<String, dynamic> json) {
    final confidenceValue = (json['confidence'] is num)
        ? (json['confidence'] as num).toDouble()
        : 0.0;

    final locationLat = (json['location_lat'] is num)
        ? (json['location_lat'] as num).toDouble()
        : null;
    final locationLng = (json['location_lng'] is num)
        ? (json['location_lng'] as num).toDouble()
        : null;

    return ClassificationHistoryItem(
      id: json['id'] ?? 0,
      diseaseName: json['disease_name'] ?? 'Unknown',
      predictedClass: json['predicted_class'] ?? '',
      confidenceValue: confidenceValue,
      confidence: '${(confidenceValue * 100).toStringAsFixed(2)}%',
      imagePath: json['image_path'],
      locationAddress: json['location_address'],
      locationLat: locationLat,
      locationLng: locationLng,
      kabupaten: json['kabupaten'],
      kecamatan: json['kecamatan'],
      kelurahan: json['kelurahan'],
      createdAt: DateTime.parse(
        json['created_at'] ?? DateTime.now().toIso8601String(),
      ),
    );
  }
}
