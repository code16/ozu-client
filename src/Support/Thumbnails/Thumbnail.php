<?php

namespace Code16\JockoClient\Support\Thumbnails;

use Code16\JockoClient\Eloquent\Media;

abstract class Thumbnail
{
    protected Media $mediaModel;

    public function forMedia(Media $model): self
    {
        $this->mediaModel = $model;

        return $this;
    }

    abstract public function make(?int $width, ?int $height = null, bool $fit = false): ?string;
}
