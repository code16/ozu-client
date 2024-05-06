<?php

namespace Code16\OzuClient\Support\Pagination;

use Illuminate\Pagination\LengthAwarePaginator;

class StaticLengthAwarePaginator extends LengthAwarePaginator
{
    use StaticPaginatorUrl;
}
