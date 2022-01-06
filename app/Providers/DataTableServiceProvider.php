<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\DataTable;

class DataTableServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('datatable', function() { return new DataTable(); });
    }
}
