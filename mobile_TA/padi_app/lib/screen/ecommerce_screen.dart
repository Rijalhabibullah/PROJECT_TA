import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../services/product_service.dart';

class EcommerceScreen extends StatefulWidget {
  const EcommerceScreen({super.key});

  @override
  State<EcommerceScreen> createState() => _EcommerceScreenState();
}

class _EcommerceScreenState extends State<EcommerceScreen> {
  final TextEditingController _searchController = TextEditingController();
  final ProductService _productService = ProductService();

  List<ProductItem> _products = [];
  List<ProductItem> _filteredProducts = [];
  bool _isLoading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadProducts();
    _searchController.addListener(_applySearch);
  }

  @override
  void dispose() {
    _searchController.removeListener(_applySearch);
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadProducts() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final products = await _productService.fetchProducts();
      setState(() {
        _products = products;
        _filteredProducts = products;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _errorMessage = e.toString();
        _isLoading = false;
      });
    }
  }

  void _applySearch() {
    final query = _searchController.text.toLowerCase().trim();
    if (query.isEmpty) {
      setState(() {
        _filteredProducts = _products;
      });
      return;
    }

    setState(() {
      _filteredProducts = _products.where((product) {
        final name = product.name.toLowerCase();
        final description = (product.description ?? '').toLowerCase();
        return name.contains(query) || description.contains(query);
      }).toList();
    });
  }

  // Fungsi untuk membuka link Shopee di browser HP M2102J20SG
  Future<void> _bukaLinkShopee(String url) async {
    final Uri uri = Uri.parse(url);
    if (!await launchUrl(uri, mode: LaunchMode.externalApplication)) {
      debugPrint("Gagal membuka link: $url");
    }
  }

  // Fungsi Pop-up: Di sini baru kita munculkan Deskripsi Lengkap
  void _tampilkanPopUpDetail(BuildContext context, ProductItem produk) {
    final priceText = _formatPrice(produk.price);
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
        content: SizedBox(
          width: 280,
          child: ConstrainedBox(
            constraints: BoxConstraints(
              maxHeight: MediaQuery.of(context).size.height * 0.6,
            ),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  _buildDialogImage(produk.imageUrl),
                  const SizedBox(height: 15),
                  Text(produk.name, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 20)),
                  const SizedBox(height: 5),
                  Text("Rp $priceText", style: const TextStyle(color: Color(0xFF0F703A), fontWeight: FontWeight.bold, fontSize: 18)),
                  const SizedBox(height: 15),
                  const Divider(),
                  const Text("Deskripsi Produk:", style: TextStyle(fontWeight: FontWeight.bold)),
                  const SizedBox(height: 5),
                  Text(produk.description ?? '-', textAlign: TextAlign.center, style: const TextStyle(fontSize: 14)),
                ],
              ),
            ),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text("Tutup", style: TextStyle(color: Color(0xFF0F703A))),
          ),
          if (produk.marketplaceLink != null && produk.marketplaceLink!.trim().isNotEmpty)
            ElevatedButton(
              onPressed: () => _bukaLinkShopee(produk.marketplaceLink!),
              style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF0F703A)),
              child: const Text("Beli di Marketplace", style: TextStyle(color: Colors.white)),
            ),
        ],
      ),
    );
  }

  Widget _buildProductImage(String? imageUrl, {double height = 100, double borderRadius = 12}) {
    if (imageUrl == null || imageUrl.trim().isEmpty) {
      return Container(
        height: height,
        width: double.infinity,
        decoration: BoxDecoration(
          color: Colors.grey,
          borderRadius: BorderRadius.vertical(top: Radius.circular(borderRadius)),
        ),
        child: const Center(child: Text("gambar", style: TextStyle(color: Colors.white))),
      );
    }

    return ClipRRect(
      borderRadius: BorderRadius.vertical(top: Radius.circular(borderRadius)),
      child: Container(
        height: height,
        width: double.infinity,
        color: Colors.grey.shade200,
        child: Image.network(
          imageUrl,
          height: height,
          width: double.infinity,
          fit: BoxFit.contain,
          errorBuilder: (context, error, stackTrace) {
            return Container(
              height: height,
              width: double.infinity,
              color: Colors.grey,
              child: const Center(child: Text("gambar", style: TextStyle(color: Colors.white))),
            );
          },
        ),
      ),
    );
  }

  Widget _buildDialogImage(String? imageUrl) {
    const double height = 180;
    const double width = 280;
    const double radius = 10;

    if (imageUrl == null || imageUrl.trim().isEmpty) {
      return Container(
        height: height,
        width: width,
        decoration: BoxDecoration(
          color: Colors.grey,
          borderRadius: BorderRadius.circular(radius),
        ),
        child: const Center(child: Text("gambar", style: TextStyle(color: Colors.white))),
      );
    }

    return ClipRRect(
      borderRadius: BorderRadius.circular(radius),
      child: Container(
        height: height,
        width: width,
        color: Colors.grey.shade200,
        child: Image.network(
          imageUrl,
          height: height,
          width: width,
          fit: BoxFit.contain,
          errorBuilder: (context, error, stackTrace) {
            return Container(
              height: height,
              width: width,
              color: Colors.grey,
              child: const Center(child: Text("gambar", style: TextStyle(color: Colors.white))),
            );
          },
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
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
              child: _buildProductGrid(),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildProductGrid() {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_errorMessage != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(_errorMessage!, textAlign: TextAlign.center),
              const SizedBox(height: 12),
              ElevatedButton(
                onPressed: _loadProducts,
                style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF0F703A)),
                child: const Text('Coba Lagi', style: TextStyle(color: Colors.white)),
              ),
            ],
          ),
        ),
      );
    }

    if (_filteredProducts.isEmpty) {
      return const Center(child: Text('Produk belum tersedia.'));
    }

    return GridView.builder(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        crossAxisSpacing: 12,
        mainAxisSpacing: 12,
        childAspectRatio: 0.68,
      ),
      itemCount: _filteredProducts.length,
      itemBuilder: (context, index) {
        final produk = _filteredProducts[index];
        final priceText = _formatPrice(produk.price);
        return Card(
          elevation: 3,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          child: Column(
            children: [
              _buildProductImage(produk.imageUrl, height: 120),
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.all(10.0),
                  child: Column(
                    children: [
                      Text(
                        produk.name,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        textAlign: TextAlign.center,
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                      ),
                      const SizedBox(height: 4),
                      Text("Rp $priceText", style: const TextStyle(color: Color(0xFF0F703A), fontWeight: FontWeight.bold)),
                      const Spacer(),
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
              ),
            ],
          ),
        );
      },
    );
  }

  String _formatPrice(double price) {
    final value = price.round();
    final raw = value.toString();
    final buffer = StringBuffer();
    for (int i = 0; i < raw.length; i++) {
      final position = raw.length - i;
      buffer.write(raw[i]);
      if (position > 1 && position % 3 == 1) {
        buffer.write('.');
      }
    }
    return buffer.toString();
  }
}