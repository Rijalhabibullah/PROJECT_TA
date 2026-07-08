import 'package:flutter/material.dart';
import 'package:fl_chart/fl_chart.dart';
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

          // Filter data bulan ini
          final now = DateTime.now();
          final thisMonthItems = items.where((item) {
            return item.createdAt.year == now.year && 
                   item.createdAt.month == now.month;
          }).toList();

          // Hitung jumlah per klasifikasi bulan ini
          final classificationCount = <String, int>{};
          for (var item in thisMonthItems) {
            classificationCount[item.diseaseName] = 
                (classificationCount[item.diseaseName] ?? 0) + 1;
          }

          return SingleChildScrollView(
            child: Column(
              children: [
                // Pie Chart Section
                if (classificationCount.isNotEmpty)
                  Container(
                    padding: const EdgeInsets.all(20),
                    margin: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(15),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.06),
                          blurRadius: 8,
                          offset: const Offset(0, 3),
                        ),
                      ],
                    ),
                    child: Column(
                      children: [
                        Text(
                          'Klasifikasi Bulan ${_getMonthName(now.month)} ${now.year}',
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 20),
                        SizedBox(
                          height: 280,
                          child: PieChart(
                            PieChartData(
                              sections: _buildPieChartSections(classificationCount),
                              centerSpaceRadius: 40,
                              sectionsSpace: 2,
                            ),
                          ),
                        ),
                        const SizedBox(height: 20),
                        // Legend
                        Column(
                          children: classificationCount.entries.map((entry) {
                            final color = _getColorForDisease(entry.key);
                            return Padding(
                              padding: const EdgeInsets.only(bottom: 8),
                              child: Row(
                                children: [
                                  Container(
                                    width: 12,
                                    height: 12,
                                    decoration: BoxDecoration(
                                      color: color,
                                      shape: BoxShape.circle,
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Text(entry.key),
                                  ),
                                  Text(
                                    '${entry.value}x',
                                    style: const TextStyle(
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ],
                              ),
                            );
                          }).toList(),
                        ),
                      ],
                    ),
                  )
                else
                  Padding(
                    padding: const EdgeInsets.all(20),
                    child: Container(
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        color: Colors.blue[50],
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: Colors.blue),
                      ),
                      child: const Center(
                        child: Text(
                          'Belum ada data klasifikasi bulan ini',
                          style: TextStyle(color: Colors.blue),
                        ),
                      ),
                    ),
                  ),

                // History List Section
                if (items.isNotEmpty)
                  Padding(
                    padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Padding(
                          padding: EdgeInsets.symmetric(vertical: 16),
                          child: Text(
                            'Semua Riwayat Klasifikasi',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                        ListView.separated(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
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
                                    text: 'Akurasi: ${item.confidence}',
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
                        ),
                      ],
                    ),
                  ),
              ],
            ),
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

  List<PieChartSectionData> _buildPieChartSections(Map<String, int> classificationCount) {
    final total = classificationCount.values.fold<int>(0, (sum, val) => sum + val);
    
    return classificationCount.entries.map((entry) {
      final value = entry.value;
      final percentage = (value / total * 100);
      final diseaseName = _getNormalizedDiseaseName(entry.key);
      
      return PieChartSectionData(
        color: _getColorForDisease(entry.key),
        value: value.toDouble(),
        title: '$diseaseName\n${percentage.toStringAsFixed(1)}%',
        radius: 80,
        titleStyle: const TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.bold,
          color: Colors.white,
          height: 1.2,
        ),
      );
    }).toList();
  }

  Color _getColorForDisease(String diseaseName) {
    final colors = {
      'Bacterial Blight': const Color(0xFFEF5350), // Merah cerah
      'Brown Spot': const Color(0xFFA1887F), // Coklat
      'Leaf Smut': const Color(0xFF455A64), // Abu-abu gelap
      'Healthy': const Color(0xFF66BB6A), // Hijau cerah
      'bacterial blight': const Color(0xFFEF5350), // Merah cerah (lowercase)
      'brown spot': const Color(0xFFA1887F), // Coklat (lowercase)
      'leaf smut': const Color(0xFF455A64), // Abu-abu gelap (lowercase)
      'healthy': const Color(0xFF66BB6A), // Hijau cerah (lowercase)
    };
    
    // Coba exact match dulu, jika tidak ada gunakan warna berdasarkan hash
    if (colors.containsKey(diseaseName)) {
      return colors[diseaseName]!;
    }
    
    // Jika tidak ada di map, assign warna berdasarkan hash nama untuk konsistensi
    final hash = diseaseName.hashCode.abs();
    final colorList = [
      const Color(0xFFEF5350), // Red
      const Color(0xFFAB47BC), // Purple
      const Color(0xFF5C6BC0), // Indigo
      const Color(0xFF42A5F5), // Blue
      const Color(0xFF29B6F6), // Light Blue
      const Color(0xFF26C6DA), // Cyan
      const Color(0xFF26A69A), // Teal
      const Color(0xFF66BB6A), // Green
      const Color(0xFF9CCC65), // Light Green
      const Color(0xFFD4AF37), // Gold
      const Color(0xFFFF7043), // Deep Orange
      const Color(0xFFA1887F), // Brown
    ];
    
    return colorList[hash % colorList.length];
  }

  String _getMonthName(int month) {
    final months = [
      'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    return months[month - 1];
  }

  String _getNormalizedDiseaseName(String diseaseName) {
    final normalized = {
      'Bacterial Blight': 'Bacterial Blight',
      'bacterial blight': 'Bacterial Blight',
      'Bacterialblight': 'Bacterial Blight',
      'Bercak Bakteri (Bacterial Blight)': 'Bacterial Blight',
      'Brown Spot': 'Brown Spot',
      'brown spot': 'Brown Spot',
      'Brownspot': 'Brown Spot',
      'Bercak Coklat (Brown Spot)': 'Brown Spot',
      'Leaf Smut': 'Leaf Smut',
      'leaf smut': 'Leaf Smut',
      'Leafsmut': 'Leaf Smut',
      'Jamur Daun (Leaf Smut)': 'Leaf Smut',
      'Healthy': 'Healthy',
      'healthy': 'Healthy',
      'Sehat (Healthy)': 'Healthy',
    };
    
    return normalized[diseaseName] ?? diseaseName;
  }
}