<?php

namespace Code16\JockoClient\Support\Pagination;

use Illuminate\Pagination\Paginator;

class StaticPaginator extends Paginator
{
    use StaticPaginatorUrl;
}
