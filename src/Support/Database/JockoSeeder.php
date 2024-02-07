<?php

namespace Code16\JockoClient\Support\Database;

use Illuminate\Database\Seeder;

class JockoSeeder extends Seeder
{
    protected function clearMediaDirectory(): void
    {
        $mediaDirectory = storage_path('app/data/medias');

        if (file_exists($mediaDirectory)) {
            collect(scandir($mediaDirectory))
                ->filter(fn ($file) => !in_array($file, ['.', '..']))
                ->each(fn ($file) => unlink($mediaDirectory . '/' . $file));
        }
    }
}
