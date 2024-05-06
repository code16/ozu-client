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
            ->state(function (array $attributes) use ($key) {
                return [
                    'model_key' => $key,
                    'file_name' => sprintf('data/medias/%s.jpg', $this->faker->unique()->slug()),
                    'mime_type' => 'image/jpeg',
                    'disk' => 'local',
                    'size' => $this->faker->numberBetween(100, 100000),
                ];
            });
    }

    public function withFile(?string $fileName = null)
    {
        return $this->state(function (array $attributes) use ($fileName) {
            $fileName = $fileName ?: fake()->slug() . '.jpg';
            $path = $this->getRandomFixtureImagePath();

            Storage::disk('local')
                ->put("/data/medias/$fileName", file_get_contents($path));

            return [
                'file_name' => "data/medias/$fileName",
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
}
