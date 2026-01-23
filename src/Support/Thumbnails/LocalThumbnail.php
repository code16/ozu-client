<?php

namespace Code16\OzuClient\Support\Thumbnails;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Vips\Driver as VipsDriver;
use Intervention\Image\Exceptions\DecoderException;
use Intervention\Image\ImageManager;
use Log;
use Storage;

class LocalThumbnail extends Thumbnail
{
    protected ImageManager $imageManager;

    protected FilesystemManager $storage;

    protected int $quality = 90;

    protected ?int $width;

    protected ?int $height;

    protected bool $fit;

    public function __construct()
    {
        try {
            $this->imageManager = new ImageManager(new VipsDriver(), strip: true);
        } catch (\Exception $e) {
            Log::error($e);
            $this->imageManager = new ImageManager(new GdDriver());
        }

        $this->storage = app(FilesystemManager::class);
    }

    public function make(?int $width, ?int $height = null, bool $fit = false): ?ThumbnailResult
    {
        if (!$this->mediaModel->disk || !$this->mediaModel->file_name) {
            return null;
        }

        if ($this->mediaModel->mime_type === 'image/gif') {
            // Gif images are not thumbnail’ed
            return $this->gifThumbnail(
                $this->mediaModel->disk,
                $this->mediaModel->file_name
            );
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

    private function generateThumbnail(string $sourceDisk, string $sourceRelPath, string $thumbnailPath): ?ThumbnailResult
    {
        $thumbnailDisk = $this->storage->disk('public');

        if (!$thumbnailDisk->exists($thumbnailPath)) {
            // Create thumbnail directories if needed
            if (!$thumbnailDisk->exists(dirname($thumbnailPath))) {
                $thumbnailDisk->makeDirectory(dirname($thumbnailPath));
            }

            if ($this->shouldOnlyCopy()) {
                $thumbnailDisk->put(
                    $thumbnailPath,
                    Storage::disk('public')->get($sourceRelPath)
                );
            } else {
                try {
                    $sourceImg = $this->imageManager->read(
                        $this->storage->disk($sourceDisk)->get($sourceRelPath),
                    );

                    if ($this->mediaModel->filters) {
                        if ($rotate = Arr::get($this->mediaModel->filters, 'rotate.angle')) {
                            $sourceImg->rotate($rotate);
                        }

                        if ($cropData = Arr::get($this->mediaModel->filters, 'crop')) {
                            $sourceImg->crop(
                                intval(round($sourceImg->width() * $cropData['width'])),
                                intval(round($sourceImg->height() * $cropData['height'])),
                                intval(round($sourceImg->width() * $cropData['x'])),
                                intval(round($sourceImg->height() * $cropData['y'])),
                            );
                        }
                    }

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
        }

        return new ThumbnailResult(
            sprintf(
                '/%s?%s',
                uri($thumbnailDisk->url($thumbnailPath))->path(),
                hash_file('xxh3', $thumbnailDisk->path($thumbnailPath))
            ),
            image: $sourceImg ?? null,
            path: $thumbnailDisk->path($thumbnailPath),
        );
    }

    private function gifThumbnail(string $sourceDisk, string $sourceRelPath): ?ThumbnailResult
    {
        if ($sourceDisk !== 'public' && !Storage::disk('public')->exists($sourceRelPath)) {
            // Copy gif to public disk
            Storage::disk('public')->put($sourceRelPath, Storage::disk($sourceDisk)->get($sourceRelPath));
        }

        return new ThumbnailResult(
            sprintf(
                '/%s?%s',
                uri(Storage::disk('public')->url($sourceRelPath))->path(),
                hash_file('xxh3', Storage::disk('public')->path($sourceRelPath))
            ),
            path: $sourceRelPath
        );
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

        return sprintf(
            '/%s',
            uri($this->storage->disk('public')->url($this->mediaModel->file_name))->path()
        );
    }

    private function shouldOnlyCopy(): bool
    {
        return in_array(
            pathinfo($this->mediaModel->file_name, PATHINFO_EXTENSION),
            [
                'svg',
            ]
        );
    }
}
