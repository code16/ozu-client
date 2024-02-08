<?php

namespace Code16\JockoClient\Eloquent;

use Code16\JockoClient\Eloquent\Concerns\CastsCollection;
use Code16\JockoClient\Eloquent\Concerns\ManagesSushiCache;
use Code16\JockoClient\Eloquent\Concerns\ManagesSushiConnections;
use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;


abstract class JockoModel extends Model
{
    use Sushi {
        Sushi::setSqliteConnection as sushiSetSqliteConnection;
    }
//    use ManagesSushiCache {
//        ManagesSushiCache::sushiCacheDirectory insteadof Sushi;
//        ManagesSushiCache::sushiShouldCache insteadof Sushi;
//    }
    use ManagesSushiConnections {
        ManagesSushiConnections::resolveConnection insteadof Sushi;
        ManagesSushiConnections::setSqliteConnection insteadof Sushi;
    }
    use CastsCollection;
}
