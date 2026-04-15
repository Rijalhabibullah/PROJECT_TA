<?php

namespace App\Http\Controllers;

use App\Models\ClassificationHistory;
use App\Models\User;
use Illuminate\Http\Request;

class ClassificationHistoryAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = ClassificationHistory::with('user')->latest();

        if ($request->filled('jenis_penyakit')) {
            $query->where('jenis_penyakit', 'like', '%' . $request->jenis_penyakit . '%');
        }

        $histories = $query->paginate(10)->withQueryString();
        $users = User::orderBy('name')->get();

        return view('admin.classification_histories.index', compact('histories', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'jenis_penyakit' => 'required|string|max:255',
        ]);

        ClassificationHistory::create($validated);

        return back()->with('success', 'Data history klasifikasi berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'jenis_penyakit' => 'required|string|max:255',
        ]);

        $history = ClassificationHistory::findOrFail($id);
        $history->update($validated);

        return back()->with('success', 'Data history klasifikasi berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $history = ClassificationHistory::findOrFail($id);
        $history->delete();

        return back()->with('success', 'Data history klasifikasi berhasil dihapus.');
    }
}
