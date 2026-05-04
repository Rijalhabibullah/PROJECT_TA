import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:geolocator/geolocator.dart';
import 'dashboard_klasifikasi.dart'; 
import 'screen/history_screen.dart';
import 'screen/ecommerce_screen.dart';
import 'screen/profile_screen.dart';
import 'package:padi_app/screen/login.dart'; 

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      theme: ThemeData(useMaterial3: true),
      home: const SplashScreen(),
    );
  }
}

// --- 1. SPLASH SCREEN DENGAN TAGLINE ---
class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  @override
  void initState() {
    super.initState();
    _pindahHalaman();
  }

  _pindahHalaman() async {
    // Tunggu 3 detik untuk menampilkan logo dan tagline
    await Future.delayed(const Duration(seconds: 3));
    
    // Cek status login di memori lokal HP
    SharedPreferences prefs = await SharedPreferences.getInstance();
    bool statusLogin = prefs.getBool('isLoggedIn') ?? false;

    if (!mounted) return;

    if (statusLogin) {
      // Jika sudah pernah login, langsung masuk ke Navigasi Utama (Dashboard)
      Navigator.pushReplacement(
        context, 
        MaterialPageRoute(builder: (context) => const MainNavigation())
      );
    } else {
      // Jika belum login, arahkan ke halaman Login
      Navigator.pushReplacement(
        context, 
        MaterialPageRoute(builder: (context) => const LoginScreen())
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      backgroundColor: Color(0xFF0F703A), // Warna hijau AgriPadi
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.eco, size: 100, color: Colors.white),
            SizedBox(height: 10),
            Text(
              "AgriPadi",
              style: TextStyle(
                color: Colors.white,
                fontSize: 28,
                fontWeight: FontWeight.bold,
              ),
            ),
            Text(
              "Solusi Deteksi Penyakit Padi Akurat", // Tagline yang kamu minta
              style: TextStyle(
                color: Colors.white70,
                fontSize: 14,
                fontStyle: FontStyle.italic,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// --- 2. NAVIGASI UTAMA (SWIPE ANTARA HISTORY, DASHBOARD, & ECOMMERCE) ---
class MainNavigation extends StatefulWidget {
  const MainNavigation({super.key});

  @override
  State<MainNavigation> createState() => _MainNavigationState();
}

class _MainNavigationState extends State<MainNavigation> {
  // Controller untuk mengatur halaman awal di Dashboard (Index 1)
  final PageController _controller = PageController(initialPage: 1); 
  int _currentIndex = 1; // Track halaman yang sedang aktif

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _promptLocationPermission();
    });
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _onPageChanged(int index) {
    setState(() {
      _currentIndex = index;
    });
  }

  void _onNavBarTapped(int index) {
    _controller.animateToPage(
      index,
      duration: const Duration(milliseconds: 400),
      curve: Curves.easeInOut,
    );
  }

  Future<void> _promptLocationPermission() async {
    if (kIsWeb) {
      return;
    }

    var permission = await Geolocator.checkPermission();
    if (permission != LocationPermission.always &&
        permission != LocationPermission.whileInUse) {
      permission = await Geolocator.requestPermission();
    }
    if (!mounted) return;

    if (permission == LocationPermission.deniedForever) {
      await showDialog<void>(
        context: context,
        builder: (context) {
          return AlertDialog(
            title: const Text('Izin Lokasi Ditolak'),
            content: const Text(
              'Silakan aktifkan izin lokasi di Pengaturan jika ingin '
              'menyimpan lokasi secara otomatis.',
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context),
                child: const Text('Tutup'),
              ),
              ElevatedButton(
                onPressed: () async {
                  await Geolocator.openAppSettings();
                  if (!context.mounted) return;
                  Navigator.pop(context);
                },
                child: const Text('Buka Pengaturan'),
              ),
            ],
          );
        },
      );
      return;
    }

    final serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      if (!mounted) return;
      final openSettings = await showDialog<bool>(
        context: context,
        builder: (context) {
          return AlertDialog(
            title: const Text('Aktifkan Lokasi'),
            content: const Text(
              'Lokasi perangkat sedang mati. Aktifkan agar lokasi tersimpan '
              'real time. Kamu tetap bisa klasifikasi tanpa lokasi.',
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context, false),
                child: const Text('Nanti'),
              ),
              ElevatedButton(
                onPressed: () => Navigator.pop(context, true),
                child: const Text('Buka Pengaturan'),
              ),
            ],
          );
        },
      );

      if (openSettings == true) {
        await Geolocator.openLocationSettings();
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: PageView(
        controller: _controller,
        onPageChanged: _onPageChanged,
        children: const [
          HistoryScreen(),    // Index 0
          HomeScreen(),       // Index 1 (Dashboard Utama)
          EcommerceScreen(),  // Index 2
          ProfileScreen(),    // Index 3
        ],
      ),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        onTap: _onNavBarTapped,
        type: BottomNavigationBarType.fixed,
        backgroundColor: Colors.white,
        selectedItemColor: const Color(0xFF0F703A),
        unselectedItemColor: Colors.grey,
        items: const [
          BottomNavigationBarItem(
            icon: Icon(Icons.history),
            label: 'Riwayat',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.eco),
            label: 'Dashboard',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.shopping_cart),
            label: 'Toko',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.person),
            label: 'Profil',
          ),
        ],
      ),
    );
  }
}