<?php

namespace Code16\JockoClient\Eloquent\Concerns;

use Code16\JockoClient\Eloquent\JockoModel;
use Sushi\Sushi;

/**
 * Sushi does not work with extended Model because it uses the same static property to store the connection.
 * We need to manage an array of connections to circumvent the issue.
 * @mixin JockoModel
 */
trait ManagesSushiConnections
{
    protected static array $sushiConnections = [];

    /**
     * @see Sushi::resolveConnection
     */
    public static function resolveConnection($connection = null)
    {
        return static::$sushiConnections[static::class];
    }

    /**
     * @see Sushi::setSqliteConnection
     */
    protected static function setSqliteConnection($database): void
    {
        static::sushiSetSqliteConnection($database);

        static::$sushiConnections[static::class] = static::$sushiConnection;
    }
}
