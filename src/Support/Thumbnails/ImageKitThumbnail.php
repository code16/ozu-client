<?php

namespace Code16\OzuClient\Support\Thumbnails;

class ImageKitThumbnail extends CdnThumbnail
{
    protected function generateUrlParameters(?int $width, ?int $height, bool $fit): string
    {
        if (! $fit) {
            if ($width && $height) {
                return sprintf('tr=w-%s,h-%s,c-at_max', $width, $height);
            }

            return $width
                ? sprintf('tr=w-%s', $width)
                : sprintf('tr=h-%s', $height ?: 400);
        }

        if ($width && $height) {
            return sprintf('tr=w-%s,h-%s,c-maintain_ratio', $width, $height);
        }

        $side = ($width ?: $height) ?: 400;

        return sprintf('tr=w-%s,h-%s,c-maintain_ratio', $side, $side);
    }
}
