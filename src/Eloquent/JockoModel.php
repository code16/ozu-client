<?php

namespace Code16\JockoClient\Eloquent;

use Code16\JockoClient\Eloquent\Concerns\CastsCollection;
use Code16\JockoClient\Eloquent\Concerns\ManagesSushiCache;
use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

abstract class JockoModel extends Model
{
    use Sushi {
        Sushi::setSqliteConnection as sushiSetSqliteConnection;
    }
    use ManagesSushiCache {
        ManagesSushiCache::sushiCacheDirectory insteadof Sushi;
    }
    use CastsCollection;

    protected static array $sushiConnections = [];

    public static function resolveConnection($connection = null)
    {
        return static::$sushiConnections[static::class];
    }

    protected static function setSqliteConnection($database): void
    {
        static::sushiSetSqliteConnection($database);

        static::$sushiConnections[static::class] = static::$sushiConnection;
    }
}
