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

    public function image(string $key): Factory
    {
        return $this
            ->state(fn () => [
                'model_key' => $key,
                'file_name' => sprintf('data/medias/%s.jpg', $this->faker->unique()->slug()),
                'mime_type' => 'image/jpeg',
                'disk' => 'local',
                'size' => $this->faker->numberBetween(100, 100000),
            ]);
    }

    public function file(string $key): Factory
    {
        return $this
            ->state(fn () => [
                'model_key' => $key,
                'file_name' => sprintf('data/files/%s.jpg', $this->faker->unique()->slug()),
                'mime_type' => 'image/jpeg',
                'disk' => 'local',
                'size' => $this->faker->numberBetween(100, 100000),
            ]);
    }

    public function withFile(?string $fileName = null, string $type = 'image')
    {
        return $this->state(function (array $attributes) use ($fileName, $type) {
            $fileName = $fileName ?: fake()->slug().($type === 'image' ? '.jpg' : '.pdf');
            $path = ($type === 'image' ? $this->getRandomFixtureImagePath() : $this->getRandomFixtureDocumentPath());

            Storage::disk('local')
                ->put('/data/'.($type === 'image' ? 'medias' : 'files')."/$fileName", file_get_contents($path));

            return [
                'file_name' => 'data/'.($type === 'image' ? 'medias' : 'files')."/$fileName",
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
