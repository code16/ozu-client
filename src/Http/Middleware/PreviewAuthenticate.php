<?php

namespace Code16\JockoClient\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as BaseAuthenticate;

class PreviewAuthenticate extends BaseAuthenticate
{
    public function handle($request, Closure $next, ...$guards)
    {
        $this->authenticate($request, guards: ['jocko-preview']);

        return $next($request);
    }

    protected function redirectTo($request): ?string
    {
        return route('jocko.preview.show');
    }
}
