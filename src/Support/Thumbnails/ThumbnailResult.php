<?php

namespace Code16\OzuClient\Support\Thumbnails;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Stringable;

class ThumbnailResult implements Stringable
{
    public function __construct(
        protected string $url,
        protected ?Image $image = null,
        protected ?string $path = null
    ) {
    }

    public function width(): ?int
    {
        return $this->image()?->width();
    }

    public function height(): ?int
    {
        return $this->image()?->height();
    }

    protected function image(): ?Image
    {
        if (!$this->image && $this->path) {
            try {
                $this->image = (new ImageManager(new Driver()))->read($this->path);
            } catch (\Intervention\Image\Exceptions\RuntimeException $e) {
                return null;
            }
        }

        return $this->image;
    }

    public function __toString()
    {
        return $this->url;
    }
}
