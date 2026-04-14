import 'package:flutter/material.dart';
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

  void _register() {
    // TODO: Tambahkan logika pendaftaran ke database di sini
    
    // Contoh jika register berhasil, munculkan pesan dan kembali ke Login
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Registrasi Berhasil! Silakan Login.'),
        backgroundColor: Color(0xFF0F703A), // Hijau sesuai tema
      ),
    );
    Navigator.pop(context); // Kembali ke halaman Login
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
                  obscureText: true, // Teks disembunyikan
                ),
                const SizedBox(height: 20),

                // Input Konfirmasi Password
                _buildTextField(
                  hintText: 'Konfirmasi Password',
                  controller: _confirmPasswordController,
                  obscureText: true, // Teks disembunyikan
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
        ),
      ),
    );
  }
}