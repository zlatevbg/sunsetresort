<?php namespace App\Http\Controllers\Owners;

use App\Http\Controllers\Controller;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Models\Owners\Notice;

class NoticeController extends Controller {

    protected $route = 'notices';
    protected $datatables;

    public function __construct()
    {
        $this->datatables = [
            $this->route => [
                'class' => 'table-checkbox table-striped table-bordered table-hover responsive no-wrap',
                'selectors' => [$this->route . '.id', 'notice_owner.is_read'],
                'columns' => [
                    [
                        'selector' => $this->route . '.name',
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.name'),
                        'search' => true,
                        'order' => false,
                        'link' => [
                            'icon' => 'file',
                            'route' => $this->route,
                            'routeParameter' => 'id',
                            'class' => 'js-popup',
                            'rules' => [
                                0 => [
                                    'column' => 'is_read',
                                    'value' => 0,
                                    'class' => 'js-notice-unread',
                                ],
                                1 => [
                                    'column' => 'is_read',
                                    'value' => 1,
                                ],
                            ],
                        ],
                    ],
                ],
                'orderByColumn' => $this->route . '.created_at',
                'order' => 'desc',
            ],
        ];
    }

    public function index(DataTable $datatable, Notice $notice, Request $request, $id = null)
    {
        if ($id) {
            $notice = \Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user()->notices()->findOrFail($id);

            $metaTitle = $notice->name;
            $metaDescription = $notice->name;

            $breadcrumbs = [];
            $breadcrumbs[] = ['id' => $notice->id, 'slug' => $notice->id, 'name' => $notice->name];

            \Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user()->notices()->updateExistingPivot($notice->id, ['is_read' => 1]);

            if ($request->ajax() || $request->wantsJson()) {
                $ajax = true;
                $view = \View::make(\Locales::getNamespace() . '/profile.notice', compact('ajax', 'notice', 'breadcrumbs'));
                $sections = $view->renderSections();
                return response()->json([$sections['content']]);
            } else {
                return view(\Locales::getNamespace() . '/profile.notice', compact('notice', 'breadcrumbs', 'metaTitle', 'metaDescription'));
            }
        } else {
            $owner_id = \Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user()->id;

            $datatable->setup($notice->join('notice_owner', function($join) use ($owner_id) {
                $join->on($this->route . '.id', '=', 'notice_owner.notice_id')->where('notice_owner.owner_id', '=', $owner_id);
            })->where($this->route . '.is_active', 1), $this->route, $this->datatables[$this->route]);

            $datatables = $datatable->getTables();

            return view(\Locales::getNamespace() . '/profile.' . $this->route, compact('datatables'));
        }
    }

    public function dismiss(Request $request)
    {
        $notices = \Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user()->notices()->where('is_read', 0)->get();

        $request->session()->put('noticesDismissed', $notices->count());

        return response()->json([
            'success' => true,
            'qtip' => '.tooltip-notices',
        ]);
    }

}
