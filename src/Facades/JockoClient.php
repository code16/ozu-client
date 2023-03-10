<?php

namespace Code16\JockoClient\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Code16\JockoClient\JockoClient
 */
class JockoClient extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Code16\JockoClient\JockoClient::class;
    }
}
