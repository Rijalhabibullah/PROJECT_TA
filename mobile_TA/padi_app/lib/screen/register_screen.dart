import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
// import 'login_screen.dart'; // Nanti ini dipakai untuk kembali ke halaman login

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _usernameController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  bool _isPasswordVisible = false;
  bool _isConfirmPasswordVisible = false;

  void _register() async {
    final username = _usernameController.text.trim();
    final password = _passwordController.text;
    final confirmPassword = _confirmPasswordController.text;

    if (username.isEmpty || password.isEmpty || confirmPassword.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Semua field wajib diisi'),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }

    if (password != confirmPassword) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Konfirmasi password tidak cocok'),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }

    try {
      final response = await http
          .post(
            Uri.parse('http://192.168.18.23:8000/api/mobile/register'),
            headers: {
              'Accept': 'application/json',
              'Content-Type': 'application/json',
            },
            body: jsonEncode({
              'username': username,
              'password': password,
              'password_confirmation': confirmPassword,
            }),
          )
          .timeout(const Duration(seconds: 15));

      if (response.statusCode != 201) {
        final jsonResponse = jsonDecode(response.body);
        throw Exception(jsonResponse['message'] ?? 'Registrasi gagal');
      }

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Registrasi berhasil! Silakan login.'),
          backgroundColor: Color(0xFF0F703A),
        ),
      );
      Navigator.pop(context);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Registrasi gagal: ${e.toString().replaceAll('Exception: ', '')}'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      // Menggunakan SafeArea dan SingleChildScrollView agar saat keyboard 
      // muncul, layarnya bisa di-scroll dan tidak error "overflow"
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 36.0, vertical: 24.0),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                // Judul Register
                const Text(
                  'Register',
                  style: TextStyle(
                    color: Color(0xFF0F703A),
                    fontSize: 32, // Sedikit diperbesar agar proporsional
                    fontFamily: 'Poppins',
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 50),

                // Input Username
                _buildTextField(
                  hintText: 'Username',
                  controller: _usernameController,
                  obscureText: false,
                ),
                const SizedBox(height: 20),

                // Input Password
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
                const SizedBox(height: 20),

                // Input Konfirmasi Password
                _buildTextField(
                  hintText: 'Konfirmasi Password',
                  controller: _confirmPasswordController,
                  obscureText: !_isConfirmPasswordVisible,
                  showToggle: true,
                  onToggle: () {
                    setState(() {
                      _isConfirmPasswordVisible = !_isConfirmPasswordVisible;
                    });
                  },
                ),
                const SizedBox(height: 50),

                // Tombol Konfirmasi
                SizedBox(
                  width: double.infinity, // Memenuhi lebar layar
                  height: 53,
                  child: ElevatedButton(
                    onPressed: _register,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF0F703A),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(25), // Sesuai desain Figma
                      ),
                      elevation: 4,
                    ),
                    child: const Text(
                      'Konfirmasi',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 16,
                        fontFamily: 'Poppins',
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 30),

                // Teks "Sudah Mempunyai Akun?" bisa diklik
                GestureDetector(
                  onTap: () {
                    // Kembali ke halaman Login
                    Navigator.pop(context);
                  },
                  child: const Text(
                    'Sudah Mempunyai Akun?',
                    style: TextStyle(
                      color: Colors.black87,
                      fontSize: 15,
                      fontFamily: 'Poppins',
                      fontWeight: FontWeight.w500,
                      decoration: TextDecoration.underline, // Tambahan garis bawah agar jelas bisa diklik
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  // Fungsi bantuan untuk membuat TextField yang bentuknya sama (Shadow & Rounded)
  Widget _buildTextField({
    required String hintText,
    required TextEditingController controller,
    required bool obscureText,
    bool showToggle = false,
    VoidCallback? onToggle,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(25),
        boxShadow: const [
          BoxShadow(
            color: Color(0x3F000000), // Shadow sesuai Figma
            blurRadius: 4,
            offset: Offset(0, 4),
          ),
        ],
      ),
      child: TextField(
        controller: controller,
        obscureText: obscureText,
        textAlign: TextAlign.center, // Teks di tengah seperti Figma
        decoration: InputDecoration(
          hintText: hintText,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(25),
            borderSide: BorderSide.none, // Menghilangkan garis pinggir bawaan
          ),
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