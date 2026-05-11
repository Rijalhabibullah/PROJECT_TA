@extends('layouts.admin')

@section('title', 'Overview Statistik')

@section('content')

<!-- Kartu Statistik Utama -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    
    <!-- Kartu 1: Total Dataset (Citra Penyakit) -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-start justify-between">
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">📊 Total Data Latih</p>
            <h3 class="text-3xl font-bold text-gray-800 mt-2">{{ number_format($totalDataset) }}</h3>
            <span class="inline-block mt-2 px-2 py-1 text-xs font-bold text-blue-600 bg-blue-50 rounded-md">{{ $totalDataset }} Citra Penyakit</span>
        </div>
        <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
    </div>

    <!-- Kartu 2: Total Produk -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-start justify-between">
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">🛒 Total Produk</p>
            <h3 class="text-3xl font-bold text-gray-800 mt-2">{{ $totalProducts }}</h3>
            <span class="inline-block mt-2 text-xs text-gray-500">Solusi Pertanian Siap Jual</span>
        </div>
        <div class="p-3 bg-purple-50 text-purple-600 rounded-xl">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
        </div>
    </div>

    <!-- Kartu 3: Total User -->
    <button type="button" onclick="toggleUserList()" class="text-left bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-start justify-between hover:border-emerald-300 hover:shadow-md transition">
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">👤 Total User</p>
            <h3 class="text-3xl font-bold text-gray-800 mt-2">{{ number_format($totalUsers) }}</h3>
            <span class="inline-block mt-2 text-xs text-emerald-600">Klik untuk lihat user terbaru</span>
        </div>
        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m6-3a4 4 0 11-8 0 4 4 0 018 0zm6 0a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </div>
    </button>

    <!-- Kartu 4: Penyakit Terbanyak di Database -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-start justify-between">
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">⚠️ Penyakit Terbanyak</p>
            <h3 class="text-xl font-bold text-gray-800 mt-2">{{ $mostCommonDisease->label ?? 'N/A' }}</h3>
            <span class="inline-block mt-2 text-xs text-gray-500">
                {{ $mostCommonDisease->total ?? 0 }} Data dalam database
            </span>
        </div>
        <div class="p-3 bg-red-50 text-red-600 rounded-xl">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
    </div>
</div>

<!-- Modal User Terbaru -->
<div id="user-list-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" onclick="toggleUserList()"></div>
    <div class="relative mx-auto mt-20 w-full max-w-3xl px-4">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100">
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <h3 class="text-lg font-bold text-gray-800">User Terbaru</h3>
                <button type="button" onclick="toggleUserList()" class="text-sm text-gray-500 hover:text-gray-700">Tutup</button>
            </div>
            <div class="max-h-[70vh] overflow-y-auto">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Daftar</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recentUsers as $user)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $user->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $user->email }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $user->created_at?->format('d M Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-sm text-gray-500">Belum ada user.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grafik dan Detail Penyakit -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Grafik Distribusi Penyakit -->
    <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <h3 class="text-lg font-bold text-gray-800 mb-4">📈 Distribusi Data Penyakit dalam Database</h3>
        <div class="h-56 md:h-64">
            <canvas id="diseaseChart"></canvas>
        </div>
        <p class="text-xs text-gray-500 mt-4">Diagram menunjukkan jumlah citra setiap jenis penyakit yang tersimpan di database untuk training AI</p>
    </div>

    <!-- Daftar Detail Penyakit -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <h3 class="text-lg font-bold text-gray-800 mb-4">📋 Detail Data Penyakit</h3>
        <div class="space-y-4">
            @forelse($diseaseStats as $disease)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-gray-800">{{ $disease->label }}</h4>
                    <p class="text-xs text-gray-500 mt-1">
                        <span class="font-semibold text-blue-600">{{ $disease->total }}</span> Citra
                    </p>
                </div>
                <div class="text-right">
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white font-bold">
                        {{ round(($disease->total / $totalDataset) * 100) }}%
                    </div>
                </div>
            </div>
            @empty
            <p class="text-gray-500 text-sm">Belum ada data penyakit</p>
            @endforelse
        </div>
    </div>
</div>

<script>
    function toggleUserList() {
        const modal = document.getElementById('user-list-modal');
        if (!modal) return;
        modal.classList.toggle('hidden');
        document.body.classList.toggle('overflow-hidden');
    }

    const ctx = document.getElementById('diseaseChart').getContext('2d');
    const myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [
                @foreach($diseaseStats as $disease)
                    '{{ $disease->label }}',
                @endforeach
            ],
            datasets: [{
                label: 'Jumlah Citra',
                data: [
                    @foreach($diseaseStats as $disease)
                        {{ $disease->total }},
                    @endforeach
                ],
                backgroundColor: [
                    'rgba(239, 68, 68, 0.8)',      // Merah (Brown Spot)
                    'rgba(245, 158, 11, 0.8)',     // Kuning (Leaf Smut)
                    'rgba(59, 130, 246, 0.8)',     // Biru (Bacterial Blight)
                    'rgba(16, 185, 129, 0.8)'      // Hijau (Healthy)
                ],
                borderColor: [
                    'rgba(239, 68, 68, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(59, 130, 246, 1)',
                    'rgba(16, 185, 129, 1)'
                ],
                borderWidth: 2,
                borderRadius: 8,
                maxBarThickness: 72,
                barPercentage: 0.9,
                categoryPercentage: 0.8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    display: false,
                    position: 'bottom',
                    labels: {
                        font: { size: 12 },
                        padding: 15
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const value = context.parsed.y;
                            const percentage = ((value / total) * 100).toFixed(1);
                            return context.label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(229, 231, 235, 0.7)' },
                    ticks: { precision: 0 }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
</script>

<!-- Statistik Penyakit Per Kabupaten -->
<div class="mt-8 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
    <h3 class="text-lg font-bold text-gray-800 mb-4">🗺️ Distribusi Penyakit Per Kabupaten</h3>
    
    @if(count($kabupatenDiseaseData) > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Kabupaten</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Bacterial Blight</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Brown Spot</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Leaf Smut</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Total</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($kabupatenDiseaseData as $kabupaten => $diseases)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ $kabupaten ?? 'Tidak Diketahui' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center justify-center px-3 py-1 text-sm font-bold text-blue-700 bg-blue-100 rounded-lg">
                            {{ $diseases['Bacterialblight'] ?? 0 }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center justify-center px-3 py-1 text-sm font-bold text-orange-700 bg-orange-100 rounded-lg">
                            {{ $diseases['Brownspot'] ?? 0 }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center justify-center px-3 py-1 text-sm font-bold text-yellow-700 bg-yellow-100 rounded-lg">
                            {{ $diseases['Leafsmut'] ?? 0 }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center justify-center px-3 py-1 text-sm font-bold text-gray-700 bg-gray-200 rounded-lg">
                            {{ array_sum($diseases) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center py-8">
        <p class="text-gray-500 text-sm">Belum ada data klasifikasi dengan informasi kabupaten</p>
    </div>
    @endif
</div>

@endsection