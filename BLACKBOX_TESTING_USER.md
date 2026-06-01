# Blackbox Testing - User (Mobile)

Dokumen ini berisi daftar test case lengkap untuk semua fitur user di aplikasi mobile, dari awal hingga selesai.

## Catatan Umum
- Role: User (mobile)
- Fokus: UI/UX, validasi input, dan hasil sesuai yang terlihat pengguna.

## Lingkup Data Uji
- Akun valid: username dan password yang terdaftar.
- Akun tidak valid: kombinasi username/password salah.
- Foto daun padi valid: gambar daun padi yang jelas.
- Foto non-daun padi: gambar objek lain (misal tangan, tanah, daun non-padi).
- Jaringan: normal, putus, dan lambat/timeout.

## Splash & Navigasi Utama

| ID | Fitur/Menu | Skenario | Precondition | Langkah Singkat | Data Uji | Expected Result |
|---|---|---|---|---|---|---|
| U-SPLASH-01 | Splash | Arahkan ke login | Status login belum tersimpan | Buka aplikasi | - | Setelah splash, masuk ke Login |
| U-SPLASH-02 | Splash | Arahkan ke dashboard | Status login tersimpan | Buka aplikasi | - | Setelah splash, masuk ke Main Navigation |
| U-NAV-01 | Bottom Nav | Pindah tab | Sudah login | Tap tab Riwayat/Dashboard/Toko/Profil | - | Halaman berpindah sesuai tab |
| U-NAV-02 | Swipe | Pindah halaman via swipe | Sudah login | Swipe kiri/kanan di Main Navigation | - | Halaman berpindah sesuai swipe |

## Login

| ID | Fitur/Menu | Skenario | Precondition | Langkah Singkat | Data Uji | Expected Result |
|---|---|---|---|---|---|---|
| U-LOGIN-01 | Login | Login berhasil | Akun valid tersedia | Isi username + password valid, tap Login | Akun valid | Masuk ke Main Navigation, status login tersimpan |
| U-LOGIN-02 | Login | Username kosong | - | Kosongkan username, isi password, tap Login | - | SnackBar error "wajib diisi" |
| U-LOGIN-03 | Login | Password kosong | - | Isi username, kosongkan password, tap Login | - | SnackBar error "wajib diisi" |
| U-LOGIN-04 | Login | Credential salah | Akun valid tersedia | Isi username/password salah, tap Login | Akun tidak valid | SnackBar error login gagal |
| U-LOGIN-05 | Login | Gangguan jaringan | Jaringan putus | Tap Login | - | SnackBar error jaringan/timeout |

## Register

| ID | Fitur/Menu | Skenario | Precondition | Langkah Singkat | Data Uji | Expected Result |
|---|---|---|---|---|---|---|
| U-REG-01 | Register | Registrasi berhasil | Username belum terdaftar | Isi username, password, konfirmasi, tap Konfirmasi | Username baru | Muncul pesan sukses, kembali ke login |
| U-REG-02 | Register | Field kosong | - | Kosongkan salah satu field, tap Konfirmasi | - | SnackBar error "wajib diisi" |
| U-REG-03 | Register | Konfirmasi tidak sama | - | Isi password dan konfirmasi berbeda | - | SnackBar error konfirmasi tidak cocok |
| U-REG-04 | Register | Username sudah ada | Username sudah terdaftar | Isi username terdaftar, tap Konfirmasi | Username existing | SnackBar error username sudah digunakan |
| U-REG-05 | Register | Password terlalu pendek | - | Isi password < 4 karakter, tap Konfirmasi | "123" | SnackBar error validasi password |

## Dashboard Klasifikasi (Home)

| ID | Fitur/Menu | Skenario | Precondition | Langkah Singkat | Data Uji | Expected Result |
|---|---|---|---|---|---|---|
| U-CLS-01 | Klasifikasi | Buka picker | Sudah login | Tap pilih foto, pilih Kamera/Galeri | - | Picker terbuka sesuai pilihan |
| U-CLS-02 | Klasifikasi | Tanpa foto | Sudah login | Tap Klasifikasi tanpa pilih foto | - | SnackBar peringatan pilih foto |
| U-CLS-03 | Klasifikasi | Ambil foto kamera | Izin kamera disetujui | Buka Kamera, ambil foto, konfirmasi | Foto valid | Foto tampil di preview |
| U-CLS-04 | Klasifikasi | Ambil foto galeri | Izin galeri disetujui | Buka Galeri, pilih foto | Foto valid | Foto tampil di preview |
| U-CLS-05 | Klasifikasi | Klasifikasi sukses | Server aktif | Pilih foto valid, tap Klasifikasi | Foto daun padi | Loading tampil, pindah ke ResultScreen |
| U-CLS-06 | Klasifikasi | Bukan daun padi | Server aktif | Pilih foto non-daun padi, tap Klasifikasi | Foto non-daun padi | Muncul peringatan "bukan daun padi" |
| U-CLS-07 | Klasifikasi | Timeout server | Server tidak responsif | Tap Klasifikasi | - | Pesan error timeout tampil |
| U-CLS-08 | Klasifikasi | Error jaringan | Jaringan putus | Tap Klasifikasi | - | Pesan error jaringan tampil |
| U-CLS-09 | Klasifikasi | Izin lokasi ditolak | Izin lokasi ditolak | Tap Klasifikasi | - | Klasifikasi tetap berjalan tanpa lokasi |
| U-CLS-10 | Klasifikasi | Simpan lokasi jika diizinkan | Izin lokasi aktif | Tap Klasifikasi | - | Lokasi terkirim dan tersimpan di hasil |

## Result Screen

| ID | Fitur/Menu | Skenario | Precondition | Langkah Singkat | Data Uji | Expected Result |
|---|---|---|---|---|---|---|
| U-RESULT-01 | Hasil | Lihat hasil | Klasifikasi sukses | Buka ResultScreen | Foto daun padi | Nama penyakit, confidence, severity tampil |
| U-RESULT-02 | Hasil | Detail & rekomendasi | Klasifikasi sukses | Tap "Detail Lengkap & Rekomendasi" | - | Popup detail tampil |
| U-RESULT-03 | Hasil | Semua prediksi | Klasifikasi sukses | Scroll ke "Analisis Semua Kelas" | - | Nilai tiap kelas tampil |
| U-RESULT-04 | Hasil | Info lokasi | Klasifikasi dengan lokasi | Lihat card lokasi | - | Informasi lokasi tampil |
| U-RESULT-05 | Hasil | Rekomendasi produk | Produk tersedia | Scroll ke rekomendasi | - | Daftar produk tampil |
| U-RESULT-06 | Hasil | Buka marketplace | Produk punya link | Tap link marketplace | - | Browser terbuka ke link |

## Riwayat Klasifikasi

| ID | Fitur/Menu | Skenario | Precondition | Langkah Singkat | Data Uji | Expected Result |
|---|---|---|---|---|---|---|
| U-HIST-01 | Riwayat | Data ada | Riwayat tersedia | Buka tab Riwayat | - | Grafik pie + list tampil |
| U-HIST-02 | Riwayat | Data kosong | Riwayat kosong | Buka tab Riwayat | - | Pesan "Belum ada riwayat" tampil |
| U-HIST-03 | Riwayat | Gagal memuat | Jaringan putus | Buka tab Riwayat | - | Pesan error tampil |
| U-HIST-04 | Riwayat | Filter bulan berjalan | Data tersedia | Buka tab Riwayat | - | Pie chart sesuai data bulan ini |

## Toko (Ecommerce)

| ID | Fitur/Menu | Skenario | Precondition | Langkah Singkat | Data Uji | Expected Result |
|---|---|---|---|---|---|---|
| U-ECOM-01 | Toko | Load produk | Server aktif | Buka tab Toko | - | Daftar produk tampil |
| U-ECOM-02 | Toko | Pencarian produk | Produk tersedia | Isi kata kunci di search | "urea" | List terfilter sesuai keyword |
| U-ECOM-03 | Toko | Pencarian kosong | Produk tersedia | Hapus kata kunci | - | List kembali ke semua produk |
| U-ECOM-04 | Toko | Detail produk | Produk tersedia | Tap item produk | - | Popup detail tampil |
| U-ECOM-05 | Toko | Buka link marketplace | Produk punya link | Tap "Beli di Marketplace" | - | Browser terbuka ke link |
| U-ECOM-06 | Toko | Gagal memuat | Jaringan putus | Buka tab Toko | - | Pesan error tampil |

## Profil

| ID | Fitur/Menu | Skenario | Precondition | Langkah Singkat | Data Uji | Expected Result |
|---|---|---|---|---|---|---|
| U-PROF-01 | Profil | Lihat profil | Sudah login | Buka tab Profil | - | Nama, email, dan data profil tampil |
| U-PROF-02 | Profil | Edit profil | Sudah login | Tap Edit Profil, isi data, tap Simpan | Data valid | Data tersimpan dan tampil di profil |
| U-PROF-03 | Profil | Simpan tanpa perubahan | Sudah login | Tap Edit Profil, tap Simpan tanpa edit | - | Tidak ada error, data tetap |
| U-PROF-04 | Profil | Gunakan lokasi saat ini | Izin lokasi aktif | Tap "Gunakan lokasi saat ini" | - | Field lokasi terisi otomatis |
| U-PROF-05 | Profil | Lokasi tidak terdeteksi | GPS mati | Tap "Gunakan lokasi saat ini" | - | SnackBar lokasi tidak terdeteksi |
| U-PROF-06 | Profil | Logout | Sudah login | Tap Logout, konfirmasi | - | Status login false, kembali ke login |
