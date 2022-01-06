<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Models\Sky\Notice;
use App\Models\Sky\Owner;
use App\Http\Requests\Sky\NoticeRequest;

class NoticeController extends Controller {

    protected $route = 'notices';
    protected $datatables;

    public function __construct()
    {
        $this->datatables = [
            'languages' => [
                'dom' => 'tr',
                'title' => trans(\Locales::getNamespace() . '/datatables.titleLanguages'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'columns' => [
                    [
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.name'),
                    ],
                ],
                'orderByColumn' => 'name',
                'order' => 'asc',
            ],
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
                            'route' => $this->route . '/preview',
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
                        'selector' => '',
                        'id' => 'owners',
                        'name' => trans(\Locales::getNamespace() . '/datatables.owners'),
                        'aggregate' => 'ownersCount',
                        'class' => 'text-center',
                    ],
                    [
                        'selector' => '',
                        'id' => 'read',
                        'name' => trans(\Locales::getNamespace() . '/datatables.read'),
                        'aggregate' => 'ownersRead',
                        'class' => 'text-center',
                    ],
                    [
                        'selector' => 'is_active',
                        'id' => 'is_active',
                        'name' => trans(\Locales::getNamespace() . '/datatables.status'),
                        'class' => 'text-center',
                        'order' => false,
                        'width' => '2.50em',
                        'status' => [
                            'class' => 'change-status',
                            'queue' => 'async-change-status',
                            'route' => $this->route . '/change-status',
                            'rules' => [
                                0 => [
                                    'status' => 1,
                                    'icon' => 'off.gif',
                                    'title' => trans(\Locales::getNamespace() . '/datatables.statusOff'),
                                ],
                                1 => [
                                    'status' => 0,
                                    'icon' => 'on.gif',
                                    'title' => trans(\Locales::getNamespace() . '/datatables.statusOn'),
                                ],
                            ],
                        ],
                    ],
                ],
                'orderByColumn' => 'id',
                'order' => 'asc',
                'buttons' => [
                    [
                        'url' => \Locales::route($this->route . '/create'),
                        'class' => 'btn-primary js-create',
                        'icon' => 'plus',
                        'name' => trans(\Locales::getNamespace() . '/forms.createButton'),
                    ],
                    [
                        'url' => \Locales::route($this->route . '/edit'),
                        'class' => 'btn-warning disabled js-edit',
                        'icon' => 'edit',
                        'name' => trans(\Locales::getNamespace() . '/forms.editButton'),
                    ],
                    [
                        'url' => \Locales::route($this->route . '/delete'),
                        'class' => 'btn-danger disabled js-destroy',
                        'icon' => 'trash',
                        'name' => trans(\Locales::getNamespace() . '/forms.deleteButton'),
                    ],
                ],
            ],
        ];
    }

    public function index(DataTable $datatable, Request $request, $locale = null)
    {
        $breadcrumbs = [];

        if ($locale) {
            $locale = \Locales::getPublicDomain()->locales()->where('locale', $locale)->firstOrFail();

            $breadcrumbs[] = ['id' => 'locales', 'slug' => $locale->locale, 'name' => $locale->name];

            $datatable->setup(Notice::where('locale_id', $locale->id), $this->route, $this->datatables[$this->route]);
            $datatable->setOption('locale', $locale->id);
        } else {
            $datatable->setup(null, 'languages', $this->datatables['languages']);
            $languages = [];
            foreach (\Locales::getPublicDomain()->locales->keyBy('locale')->lists('name', 'locale')->toArray() as $locale => $name) {
                $languages[] = ['name' => '<a href="' . \Locales::route($this->route) . '/' . $locale . '"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . $name . '</a>'];
            }
            $datatable->setOption('data', $languages);
        }

        $datatables = $datatable->getTables();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($datatables);
        } else {
            return view(\Locales::getNamespace() . '.' . $this->route . '.index', compact('datatables', 'breadcrumbs'));
        }
    }

    public function create(Request $request)
    {
        $table = $request->input('table');

        $locale = $request->input('locale') ?: null;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('table', 'locale'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function store(DataTable $datatable, NoticeRequest $request)
    {
        $request->merge([
            'locale_id' => $request->input('locale'),
        ]);

        $newNotice = Notice::create($request->all());

        if ($newNotice->id) {
            $newNotice->owners()->saveMany(Owner::select('id')->where('locale_id', $newNotice->locale_id)->get());

            $successMessage = trans(\Locales::getNamespace() . '/forms.storedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityNotices', 1)]);

            $datatable->setup(Notice::where('locale_id', $request->input('locale')), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.createError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityNotices', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function delete(Request $request)
    {
        $table = $request->input('table');

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.delete', compact('table'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function destroy(DataTable $datatable, Notice $nortice, Request $request)
    {
        $count = count($request->input('id'));

        $row = Notice::select('locale_id')->whereIn('id', $request->input('id'))->first();

        if ($count > 0 && $nortice->destroy($request->input('id'))) {
            $datatable->setup(Notice::where('locale_id', $row->locale_id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => trans(\Locales::getNamespace() . '/forms.destroyedSuccessfully'),
                'closePopup' => true,
            ]);
        } else {
            if ($count > 0) {
                $errorMessage = trans(\Locales::getNamespace() . '/forms.deleteError');
            } else {
                $errorMessage = trans(\Locales::getNamespace() . '/forms.countError');
            }

            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function edit(Request $request, $id = null)
    {
        $notice = Notice::findOrFail($id);

        $table = $request->input('table');

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('notice', 'table'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, NoticeRequest $request)
    {
        $notice = Notice::findOrFail($request->input('id'))->first();

        $request->merge([
            'locale_id' => $notice->locale_id,
        ]);

        if ($notice->update($request->all())) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityNotices', 1)]);

            $datatable->setup(Notice::where('locale_id', $notice->locale_id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityNotices', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function changeStatus($id, $status)
    {
        $notice = Notice::findOrFail($id);

        $notice->is_active = $status;
        $notice->save();

        $href = '';
        $img = '';
        foreach ($this->datatables[$this->route]['columns'] as $column) {
            if ($column['id'] == 'is_active') {
                foreach ($column['status']['rules'] as $key => $value) {
                    if ($key == $status) {
                        $href = \Locales::route($column['status']['route'], [$id, $value['status']]);
                        $img = \HTML::image(\App\Helpers\autover('/img/' . \Locales::getNamespace() . '/' . $value['icon']), $value['title']);
                        break 2;
                    }
                }
            }
        }

        return response()->json(['success' => true, 'href' => $href, 'img' => $img]);
    }

    public function preview(Request $request, $id)
    {
        $notice = Notice::findOrFail($id);

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.preview', compact('notice'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

}
