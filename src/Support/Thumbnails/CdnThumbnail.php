<?php

namespace Code16\OzuClient\Support\Thumbnails;

class CdnThumbnail extends Thumbnail
{
    public function make(?int $width, ?int $height = null, bool $fit = false): ?string
    {
        return sprintf(
            '%s%s/storage/%s',
            config('ozu-client.cdn_url'),
            $this->generateUrlParameters($width, $height, $fit),
            $this->mediaModel->file_name
        );
    }

    private function generateUrlParameters(?int $width, ?int $height, bool $fit): string
    {
        if (!$fit) {
            if ($width && $height) {
                return sprintf('tr:w-%s,h-%s,c-at_max', $width, $height);
            }
            if ($width) {
                return sprintf('tr:w-%s', $width);
            }
            return sprintf('tr:h-%s', $height ?: 400);
        }

        if ($width && $height) {
            return sprintf('tr:w-%s,h-%s,c-at_max', $width, $height);
        }

        $side = ($width ?: $height) ?: 400;

        return sprintf('tr:w-%s,h-%s,c-maintain_ratio', $side, $side);
    }
}
