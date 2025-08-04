<?php

namespace Code16\OzuClient\View\Components\Embeds;

use Code16\OzuClient\Eloquent\Media;
use Code16\OzuClient\Exceptions\OzuClientException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Component;

class File extends Component
{
    public array $file;

    public ?string $name = null;

    public ?Media $fileModel = null;

    public ?Filesystem $disk = null;

    public bool $exists = false;

    public function __construct(
        string $file,
        public ?string $legend = null
    ) {
        if ($this->file = json_decode(htmlspecialchars_decode($file), true)) {
            $this->fileModel = Media::make([
                'file_name' => $this->file['file_name'],
                'disk' => $this->file['disk'] ?? null,
            ]);
            $this->disk = Storage::disk($this->fileModel->disk);
            $this->exists = $this->disk->exists($this->fileModel->file_name);
            $this->name ??= basename($this->fileModel->file_name);
        }
    }

    public function render(): View
    {
        if (! $this->fileModel) {
            throw new OzuClientException('Unable to render embedded file: invalid file');
        }

        return view('ozu::components.embeds.ozu-content-file');
    }
}
