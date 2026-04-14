import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

class EcommerceScreen extends StatefulWidget {
  const EcommerceScreen({super.key});

  @override
  State<EcommerceScreen> createState() => _EcommerceScreenState();
}

class _EcommerceScreenState extends State<EcommerceScreen> {
  final TextEditingController _searchController = TextEditingController();

  // Fungsi untuk membuka link Shopee di browser HP M2102J20SG
  Future<void> _bukaLinkShopee(String url) async {
    final Uri uri = Uri.parse(url);
    if (!await launchUrl(uri, mode: LaunchMode.externalApplication)) {
      debugPrint("Gagal membuka link: $url");
    }
  }

  // Fungsi Pop-up: Di sini baru kita munculkan Deskripsi Lengkap
  void _tampilkanPopUpDetail(BuildContext context, Map<String, String> produk) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Gambar di dalam pop-up (Placeholder Hitam)
            Container(
              width: double.infinity,
              height: 180,
              decoration: BoxDecoration(
                color: Colors.black,
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Center(child: Text("gambar produk", style: TextStyle(color: Colors.white))),
            ),
            const SizedBox(height: 15),
            Text(produk['nama']!, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 20)),
            const SizedBox(height: 5),
            Text("Rp ${produk['harga']}", style: const TextStyle(color: Color(0xFF0F703A), fontWeight: FontWeight.bold, fontSize: 18)),
            const SizedBox(height: 15),
            const Divider(),
            const Text("Deskripsi Produk:", style: TextStyle(fontWeight: FontWeight.bold)),
            const SizedBox(height: 5),
            Text(produk['deskripsi']!, textAlign: TextAlign.center, style: const TextStyle(fontSize: 14)),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text("Tutup", style: TextStyle(color: Color(0xFF0F703A))),
          ),
          ElevatedButton(
            onPressed: () => _bukaLinkShopee(produk['link']!),
            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF0F703A)),
            child: const Text("Beli di Shopee", style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    // Data Dummy (Nanti diganti data dari MySQL db_rice)
    final List<Map<String, String>> listProduk = [
      {
        "nama": "Pupuk Urea",
        "harga": "75.000",
        "deskripsi": "Pupuk urea berkualitas tinggi untuk mempercepat pertumbuhan daun dan batang padi agar lebih hijau dan kuat.",
        "link": "https://shopee.co.id"
      },
      {
        "nama": "Benih Inpari 32",
        "harga": "120.000",
        "deskripsi": "Benih padi varietas unggul yang tahan terhadap hawar daun bakteri dan memiliki potensi hasil yang sangat tinggi.",
        "link": "https://shopee.co.id"
      },
      {
        "nama": "Pestisida Cair",
        "harga": "45.000",
        "deskripsi": "Cairan pembasmi hama wereng dan ulat grayak yang efektif melindungi tanaman padi dari serangan penyakit.",
        "link": "https://shopee.co.id"
      },
    ];

    return Scaffold(
      backgroundColor: Colors.grey[100],
      appBar: AppBar(
        title: const Text(
          'Toko Produk AgriPadi',
          style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white),
        ),
        backgroundColor: const Color(0xFF0F703A),
        centerTitle: true,
        elevation: 0,
      ),
      body: SafeArea(
        child: Column(
          children: [
            // 1. Kotak Pencarian (SEO) - BACKROUND.png
            Padding(
              padding: const EdgeInsets.all(16.0),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 15),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.green.shade300, width: 2),
                ),
                child: TextField(
                  controller: _searchController,
                  textAlign: TextAlign.center,
                  decoration: const InputDecoration(
                    hintText: "Cari Produk AgriPadi...",
                    border: InputBorder.none,
                    prefixIcon: Icon(Icons.search, color: Color(0xFF0F703A)),
                  ),
                ),
              ),
            ),

            // 2. Grid Produk: Nama & Harga saja - BACKROUND.png
            Expanded(
              child: GridView.builder(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: 2, // 2 kolom agar lebih rapi di HP
                  crossAxisSpacing: 12,
                  mainAxisSpacing: 12,
                  childAspectRatio: 0.75,
                ),
                itemCount: listProduk.length,
                itemBuilder: (context, index) {
                  final produk = listProduk[index];
                  return Card(
                    elevation: 3,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    child: Column(
                      children: [
                        // Bagian Gambar
                        Container(
                          height: 100,
                          width: double.infinity,
                          decoration: const BoxDecoration(
                            color: Colors.grey,
                            borderRadius: BorderRadius.vertical(top: Radius.circular(12)),
                          ),
                          child: const Center(child: Text("gambar", style: TextStyle(color: Colors.white))),
                        ),
                        // Bagian Nama & Harga
                        Padding(
                          padding: const EdgeInsets.all(10.0),
                          child: Column(
                            children: [
                              Text(produk['nama']!, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                              const SizedBox(height: 4),
                              Text("Rp ${produk['harga']}", style: const TextStyle(color: Color(0xFF0F703A), fontWeight: FontWeight.bold)),
                              const SizedBox(height: 10),
                              // Tombol Lihat Detail (Akan munculkan Deskripsi)
                              SizedBox(
                                width: double.infinity,
                                height: 35,
                                child: ElevatedButton(
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: const Color(0xFF0F703A),
                                    foregroundColor: Colors.white,
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                  ),
                                  onPressed: () => _tampilkanPopUpDetail(context, produk),
                                  child: const Text("Lihat Detail", style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }
}