<?php

namespace App\Http\Controllers;

use App\Services\PythonClassificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    private PythonClassificationService $classificationService;

    public function __construct(PythonClassificationService $classificationService)
    {
        $this->classificationService = $classificationService;
    }

    /**
     * Get system and model health status
     */
    public function check(): JsonResponse
    {
        $health = [
            'timestamp' => now()->toIso8601String(),
            'laravel' => [
                'status' => 'ok',
                'version' => app()->version(),
                'debug' => config('app.debug'),
                'env' => config('app.env'),
            ],
            'database' => $this->checkDatabase(),
            'python_model' => $this->checkPythonModel(),
            'system' => $this->checkSystemResources(),
        ];

        $allHealthy = $health['database']['status'] === 'ok' && 
                      $health['python_model']['status'] === 'ok';

        return response()->json([
            'healthy' => $allHealthy,
            'health' => $health,
        ], $allHealthy ? 200 : 503);
    }

    /**
     * Run intensive diagnostic check (may take 30+ seconds)
     */
    public function diagnose(): JsonResponse
    {
        try {
            $result = $this->classificationService->health();
            
            return response()->json([
                'success' => true,
                'message' => 'Model health check passed',
                'details' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Model health check failed',
                'error' => $e->getMessage(),
            ], 503);
        }
    }

    private function checkDatabase(): array
    {
        try {
            \DB::connection()->getPdo();
            return [
                'status' => 'ok',
                'driver' => config('database.default'),
                'database' => config('database.connections.mysql.database'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    private function checkPythonModel(): array
    {
        try {
            $pythonExe = env('PYTHON_EXECUTABLE', 'python');
            $modelDir = env('RICE_MODEL_DIR', base_path('../rice leaf diseases dataset'));
            
            return [
                'status' => 'ok',
                'python_executable' => $pythonExe,
                'model_directory' => $modelDir,
                'model_exists' => is_dir($modelDir),
                'note' => 'Full health check available via /api/health/diagnose',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    private function checkSystemResources(): array
    {
        $memory = [];
        if (function_exists('memory_get_usage')) {
            $memory['php_memory_mb'] = round(memory_get_usage(true) / (1024 * 1024), 2);
            $memory['php_peak_mb'] = round(memory_get_peak_usage(true) / (1024 * 1024), 2);
        }

        return [
            'memory' => $memory,
            'disk_free_gb' => round(disk_free_space('/') / (1024**3), 2),
        ];
    }
}
