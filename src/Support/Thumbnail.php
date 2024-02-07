<?php

namespace Code16\JockoClient\Support;

use Code16\JockoClient\Eloquent\Media;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;

class Thumbnail
{
    protected ImageManager $imageManager;
    protected FilesystemManager $storage;
    protected Media $mediaModel;
    protected int $quality = 90;
    protected bool $appendTimestamp = false;

    public function __construct(Media $model)
    {
        $this->mediaModel = $model;
        $this->imageManager = new ImageManager(new Driver());
        $this->storage = app(FilesystemManager::class);
    }

    public function setQuality(int $quality): self
    {
        $this->quality = $quality;

        return $this;
    }

    public function setAppendTimestamp(bool $appendTimestamp = true): self
    {
        $this->appendTimestamp = $appendTimestamp;

        return $this;
    }

    public function make(?int $width, ?int $height = null, array $filters = []): ?string
    {
        if (! $this->mediaModel->disk || ! $this->mediaModel->file_name) {
            return null;
        }

        $thumbnailPath = sprintf(
            'thumbnails/%s/%s-%s_q-%s/%s',
            dirname($this->mediaModel->file_name),
            $width,
            $height,
            $this->quality,
            basename($this->mediaModel->file_name),
        );

        // Strip double /
        $thumbnailPath = Str::replace('//', '/', $thumbnailPath);

        return $this->generateThumbnail(
            $this->mediaModel->disk,
            $this->mediaModel->file_name,
            $thumbnailPath,
            $width, $height
        );
    }

    private function generateThumbnail(
        string $sourceDisk, string $sourceRelativeFilePath,
        string $thumbnailPath, ?int $width, ?int $height): ?string
    {
        if ($width == 0) {
            $width = null;
        }
        if ($height == 0) {
            $height = null;
        }

        $thumbnailDisk = $this->storage->disk('public');

        if (! $thumbnailDisk->exists($thumbnailPath)) {
            // Create thumbnail directories if needed
            if (! $thumbnailDisk->exists(dirname($thumbnailPath))) {
                $thumbnailDisk->makeDirectory(dirname($thumbnailPath));
            }

            try {
                $sourceImg = $this->imageManager->read(
                    $this->storage->disk($sourceDisk)->get($sourceRelativeFilePath),
                );

                // Resize if needed
                $sourceImg->scaleDown($width, $height);

                $thumbnailDisk->put($thumbnailPath, $sourceImg->toJpeg(quality: $this->quality));
            } catch (FileNotFoundException $ex) {
                return null;
            }
        }

        return $thumbnailDisk->url($thumbnailPath)
            .($this->appendTimestamp ? '?'.$thumbnailDisk->lastModified($thumbnailPath) : '');
    }
}
