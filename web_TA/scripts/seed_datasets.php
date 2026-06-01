<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sourceBase = env('RICE_MODEL_DIR');
if (!$sourceBase) {
    $sourceBase = base_path('../rice leaf diseases dataset');
}

$labels = [
    'Bacterialblight' => 'Bacterialblight',
    'Brownspot' => 'Brownspot',
    'Healthy' => 'Healthy',
    'Leafsmut' => 'Leafsmut',
];

$existing = DB::table('datasets')->pluck('image_path')->all();
$existingSet = array_fill_keys($existing, true);

$totalInserted = 0;
$totalCopied = 0;

foreach ($labels as $label => $folder) {
    $sourceDir = $sourceBase . DIRECTORY_SEPARATOR . $folder;
    if (!is_dir($sourceDir)) {
        echo "[SKIP] Folder not found: {$sourceDir}\n";
        continue;
    }

    $destDir = storage_path('app/public/datasets/' . $label);
    if (!is_dir($destDir)) {
        mkdir($destDir, 0775, true);
    }

    $files = scandir($sourceDir);
    if ($files === false) {
        echo "[SKIP] Cannot read folder: {$sourceDir}\n";
        continue;
    }

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $sourcePath = $sourceDir . DIRECTORY_SEPARATOR . $file;
        if (!is_file($sourcePath)) {
            continue;
        }

        if (!preg_match('/\.(jpg|jpeg|png)$/i', $file)) {
            continue;
        }

        $destPath = $destDir . DIRECTORY_SEPARATOR . $file;
        if (!file_exists($destPath)) {
            if (!copy($sourcePath, $destPath)) {
                echo "[WARN] Failed to copy: {$sourcePath}\n";
                continue;
            }
            $totalCopied++;
        }

        $relativePath = 'storage/datasets/' . $label . '/' . $file;
        if (isset($existingSet[$relativePath])) {
            continue;
        }

        DB::table('datasets')->insert([
            'label' => $label,
            'image_path' => $relativePath,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $existingSet[$relativePath] = true;
        $totalInserted++;
    }
}

echo "Done. Copied: {$totalCopied}, Inserted: {$totalInserted}\n";
