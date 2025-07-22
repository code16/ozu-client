<?php

namespace Code16\OzuClient\View\Components;

use Code16\Embed\ServiceContract;
use Code16\Embed\ServiceFactory;
use Code16\Embed\ValueObjects\Url;
use Illuminate\View\Component;

class Video extends Component
{
    protected ServiceContract $service;

    public function __construct(
        public string $url,
        public ?string $legend = null,
    ) {
        $url = new Url($this->url);
        $this->service = ServiceFactory::getByUrl($url) ?: ServiceFactory::getFallback($url);
    }

    public function render()
    {
        return view('ozu::components.video', [
            'url' => $this->url,
            'legend' => $this->legend,
        ]);
    }
}
