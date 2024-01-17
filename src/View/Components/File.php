<?php

namespace Code16\JockoClient\View\Components;

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
        return view('jocko::components.file');
    }
}
