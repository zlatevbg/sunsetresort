<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Models\Sky\Notice;
use App\Models\Sky\Owner;
use App\Http\Requests\Sky\OwnerNoticeRequest;

class OwnerNoticesController extends Controller {

    protected $route = 'owner-notices';
    protected $datatables;
    protected $multiselect;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->datatables = [
            $this->route => [
                'title' => trans(\Locales::getNamespace() . '/datatables.titleNotices'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'columns' => [
                    [
                        'selector' => 'id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center',
                    ],
                    [
                        'id' => 'preview',
                        'name' => trans(\Locales::getNamespace() . '/datatables.preview'),
                        'order' => false,
                        'class' => 'text-center',
                        'width' => '2.50em',
                        'preview' => [
                            'icon' => 'search',
                            'route' => 'notices/preview',
                            'routeParameter' => 'id',
                            'title' => trans(\Locales::getNamespace() . '/datatables.previewNotice'),
                        ],
                    ],
                    [
                        'selector' => 'name',
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.name'),
                        'search' => true,
                    ],
                    [
                        'selector' => 'is_read',
                        'id' => 'is_read',
                        'name' => trans(\Locales::getNamespace() . '/datatables.read'),
                        'order' => false,
                        'class' => 'text-center',
                        'pivot' => [
                            'columns' => ['is_read'],
                            'rules' => [
                                0 => [
                                    'status' => 1,
                                    'icon' => 'off.gif',
                                    'title' => trans(\Locales::getNamespace() . '/datatables.statusUnread'),
                                ],
                                1 => [
                                    'status' => 0,
                                    'icon' => 'on.gif',
                                    'title' => trans(\Locales::getNamespace() . '/datatables.statusRead'),
                                ],
                            ],
                        ],
                    ],
                ],
                'orderByColumn' => 'notices.created_at',
                'order' => 'desc',
                'buttons' => [
                    [
                        'url' => \Locales::route($this->route . '/add'),
                        'class' => 'btn-primary js-create',
                        'icon' => 'plus',
                        'name' => trans(\Locales::getNamespace() . '/forms.addButton'),
                    ],
                    [
                        'url' => \Locales::route($this->route . '/remove'),
                        'class' => 'btn-danger disabled js-destroy',
                        'icon' => 'trash',
                        'name' => trans(\Locales::getNamespace() . '/forms.removeButton'),
                    ],
                ],
            ],
        ];

        $this->multiselect = [
            'notices' => [
                'id' => 'id',
                'name' => 'name',
            ],
        ];
    }

    public function index(DataTable $datatable, Request $request, $id = null)
    {
        $breadcrumbs = [];

        $owner = Owner::findOrFail($id);
        $breadcrumbs[] = ['id' => $owner->id, 'slug' => $owner->id, 'name' => $owner->full_name];
        $breadcrumbs[] = ['id' => 'notices', 'slug' => 'notices', 'name' => trans(\Locales::getNamespace() . '/multiselect.ownerProperties.notices')];

        $datatable->setup($owner->notices(), $this->route, $this->datatables[$this->route]);
        $datatable->setOption('owner', $owner->id);

        $datatables = $datatable->getTables();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($datatables);
        } else {
            return view(\Locales::getNamespace() . '.' . $this->route . '.index', compact('datatables', 'breadcrumbs'));
        }
    }

    public function add(Request $request)
    {
        $table = $this->route;
        if ($request->input('table')) { // magnific popup request
            $table = $request->input('table');
        }

        $owner_id = $request->input('owner') ?: null;

        $this->multiselect['notices']['options'] = Notice::whereDoesntHave('owners', function ($query) use($owner_id) {
            $query->where('owners.id', $owner_id);
        })->get()->toarray();
        $this->multiselect['notices']['selected'] = '';
        $multiselect = $this->multiselect;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.add', compact('table', 'owner_id', 'multiselect'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function save(DataTable $datatable, OwnerNoticeRequest $request)
    {
        $owner = Owner::findOrFail([$request->input('owner')])->first();
        $owner->notices()->attach($request->input('notices'));

        $successMessage = trans(\Locales::getNamespace() . '/forms.savedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityNotices', 1)]);

        $datatable->setup($owner->notices(), $request->input('table'), $this->datatables[$request->input('table')], true);
        $datatable->setOption('url', \Locales::route($this->route));
        $datatables = $datatable->getTables();

        return response()->json($datatables + [
            'success' => $successMessage,
            'closePopup' => true,
        ]);
    }

    public function remove(Request $request)
    {
        $table = $request->input('table');

        $owner_id = $request->input('owner') ?: null;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.remove', compact('table', 'owner_id'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function destroy(DataTable $datatable, Request $request)
    {
        $owner = Owner::findOrFail([$request->input('owner')])->first();

        $count = count($request->input('id'));

        if ($count > 0) {
            $owner->notices()->detach($request->input('id'));

            $datatable->setup($owner->notices(), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => trans(\Locales::getNamespace() . '/forms.removedSuccessfully'),
                'closePopup' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.countError');

            return response()->json(['errors' => [$errorMessage]]);
        }
    }
}
