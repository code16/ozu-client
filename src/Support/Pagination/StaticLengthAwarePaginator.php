<?php

namespace Code16\JockoClient\Support\Pagination;

use Illuminate\Pagination\LengthAwarePaginator;

class StaticLengthAwarePaginator extends LengthAwarePaginator
{
    use StaticPaginatorUrl;
}
