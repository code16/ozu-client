<?php

namespace Code16\OzuClient\Support\Thumbnails;

class KeyCdnThumbnail extends CdnThumbnail
{
    protected function generateUrlParameters(?int $width, ?int $height, bool $fit): string
    {
        if (! $fit) {
            if ($width && $height) {
                return sprintf('width=%s&height=%s&fit=inside', $width, $height);
            }

            return $width
                ? sprintf('width=%s', $width)
                : sprintf('height=%s', $height ?: 400);
        }

        if ($width && $height) {
            return sprintf('width=%s&height=%s&fit=cover', $width, $height);
        }

        $side = ($width ?: $height) ?: 400;

        return sprintf('width=%s&height=%s&fit=cover', $side, $side);
    }
}
