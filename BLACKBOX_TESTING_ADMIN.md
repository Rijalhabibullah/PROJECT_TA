# Blackbox Testing - Admin (Web)

Dokumen ini berisi daftar test case lengkap untuk semua menu admin di aplikasi web, dari awal hingga selesai.

## Catatan Umum
- Role: Admin (web)
- Fokus: UI/UX, validasi input, dan hasil sesuai yang terlihat pengguna.

## Lingkup Data Uji
- Akun admin valid: email dan password terdaftar.
- Akun admin tidak valid: kombinasi email/password salah.
- Gambar valid: JPG/PNG di bawah 2MB.
- Gambar tidak valid: file non-gambar atau ukuran > 2MB.

## Login & Logout

| ID | Fitur/Menu | Skenario | Precondition | Langkah Singkat | Data Uji | Expected Result |
|---|---|---|---|---|---|---|
| A-LOGIN-01 | Login | Login berhasil | Akun admin valid | Buka login, isi email + password valid, klik Masuk Dashboard | Akun valid | Masuk Dashboard, sesi login aktif |
| A-LOGIN-02 | Login | Email tidak valid | - | Isi email format salah, klik Masuk Dashboard | "admin@" | Validasi email tampil, tetap di login |
| A-LOGIN-03 | Login | Password salah | Akun admin valid | Isi email valid, password salah, klik Masuk Dashboard | Akun tidak valid | Pesan "Email atau password salah" |
| A-LOGIN-04 | Login | Field kosong | - | Klik Masuk Dashboard tanpa isi field | - | Validasi tampil pada field |
| A-LOGOUT-01 | Logout | Logout berhasil | Sudah login | Klik Logout pada sidebar | - | Kembali ke login, sesi berakhir |

## Dashboard

| ID | Fitur/Menu | Skenario | Precondition | Langkah Singkat | Data Uji | Expected Result |
|---|---|---|---|---|---|---|
| A-DASH-01 | Dashboard | Halaman terbuka | Sudah login | Buka menu Dashboard | - | Statistik tampil (dataset, produk, user, penyakit terbanyak) |
| A-DASH-02 | Dashboard | Modal user terbaru | Sudah login | Klik kartu Total User | - | Modal user terbaru tampil dan bisa ditutup |
| A-DASH-03 | Dashboard | Grafik distribusi penyakit | Data penyakit tersedia | Lihat grafik | - | Grafik tampil tanpa error |
| A-DASH-04 | Dashboard | Pie chart user kabupaten | Data kabupaten tersedia | Lihat pie chart kabupaten | - | Pie chart tampil dan label sesuai data |
| A-DASH-05 | Dashboard | Tanpa data kabupaten | Data kabupaten kosong | Buka Dashboard | - | Pesan "Belum ada data" tampil |

## Manajemen Dataset

| ID | Fitur/Menu | Skenario | Precondition | Langkah Singkat | Data Uji | Expected Result |
|---|---|---|---|---|---|---|
| A-DATASET-01 | Dataset | Upload dataset sukses | Sudah login | Pilih label, upload gambar, klik Upload Dataset | JPG/PNG < 2MB | Data baru muncul di galeri, notifikasi sukses |
| A-DATASET-02 | Dataset | Upload tanpa gambar | Sudah login | Pilih label, klik Upload Dataset | - | Validasi file muncul |
| A-DATASET-03 | Dataset | Upload file bukan gambar | Sudah login | Upload file non-gambar | PDF/DOC | Validasi file muncul |
| A-DATASET-04 | Dataset | Upload > 2MB | Sudah login | Upload file besar | > 2MB | Validasi ukuran file muncul |
| A-DATASET-05 | Dataset | Filter label | Sudah login | Pilih label di filter, klik Filter | - | Galeri hanya tampil label terpilih |
| A-DATASET-06 | Dataset | Reset filter | Sudah login | Pilih "Semua Label" | - | Galeri tampil semua label |
| A-DATASET-07 | Dataset | Hapus data | Sudah login | Klik hapus pada item, konfirmasi | - | Item hilang, notifikasi sukses |
| A-DATASET-08 | Dataset | Pagination | Data > 1 halaman | Pindah halaman | - | Data berubah sesuai halaman |

## Manajemen Produk

| ID | Fitur/Menu | Skenario | Precondition | Langkah Singkat | Data Uji | Expected Result |
|---|---|---|---|---|---|---|
| A-PROD-01 | Produk | Tambah produk sukses | Sudah login | Isi nama, harga, deskripsi, link, kategori, upload gambar, simpan | Data valid | Produk baru muncul di tabel |
| A-PROD-02 | Produk | Tambah produk tanpa gambar | Sudah login | Isi field wajib tanpa gambar, simpan | - | Produk tersimpan dengan gambar kosong |
| A-PROD-03 | Produk | Harga bukan angka | Sudah login | Isi harga dengan teks, simpan | "abc" | Validasi harga numeric muncul |
| A-PROD-04 | Produk | Link marketplace tidak valid | Sudah login | Isi link tidak berformat URL, simpan | "shopee" | Validasi URL muncul |
| A-PROD-05 | Produk | Edit produk sukses | Produk tersedia | Klik Edit, ubah data, simpan | Data valid | Data produk diperbarui |
| A-PROD-06 | Produk | Edit produk ganti gambar | Produk tersedia | Klik Edit, pilih gambar baru, simpan | JPG/PNG < 2MB | Gambar produk berubah |
| A-PROD-07 | Produk | Edit produk batal | Produk tersedia | Klik Edit, klik Batal/X | - | Modal tertutup tanpa perubahan |
| A-PROD-08 | Produk | Hapus produk | Produk tersedia | Klik Hapus, konfirmasi | - | Produk hilang dari tabel |
| A-PROD-09 | Produk | Gambar gagal dimuat | Produk tanpa gambar | Buka daftar produk | - | Placeholder tampil tanpa error |

## History Classification

| ID | Fitur/Menu | Skenario | Precondition | Langkah Singkat | Data Uji | Expected Result |
|---|---|---|---|---|---|---|
| A-HIST-01 | History | List tampil | Sudah login | Buka menu History Classification | - | Tabel history tampil |
| A-HIST-02 | History | Filter keyword | Data tersedia | Masukkan keyword penyakit/user/lokasi, klik Filter | "Brown" | Data terfilter sesuai keyword |
| A-HIST-03 | History | Edit data | Data tersedia | Klik Edit, ubah user/jenis penyakit, simpan | Data valid | Data baris ter-update |
| A-HIST-04 | History | Batal edit | Data tersedia | Klik Edit, klik Batal | - | Form edit tertutup, data tidak berubah |
| A-HIST-05 | History | Hapus data | Data tersedia | Klik Hapus, konfirmasi | - | Data hilang dari tabel |
| A-HIST-06 | History | Pagination | Data > 1 halaman | Pindah halaman | - | Data berubah sesuai halaman |
