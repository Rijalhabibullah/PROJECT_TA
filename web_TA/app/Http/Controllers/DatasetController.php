<?php
namespace App\Http\Controllers;
use App\Models\Dataset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DatasetController extends Controller
{
    public function index() {
        $datasets = Dataset::all();
        // Hitung jumlah per label untuk statistik mini
        $stats = Dataset::selectRaw('label, count(*) as total')->groupBy('label')->get();
        return view('admin.datasets.index', compact('datasets', 'stats'));
    }

    public function store(Request $request) {
        $request->validate([
            'label' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $path = $request->file('image')->store('datasets', 'public');

        Dataset::create([
            'label' => $request->label,
            'image_path' => $path
        ]);

        return back()->with('success', 'Gambar dataset berhasil diupload!');
    }

    public function destroy($id) {
        $data = Dataset::findOrFail($id);
        Storage::disk('public')->delete($data->image_path);
        $data->delete();
        return back()->with('success', 'Data dihapus!');
    }
}