<?php

namespace Code16\OzuClient\View\Components;

use Code16\Embed\ServiceContract;
use Code16\Embed\ServiceFactory;
use Code16\Embed\ValueObjects\Url;
use Illuminate\View\Component;

class Quote extends Component
{
    protected ServiceContract $service;

    public function __construct(
        public string $quote,
        public ?string $author = null,
    ) {}

    public function render()
    {
        return view('ozu::components.quote', [
            'quote' => $this->quote,
            'author' => $this->author,
        ]);
    }
}
