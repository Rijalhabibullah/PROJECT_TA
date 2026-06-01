<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PythonClassificationService;

class DebugController extends Controller
{
    public function __construct(
        private readonly PythonClassificationService $classificationService
    ) {
    }

    /**
     * Raw debug endpoint - returns exactly what Python returns
     * POST /api/debug/raw-classify
     */
    public function rawClassify(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            ]);

            $file = $request->file('image');
            $imageContent = file_get_contents($file->getRealPath());
            $base64Image = base64_encode($imageContent);

            \Log::info('DEBUG: Calling Python classification service', [
                'filename' => $file->getClientOriginalName(),
                'size_bytes' => strlen($imageContent),
                'base64_size' => strlen($base64Image),
            ]);

            // Call Python service and get RAW response
            $result = $this->classificationService->classifyFromBase64([
                'image' => $base64Image,
                'filename' => $file->getClientOriginalName(),
            ]);

            // Return EXACT Python response for debugging
            return response()->json([
                'success' => true,
                'message' => 'Raw Python response (no filtering)',
                'debug' => [
                    'filename' => $file->getClientOriginalName(),
                    'timestamp' => now(),
                ],
                'python_response' => $result,
            ], 200);

        } catch (\Exception $e) {
            \Log::error('DEBUG: Raw classify error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error during classification',
                'error' => $e->getMessage(),
                'debug_trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    /**
     * Check Laravel logs for recent errors
     * GET /api/debug/logs
     */
    public function logs(Request $request)
    {
        $lines = $request->input('lines', 50);
        $logFile = storage_path('logs/laravel.log');

        if (!file_exists($logFile)) {
            return response()->json([
                'success' => false,
                'message' => 'Log file not found',
            ], 404);
        }

        $content = file_get_contents($logFile);
        $allLines = explode("\n", $content);
        $lastLines = array_slice($allLines, -$lines);

        return response()->json([
            'success' => true,
            'total_lines' => count($allLines),
            'showing_lines' => count($lastLines),
            'logs' => array_filter($lastLines), // Remove empty lines
        ], 200);
    }

    /**
     * Test image processing
     * POST /api/debug/test-image-processing
     */
    public function testImageProcessing(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            ]);

            $file = $request->file('image');
            $imageContent = file_get_contents($file->getRealPath());

            // Test image loading with PIL/Pillow
            $base64Image = base64_encode($imageContent);

            $pythonCode = <<<'PYTHON'
import base64
import json
from io import BytesIO
from PIL import Image
import numpy as np

image_base64 = """BASE64_IMAGE"""
image_bytes = base64.b64decode(image_base64)
image = Image.open(BytesIO(image_bytes))

result = {
    "success": True,
    "image_info": {
        "mode": str(image.mode),
        "size": list(image.size),
        "format": image.format,
    },
    "numpy_array_shape": str(np.array(image).shape),
    "can_resize": True,
}

print(json.dumps(result))
PYTHON;

            $pythonCode = str_replace('BASE64_IMAGE', $base64Image, $pythonCode);

            \Log::info('Testing image processing with Python');

            return response()->json([
                'success' => true,
                'message' => 'Image processing test',
                'file_info' => [
                    'name' => $file->getClientOriginalName(),
                    'size_bytes' => strlen($imageContent),
                    'mime_type' => $file->getMimeType(),
                ],
                'base64_size' => strlen($base64Image),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
