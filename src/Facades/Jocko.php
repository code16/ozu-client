<?php

namespace Code16\JockoClient\Facades;

use Code16\JockoClient\Client;
use Code16\JockoClient\ClientV3;
use Illuminate\Support\Facades\Facade;

/**
 * @see \Code16\JockoClient\ClientV3
 */
class Jocko extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ClientV3::class;
    }
}
