<?php

namespace App\Console\Commands;

use App\Models\Dataset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ImportDatasetImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:dataset {--force : Force import even if records exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import rice leaf disease images into the dataset table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');
        
        // Define disease types and their source directories
        $diseases = [
            'Bacterial Blight' => 'd:\\PROJECT TA\\rice leaf diseases dataset\\Bacterialblight',
            'Brown Spot' => 'd:\\PROJECT TA\\rice leaf diseases dataset\\Brownspot',
            'Leaf Smut' => 'd:\\PROJECT TA\\rice leaf diseases dataset\\Leafsmut',
        ];

        $totalImported = 0;

        foreach ($diseases as $label => $sourcePath) {
            if (!File::exists($sourcePath)) {
                $this->error("Source path not found: $sourcePath");
                continue;
            }

            // Get all image files
            $imageFiles = File::files($sourcePath);
            $imageCount = count($imageFiles);

            $this->info("\n--- Processing: $label ---");
            $this->info("Found $imageCount images in: $sourcePath");

            $bar = $this->output->createProgressBar($imageCount);
            $bar->start();

            $importedCount = 0;

            foreach ($imageFiles as $file) {
                // Check if file is an image
                $extension = strtolower($file->getExtension());
                if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $bar->advance();
                    continue;
                }

                // Generate unique filename to avoid conflicts
                $filename = time() . '_' . md5($file->getPathname()) . '.' . $extension;
                $storagePath = 'datasets/' . $filename;

                try {
                    // Check if file already exists in database
                    $exists = Dataset::where('image_path', $storagePath)->exists();
                    
                    if (!$exists || $force) {
                        // Copy file to storage
                        $fileContents = File::get($file->getPathname());
                        Storage::disk('public')->put($storagePath, $fileContents);

                        // Create or update database record
                        Dataset::updateOrCreate(
                            ['image_path' => $storagePath],
                            ['label' => $label]
                        );

                        $importedCount++;
                    }
                } catch (\Exception $e) {
                    $this->warn("Error processing {$file->getFilename()}: {$e->getMessage()}");
                }

                $bar->advance();
            }

            $bar->finish();
            $this->info("\n✓ Imported $importedCount images with label: $label");
            $totalImported += $importedCount;
        }

        $this->info("\n" . str_repeat('=', 50));
        $this->info("✓ Import Complete!");
        $this->info("Total images imported: $totalImported");
        $this->info("Total dataset records: " . Dataset::count());
        $this->info(str_repeat('=', 50));
    }
}
