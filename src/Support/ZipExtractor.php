<?php

namespace Code16\OzuClient\Support;

use ZipArchive;

class ZipExtractor
{
    public function extract(string $zipPath, string $extractPath): bool
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) === true) {
            $zip->extractTo($extractPath);
            $zip->close();

            return true;
        }

        return false;
    }
}
