<?php

namespace Code16\JockoClient\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Image extends Component
{
    public \Code16\JockoClient\Support\Image $image;

    public function __construct(
        public string $src,
        public ?int $thumbnailWidth = null,
        public ?int $thumbnailHeight = null,
    ) {
        $this->image = \Code16\JockoClient\Support\Image::make($src);
    }

    public function render(): View
    {
        return view('jocko::components.image');
    }
}
