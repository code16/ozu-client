<?php

namespace Code16\JockoClient\Database\Factories;

use Code16\JockoClient\Eloquent\Media;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition()
    {
        return [
            'file_name' => sprintf('%s/%s/%s.jpg', $this->faker->word, $this->faker->word, $this->faker->word),
            'mime_type' => 'image/jpeg',
            'disk' => 'local',
            'size' => $this->faker->numberBetween(100, 100000),
        ];
    }

    public function withFile(string $path, bool $randomFileName = true)
    {
        return $this->state(function (array $attributes) use ($path, $randomFileName) {
            $type = class_basename($attributes['model_type']);
            $modelId = $attributes['model_id'];
            $filename = basename($path);
            if ($randomFileName) {
                $filename = sprintf(
                    '%s-%s.%s',
                    $this->faker->word,
                    $this->faker->word,
                    \Str::afterLast($filename, '.')
                );
            }

            Storage::disk('local')
                ->put("/data/$type/$modelId/$filename", file_get_contents($path));

            return [
                'file_name' => "data/$type/$modelId/$filename",
            ];
        });
    }
}
