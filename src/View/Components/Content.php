<?php

namespace Code16\OzuClient\View\Components;

use Code16\ContentRenderer\View\Components\Content as ContentComponent;

class Content extends ContentComponent
{
    public function __construct(
        public ?int $imageThumbnailWidth = null,
        public ?int $imageThumbnailHeight = null,
    ) {
        parent::__construct();
        $this->contentComponentAttributes->put('ozu-image', [
            'thumbnail-width' => $this->imageThumbnailWidth,
            'thumbnail-height' => $this->imageThumbnailHeight,
        ]);
    }
}
