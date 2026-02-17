<?php

namespace Code16\OzuClient\Support\Thumbnails;

class ImageKitThumbnail extends CdnThumbnail
{
    public function make(?int $width, ?int $height = null, bool $fit = false): ?ThumbnailResult
    {
        if ($cdnUrl = str(config('ozu-client.cdn_url'))->rtrim('/')) {
            $urlParams = $this->generateUrlParameters($width, $height, $fit);

            return new ThumbnailResult(sprintf(
                '%s%s/storage/%s',
                $cdnUrl,
                !empty($urlParams) ? '/'.$urlParams : '',
                $this->mediaModel->file_name,
            ));
        }

        return null;
    }

    protected function generateUrlParameters(?int $width, ?int $height, bool $fit): string
    {

        if (
            isset($this->mediaModel->filters)
            && isset($this->mediaModel->filters['crop'])
            && $coords = $this->convertCropParamsToPixels()
        ) {
            return sprintf(
                'tr:w-%s,h-%s,cm-extract,x-%s,y-%s%s%s%s%s',
                $coords['w'],
                $coords['h'],
                $coords['x'],
                $coords['y'],
                ($width || $height) ? ':' : '',
                ($width ? 'w-'.$width : ''),
                ($width && $height) ? ',' : '',
                ($height ? 'h-'.$height.',c-'.($fit ? 'maintain_ratio' : 'at_max') : '')
            );
        }

        if ($width && $height) {
            return sprintf('tr:w-%s,h-%s,'.($fit ? 'c-maintain_ratio' : 'at_max'), $width, $height);
        }

        $side = ($width ?: $height) ?: 400;

        return $fit ?
            sprintf('tr:w-%s,h-%s,c-maintain_ratio', $side, $side)
            : (
                $width
                    ? sprintf('tr:w-%s', $width)
                    : sprintf('tr:h-%s', $height ?: 400)
            );
    }
}
