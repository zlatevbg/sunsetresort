<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Locales;

class LocaleServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('locales', function() { return new Locales(); });
    }

    /**
     * Set current locale
     *
     * @return void
     */
    public function boot()
    {
        if (!\Locales::set()) {
            \Redirect::to(trim(\Request::root() . '/' . \Locales::getCurrent() . (\Request::path() != '/' ? '/' . \Request::path() : '') . str_replace(\Request::url(), '', \Request::fullUrl()), '/'), 301)->send();
        }
    }
}
