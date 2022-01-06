<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        /* This however doe not take into account the 'remember me' checkbox.
        if (\Session::has('lastActivityTime')) {
            if (time() - \Session::get('lastActivityTime') > (\Config::get('session.lifetime') * 60)) {
                \Session::forget('lastActivityTime');
                $this->auth->logout();

                $redirect = redirect(\Locales::route('/'))->withErrors([trans('messages.sessionExpired')]);
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['redirect' => $redirect->getTargetUrl()]);
                } else {
                    return $redirect;
                }
            }
        }*/

        if (Auth::guard($guard)->guest()) {
            $redirect = redirect()->guest(\Locales::route('/'))->withErrors([trans('messages.sessionExpired')])->with('session_expired', true);

            if ($request->ajax() || $request->wantsJson()) {
                // return response('Unauthorized.', 401);
                return response()->json(['redirect' => $redirect->getTargetUrl()]);
            } else {
                return $redirect;
            }
        } else {
            if ($guard == env('APP_OWNERS_SUBDOMAIN')) {
                $user = Auth::guard($guard)->user();
                if (!$user->is_active) {
                    Auth::guard($guard)->logout();
                    $redirect = redirect()->guest(\Locales::route('/'))->withErrors([trans('messages.sessionExpired')])->with('session_expired', true);

                    if ($request->ajax() || $request->wantsJson()) {
                        // return response('Unauthorized.', 401);
                        return response()->json(['redirect' => $redirect->getTargetUrl()]);
                    } else {
                        return $redirect;
                    }
                }
            }
        }

        // \Session::put('lastActivityTime', time());

        return $next($request);
    }
}
