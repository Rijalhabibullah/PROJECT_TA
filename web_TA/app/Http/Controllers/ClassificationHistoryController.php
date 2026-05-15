<?php

namespace App\Http\Controllers;

use App\Models\Classification;
use Illuminate\Http\Request;

class ClassificationHistoryController extends Controller
{
    /**
     * Get semua classification history
     * GET /api/classifications
     */
    public function index(Request $request)
    {
        $query = Classification::orderBy('created_at', 'desc');

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->input('user_id'));
        }

        $perPage = (int) $request->input('per_page', 15);
        if ($perPage < 1) {
            $perPage = 15;
        }
        if ($perPage > 1000) {
            $perPage = 1000;
        }

        $classifications = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Classification history retrieved',
            'data' => $classifications,
        ], 200);
    }

    /**
     * Get detail classification tertentu
     * GET /api/classifications/{id}
     */
    public function show($id)
    {
        $classification = Classification::find($id);

        if (!$classification) {
            return response()->json([
                'success' => false,
                'message' => 'Classification not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $classification,
        ], 200);
    }

    /**
     * Delete classification
     * DELETE /api/classifications/{id}
     */
    public function destroy($id)
    {
        $classification = Classification::find($id);

        if (!$classification) {
            return response()->json([
                'success' => false,
                'message' => 'Classification not found',
            ], 404);
        }

        // Delete image if exists
        if ($classification->image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($classification->image_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($classification->image_path);
        }

        $classification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Classification deleted successfully',
        ], 200);
    }

    /**
     * Get statistics
     * GET /api/classifications/stats
     */
    public function stats()
    {
        $total = Classification::count();
        
        $byDisease = Classification::groupBy('disease_name')
            ->selectRaw('disease_name, COUNT(*) as count')
            ->get();

        $averageConfidence = Classification::avg('confidence');

        return response()->json([
            'success' => true,
            'data' => [
                'total_classifications' => $total,
                'average_confidence' => round($averageConfidence * 100, 2) . '%',
                'by_disease' => $byDisease,
            ],
        ], 200);
    }
}
