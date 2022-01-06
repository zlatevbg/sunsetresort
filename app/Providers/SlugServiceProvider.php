<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Slug;

class SlugServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('slug', function() { return new Slug(); });
    }
}
