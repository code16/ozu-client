<?php

namespace Code16\OzuClient\Support\Thumbnails;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Imagick\Driver;
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
        $this->imageManager = new ImageManager(new Driver());
        $this->storage = app(FilesystemManager::class);
    }

    public function make(?int $width, ?int $height = null, bool $fit = false): ?string
    {
        if (! $this->mediaModel->disk || ! $this->mediaModel->file_name) {
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
            if (! $thumbnailDisk->exists(dirname($thumbnailPath))) {
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
            } catch (FileNotFoundException $ex) {
                return null;
            }
        }

        return $thumbnailDisk->url($thumbnailPath)
            .($this->appendTimestamp ? '?'.$thumbnailDisk->lastModified($thumbnailPath) : '');
    }
}
