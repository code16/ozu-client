<?php

namespace Code16\OzuClient\Database\Factories;

use Code16\OzuClient\Eloquent\Media;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition()
    {
        return [
        ];
    }

    public function image(?string $key = null): self
    {
        return $this
            ->when($key, fn () => $this->state(fn () => ['model_key' => $key]))
            ->state(fn () => [
                ...$key ? ['model_key' => $key] : [],
                'file_name' => sprintf('data/medias/%s.jpg', $this->faker->unique()->slug()),
                'mime_type' => 'image/jpeg',
                'disk' => 'local',
                'size' => $this->faker->numberBetween(100, 100000),
            ]);
    }

    public function file(?string $key = null): self
    {
        return $this
            ->when($key, fn () => $this->state(fn () => ['model_key' => $key]))
            ->state(fn () => [
                'file_name' => sprintf('data/files/%s.pdf', $this->faker->unique()->slug()),
                'mime_type' => 'application/pdf',
                'disk' => 'local',
                'size' => $this->faker->numberBetween(100, 100000),
            ]);
    }

    public function withFile(?string $fileName = null): self
    {
        return $this->state(function (array $attributes) use ($fileName) {
            if ($fileName && file_exists($fileName)) {
                $path = $fileName;
                $fileName = basename($fileName);
            } else {
                $path = str_starts_with($attributes['mime_type'], 'image/')
                    ? $this->getRandomFixtureImagePath()
                    : $this->getRandomFixtureDocumentPath();
                $fileName = basename($attributes['file_name']);
            }

            return [
                'file_name' => function ($attributes) use ($fileName, $path) {
                    $storagePath = isset($attributes['model_type'])
                        ? sprintf(
                            'data/medias/%s/%s/%s',
                            class_basename($attributes['model_type']),
                            $attributes['model_id'],
                            $fileName
                        )
                        : sprintf(
                            'data/medias/embeds/%s-%s.%s',
                            pathinfo($fileName, PATHINFO_FILENAME),
                            uniqid(),
                            pathinfo($fileName, PATHINFO_EXTENSION)
                        );

                    Storage::disk('local')->put($storagePath, file_get_contents($path));

                    return $storagePath;
                },
                'mime_type' => mime_content_type($path),
                'disk' => 'local',
                'size' => filesize($path),
            ];
        });
    }

    private function getRandomFixtureImagePath(): string
    {
        return base_path(
            sprintf(
                'vendor/code16/ozu-client/database/fixtures/images/%s.jpeg',
                rand(1, 26)
            )
        );
    }

    private function getRandomFixtureDocumentPath(): string
    {
        return base_path('vendor/code16/ozu-client/database/fixtures/documents/doc.pdf');
    }
}
