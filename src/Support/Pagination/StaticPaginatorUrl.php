<?php

namespace Code16\OzuClient\Support\Pagination;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;

trait StaticPaginatorUrl
{
    public function url($page)
    {
        return URL::toRoute(
            Request::route(),
            [
                ...Request::route()->parameters(),
                'page' => $page > 1 ? $page : null,
            ],
            false
        );
    }
}
