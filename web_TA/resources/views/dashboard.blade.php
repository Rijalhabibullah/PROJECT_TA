@extends('layouts.admin')

@section('title', 'Overview Statistik')

@section('content')

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-start justify-between">
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Diagnosa</p>
            <h3 class="text-3xl font-bold text-gray-800 mt-2">1,240</h3>
            <span class="inline-block mt-2 px-2 py-1 text-xs font-bold text-emerald-600 bg-emerald-50 rounded-md">+12% Bulan ini</span>
        </div>
        <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-start justify-between">
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Produk</p>
            <h3 class="text-3xl font-bold text-gray-800 mt-2">24</h3>
            <span class="inline-block mt-2 text-xs text-gray-500">Siap Jual</span>
        </div>
        <div class="p-3 bg-purple-50 text-purple-600 rounded-xl">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-start justify-between">
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Penyakit Terbanyak</p>
            <h3 class="text-xl font-bold text-gray-800 mt-2">Brown Spot</h3>
            <span class="inline-block mt-2 text-xs text-gray-500">450 Kasus terdeteksi</span>
        </div>
        <div class="p-3 bg-red-50 text-red-600 rounded-xl">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Statistik Penyakit (Bulan Ini)</h3>
        <canvas id="diseaseChart" height="150"></canvas>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Produk Paling Sering Direkomendasikan</h3>
        <div class="space-y-4">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-lg">💊</div>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-gray-800">Fungisida Score 250</h4>
                    <p class="text-xs text-gray-500">Untuk Brown Spot</p>
                </div>
                <span class="font-bold text-emerald-600">84x</span>
            </div>
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-lg">🌿</div>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-gray-800">Pupuk Urea</h4>
                    <p class="text-xs text-gray-500">Penyubur Daun</p>
                </div>
                <span class="font-bold text-emerald-600">56x</span>
            </div>
             <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-lg">🧪</div>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-gray-800">Bakterisida Agrept</h4>
                    <p class="text-xs text-gray-500">Untuk Leaf Blight</p>
                </div>
                <span class="font-bold text-emerald-600">32x</span>
            </div>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('diseaseChart').getContext('2d');
    const myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Brown Spot', 'Leaf Blast', 'Hispa', 'Healthy'],
            datasets: [{
                label: 'Jumlah Kasus',
                data: [45, 30, 20, 55], // Data Dummy
                backgroundColor: [
                    'rgba(239, 68, 68, 0.7)', // Merah
                    'rgba(245, 158, 11, 0.7)', // Kuning
                    'rgba(59, 130, 246, 0.7)', // Biru
                    'rgba(16, 185, 129, 0.7)'  // Hijau
                ],
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, grid: { display: false } },
                x: { grid: { display: false } }
            }
        }
    });
</script>

@endsection