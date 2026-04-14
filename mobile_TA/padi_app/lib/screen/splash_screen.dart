import 'dart:async';
import 'package:flutter/material.dart';
// Import file sesuai lokasi folder kamu
import 'login.dart'; 

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  @override
  void initState() {
    super.initState();
    // Memulai timer 3 detik saat aplikasi dibuka
    Timer(const Duration(seconds: 3), () {
      navigateToNextPage();
    });
  }

  void navigateToNextPage() {
    // Sesuai alur proposal: Splash -> Login
    if (mounted) {
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (context) => const LoginScreen()),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      // Menggunakan warna hijau tema AgriPadi dari proposal TA kamu
      backgroundColor: const Color(0xFF0F703A), 
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            // Logo atau ilustrasi padi
            const Icon(Icons.eco, size: 100, color: Colors.white),
            const SizedBox(height: 24),
            const Text(
              'AgriPadi',
              style: TextStyle(
                fontSize: 32,
                fontWeight: FontWeight.bold,
                color: Colors.white,
                fontFamily: 'Atma',
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Sahabat Cerdas Petani Padi',
              style: TextStyle(
                fontSize: 16,
                color: Colors.white.withValues(alpha: 0.8),
              ),
            ),
          ],
        ),
      ),
    );
  }
}