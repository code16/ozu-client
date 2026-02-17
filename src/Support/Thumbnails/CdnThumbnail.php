<?php

namespace Code16\OzuClient\Support\Thumbnails;

abstract class CdnThumbnail extends Thumbnail
{
    public function make(?int $width, ?int $height = null, bool $fit = false): ?ThumbnailResult
    {
        if ($cdnUrl = str(config('ozu-client.cdn_url'))->rtrim('/')) {
            return new ThumbnailResult(sprintf(
                '%s/storage/%s?%s',
                $cdnUrl,
                $this->mediaModel->file_name,
                $this->generateUrlParameters($width, $height, $fit),
            ));
        }

        return null;
    }

    abstract protected function generateUrlParameters(?int $width, ?int $height, bool $fit): string;

    protected function convertCropParamsToPixels()
    {
        if (!$this->mediaModel->filters || !is_array($this->mediaModel->filters) || !isset($this->mediaModel->filters['crop'])) {
            return [];
        }

        $originalWidth = $this->mediaModel->width ?: null;
        $originalHeight = $this->mediaModel->height ?: null;

        if (!$originalWidth || !$originalHeight) {
            return [];
        }

        try {
            $cropWidth = round($this->mediaModel->filters['crop']['width'] * $originalWidth);
            $cropHeight = round($this->mediaModel->filters['crop']['height'] * $originalHeight);
            $cropX = round($this->mediaModel->filters['crop']['x'] * $originalWidth);
            $cropY = round($this->mediaModel->filters['crop']['y'] * $originalHeight);

            return [
                'x' => $cropX, 'y' => $cropY, 'w' => $cropWidth, 'h' => $cropHeight,
            ];
        } catch (\Exception $e) {
            report($e);

            return null;
        }

    }

    public function download(): ?string
    {
        if ($cdnUrl = str(config('ozu-client.cdn_url'))->rtrim('/')) {
            return sprintf(
                '%s/storage/%s',
                $cdnUrl,
                $this->mediaModel->file_name,
            );
        }

        return null;
    }
}
