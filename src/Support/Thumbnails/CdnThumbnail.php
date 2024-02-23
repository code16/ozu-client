<?php

namespace Code16\JockoClient\Support\Thumbnails;

class CdnThumbnail extends Thumbnail
{
    public function make(?int $width, ?int $height = null, array $filters = []): ?string
    {
        return sprintf(
            'https://ik.imagekit.io/rb2r7d3xo/jocko3-staging/%s/storage/%s',
            sprintf('tr:w-%s,h-%s', $width, $height),
            $this->mediaModel->file_name
        );
    }
}
