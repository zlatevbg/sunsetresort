<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Models\Sky\ExtraService;
use App\Http\Requests\Sky\ExtraServiceRequest;

class ExtraServiceController extends Controller {

    protected $route = 'extra-services';
    protected $datatables;

    public function __construct()
    {
        $this->datatables = [
            'extra-services-categories' => [
                'translation' => true,
                'title' => trans(\Locales::getNamespace() . '/datatables.titleExtraServices'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'columns' => [
                    [
                        'selector' => 'extra_services.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center',
                    ],
                    [
                        'selector' => 'extra_service_translations.name',
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.name'),
                        'search' => true,
                        'join' => [
                            'table' => 'extra_service_translations',
                            'localColumn' => 'extra_service_translations.extra_service_id',
                            'constrain' => '=',
                            'foreignColumn' => 'extra_services.id',
                            'whereColumn' => 'extra_service_translations.locale',
                            'whereConstrain' => '=',
                            'whereValue' => \Locales::getCurrent(),
                        ],
                        'link' => [
                            'route' => $this->route,
                            'routeParameters' => ['id'],
                            'icon' => 'folder-open',
                        ],
                    ],
                ],
                'orderByColumn' => 'name',
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
            $this->route => [
                'translation' => true,
                'title' => trans(\Locales::getNamespace() . '/datatables.titleExtraServices'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'columns' => [
                    [
                        'selector' => 'extra_services.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center',
                    ],
                    [
                        'selector' => 'extra_service_translations.name',
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.name'),
                        'search' => true,
                        'join' => [
                            'table' => 'extra_service_translations',
                            'localColumn' => 'extra_service_translations.extra_service_id',
                            'constrain' => '=',
                            'foreignColumn' => 'extra_services.id',
                            'whereColumn' => 'extra_service_translations.locale',
                            'whereConstrain' => '=',
                            'whereValue' => \Locales::getCurrent(),
                        ],
                    ],
                    [
                        'selector' => 'extra_services.price',
                        'id' => 'price',
                        'name' => trans(\Locales::getNamespace() . '/datatables.price'),
                        'class' => 'text-right',
                        'order' => false,
                    ],
                ],
                'orderByColumn' => 'name',
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

    public function index(DataTable $datatable, ExtraService $extraservice, Request $request, $id = null)
    {
        $breadcrumbs = [];

        if ($id) {
            $service = ExtraService::findOrFail($id);
            $breadcrumbs[] = ['id' => $service->id, 'slug' => $service->id, 'name' => $service->name];

            $datatable->setup($extraservice->where('parent', $id), $this->route, $this->datatables[$this->route]);
            $datatable->setOption('parent', $service->id);
        } else {
            $datatable->setup($extraservice->whereNull('parent'), 'extra-services-categories', $this->datatables['extra-services-categories']);
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

        $parent = $request->input('parent') ?: null;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create' . ($parent ? '' : '-category'), compact('table', 'parent'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function store(DataTable $datatable, ExtraService $extraservice, ExtraServiceRequest $request)
    {
        $parent = $request->input('parent') ?: null;

        $request->merge([
            'parent' => $parent,
        ]);

        $data = \Locales::prepareTranslations($request);

        $newService = ExtraService::create($data);

        if ($newService->id) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.storedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityServices', 1)]);

            $datatable->setup($extraservice->where('parent', $parent), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, [$parent]));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'reset' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.createError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityServices', 1)]);
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

    public function destroy(DataTable $datatable, ExtraService $extraservice, Request $request)
    {
        $count = count($request->input('id'));

        $row = ExtraService::select('parent')->whereIn('id', $request->input('id'))->first();

        if ($count > 0 && $extraservice->destroy($request->input('id'))) {
            $datatable->setup($extraservice->where('parent', $row->parent), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, [$row->parent]));
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
        $service = ExtraService::findOrFail($id);

        $table = $request->input('table');

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create' . ($service->parent ? '' : '-category'), compact('service', 'table', 'parent'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, ExtraServiceRequest $request)
    {
        $extraservice = ExtraService::findOrFail($request->input('id'))->first();

        $data = \Locales::prepareTranslations($request);

        if ($extraservice->update($data)) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityServices', 1)]);

            $datatable->setup(ExtraService::where('parent', $extraservice->parent), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, [$extraservice->parent]));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityServices', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

}
