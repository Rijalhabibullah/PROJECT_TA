import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:geolocator/geolocator.dart';
import 'package:geocoding/geocoding.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  String _userName = "";
  String _userEmail = "";
  String _userPhone = "";
  String _userLocation = "";

  @override
  void initState() {
    super.initState();
    _loadProfile();
    _tryAutoFillLocation();
  }

  Future<void> _loadProfile() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _userName = prefs.getString('user_name') ?? '';
      _userEmail = prefs.getString('user_email') ?? '';
      _userPhone = prefs.getString('user_phone') ?? '';
      _userLocation = prefs.getString('user_location') ?? '';
    });
  }

  Future<void> _tryAutoFillLocation() async {
    if (_userLocation.isNotEmpty) {
      return;
    }

    final address = await _fetchCurrentAddress();
    if (address == null) {
      return;
    }

    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('user_location', address);
    if (mounted) {
      setState(() {
        _userLocation = address;
      });
    }
  }

  Future<String?> _fetchCurrentAddress() async {
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

    if (placemarks.isEmpty) {
      return null;
    }

    final place = placemarks.first;
    final parts = [
      place.street,
      place.subLocality,
      place.locality,
      place.administrativeArea,
      place.country,
    ].where((part) => part != null && part!.trim().isNotEmpty)
        .map((part) => part!.trim())
        .toList();

    if (parts.isEmpty) {
      return null;
    }

    return parts.join(', ');
  }

  void _openEditProfile() {
    final phoneController = TextEditingController(text: _userPhone);
    final locationController = TextEditingController(text: _userLocation);

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) {
        return Padding(
          padding: EdgeInsets.only(
            left: 20,
            right: 20,
            top: 20,
            bottom: MediaQuery.of(context).viewInsets.bottom + 20,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Edit Profil',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 16),
              TextField(
                controller: phoneController,
                keyboardType: TextInputType.phone,
                decoration: const InputDecoration(
                  labelText: 'No. Telepon',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: locationController,
                decoration: const InputDecoration(
                  labelText: 'Lokasi',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 10),
              TextButton.icon(
                onPressed: () async {
                  final address = await _fetchCurrentAddress();
                  if (address == null) {
                    if (!mounted) return;
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Lokasi tidak terdeteksi'),
                        backgroundColor: Colors.red,
                      ),
                    );
                    return;
                  }
                  locationController.text = address;
                },
                icon: const Icon(Icons.my_location),
                label: const Text('Gunakan lokasi saat ini'),
              ),
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(
                    child: ElevatedButton(
                      onPressed: () async {
                        final prefs = await SharedPreferences.getInstance();
                        await prefs.setString(
                          'user_phone',
                          phoneController.text.trim(),
                        );
                        await prefs.setString(
                          'user_location',
                          locationController.text.trim(),
                        );

                        if (mounted) {
                          setState(() {
                            _userPhone = phoneController.text.trim();
                            _userLocation = locationController.text.trim();
                          });
                        }

                        if (!mounted) return;
                        Navigator.pop(context);
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF0F703A),
                      ),
                      child: const Text(
                        'Simpan',
                        style: TextStyle(color: Colors.white),
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        );
      },
    );
  }

  Future<void> _logout() async {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text("Konfirmasi Logout"),
        content: const Text("Apakah Anda yakin ingin keluar?"),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text("Batal", style: TextStyle(color: Color(0xFF0F703A))),
          ),
          ElevatedButton(
            onPressed: () async {
              SharedPreferences prefs = await SharedPreferences.getInstance();
              await prefs.setBool('isLoggedIn', false);
              await prefs.remove('user_id');
              await prefs.remove('user_name');
              await prefs.remove('user_email');
              await prefs.remove('user_phone');
              await prefs.remove('user_location');
              if (mounted) {
                Navigator.pushNamedAndRemoveUntil(context, '/', (route) => false);
              }
            },
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
            child: const Text("Logout", style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[100],
      appBar: AppBar(
        title: const Text(
          'Profil Saya',
          style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white),
        ),
        backgroundColor: const Color(0xFF0F703A),
        centerTitle: true,
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            // Profil Card
            Container(
              padding: const EdgeInsets.all(25),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(20),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.1),
                    blurRadius: 10,
                    offset: const Offset(0, 5),
                  ),
                ],
              ),
              child: Column(
                children: [
                  // Avatar
                  Center(
                    child: Container(
                      width: 200,
                      height: 200,
                      decoration: BoxDecoration(
                        color: const Color(0xFF0F703A),
                        borderRadius: BorderRadius.circular(100),
                      ),
                      child: const Center(
                        child: Icon(Icons.person, size: 100, color: Colors.white),
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),
                  // Nama User
                  Text(
                    _userName.isNotEmpty ? _userName : 'Pengguna',
                    style: const TextStyle(
                      fontSize: 22,
                      fontWeight: FontWeight.bold,
                      color: Colors.black,
                    ),
                  ),
                  const SizedBox(height: 5),
                  Text(
                    _userEmail.isNotEmpty ? _userEmail : 'Akun pengguna',
                    style: TextStyle(fontSize: 14, color: Colors.grey[600]),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 25),

            // Data Profil
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(15),
                border: Border.all(color: Colors.green.shade300, width: 1),
              ),
              child: Column(
                children: [
                  _buildProfileField(
                    icon: Icons.email,
                    label: "Email",
                    value: _userEmail,
                    placeholder: 'Tambahkan email',
                  ),
                  const Divider(height: 20),
                  _buildProfileField(
                    icon: Icons.phone,
                    label: "No. Telepon",
                    value: _userPhone,
                    placeholder: 'Tambahkan no telepon',
                  ),
                  const Divider(height: 20),
                  _buildProfileField(
                    icon: Icons.location_on,
                    label: "Lokasi",
                    value: _userLocation,
                    placeholder: 'Tambahkan lokasi',
                    maxLines: 3,
                  ),
                ],
              ),
            ),
            const SizedBox(height: 25),

            // Edit & Logout Buttons
            Row(
              children: [
                Expanded(
                  child: ElevatedButton.icon(
                    icon: const Icon(Icons.edit),
                    label: const Text('Edit Profil'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.blue,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    onPressed: () {
                      _openEditProfile();
                    },
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton.icon(
                    icon: const Icon(Icons.logout),
                    label: const Text('Logout'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.red,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    onPressed: _logout,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 25),

            // Info Card
            Container(
              padding: const EdgeInsets.all(15),
              decoration: BoxDecoration(
                color: Colors.green.shade50,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: Colors.green.shade300,
                  width: 2,
                ),
              ),
              child: Row(
                children: [
                  Icon(Icons.info, color: Colors.green.shade700, size: 24),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      "AgriPadi v1.0 - Solusi Deteksi Penyakit Padi",
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.green.shade700,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildProfileField({
    required IconData icon,
    required String label,
    required String value,
    required String placeholder,
    int? maxLines,
  }) {
    final displayValue = value.isNotEmpty ? value : placeholder;
    final isPlaceholder = value.isEmpty;

    return Row(
      children: [
        Icon(icon, color: const Color(0xFF0F703A), size: 24),
        const SizedBox(width: 15),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: TextStyle(fontSize: 12, color: Colors.grey[600]),
              ),
              const SizedBox(height: 4),
              Text(
                displayValue,
                maxLines: maxLines,
                overflow: maxLines != null ? TextOverflow.ellipsis : null,
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: isPlaceholder ? Colors.grey[500] : Colors.black,
                  fontStyle: isPlaceholder ? FontStyle.italic : FontStyle.normal,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
