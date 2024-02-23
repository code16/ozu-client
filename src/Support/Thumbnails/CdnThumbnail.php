<?php

namespace Code16\JockoClient\Support\Thumbnails;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;

class CdnThumbnail extends Thumbnail
{
    public function make(?int $width, ?int $height = null, array $filters = []): ?string
    {
        return $this->generatePublicUrlForMedia();
    }

    private function generatePublicUrlForMedia(bool $isImage = true): string
    {
//        // Normalize media with path and filters
//        $media = (object) $this->mediaModel;
//        $media->path = $media->path ?? $media->file_name;

        if ($isImage) {
//            $media->filters = json_decode(json_encode(
//                $media->filters ?? $media->custom_properties['filters'] ?? null
//            ));

            return $this->applyCdnToPath(
                'storage/'.$this->copyStorageFileToPublicWithFilters()
            );
        }

        return url('storage/'.$this->copyStorageFileToPublicWithFilters());
    }

    private function copyStorageFileToPublicWithFilters(): string
    {
        $path = $this->mediaModel->file_name;
        $filters = $this->mediaModel->filters;

        $publicPath = $filters
            ? sprintf('%s/%s/%s', dirname($path), md5(serialize($filters)), basename($path))
            : $path;

        if (!Storage::disk('public')->exists($publicPath)) {
            if (Storage::disk('local')->exists($path)) {
                $fileContent = Storage::disk('local')->get($path);

                Storage::disk('public')->put(
                    $publicPath,
                    $filters
                        ? $this->handleImageTransformations($fileContent, $filters)
                        : $fileContent
                );
            }
        }

        return $publicPath;
    }

    private function handleImageTransformations($fileContent, ?object $filters): string
    {
        if (!$filters) {
            return $fileContent;
        }

        $img = app(ImageManager::class)->make($fileContent);

        if ($rotate = $filters->rotate?->angle) {
            $img->rotate($rotate);
        }

        if ($cropData = $filters->crop) {
            $img->crop(
                intval(round($img->width() * $cropData->width)),
                intval(round($img->height() * $cropData->height)),
                intval(round($img->width() * $cropData->x)),
                intval(round($img->height() * $cropData->y)),
            );
        }

        return $img->encode();
    }

    private function applyCdnToPath(string $path): string
    {
        return 'https://ik.imagekit.io/rb2r7d3xo/jocko3-staging/'.$path;
    }
}
