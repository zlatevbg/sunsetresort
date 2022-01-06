<?php namespace App\Http\Controllers\Owners;

use App\Http\Controllers\Controller;
use App\Models\Owners\Navigation;

class DashboardController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function dashboard(Navigation $page)
    {
        $page = $page->where('slug', \Locales::getDomain()->route)->where('locale_id', \Locales::getId())->firstOrFail();
        return view(\Locales::getNamespace() . '.dashboard', compact('page'));
    }

}
