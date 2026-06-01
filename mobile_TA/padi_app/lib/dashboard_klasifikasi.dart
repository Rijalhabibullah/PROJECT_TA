import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:flutter/foundation.dart'; // Penting untuk kIsWeb
import 'package:geolocator/geolocator.dart';
import 'package:geocoding/geocoding.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'utils/image_compress.dart';

// Pastikan file result_screen.dart sudah kamu buat di folder lib/screen/
import 'screen/result_screen.dart';
import 'services/classification_service.dart'; 

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  File? _image;
  XFile? _webImage; // Tambahan untuk handling gambar di Web
  final ImagePicker _picker = ImagePicker();
  final ClassificationService _classificationService = ClassificationService();
  bool _isLoading = false;
  String? _errorMessage;

  // Daftar Kabupaten/Kota di Indonesia (mapping Kecamatan -> Kabupaten)
  final Map<String, String> _districtToRegencyMap = {
    // Jawa Timur - Jember
    'Sumbersari': 'Kabupaten Jember',
    'Ambulu': 'Kabupaten Jember',
    'Ajung': 'Kabupaten Jember',
    'Mayang': 'Kabupaten Jember',
    'Mumbulsari': 'Kabupaten Jember',
    'Kalisat': 'Kabupaten Jember',
    'Jenggawah': 'Kabupaten Jember',
    'Sukorambi': 'Kabupaten Jember',
    'Rambipuji': 'Kabupaten Jember',
    'Silo': 'Kabupaten Jember',
    'Kencong': 'Kabupaten Jember',
    'Puger': 'Kabupaten Jember',
    'Wuluhan': 'Kabupaten Jember',
    'Tempeh': 'Kabupaten Jember',
    'Ledokombo': 'Kabupaten Jember',
    'Panti': 'Kabupaten Jember',
    'Pakusari': 'Kabupaten Jember',
    'Tanggul': 'Kabupaten Jember',

    // Jawa Timur - Surabaya
    'Surabaya': 'Kota Surabaya',
    'Gubeng': 'Kota Surabaya',
    'Rungkut': 'Kota Surabaya',

    // Jawa Timur - Malang
    'Malang': 'Kota Malang',

    // Jawa Timur - Batu
    'Batu': 'Kota Batu',

    // Jawa Tengah - Semarang
    'Semarang': 'Kota Semarang',

    // Jawa Barat - Bandung
    'Bandung': 'Kota Bandung',
  };

  Future<void> _getImage(ImageSource source) async {
    final pickedFile = await _picker.pickImage(
      source: source,
    );
    if (pickedFile != null) {
      if (kIsWeb) {
        setState(() {
          _webImage = pickedFile;
        });
      } else {
        final compressed = await compressImageFile(File(pickedFile.path));
        setState(() {
          _image = compressed;
        });
      }
    }
  }

  void _showPicker(BuildContext context) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (BuildContext bc) {
        return SafeArea(
          child: Wrap(
            children: <Widget>[
              const Padding(
                padding: EdgeInsets.all(16.0),
                child: Text(
                  'Pilih Sumber Foto',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                ),
              ),
              ListTile(
                leading: const Icon(Icons.photo_library, color: Colors.green),
                title: const Text('Ambil dari Galeri'),
                onTap: () {
                  _getImage(ImageSource.gallery);
                  Navigator.of(context).pop();
                },
              ),
              ListTile(
                leading: const Icon(Icons.photo_camera, color: Colors.green),
                title: const Text('Buka Kamera'),
                onTap: () {
                  _getImage(ImageSource.camera);
                  Navigator.of(context).pop();
                },
              ),
            ],
          ),
        );
      },
    );
  }

  Future<void> _handleClassification() async {
    if (_image == null && _webImage == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Silakan pilih foto daun padi terlebih dahulu!'),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final location = await _resolveLocation();
      final imageFile = _image ?? File(_webImage!.path);
      final result = await _classificationService.classifyAndSave(
        imageFile,
        locationAddress: location?.address,
        locationLat: location?.lat,
        locationLng: location?.lng,
        kabupaten: location?.kabupaten,
        kecamatan: location?.kecamatan,
        kelurahan: location?.kelurahan,
      );

      // Validasi: cek apakah confidence cukup tinggi (artinya itu daun padi)
      const double confidenceThreshold = 0.60; // 60% minimum - sesuai dengan backend
      if (result.confidenceValue < confidenceThreshold) {
        if (mounted) {
          _showNotRiceLeafWarning(context);
        }
        return;
      }

      if (mounted) {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => ResultScreen(result: result),
          ),
        );
      }
    } catch (e) {
      final errorMsg = e.toString().replaceAll('Exception: ', '');
      
      // Cek apakah error dari backend tentang bukan daun padi
      if (errorMsg.contains('Bukan daun padi')) {
        if (mounted) {
          _showNotRiceLeafWarning(context);
        }
      } else {
        setState(() {
          _errorMessage = errorMsg;
        });
      }
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  Future<_LocationPayload?> _resolveLocation() async {
    if (kIsWeb) {
      return null;
    }

    final serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      return null;
    }

    var permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
    }

    if (permission == LocationPermission.denied ||
        permission == LocationPermission.deniedForever) {
      return null;
    }

    final position = await Geolocator.getCurrentPosition(
      desiredAccuracy: LocationAccuracy.high,
    );

    final placemarks = await placemarkFromCoordinates(
      position.latitude,
      position.longitude,
    );

    String? address;
    String? kabupaten;
    String? kecamatan;
    String? kelurahan;
    
    if (placemarks.isNotEmpty) {
      final place = placemarks.first;
      
      // Extract components dengan urutan yang sesuai untuk Indonesia
      kelurahan = place.subLocality?.trim(); // Kelurahan/Desa
      kecamatan = place.locality?.trim(); // Kecamatan
      
      final provinsi = place.administrativeArea?.trim(); // Provinsi
      final negara = place.country?.trim(); // Negara

      // Extract kabupaten - try multiple sources
      if (place.subAdministrativeArea != null &&
          place.subAdministrativeArea!.isNotEmpty) {
        kabupaten = place.subAdministrativeArea!.trim();
      }

      if (kabupaten == null &&
          place.thoroughfare != null &&
          place.thoroughfare!.isNotEmpty) {
        final match =
            RegExp(r'(?:Kabupaten|Kota)\s+([^,]+)').firstMatch(place.thoroughfare!);
        if (match != null) {
          kabupaten = match.group(1)?.trim();
        }
      }

      if (kabupaten == null && kecamatan != null) {
        kabupaten = _districtToRegencyMap[kecamatan];
      }

      if (kabupaten != null &&
          !kabupaten.contains('Kabupaten') &&
          !kabupaten.contains('Kota')) {
        kabupaten = 'Kabupaten $kabupaten';
      }

      // Build full address: kelurahan, kecamatan, kabupaten, provinsi, negara
      final parts = [
        kelurahan,
        kecamatan,
        kabupaten,
        provinsi,
        negara,
      ].where((part) => part != null && part!.isNotEmpty).toList();

      if (parts.isNotEmpty) {
        address = parts.join(', ');
      }
    }

    if (address != null) {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('user_location', address);
      if (kabupaten != null) {
        await prefs.setString('user_kabupaten', kabupaten);
      }
      if (kecamatan != null) {
        await prefs.setString('user_kecamatan', kecamatan);
      }
      if (kelurahan != null) {
        await prefs.setString('user_kelurahan', kelurahan);
      }
      await prefs.setDouble('user_location_lat', position.latitude);
      await prefs.setDouble('user_location_lng', position.longitude);
    }

    return _LocationPayload(
      address: address,
      lat: position.latitude,
      lng: position.longitude,
      kabupaten: kabupaten,
      kecamatan: kecamatan,
      kelurahan: kelurahan,
    );
  }

  void _showNotRiceLeafWarning(BuildContext context) {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          title: const Row(
            children: [
              Icon(Icons.warning_amber_rounded, color: Colors.orange, size: 28),
              SizedBox(width: 10),
              Text('Bukan Daun Padi'),
            ],
          ),
          content: const Text(
            'Foto yang Anda upload terdeteksi bukan daun padi. '
            'Silahkan upload foto daun padi yang jelas dan bagus untuk hasil deteksi penyakit yang akurat.',
            style: TextStyle(fontSize: 15),
          ),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.of(context).pop();
              },
              child: const Text('Kembali ke Upload'),
            ),
          ],
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[100],
      appBar: AppBar(
        title: const Text(
          'AgriPadi - Deteksi',
          style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white),
        ),
        backgroundColor: const Color(0xFF0F703A),
        centerTitle: true,
        elevation: 0,
      ),
      body: Padding(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Text(
              'Unggah foto daun padi untuk mendeteksi penyakit secara akurat.',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 16, color: Colors.black54),
            ),
            const SizedBox(height: 30),
            
            // Area Upload Preview
            GestureDetector(
              onTap: () => _showPicker(context),
              child: Container(
                height: 300,
                width: double.infinity,
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: Colors.green.shade300, width: 2),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.1),
                      blurRadius: 10,
                      offset: const Offset(0, 5),
                    ),
                  ],
                ),
                child: (_image != null || _webImage != null)
                    ? ClipRRect(
                        borderRadius: BorderRadius.circular(18),
                        child: kIsWeb 
                          ? Image.network(_webImage!.path, width: double.infinity, height: 300, fit: BoxFit.cover)
                          : Image.file(_image!, width: double.infinity, height: 300, fit: BoxFit.cover),
                      )
                    : Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.add_a_photo_outlined, size: 60, color: Colors.green[400]),
                          const SizedBox(height: 15),
                          Text(
                            'Tap untuk Upload Foto',
                            style: TextStyle(color: Colors.green[700], fontWeight: FontWeight.bold),
                          ),
                        ],
                      ),
              ),
            ),
            
            // Tombol Aksi Foto (hanya muncul jika foto sudah dipilih)
            if (_image != null || _webImage != null)
              Padding(
                padding: const EdgeInsets.only(top: 20.0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                  children: [
                    // Tombol Ubah Foto
                    Expanded(
                      child: ElevatedButton.icon(
                        icon: const Icon(Icons.refresh, color: Colors.white),
                        label: const Text('Ubah Foto'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.orange,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          padding: const EdgeInsets.symmetric(vertical: 12),
                        ),
                        onPressed: () => _showPicker(context),
                      ),
                    ),
                    const SizedBox(width: 12),
                    // Tombol Hapus Foto
                    Expanded(
                      child: ElevatedButton.icon(
                        icon: const Icon(Icons.delete, color: Colors.white),
                        label: const Text('Hapus'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.red.shade400,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          padding: const EdgeInsets.symmetric(vertical: 12),
                        ),
                        onPressed: () {
                          setState(() {
                            _image = null;
                            _webImage = null;
                          });
                        },
                      ),
                    ),
                  ],
                ),
              ),
            
            const SizedBox(height: 40),
            
            // Error Message
            if (_errorMessage != null)
              Container(
                padding: const EdgeInsets.all(12),
                margin: const EdgeInsets.only(bottom: 16),
                decoration: BoxDecoration(
                  color: Colors.red[100],
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  children: [
                    Icon(Icons.error, color: Colors.red[700]),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        _errorMessage!,
                        style: TextStyle(color: Colors.red[700], fontSize: 14),
                      ),
                    ),
                  ],
                ),
              ),
            
            // Tombol Klasifikasi
            SizedBox(
              width: double.infinity,
              height: 55,
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF0F703A),
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
                  disabledBackgroundColor: Colors.grey,
                ),
                onPressed: _isLoading ? null : () => _handleClassification(),
                child: _isLoading
                    ? const SizedBox(
                        height: 24,
                        width: 24,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                        ),
                      )
                    : const Text(
                        'Klasifikasi Sekarang',
                        style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                      ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _LocationPayload {
  final String? address;
  final double lat;
  final double lng;
  final String? kabupaten;
  final String? kecamatan;
  final String? kelurahan;

  const _LocationPayload({
    required this.address,
    required this.lat,
    required this.lng,
    this.kabupaten,
    this.kecamatan,
    this.kelurahan,
  });
}