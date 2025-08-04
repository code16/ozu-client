<?php

namespace Code16\OzuClient\Eloquent;

use Code16\OzuClient\Database\Factories\MediaFactory;
use Code16\OzuClient\Support\Thumbnails\Thumbnail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Schema;
use Number;

class Media extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'medias';

    protected $casts = [
        'custom_properties' => 'array',
        'size' => 'integer',
    ];

    protected static function newFactory()
    {
        return new MediaFactory();
    }

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }

    public function thumbnail(?int $width = null, ?int $height = null, bool $fit = false): ?string
    {
        return app(Thumbnail::class)
            ->forMedia($this)
            ->make($width, $height, $fit);
    }

    public function downloadUrl(): ?string
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

        $size = (int) $this->size;

        return Number::fileSize($size, 1, 3);
    }

    /**
     * @param  string  $key
     * @return mixed|null
     */
    public function getAttribute($key)
    {
        if (!$this->isRealAttribute($key)) {
            return $this->getAttribute('custom_properties')[$key] ?? null;
        }

        return parent::getAttribute($key);
    }

    /**
     * @param  string  $key
     * @param  mixed  $value
     * @return Model
     */
    public function setAttribute($key, $value)
    {
        if (!$this->isRealAttribute($key)) {
            return $this->updateCustomProperty($key, $value);
        }

        return parent::setAttribute($key, $value);
    }

    protected function updateCustomProperty(string $key, $value): self
    {
        $properties = $this->getAttribute('custom_properties');
        $properties[$key] = $value;
        $this->setAttribute('custom_properties', $properties);

        return $this;
    }

    protected function isRealAttribute(string $name): bool
    {
        return Schema::hasColumn($this->getTable(), $name) ?? false;
    }
}
