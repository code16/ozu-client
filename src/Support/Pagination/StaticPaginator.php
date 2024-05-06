<?php

namespace Code16\OzuClient\Support\Pagination;

use Illuminate\Pagination\Paginator;

class StaticPaginator extends Paginator
{
    use StaticPaginatorUrl;
}
