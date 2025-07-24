<?php

namespace Code16\OzuClient\Support\Database;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class OzuSeeder extends Seeder
{
    protected function clearMediaDirectory(): void
    {
        $mediaDirectory = storage_path('app/data/medias');

        if (file_exists($mediaDirectory)) {
            collect(scandir($mediaDirectory))
                ->filter(fn ($file) => ! in_array($file, ['.', '..']))
                ->each(fn ($file) => unlink($mediaDirectory.'/'.$file));
        }
    }

    public function seedVideoEmbed(Model &$model, string $editorColumnName, ?string $videoUrl = null, ?string $legend = null): void
    {
        $editorContent = $model->$editorColumnName;
        $randomVideoUrl = collect([
            'https://www.youtube.com/watch?v=lXKDu6cdXLI',
            'https://www.youtube.com/watch?v=ZBYZHeB67O4',
            'https://www.youtube.com/watch?v=7nQ2oiVqKHw',
        ])->random();

        $videoTag = sprintf(
            '<x-ozu-content-video url="%s" %s ></x-ozu-content-video>',
            $videoUrl ?? $randomVideoUrl,
            $legend ? sprintf('legend="%s"', $legend) : ''
        );

        $model->$editorColumnName = $editorContent.' '.$videoTag;
        if ($model->id !== null) {
            $model->save();
        }
    }

    public function seedQuoteEmbed(Model &$model, string $editorColumnName, ?string $quote = null, ?string $author = null): void
    {
        $editorContent = $model->$editorColumnName;
        $randomQuote = collect([
            ['The only thing we have to fear is fear itself.', 'Franklin D. Roosevelt'],
            ['I think, therefore I am.', 'René Descartes'],
            ['Be the change that you wish to see in the world.', 'Gandhi'],
            ['In the middle of difficulty lies opportunity.', 'Albert Einstein'],
        ])->random();

        $quoteTag = sprintf(
            '<x-ozu-content-quote quote="%s" author="%s"></x-ozu-content-quote>',
            $quote ?? $randomQuote[0],
            $author ?? $randomQuote[1]
        );

        $model->$editorColumnName = $editorContent.' '.$quoteTag;
        if ($model->id !== null) {
            $model->save();
        }
    }
}
