<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait CompressesImages
{
    /**
     * Compress and store image without reducing its resolution.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $folder
     * @param int $quality
     * @return string|null Path to the stored file relative to public disk, or null on failure
     */
    protected function compressAndStoreImage($file, string $folder, int $quality = 80): ?string
    {
        if (!function_exists('imagecreatefromjpeg')) {
            return null;
        }

        $mime = $file->getMimeType();
        $sourcePath = $file->getRealPath();

        if (!$sourcePath) {
            return null;
        }

        // Create image resource based on mime type
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = @imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $image = @imagecreatefrompng($sourcePath);
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $image = @imagecreatefromwebp($sourcePath);
                } else {
                    return null;
                }
                break;
            default:
                return null;
        }

        if (!$image) {
            return null;
        }

        // We do NOT resize the image to keep the original resolution!
        // We only compress using GD's quality settings.
        
        $width = imagesx($image);
        $height = imagesy($image);

        // Create a new true color image to copy the source image onto.
        // This ensures transparency is converted to a clean white background rather than black.
        $outputImage = imagecreatetruecolor($width, $height);
        
        // Setup transparency preservation / background color for PNG/WebP conversion
        $white = imagecolorallocate($outputImage, 255, 255, 255);
        imagefill($outputImage, 0, 0, $white);
        
        imagecopy($outputImage, $image, 0, 0, 0, 0, $width, $height);

        $filename = $folder . '/' . Str::uuid() . '.jpg';
        $fullPath = storage_path('app/public/' . $filename);

        // Ensure target directory exists
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Compress and save as JPEG to reduce file size significantly
        $saved = imagejpeg($outputImage, $fullPath, $quality);

        // Clean up resources
        imagedestroy($image);
        imagedestroy($outputImage);

        return $saved ? $filename : null;
    }
}
