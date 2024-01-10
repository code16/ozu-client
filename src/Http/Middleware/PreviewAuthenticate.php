<?php

namespace Code16\JockoClient\Http\Middleware;

use Closure;
use Code16\JockoClient\Facades\Jocko;
use Illuminate\Auth\Middleware\Authenticate as BaseAuthenticate;

class PreviewAuthenticate extends BaseAuthenticate
{
    public function handle($request, Closure $next, ...$guards)
    {
        if(Jocko::isExporting()) {
            return $next($request);
        }

        $this->authenticate($request, guards: ['jocko-preview']);

        return $next($request);
    }

    protected function redirectTo($request): ?string
    {
        return route('jocko.preview.show');
    }
}
