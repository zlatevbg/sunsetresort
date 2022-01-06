<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\FineUploader;

class FineUploaderServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('fineuploader', function() { return new FineUploader(); });
    }
}
