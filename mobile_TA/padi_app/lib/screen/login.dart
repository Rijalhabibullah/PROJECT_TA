import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../main.dart'; // Mengarah ke MainNavigation di main.dart
import 'register_screen.dart'; 

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final TextEditingController _usernameController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();

  // FUNGSI LOGIN TEGAS: Simpan status & Pindah ke Navigasi Utama
  void _login() async {
    SharedPreferences prefs = await SharedPreferences.getInstance();
    await prefs.setBool('isLoggedIn', true); // Simpan status login permanen

    if (!mounted) return;
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(builder: (context) => const MainNavigation()), 
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: SingleChildScrollView(
        child: Column(
          children: [
            _buildHeader(),
            const SizedBox(height: 30),
            _buildWelcomeText(),
            const SizedBox(height: 40),
            _buildTextField(hintText: 'Username', controller: _usernameController, obscureText: false),
            const SizedBox(height: 20),
            _buildTextField(hintText: 'Password', controller: _passwordController, obscureText: true),
            const SizedBox(height: 40),
            _buildLoginButton(),
            const SizedBox(height: 25),
            _buildRegisterLink(),
          ],
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return SizedBox(
      height: 380,
      child: Stack(
        alignment: Alignment.topCenter,
        children: [
          Container(
            width: double.infinity, height: 340,
            decoration: const BoxDecoration(
              color: Color(0xFF0F703A),
              borderRadius: BorderRadius.vertical(bottom: Radius.circular(100)),
            ),
          ),
          Positioned(
            bottom: 0,
            child: Container(
              width: 200, height: 140,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(20),
                boxShadow: const [BoxShadow(color: Colors.black26, blurRadius: 8)],
              ),
              child: const Icon(Icons.eco, size: 80, color: Color(0xFF0F703A)),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildWelcomeText() {
    return const Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Text('Welcome To ', style: TextStyle(color: Color(0xFF0F703A), fontSize: 24, fontWeight: FontWeight.bold)),
        Text('AgriPadi', style: TextStyle(color: Color(0xFF0F703A), fontSize: 28, fontWeight: FontWeight.bold)),
      ],
    );
  }

  Widget _buildLoginButton() {
    return SizedBox(
      width: 250, height: 53,
      child: ElevatedButton(
        onPressed: _login,
        style: ElevatedButton.styleFrom(
          backgroundColor: const Color(0xFF0F703A),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(25)),
        ),
        child: const Text('Login', style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
      ),
    );
  }

  Widget _buildRegisterLink() {
    return GestureDetector(
      onTap: () {
        Navigator.push(context, MaterialPageRoute(builder: (context) => const RegisterScreen()));
      },
      child: const Text('Belum punya akun? Register', style: TextStyle(decoration: TextDecoration.underline)),
    );
  }

  Widget _buildTextField({required String hintText, required TextEditingController controller, required bool obscureText}) {
    return Container(
      width: 250,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(25),
        boxShadow: const [BoxShadow(color: Color(0x3F000000), blurRadius: 4, offset: Offset(0, 4))],
      ),
      child: TextField(
        controller: controller,
        obscureText: obscureText,
        textAlign: TextAlign.center,
        decoration: InputDecoration(
          hintText: hintText,
          border: OutlineInputBorder(borderRadius: BorderRadius.circular(25), borderSide: BorderSide.none),
          contentPadding: const EdgeInsets.symmetric(vertical: 16),
        ),
      ),
    );
  }
}