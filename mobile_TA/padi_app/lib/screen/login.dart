import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import '../main.dart'; // Mengarah ke MainNavigation di main.dart
import 'register_screen.dart'; 
import '../utils/api_config.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final TextEditingController _usernameController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  bool _isPasswordVisible = false;

  // FUNGSI LOGIN TEGAS: Simpan status & Pindah ke Navigasi Utama
  void _login() async {
    final username = _usernameController.text.trim();
    final password = _passwordController.text;

    if (username.isEmpty || password.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Username dan password wajib diisi'),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }

    try {
      final response = await http
          .post(
            Uri.parse('${ApiConfig.apiRoot}/mobile/login'),
            headers: {
              'Accept': 'application/json',
              'Content-Type': 'application/json',
            },
            body: jsonEncode({
              'username': username,
              'password': password,
            }),
          )
          .timeout(const Duration(seconds: 30));

      if (response.statusCode != 200) {
        throw Exception('Login gagal');
      }

      final jsonResponse = jsonDecode(response.body);
      if (jsonResponse['success'] != true) {
        throw Exception(jsonResponse['message'] ?? 'Login gagal');
      }

      final data = jsonResponse['data'] ?? {};
      SharedPreferences prefs = await SharedPreferences.getInstance();
      await prefs.setBool('isLoggedIn', true);
      await prefs.setInt('user_id', data['user_id'] ?? 0);
      await prefs.setString('user_name', data['name'] ?? username);
      if (data['email'] != null) {
        await prefs.setString('user_email', data['email'] ?? '');
      }

      if (!mounted) return;
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (context) => const MainNavigation()), 
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Login gagal: ${e.toString().replaceAll('Exception: ', '')}'),
          backgroundColor: Colors.red,
        ),
      );
    }
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
            _buildTextField(
              hintText: 'Password',
              controller: _passwordController,
              obscureText: !_isPasswordVisible,
              showToggle: true,
              onToggle: () {
                setState(() {
                  _isPasswordVisible = !_isPasswordVisible;
                });
              },
            ),
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

  Widget _buildTextField({
    required String hintText,
    required TextEditingController controller,
    required bool obscureText,
    bool showToggle = false,
    VoidCallback? onToggle,
  }) {
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
          suffixIcon: showToggle
              ? IconButton(
                  onPressed: onToggle,
                  icon: Icon(
                    obscureText ? Icons.visibility_off : Icons.visibility,
                    color: Colors.grey,
                  ),
                )
              : null,
        ),
      ),
    );
  }
}