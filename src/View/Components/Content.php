<?php

namespace Code16\JockoClient\View\Components;

use Code16\ContentRenderer\View\Components\Content as ContentComponent;

class Content extends ContentComponent
{
    public function __construct(
        public ?int $imageThumbnailWidth = null,
        public ?int $imageThumbnailHeight = null,
    ) {
        parent::__construct();
        $this->contentComponentAttributes->put('jocko-image', [
            'thumbnail-width' => $this->imageThumbnailWidth,
            'thumbnail-height' => $this->imageThumbnailHeight,
        ]);
    }
}
