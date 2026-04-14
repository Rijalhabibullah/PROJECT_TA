<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Classification;

class ClassificationController extends Controller
{
    private $pythonApiUrl = 'http://127.0.0.1:5000'; // URL Flask API

    /**
     * Menerima gambar dan mengirim ke Python API untuk klasifikasi
     * POST /api/classify
     */
    public function classify(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // Max 5MB
            ]);

            // Ambil file gambar
            $file = $request->file('image');
            
            // Baca content gambar sebagai base64
            $imageContent = file_get_contents($file->getRealPath());
            $base64Image = base64_encode($imageContent);

            // Kirim request ke Flask API
            $response = Http::timeout(30)->post($this->pythonApiUrl . '/classify', [
                'image' => $base64Image,
                'filename' => $file->getClientOriginalName(),
            ]);

            // Cek apakah request berhasil
            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghubungi model classification',
                    'error' => $response->body()
                ], 500);
            }

            $result = $response->json();

            // Tambahkan informasi detail tentang penyakit
            $diseaseInfo = $this->getDiseaseInfo($result['predicted_class']);

            // Save to database
            Classification::create([
                'filename' => $file->getClientOriginalName(),
                'predicted_class' => $result['predicted_class'],
                'confidence' => $result['confidence'],
                'all_predictions' => $result['all_predictions'],
                'disease_name' => $diseaseInfo['name'],
                'severity' => $diseaseInfo['severity'],
                'notes' => 'Classification without storage',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Klasifikasi berhasil',
                'data' => [
                    'predicted_class' => $result['predicted_class'],
                    'confidence' => round($result['confidence'] * 100, 2) . '%',
                    'confidence_value' => $result['confidence'],
                    'all_predictions' => $result['all_predictions'],
                    'disease_info' => $diseaseInfo,
                    'timestamp' => now(),
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload gambar dan simpan ke database, kemudian klasifikasi
     * POST /api/classify-and-save
     */
    public function classifyAndSave(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
                'notes' => 'nullable|string|max:500'
            ]);

            // Simpan gambar
            $file = $request->file('image');
            $storagePath = $file->store('classifications', 'public');

            // Baca content gambar sebagai base64
            $imageContent = file_get_contents($file->getRealPath());
            $base64Image = base64_encode($imageContent);

            // Kirim request ke Flask API
            $response = Http::timeout(30)->post($this->pythonApiUrl . '/classify', [
                'image' => $base64Image,
                'filename' => $file->getClientOriginalName(),
            ]);

            if ($response->failed()) {
                // Hapus file yang sudah disimpan jika klasifikasi gagal
                Storage::disk('public')->delete($storagePath);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghubungi model classification',
                ], 500);
            }

            $result = $response->json();
            $diseaseInfo = $this->getDiseaseInfo($result['predicted_class']);

            // Save to database
            Classification::create([
                'image_path' => $storagePath,
                'filename' => $file->getClientOriginalName(),
                'predicted_class' => $result['predicted_class'],
                'confidence' => $result['confidence'],
                'all_predictions' => $result['all_predictions'],
                'disease_name' => $diseaseInfo['name'],
                'severity' => $diseaseInfo['severity'],
                'notes' => $request->input('notes'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Gambar berhasil diklasifikasi dan disimpan',
                'data' => [
                    'image_path' => Storage::url($storagePath),
                    'predicted_class' => $result['predicted_class'],
                    'confidence' => round($result['confidence'] * 100, 2) . '%',
                    'confidence_value' => $result['confidence'],
                    'all_predictions' => $result['all_predictions'],
                    'disease_info' => $diseaseInfo,
                    'notes' => $request->input('notes'),
                    'timestamp' => now(),
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test koneksi ke Python API
     * GET /api/classification/test
     */
    public function testConnection()
    {
        try {
            $response = Http::timeout(10)->post($this->pythonApiUrl . '/health', []);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Koneksi ke model API berhasil',
                    'model_info' => $response->json()
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Model API tidak merespons dengan benar'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghubungi model API: ' . $e->getMessage(),
                'hint' => 'Pastikan server Python API sudah berjalan di ' . $this->pythonApiUrl
            ], 500);
        }
    }

    /**
     * Mendapatkan informasi detail tentang penyakit
     */
    private function getDiseaseInfo($className)
    {
        $diseaseDatabase = [
            'Bacterialblight' => [
                'name' => 'Bercak Bakteri (Bacterial Blight)',
                'description' => 'Penyakit yang disebabkan oleh bakteri Xanthomonas oryzae pv. oryzae',
                'symptoms' => [
                    'Bercak kecil berwarna kuning dengan halo kuning di tepi',
                    'Bercak berkembang menjadi panjang dan menyempit',
                    'Daun berubah warna menjadi kuning dan akhirnya mati'
                ],
                'treatment' => [
                    'Gunakan varietas padi yang tahan',
                    'Praktikkan rotasi tanaman',
                    'Hindari irigasi berlebihan',
                    'Aplikasikan fungisida bakteri jika diperlukan'
                ],
                'severity' => 'Sedang hingga Tinggi'
            ],
            'Brownspot' => [
                'name' => 'Bercak Coklat (Brown Spot)',
                'description' => 'Penyakit yang disebabkan oleh jamur Bipolaris oryzae',
                'symptoms' => [
                    'Bercak oval atau bundar berwarna coklat gelap',
                    'Bercak dikelilingi cincin kuning pucat',
                    'Bercak dapat berkabung di pusat dengan tepi merah ungu'
                ],
                'treatment' => [
                    'Gunakan benih yang sehat dan telah disertifikasi',
                    'Terapkan pemupukan seimbang',
                    'Gunakan varietas tahan',
                    'Aplikasikan fungisida: Mancozeb atau Carbendazim'
                ],
                'severity' => 'Rendah hingga Sedang'
            ],
            'Leafsmut' => [
                'name' => 'Jamur Daun (Leaf Smut)',
                'description' => 'Penyakit yang disebabkan oleh jamur Tilletia barclayana (Sheath Smut)',
                'symptoms' => [
                    'Bercak coklat kecil pada daun',
                    'Dalam kondisi lanjut terlihat serbuk hitam di dalam bercak',
                    'Daun dapat mengkeriting dan mengering'
                ],
                'treatment' => [
                    'Gunakan benih yang sehat',
                    'Drainase yang baik',
                    'Hindari kepadatan tanaman yang berlebihan',
                    'Aplikasikan fungisida pada tahap awal'
                ],
                'severity' => 'Rendah'
            ]
        ];

        return $diseaseDatabase[$className] ?? [
            'name' => $className,
            'description' => 'Informasi tidak tersedia',
            'symptoms' => [],
            'treatment' => [],
            'severity' => 'Unknown'
        ];
    }
}
