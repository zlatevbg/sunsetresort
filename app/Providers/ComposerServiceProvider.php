<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ComposerServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer(
            'sky/master', 'App\Services\SkyViewComposer'
        );

        view()->composer(
            'owners/master', 'App\Services\OwnersViewComposer@master'
        );

        view()->composer(
            ['owners/auth.login', 'owners/auth.password', 'owners/auth.reset'], 'App\Services\OwnersViewComposer@authMaster'
        );
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
