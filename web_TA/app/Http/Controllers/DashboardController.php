<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Hitung total dataset
        $totalDataset = Dataset::count();
        
        // Statistik per penyakit
        $diseaseStats = Dataset::selectRaw('label, count(*) as total')
            ->groupBy('label')
            ->orderByDesc('total')
            ->get();
        
        // Penyakit dengan jumlah terbanyak
        $mostCommonDisease = $diseaseStats->first();
        
        // Total produk
        $totalProducts = Product::count();

        // Total user dan user terbaru
        $totalUsers = User::count();
        $recentUsers = User::orderByDesc('created_at')->take(10)->get();
        
        // Statistik penyakit per kabupaten
        $diseaseByKabupaten = \App\Models\Classification::selectRaw('kabupaten, predicted_class, count(*) as total')
            ->whereNotNull('kabupaten')
            ->groupBy('kabupaten', 'predicted_class')
            ->orderBy('kabupaten')
            ->orderByDesc('total')
            ->get();
        
        // Format data untuk chart
        $kabupatenDiseaseData = [];
        foreach ($diseaseByKabupaten as $record) {
            if (!isset($kabupatenDiseaseData[$record->kabupaten])) {
                $kabupatenDiseaseData[$record->kabupaten] = [];
            }
            $kabupatenDiseaseData[$record->kabupaten][$record->predicted_class] = $record->total;
        }

        return view('dashboard', compact(
            'totalDataset',
            'diseaseStats',
            'mostCommonDisease',
            'totalProducts',
            'totalUsers',
            'recentUsers',
            'kabupatenDiseaseData'
        ));
    }
}
