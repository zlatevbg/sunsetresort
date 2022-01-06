<?php

namespace App\Services;

use Illuminate\Contracts\View\View;

class SkyViewComposer
{
    /**
     * Creates new instance.
     */
    public function __construct()
    {
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with('jsCookies', isset($_COOKIE['jsCookies']) ? json_decode($_COOKIE['jsCookies'], true) : []);
    }
}
