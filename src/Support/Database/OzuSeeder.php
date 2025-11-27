<?php

namespace Code16\OzuClient\Support\Database;

use Code16\OzuClient\Eloquent\Media;
use Illuminate\Database\Seeder;

class OzuSeeder extends Seeder
{
    protected function clearMediaDirectory(): void
    {
        $mediaDirectory = storage_path('app/data/medias');

        if (file_exists($mediaDirectory)) {
            collect(scandir($mediaDirectory))
                ->filter(fn ($file) => !in_array($file, ['.', '..']))
                ->each(fn ($file) => unlink($mediaDirectory.'/'.$file));
        }
    }

    protected function makeImageEmbed(?string $legend = null): string
    {
        $media = Media::factory()->image('embed')->withFile()->make();

        return sprintf(
            '<x-ozu-content-image file="%s" legend="%s"></x-ozu-content-image>',
            e(json_encode([
                'file_name' => $media->file_name,
                'disk' => $media->disk,
                'filters' => $media->filters,
            ])),
            $legend ?: ''
        );
    }

    public function makeVideoEmbed(?string $videoUrl = null, ?string $legend = null): string
    {
        $videoUrl = $videoUrl ?: collect(
            [
                'https://www.youtube.com/watch?v=lXKDu6cdXLI',
                'https://www.youtube.com/watch?v=ZBYZHeB67O4',
                'https://www.youtube.com/watch?v=7nQ2oiVqKHw',
            ])->random();

        return sprintf(
            '<x-ozu-content-video url="%s" %s></x-ozu-content-video>',
            $videoUrl,
            $legend ? sprintf('legend="%s"', $legend) : ''
        );
    }

    public function makeQuoteEmbed(?string $quote = null, ?string $author = null): string
    {
        $quote = $quote ?: collect(
            [
                'The only thing we have to fear is fear itself.',
                'I think, therefore I am.',
                'Be the change that you wish to see in the world.',
                'In the middle of difficulty lies opportunity.',
            ])
            ->random();

        $author = $author ?: collect(['Franklin D. Roosevelt', 'René Descartes', 'Gandhi', 'Albert Einstein'])
            ->random();

        return sprintf('<x-ozu-content-quote author="%s">%s</x-ozu-content-quote>', $author, $quote);
    }
}
