<?php

namespace Code16\JockoClient\Facades;

use Code16\JockoClient\Client;
use Illuminate\Support\Facades\Facade;

/**
 * @see \Code16\JockoClient\Client
 */
class Jocko extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Client::class;
    }
}
