<?php

namespace Code16\OzuClient\Support\Thumbnails;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Exceptions\DecoderException;
use Intervention\Image\ImageManager;

class LocalThumbnail extends Thumbnail
{
    protected ImageManager $imageManager;

    protected FilesystemManager $storage;

    protected int $quality = 90;

    protected ?int $width;

    protected ?int $height;

    protected bool $fit;

    protected bool $appendTimestamp = true;

    public function __construct()
    {
        $hasGd = extension_loaded('gd') && function_exists('gd_info');
        $hasImagickExt = extension_loaded('imagick') && class_exists(\Imagick::class);

        $driver = null;

        // Try to use Imagick driver first
        if ($hasImagickExt && class_exists(ImagickDriver::class)) {
            try {
                $driver = new ImagickDriver();
            } catch (\Throwable $e) {
                // log pour diagnostic — très utile en prod
                Log::warning('ImagickDriver instantiation failed', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'sapi' => php_sapi_name(),
                    'php_version' => phpversion(),
                ]);
                $driver = null;
            }
        }

        // 2) Fallback sur GD si Imagick non disponible ou instanciation échoue
        if ($driver === null && $hasGd && class_exists(GdDriver::class)) {
            try {
                $driver = new GdDriver();
            } catch (\Throwable $e) {
                Log::warning('GdDriver instantiation failed', [
                    'message' => $e->getMessage(),
                    'sapi' => php_sapi_name(),
                ]);
                $driver = null;
            }
        }

        // 3) Si toujours rien -> exception claire
        if ($driver === null) {
            throw new \RuntimeException('Neither GD nor Imagick driver can be instantiated. Check PHP extensions (gd, imagick) and ImageMagick binaries for the SAPI running this code.');
        }

        $this->imageManager = new ImageManager($driver);
        $this->storage = app(FilesystemManager::class);
    }

    public function make(?int $width, ?int $height = null, bool $fit = false): ?string
    {
        if (!$this->mediaModel->disk || !$this->mediaModel->file_name) {
            return null;
        }

        $this->width = $width ?: null;
        $this->height = $height ?: null;
        $this->fit = $fit;

        $thumbnailPath = sprintf(
            'thumbnails/%s/%s-%s_f%s_q-%s/%s',
            dirname($this->mediaModel->file_name),
            $width,
            $height,
            $fit ? '1' : '0',
            $this->quality,
            basename($this->mediaModel->file_name),
        );

        // Strip double /
        $thumbnailPath = Str::replace('//', '/', $thumbnailPath);

        return $this->generateThumbnail(
            $this->mediaModel->disk,
            $this->mediaModel->file_name,
            $thumbnailPath,
        );
    }

    private function generateThumbnail(string $sourceDisk, string $sourceRelPath, string $thumbnailPath): ?string
    {
        $thumbnailDisk = $this->storage->disk('public');

        if (!$thumbnailDisk->exists($thumbnailPath)) {
            // Create thumbnail directories if needed
            if (!$thumbnailDisk->exists(dirname($thumbnailPath))) {
                $thumbnailDisk->makeDirectory(dirname($thumbnailPath));
            }

            try {
                $sourceImg = $this->imageManager->read(
                    $this->storage->disk($sourceDisk)->get($sourceRelPath),
                );

                if ($this->fit) {
                    $sourceImg->cover($this->width, $this->height ?: $this->width);
                } else {
                    $sourceImg->scaleDown($this->width, $this->height);
                }

                $thumbnailDisk->put($thumbnailPath, $sourceImg->toJpeg(quality: $this->quality));
            } catch (FileNotFoundException|DecoderException) {
                return null;
            }
        }

        return $thumbnailDisk->url($thumbnailPath)
            .($this->appendTimestamp ? '?'.$thumbnailDisk->lastModified($thumbnailPath) : '');
    }

    public function download(): ?string
    {
        $filesDisk = $this->storage->disk('public');

        if (!$filesDisk->exists($this->mediaModel->file_name)) {
            // Create files directories if needed
            if (!$filesDisk->exists(dirname($this->mediaModel->file_name))) {
                $filesDisk->makeDirectory(dirname($this->mediaModel->file_name));
            }

            try {
                $filesDisk->put($this->mediaModel->file_name, $this->storage->disk($this->mediaModel->disk)->get($this->mediaModel->file_name));
            } catch (FileNotFoundException|DecoderException) {
                return null;
            }
        }

        return $this->storage->disk('public')->url($this->mediaModel->file_name);
    }
}
