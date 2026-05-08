<?php
namespace App\Http\Controllers;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index() {
        $products = Product::all();
        return view('admin.products.index', compact('products'));
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'image' => 'image|mimes:jpeg,png,jpg|max:2048',
            'marketplace_link' => 'required|url|max:2048'
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'image' => $imagePath,
            'marketplace_link' => $request->marketplace_link
        ]);

        return back()->with('success', 'Produk berhasil ditambahkan!');
    }

    public function destroy($id) {
        $product = Product::findOrFail($id);
        if($product->image) Storage::disk('public')->delete($product->image);
        $product->delete();
        return back()->with('success', 'Produk dihapus!');
    }

    public function apiIndex(Request $request) {
        $products = Product::all()->map(function ($product) {
            $imageUrl = $product->image ? url(Storage::url($product->image)) : null;

            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'image_url' => $imageUrl,
                'marketplace_link' => $product->marketplace_link,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }
}