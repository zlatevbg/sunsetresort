<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;

class StartSessionExtend extends StartSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return parent::handle($request, $next); // defer to the right stuff
    }

    /**
     * Store the current URL for the request if necessary.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Session\SessionInterface  $session
     * @return void
     */
    protected function storeCurrentUrl(Request $request, $session)
    {
        if (!str_contains($request->fullUrl(), '_debugbar') && !str_contains($request->fullUrl(), 'download/')) { // don't store debugbar urls in the _previous session url
            parent::storeCurrentUrl($request, $session);
        }
    }
}
