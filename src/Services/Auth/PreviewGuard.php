<?php

namespace Code16\JockoClient\Services\Auth;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Session\Session;

class PreviewGuard implements Guard
{
    use GuardHelpers;

    public function __construct(protected Session $session)
    {
    }

    public function check()
    {
        return $this->session->has('jocko_preview_auth');
    }

    public function user()
    {
        return null;
    }

    public function validate(array $credentials = [])
    {
    }
}
