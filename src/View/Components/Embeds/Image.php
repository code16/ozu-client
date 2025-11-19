<?php

namespace Code16\OzuClient\View\Components\Embeds;

use Code16\OzuClient\Eloquent\Media;
use Code16\OzuClient\Exceptions\OzuClientException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Component;
use Illuminate\View\View;

class Image extends Component
{
    public array $file;

    public ?string $name = null;

    public ?Media $media = null;

    public ?Filesystem $disk = null;

    public bool $exists = false;

    public function __construct(
        string $file,
        public ?string $legend = null,
        public ?int $thumbnailWidth = null,
        public ?int $thumbnailHeight = null,
    ) {
        if ($this->file = json_decode(htmlspecialchars_decode($file), true)) {
            $this->media = Media::make([
                'file_name' => $this->file['file_name'],
                'disk' => $this->file['disk'] ?? null,
                'filters' => $this->file['filters'] ?? null,
            ]);
            $this->disk = Storage::disk($this->media->disk);
            $this->exists = $this->disk->exists($this->media->file_name);
            $this->name = $this->file['name'] ?? basename($this->media->file_name);
        }

        if (!$this->thumbnailWidth && !$this->thumbnailHeight) {
            $this->thumbnailWidth = 500;
        }
    }

    public function render(): View
    {
        if (!$this->media) {
            throw new OzuClientException('Unable to render embedded image: invalid file');
        }

        return view('ozu::components.embeds.ozu-content-image');
    }
}
