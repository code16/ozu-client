<?php

namespace Code16\OzuClient\Support\Database;

use Code16\OzuClient\Eloquent\Media;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class OzuSeeder extends Seeder
{
    protected function clearMediaDirectory(): void
    {
        Storage::disk('local')->deleteDirectory('data/medias');
    }

    protected function makeImageEmbed(?Media $media = null, ?string $legend = null): string
    {
        $media ??= Media::factory()->image()->withFile()->make();

        return sprintf(
            '<x-ozu-content-image file="%s" legend="%s"></x-ozu-content-image>',
            e(json_encode([
                'file_name' => $media->file_name,
                'disk' => $media->disk,
                'mime_type' => $media->mime_type,
                'filters' => $media->filters,
            ])),
            $legend ?: ''
        );
    }

    protected function makeFileEmbed(?Media $media = null, ?string $legend = null): string
    {
        $media ??= Media::factory()->image()->withFile()->make();

        return sprintf(
            '<x-ozu-content-file file="%s" legend="%s"></x-ozu-content-file>',
            e(json_encode([
                'file_name' => $media->file_name,
                'disk' => $media->disk,
                'mime_type' => $media->mime_type,
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
