<?php

namespace Code16\OzuClient\Support\Thumbnails;

class CdnThumbnail extends Thumbnail
{
    public function make(?int $width, ?int $height = null, bool $fit = false): ?string
    {
        if($cdnUrl = str(config('ozu-client.cdn_url'))->rtrim('/')) {
            return sprintf(
                '%s/storage/%s?%s',
                $cdnUrl,
                $this->mediaModel->file_name,
                $this->generateUrlParameters($width, $height, $fit),
            );
        }

        return null;
    }

    private function generateUrlParameters(?int $width, ?int $height, bool $fit): string
    {
        if (!$fit) {
            if ($width && $height) {
                return sprintf('tr=w-%s,h-%s,c-at_max', $width, $height);
            }

            return $width
                ? sprintf('tr=w-%s', $width)
                : sprintf('tr=h-%s', $height ?: 400);
        }

        if ($width && $height) {
            return sprintf('tr=w-%s,h-%s,c-at_max', $width, $height);
        }

        $side = ($width ?: $height) ?: 400;

        return sprintf('tr=w-%s,h-%s,c-maintain_ratio', $side, $side);
    }
}
