import 'package:flutter/material.dart';
import '../services/classification_service.dart';

class HistoryScreen extends StatefulWidget {
  const HistoryScreen({super.key});

  @override
  State<HistoryScreen> createState() => _HistoryScreenState();
}

class _HistoryScreenState extends State<HistoryScreen> {
  final ClassificationService _classificationService = ClassificationService();
  late Future<List<ClassificationHistoryItem>> _historyFuture;

  @override
  void initState() {
    super.initState();
    _historyFuture = _classificationService.fetchHistory();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[100],
      appBar: AppBar(
        title: const Text(
          'Riwayat Klasifikasi',
          style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white),
        ),
        backgroundColor: const Color(0xFF0F703A),
        centerTitle: true,
        elevation: 0,
      ),
      body: FutureBuilder<List<ClassificationHistoryItem>>(
        future: _historyFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }

          if (snapshot.hasError) {
            return Center(
              child: Text(
                'Gagal memuat riwayat: ${snapshot.error}',
                textAlign: TextAlign.center,
              ),
            );
          }

          final items = snapshot.data ?? [];
          if (items.isEmpty) {
            return const Center(child: Text('Belum ada riwayat klasifikasi.'));
          }

          return ListView.separated(
            padding: const EdgeInsets.all(16),
            itemCount: items.length,
            separatorBuilder: (_, __) => const SizedBox(height: 12),
            itemBuilder: (context, index) {
              final item = items[index];
              return Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.06),
                      blurRadius: 8,
                      offset: const Offset(0, 3),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      item.diseaseName,
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 6),
                    _infoRow(
                      icon: Icons.label,
                      text: 'Kelas: ${item.predictedClass}',
                    ),
                    _infoRow(
                      icon: Icons.bar_chart,
                      text: 'Confidence: ${item.confidence}',
                    ),
                    _infoRow(
                      icon: Icons.location_on,
                      text:
                          'Lokasi: ${item.locationAddress ?? 'Alamat tidak terdeteksi'}',
                    ),
                    _infoRow(
                      icon: Icons.calendar_today,
                      text: _formatTimestamp(item.createdAt),
                    ),
                  ],
                ),
              );
            },
          );
        },
      ),
    );
  }

  String _formatTimestamp(DateTime value) {
    final twoDigits = (int n) => n.toString().padLeft(2, '0');
    final day = twoDigits(value.day);
    final month = twoDigits(value.month);
    final year = value.year.toString();
    final hour = twoDigits(value.hour);
    final minute = twoDigits(value.minute);
    return '$day-$month-$year $hour:$minute';
  }

  Widget _infoRow({required IconData icon, required String text}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 16, color: Colors.black54),
          const SizedBox(width: 6),
          Expanded(child: Text(text)),
        ],
      ),
    );
  }
}