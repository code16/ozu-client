<?php

namespace Code16\JockoClient\Eloquent\Concerns;


use Code16\JockoClient\Facades\Jocko;
use Sushi\Sushi;


trait ManagesSushiCache
{
    public static function bootManagesSushiCache(): void
    {
        $instance = new static;

        if(!Jocko::shouldCache()) {
            $instance->clearSushiCache();
        }
    }

    public function clearSushiCache(): void
    {
        $files = glob($this->sushiCacheDirectory().'/'.config('sushi.cache-prefix', 'sushi').'-*.sqlite');

        foreach ($files as $filename) {
            @unlink($filename);
        }
    }

    /**
     * @see Sushi::sushiCacheDirectory
     */
    protected function sushiCacheDirectory(): string
    {
        return realpath(storage_path('framework/cache'));
    }

    /**
     * @see Sushi::sushiShouldCache
     */
    protected function sushiShouldCache(): bool
    {
        return Jocko::shouldCache();
    }
}
