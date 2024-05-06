<?php

namespace Code16\OzuClient\Facades;

use Code16\OzuClient\Client;
use Illuminate\Support\Facades\Facade;

/**
 * @see \Code16\OzuClient\Client
 */
class Ozu extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Client::class;
    }
}
