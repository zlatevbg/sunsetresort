<?php

namespace App\Services;

use Illuminate\Contracts\View\View;
use App\Models\Sky\Navigation;

class OwnersViewComposer
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
    public function master(View $view)
    {
        $nav = \Locales::getMenu('main-navigation-category', true);

        $user = \Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user();

        if ($user->email == 'dummy@sunsetresort.bg') {
            unset($nav['info']['children']['rental-options']);
        }

        $apartments = $user->ownership()->select('project_translations.name as project', 'apartments.number')->leftJoin('apartments', 'apartments.id', '=', 'ownership.apartment_id')->leftJoin('project_translations', 'project_translations.project_id', '=', 'apartments.project_id')->where('project_translations.locale', \Locales::getCurrent())->get();
        if ($apartments->count() > 1) {
            $apartments = $apartments->pluck('project', 'number')->all();
            foreach ($apartments as $number => $project) {
                $nav['apartments']['children'][] = [
                    'slug' => $number,
                    'url' => \Locales::route('apartments', $number),
                    'name' => '<table class="table table-nav"><tbody><tr><td>' . $project . '</td><td>' . $number . '</td></tr></tbody></table>',
                ];
            }
        } else {
            if ($apartments->first()) {
                $nav['apartments']['url'] .= '/' . $apartments->first()->number;
            }
        }

        if (isset($nav['newsletters'])) {
            $nav['newsletters']['url'] = \Locales::route('newsletters');
        }

        if (isset($nav['polls'])) {
            $nav['polls']['url'] = \Locales::route('polls');
        }

        if (isset($nav['condominium']) && isset($nav['condominium']['children']) && isset($nav['condominium']['children']['steering-committee-newsletters'])) {
            $nav['condominium']['children']['steering-committee-newsletters']['url'] = \Locales::route('steering-committee-newsletters');
        }

        if (isset($nav['bookings'])) {
            $nav['bookings']['url'] = \Locales::route('bookings');
        }

        $notices = $user->notices()->where('is_read', 0)->get();

        if ($notices->count() > \Session::get('noticesDismissed', 0)) {
            \Session::forget('noticesDismissed');
        }

        if (\Session::has('noticesDismissed')) {
            \Session::put('noticesDismissed', $notices->count());
        }

        $tooltip = '-notices';
        if (!$notices->count() || \Session::has('noticesDismissed') || \Locales::getMenu(\Slug::getRouteSlug())['type'] == 'notices') {
            $tooltip = null;
        }

        $view->with(compact('nav', 'notices', 'tooltip'));
    }

    public function authMaster(View $view)
    {
        $page = Navigation::where('slug', \Slug::getRouteSlug())->where('locale_id', \Locales::getId())->first();
        $view->with(compact('page'));
    }
}
