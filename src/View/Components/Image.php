<?php

namespace Code16\OzuClient\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Image extends Component
{
    public \Code16\OzuClient\Support\Image $image;

    public function __construct(
        public string $src,
        public ?int $thumbnailWidth = null,
        public ?int $thumbnailHeight = null,
    ) {
        $this->image = \Code16\OzuClient\Support\Image::make($src);
    }

    public function render(): View
    {
        return view('ozu::components.image');
    }
}
