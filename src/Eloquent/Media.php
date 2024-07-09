<?php

namespace Code16\OzuClient\Eloquent;

use Code16\OzuClient\Database\Factories\MediaFactory;
use Code16\OzuClient\Support\Thumbnails\LocalThumbnail;
use Code16\OzuClient\Support\Thumbnails\Thumbnail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Media extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'medias';

    protected static function newFactory()
    {
        return new MediaFactory();
    }

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }

    public function thumbnail(int $width = null, int $height = null, bool $fit = false): ?string
    {
        return app(Thumbnail::class)
            ->forMedia($this)
            ->make($width, $height, $fit);
    }

    public function download(): ?string
    {
        return app(Thumbnail::class)
            ->forMedia($this)
            ->download();
    }

    public function humanReadableSize($precision = 2): ?string
    {
        if ($this->size < 0) {
            return null;
        }

        if ($this->size >= 0) {
            $size = (int) $this->size;
            $base = log($size) / log(1024);
            $suffixes = array(' bytes', ' KB', ' MB', ' GB', ' TB');

            return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
        } else {
            return $this->size;
        }
    }
}
