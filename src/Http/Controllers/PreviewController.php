<?php

namespace Code16\JockoClient\Http\Controllers;

use Code16\JockoClient\Facades\Jocko;
use Illuminate\Support\Facades\Hash;

class PreviewController
{
    public function show()
    {
        if(!Jocko::isPreview() || session()->has('jocko_preview_auth')) {
            return redirect()->to('/');
        }

        if(Hash::check(config('jocko-client.api_key').date('Y-m-d'), request()->get('token'))) {
            session()->put('jocko_preview_auth', true);
            return redirect()->to('/');
        }

        return response()->view('jocko::preview-unauthorized', [], 401);
    }
}
