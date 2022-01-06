<?php

namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\ResetsPasswords;

class PasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    protected $guard = 'admins';
    protected $broker = 'admins';

    public $redirectPath;

    /**
     * Create a new password controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->redirectPath = \Locales::route();
        $this->subject = trans('passwords.reset_link');
        $this->linkRequestView = \Locales::getNamespace() . '.auth.password';
        $this->resetView = \Locales::getNamespace() . '.auth.reset';
    }

    /**
     * Get the response for after the reset link has been successfully sent.
     *
     * @param  string  $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getSendResetLinkEmailSuccessResponse($response)
    {
        if (\Request::ajax() || \Request::wantsJson()) {
            return response()->json(['success' => trans($response), 'reset' => true]);
        } else {
            return redirect()->back()->withSuccess([trans($response)]);
        }
    }

    /**
     * Get the response for after the reset link could not be sent.
     *
     * @param  string  $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getSendResetLinkEmailFailureResponse($response)
    {
        if (\Request::ajax() || \Request::wantsJson()) {
            return response()->json(['errors' => [trans($response)], 'ids' => ['email']]);
        } else {
            return redirect()->back()->withErrors(['email' => trans($response)]);
        }
    }

    /**
     * Get the response for after a successful password reset.
     *
     * @param  string  $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getResetSuccessResponse($response)
    {
        $redirect = redirect($this->redirectPath());
        if (\Request::ajax() || \Request::wantsJson()) {
            return response()->json(['redirect' => $redirect->getTargetUrl()]);
        } else {
            return $redirect;
        }
    }

    /**
     * Get the response for after a failing password reset.
     *
     * @param  Request  $request
     * @param  string  $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getResetFailureResponse(Request $request, $response)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['errors' => [trans($response)], 'resetExcept' => ['email']]);
        } else {
            return redirect()->back()->withInput($request->only('email'))->withErrors(['email' => trans($response)]);
        }
    }
}
