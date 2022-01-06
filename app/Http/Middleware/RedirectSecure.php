<?php

namespace App\Http\Middleware;

use Closure;

class RedirectSecure
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
        $request->setTrustedProxies([$request->getClientIp()]);

        if(!$request->secure()) {
            return redirect()->secure($request->path()); // $request->getRequestUri()
        }
        return $next($request);
    }
}
