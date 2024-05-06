<?php

namespace Code16\OzuClient\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class File extends Component
{
    public function __construct(
        public string $name,
        public string $href,
    ) {
    }

    public function render(): View
    {
        return view('ozu::components.file');
    }
}
