@extends('layouts.admin')

@section('title', 'Overview Statistik')

@section('content')

<!-- Kartu Statistik Utama -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    
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

    <!-- Kartu 3: Penyakit Terbanyak di Database -->
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

@endsection