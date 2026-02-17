<?php

namespace Code16\OzuClient\Support\Thumbnails;

class KeyCdnThumbnail extends CdnThumbnail
{
    protected function generateUrlParameters(?int $width, ?int $height, bool $fit): string
    {
        if (
            isset($this->mediaModel->filters)
            && isset($this->mediaModel->filters['crop'])
            && $coords = $this->convertCropParamsToPixels()
        ) {
            return sprintf(
                'crop=%s,%s,%s,%s%s%s',
                $coords['w'],
                $coords['h'],
                $coords['x'],
                $coords['y'],
                $width ? "&width=$width" : '',
                $height ? "&height=$height" : '',
            );
        }

        if ($width && $height) {
            return sprintf('width=%s&height=%s&fit='.($fit ? 'cover' : 'inside'), $width, $height);
        }

        $side = ($width ?: $height) ?: 400;

        return $fit ?
            sprintf('width=%s&height=%s&fit=cover', $side, $side) :
            (
                $width
                    ? sprintf('width=%s', $width)
                    : sprintf('height=%s', $height ?: 400)
            );
    }
}
