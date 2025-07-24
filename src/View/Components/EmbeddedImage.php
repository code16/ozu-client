<?php

namespace Code16\OzuClient\View\Components;

use Code16\OzuClient\Eloquent\Media;
use Code16\OzuClient\Exceptions\OzuClientException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Component;

class EmbeddedImage extends Component
{
    public array $file;

    public ?string $name = null;

    public ?Media $fileModel = null;

    public ?Filesystem $disk = null;

    public bool $exists = false;

    public function __construct(
        string $file,
        public ?string $legend = null,
        public ?int $thumbnailWidth = null,
        public ?int $thumbnailHeight = null,
    ) {
        if ($this->file = json_decode(htmlspecialchars_decode($file), true)) {
            $this->fileModel = Media::make([
                'file_name' => $this->file['file_name'],
                'disk' => $this->file['disk'] ?? null,
                'filters' => $this->file['filters'] ?? null,
            ]);
            $this->disk = Storage::disk($this->fileModel->disk);
            $this->exists = $this->disk->exists($this->fileModel->file_name);
            $this->name = $this->file['name'] ?? basename($this->fileModel->file_name);
        }

        if (! $this->thumbnailWidth && ! $this->thumbnailHeight) {
            $this->thumbnailWidth = 500;
        }
    }

    public function render(): \Illuminate\View\View
    {
        if (! $this->fileModel) {
            throw new OzuClientException('Unable to render embedded image: invalid file');
        }

        return view('ozu::components.embedded-image');
    }
}
