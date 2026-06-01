<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Classification;
use App\Models\ClassificationHistory;
use App\Models\User;
use App\Services\PythonClassificationService;

class ClassificationController extends Controller
{
    public function __construct(
        private readonly PythonClassificationService $classificationService
    ) {
    }

    /**
     * Menerima gambar dan mengklasifikasi melalui service internal Laravel
     * POST /api/classify
     */
    public function classify(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // Max 5MB
                'location_address' => 'nullable|string|max:255',
                'location_lat' => 'nullable|numeric',
                'location_lng' => 'nullable|numeric',
                'kabupaten' => 'nullable|string|max:255',
                'kecamatan' => 'nullable|string|max:255',
                'kelurahan' => 'nullable|string|max:255',
            ]);

            // Ambil file gambar
            $file = $request->file('image');
            
            // Baca content gambar sebagai base64
            $imageContent = file_get_contents($file->getRealPath());
            $base64Image = base64_encode($imageContent);

            // Jalankan klasifikasi melalui service lokal (tanpa Flask API)
            $result = $this->classificationService->classifyFromBase64([
                'image' => $base64Image,
                'filename' => $file->getClientOriginalName(),
            ]);

            if (!($result['success'] ?? false)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghubungi model classification',
                    'error' => $result['message'] ?? 'Unknown error',
                ], 500);
            }

            // DISABLED: Leafiness check temporarily for debugging
            // $minLeafiness = 0.12;
            // if (isset($result['leafiness']) && $result['leafiness'] < $minLeafiness) {
            //     return 422
            // }

            // Tambahkan informasi detail tentang penyakit
            $diseaseInfo = $this->getDiseaseInfo($result['predicted_class']);

            // Skip database save for now - just return prediction result
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
                'notes' => 'nullable|string|max:500',
                'user_id' => 'nullable|exists:users,id',
                'user_name' => 'nullable|string|max:255',
                'location_address' => 'nullable|string|max:255',
                'location_lat' => 'nullable|numeric',
                'location_lng' => 'nullable|numeric',
                'kabupaten' => 'nullable|string|max:255',
                'kecamatan' => 'nullable|string|max:255',
                'kelurahan' => 'nullable|string|max:255',
            ]);

            // Simpan gambar
            $file = $request->file('image');
            $storagePath = $file->store('classifications', 'public');

            // Baca content gambar sebagai base64
            $imageContent = file_get_contents($file->getRealPath());
            $base64Image = base64_encode($imageContent);

            // Jalankan klasifikasi melalui service lokal (tanpa Flask API)
            $result = $this->classificationService->classifyFromBase64([
                'image' => $base64Image,
                'filename' => $file->getClientOriginalName(),
            ]);

            if (!($result['success'] ?? false)) {
                // Hapus file yang sudah disimpan jika klasifikasi gagal
                Storage::disk('public')->delete($storagePath);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghubungi model classification',
                    'error' => $result['message'] ?? 'Unknown error',
                ], 500);
            }

            // DISABLED: Leafiness check temporarily for debugging
            // $minLeafiness = 0.12;
            // if (isset($result['leafiness']) && $result['leafiness'] < $minLeafiness) {
            //     return 422
            // }

            // DISABLED TEMPORARY: All validations disabled for emergency debugging
            // Will re-enable after root cause found
            
            \Log::info('DEBUG: Raw prediction result - classifyAndSave', [
                'predicted_class' => $result['predicted_class'] ?? 'MISSING',
                'confidence' => $result['confidence'] ?? 'MISSING',
                'all_predictions' => $result['all_predictions'] ?? 'MISSING',
                'full_result' => json_encode($result)
            ]);

            $diseaseInfo = $this->getDiseaseInfo($result['predicted_class']);

            // Skip database save for now - just return prediction result
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
     * Klasifikasi gambar dari URL
     * POST /api/classification/classify-from-url
     */
    public function classifyFromUrl(Request $request)
    {
        try {
            $request->validate([
                'image_url' => 'required|url|max:2048',
                'notes' => 'nullable|string|max:500',
                'save' => 'nullable|boolean',
            ]);

            $imageUrl = $request->input('image_url');
            $result = $this->classificationService->classifyFromUrl([
                'image_url' => $imageUrl,
            ]);

            if (!($result['success'] ?? false)) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Gagal memproses gambar dari URL',
                ], 400);
            }

            $diseaseInfo = $this->getDiseaseInfo($result['predicted_class']);

            if ($request->boolean('save')) {
                $urlPath = parse_url($imageUrl, PHP_URL_PATH);
                $filename = is_string($urlPath) && $urlPath !== ''
                    ? basename($urlPath)
                    : 'from_url_image';

                Classification::create([
                    'filename' => $filename,
                    'predicted_class' => $result['predicted_class'],
                    'confidence' => $result['confidence'],
                    'all_predictions' => $result['all_predictions'],
                    'disease_name' => $diseaseInfo['name'],
                    'severity' => $diseaseInfo['severity'],
                    'notes' => $request->input('notes', 'Classification from URL'),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Klasifikasi dari URL berhasil',
                'data' => [
                    'url' => $imageUrl,
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
     * Health check klasifikasi service
     * GET /api/classification/health
     */
    public function health()
    {
        try {
            $result = $this->classificationService->health();

            if ($result['status'] !== 'ok') {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Model tidak siap',
                    'model_info' => $result,
                ], 503);
            }

            return response()->json([
                'success' => true,
                'message' => 'Koneksi ke model classification berhasil',
                'model_info' => $result,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat melakukan health check model: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Informasi model klasifikasi
     * GET /api/classification/info
     */
    public function info()
    {
        try {
            $result = $this->classificationService->info();

            if (!($result['model_loaded'] ?? false)) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Model tidak tersedia',
                    'data' => $result,
                ], 503);
            }

            return response()->json([
                'success' => true,
                'message' => 'Informasi model berhasil diambil',
                'data' => $result,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil informasi model: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Backward-compatible endpoint test (alias untuk health)
     * GET /api/classification/test
     */
    public function testConnection()
    {
        return $this->health();
    }

    /**
     * Extract kabupaten, kecamatan, kelurahan dari location_address string
     * Format Indonesia: "Kelurahan, Kecamatan ..., Kabupaten ..., Provinsi, Negara"
     * Contoh: "Sumbersari, Kecamatan Sumbersari, Kabupaten Jember, Jawa Timur, Indonesia"
     */
    private function extractLocationComponents($locationAddress, $lat = null, $lng = null)
    {
        $components = [
            'kabupaten' => null,
            'kecamatan' => null,
            'kelurahan' => null,
        ];

        if (!$locationAddress) {
            return $components;
        }

        // Extract "Kabupaten [name]" atau "Kota [name]"
        if (preg_match('/(?:Kabupaten|Kota)\s+([^,]+)/', $locationAddress, $matches)) {
            $components['kabupaten'] = trim($matches[1]);
        }

        // Extract "Kecamatan [name]"
        if (preg_match('/Kecamatan\s+([^,]+)/', $locationAddress, $matches)) {
            $components['kecamatan'] = trim($matches[1]);
        }

        // Extract first part (before first comma) as kelurahan
        $parts = explode(',', $locationAddress);
        if (!empty($parts)) {
            $kelurahan = trim($parts[0]);
            if ($kelurahan && !preg_match('/^(Kecamatan|Kabupaten|Kota|Provinsi)/', $kelurahan)) {
                $components['kelurahan'] = $kelurahan;
            }
        }

        // Fallback: If kabupaten not found in address, use lat/lng to determine it
        // For Jember area (coordinates around -8.1656155, 113.7212891)
        if (!$components['kabupaten'] && $lat && $lng) {
            $lat = (float) $lat;
            $lng = (float) $lng;
            
            // Jember region boundaries (approximate)
            if ($lat >= -8.35 && $lat <= -7.85 && $lng >= 113.50 && $lng <= 114.00) {
                $components['kabupaten'] = 'Jember';
            }
        }

        return $components;
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
            'Healthy' => [
                'name' => 'Daun Sehat',
                'description' => 'Daun padi dalam kondisi sehat tanpa penyakit',
                'symptoms' => [
                    'Tidak ada bercak atau lesi pada daun',
                    'Warna daun hijau cerah dan seragam',
                    'Tekstur daun normal tanpa perubahan'
                ],
                'treatment' => [
                    'Lanjutkan pemeliharaan rutin',
                    'Pantau kesehatan tanaman secara berkala',
                    'Terapkan praktik budidaya yang baik',
                    'Pertahankan kondisi lingkungan optimal'
                ],
                'severity' => 'Tidak Ada'
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
