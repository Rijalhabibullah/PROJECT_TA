<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use App\Models\Product;
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
        
        return view('dashboard', compact(
            'totalDataset',
            'diseaseStats',
            'mostCommonDisease',
            'totalProducts'
        ));
    }
}
