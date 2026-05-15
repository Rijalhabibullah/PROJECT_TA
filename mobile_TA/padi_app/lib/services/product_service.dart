import 'dart:convert';
import 'dart:async';
import 'package:http/http.dart' as http;

class ProductItem {
  final int id;
  final String name;
  final String? description;
  final double price;
  final String? imageUrl;
  final String? marketplaceLink;
  final String? diseaseTag;

  ProductItem({
    required this.id,
    required this.name,
    required this.price,
    this.description,
    this.imageUrl,
    this.marketplaceLink,
    this.diseaseTag,
  });

  factory ProductItem.fromJson(Map<String, dynamic> json) {
    final dynamic rawPrice = json['price'];
    double priceValue = 0.0;
    if (rawPrice is num) {
      priceValue = rawPrice.toDouble();
    } else if (rawPrice is String) {
      priceValue = double.tryParse(rawPrice) ?? 0.0;
    }

    return ProductItem(
      id: json['id'] ?? 0,
      name: json['name'] ?? '',
      description: json['description'],
      price: priceValue,
      imageUrl: json['image_url'],
      marketplaceLink: json['marketplace_link'],
      diseaseTag: json['disease_tag'],
    );
  }
}

class ProductService {
  // Samakan dengan API root di classification_service.dart
  static const String _apiRoot = 'https://gobony-wedgy-cathi.ngrok-free.dev/api';
  static const String _productsUrl = '$_apiRoot/products';

  final http.Client _httpClient;

  ProductService({http.Client? httpClient}) : _httpClient = httpClient ?? http.Client();

  Future<List<ProductItem>> fetchProducts() async {
    try {
      final response = await _httpClient
          .get(Uri.parse(_productsUrl), headers: {'Accept': 'application/json'})
          .timeout(const Duration(seconds: 15));

      if (response.statusCode != 200) {
        throw Exception('Gagal mengambil produk');
      }

      final jsonResponse = jsonDecode(response.body);
      if (jsonResponse is! Map || jsonResponse['success'] != true) {
        throw Exception(jsonResponse['message'] ?? 'Gagal mengambil produk');
      }

      final data = jsonResponse['data'];
      if (data is! List) {
        return [];
      }

      return data
          .map((item) => ProductItem.fromJson(Map<String, dynamic>.from(item)))
          .toList();
    } on TimeoutException catch (e) {
      throw Exception(e.message);
    } catch (e) {
      throw Exception('Error: $e');
    }
  }
}
