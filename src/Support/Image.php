<?php

namespace Code16\OzuClient\Support;

class Image
{
    public function __construct(
        public string $src,
        public ?string $legend,
    ) {
    }

    public static function make(string|array $data): static
    {
        if(is_string($data)) {
            return new static($data, null);
        }

        return new static($data['url'], $data['legend'] ?? null);
    }

    public static function collection(?array $visuals): ImageCollection
    {
        return ImageCollection::make($visuals)->map(fn ($visual) => static::make($visual));
    }

    public function thumbnail(?int $width = null, ?int $height = null, ?float $scale = 1, bool $crop = false): string
    {
        $width = $width ? $width * $scale : null;
        $height = $height ? $height * $scale : null;

        // https://docs.imagekit.io/features/image-transformations/resize-crop-and-other-transformations
        return $this->src . '?tr=' . implode(',', array_keys(array_filter([
            "w-$width" => $width,
            "h-$height" => $height,
            "c-at_max" => !$crop,
        ])));
    }

    public function thumbnailFit(?int $width = null, ?int $height = null, ?float $scale = 1): string
    {
        return $this->thumbnail($width, $height, $scale, true);
    }

    // https://image-component.nextjs.gallery/ "responsive" mode
    public function responsiveSrcSet(array $sizes = [640, 750, 828, 1080, 1200, 1920, 2048, 3840]): string
    {
        return collect($sizes)->map(fn ($size) => "{$this->thumbnail($size)} {$size}w")->join(', ');
    }

    public function __toString(): string
    {
        return $this->src;
    }
}
