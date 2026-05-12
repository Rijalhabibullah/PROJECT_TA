<?php
namespace App\Http\Controllers;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            $imagePath = $this->compressAndStoreProductImage($request->file('image'))
                ?? $request->file('image')->store('products', 'public');
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

    private function compressAndStoreProductImage($file): ?string
    {
        if (!function_exists('imagecreatefromjpeg')) {
            return null;
        }

        $mime = $file->getMimeType();
        $sourcePath = $file->getRealPath();

        if (!$sourcePath) {
            return null;
        }

        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($sourcePath);
                break;
            case 'image/webp':
                if (!function_exists('imagecreatefromwebp')) {
                    return null;
                }
                $image = imagecreatefromwebp($sourcePath);
                break;
            default:
                return null;
        }

        if (!$image) {
            return null;
        }

        $maxWidth = 1280;
        $quality = 75;
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width > $maxWidth) {
            $newHeight = (int) round($height * ($maxWidth / $width));
            $resized = imagecreatetruecolor($maxWidth, $newHeight);
            $white = imagecolorallocate($resized, 255, 255, 255);
            imagefill($resized, 0, 0, $white);
            imagecopyresampled(
                $resized,
                $image,
                0,
                0,
                0,
                0,
                $maxWidth,
                $newHeight,
                $width,
                $height
            );
            imagedestroy($image);
            $image = $resized;
        }

        $filename = 'products/' . Str::uuid() . '.jpg';
        $fullPath = storage_path('app/public/' . $filename);

        imagejpeg($image, $fullPath, $quality);
        imagedestroy($image);

        return $filename;
    }
}