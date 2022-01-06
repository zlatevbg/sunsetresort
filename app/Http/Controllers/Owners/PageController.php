<?php namespace App\Http\Controllers\Owners;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Owners\Navigation;

class PageController extends Controller {

    public function __construct()
    {
    }

    public function page(Request $request, Navigation $page, $slug = null)
    {
        if (!$slug) {
            $slug = \Slug::getRouteSlug();
        }

        $page = $page->where('slug', $slug)->where('locale_id', \Locales::getId())->firstOrFail();

        if ($request->ajax() || $request->wantsJson()) {
            $ajax = true;
            $view = \View::make(\Locales::getNamespace() . '.page', compact('ajax', 'page'));
            $sections = $view->renderSections();
            return response()->json([$sections['content']]);
        } else {
            if ($page->slug == 'steering-committee' && \Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user()->email == 'dummy@sunsetresort.bg') {
                $page->content = str_replace('steeringcommittee@sunsetresort.bg', 'dummy@sunsetresort.bg', $page->content);
            }

            return view(\Locales::getNamespace() . '.page', compact('page'));
        }
    }

}
