<?php

namespace Code16\OzuClient\Deploy\Routing;
use Illuminate\Routing\UrlGenerator as BaseUrlGenerator;


class UrlGenerator extends BaseUrlGenerator
{
    // force relative urls
    public function format($root, $path, $route = null)
    {
        return preg_replace('#^'.$root.'/?#', '/', parent::format($root, $path, $route));
    }
}
