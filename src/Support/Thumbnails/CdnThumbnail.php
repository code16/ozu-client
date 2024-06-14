<?php

namespace Code16\OzuClient\Support\Thumbnails;

abstract class CdnThumbnail extends Thumbnail
{
    public function make(?int $width, ?int $height = null, bool $fit = false): ?string
    {
        if($cdnUrl = str(config('ozu-client.cdn.url'))->rtrim('/')) {
            return sprintf(
                '%s/storage/%s?%s',
                $cdnUrl,
                $this->mediaModel->file_name,
                $this->generateUrlParameters($width, $height, $fit),
            );
        }

        return null;
    }

    abstract protected function generateUrlParameters(?int $width, ?int $height, bool $fit): string;
}
