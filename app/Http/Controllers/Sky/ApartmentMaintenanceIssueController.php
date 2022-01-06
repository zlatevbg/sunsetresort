<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Models\Sky\Apartment;
use App\Models\Sky\MaintenanceIssue;
use App\Services\DataTable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\Sky\ApartmentMaintenanceIssueRequest;

class ApartmentMaintenanceIssueController extends Controller {

    protected $route = 'apartment-maintenance-issues';
    protected $datatables;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->datatables = [
            $this->route => [
                'title' => trans(\Locales::getNamespace() . '/datatables.titleMaintenance'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'selectors' => ['comments'],
                'columns' => [
                    [
                        'selector' => 'id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center',
                    ],
                    [
                        'selector' => 'created_at',
                        'id' => 'created_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.dateCreated'),
                        'data' => [
                            'type' => 'sort',
                            'id' => 'created_at',
                            'date' => 'YYmmdd',
                        ],
                    ],
                    [
                        'selector' => 'updated_at',
                        'id' => 'updated_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.dateUpdated'),
                        'data' => [
                            'type' => 'sort',
                            'id' => 'updated_at',
                            'date' => 'YYmmdd',
                        ],
                    ],
                    [
                        'selector' => 'title',
                        'id' => 'title',
                        'name' => trans(\Locales::getNamespace() . '/datatables.title'),
                        'search' => true,
                        'order' => false,
                        'info' => ['comments' => 'comments'],
                    ],
                    [
                        'selector' => 'status',
                        'id' => 'status',
                        'name' => trans(\Locales::getNamespace() . '/datatables.status'),
                        'trans' => 'maintenanceStatusOptions',
                        'search' => true,
                    ],
                    [
                        'selector' => 'responsibility',
                        'id' => 'responsibility',
                        'name' => trans(\Locales::getNamespace() . '/datatables.responsibility'),
                        'search' => true,
                        'trans' => 'maintenanceResponsibilityOptions',
                    ],
                    [
                        'selector' => 'is_visible',
                        'id' => 'is_visible',
                        'name' => trans(\Locales::getNamespace() . '/datatables.infoPortal'),
                        'class' => 'text-center',
                        'order' => false,
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
                'orderByColumn' => 1,
                'order' => 'desc',
                'buttons' => [
                    [
                        'url' => \Locales::route($this->route . '/add'),
                        'class' => 'btn-primary js-create',
                        'icon' => 'plus',
                        'name' => trans(\Locales::getNamespace() . '/forms.addButton'),
                    ],
                    [
                        'url' => \Locales::route($this->route . '/edit'),
                        'class' => 'btn-warning disabled js-edit',
                        'icon' => 'edit',
                        'name' => trans(\Locales::getNamespace() . '/forms.editButton'),
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
    }

    public function index(DataTable $datatable, Request $request, $id = null)
    {
        $breadcrumbs = [];

        $apartment = Apartment::findOrFail($id);
        $breadcrumbs[] = ['id' => $apartment->id, 'slug' => $apartment->id, 'name' => $apartment->number];
        $breadcrumbs[] = ['id' => 'maintenance-issues', 'slug' => 'maintenance-issues', 'name' => trans(\Locales::getNamespace() . '/multiselect.apartmentProperties.maintenance-issues')];

        $datatable->setup(MaintenanceIssue::where('apartment_id', $apartment->id), $this->route, $this->datatables[$this->route]);
        $datatable->setOption('apartment', $apartment->id);

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

        $apartment = $request->input('apartment') ?: null;

        $status = trans(\Locales::getNamespace() . '/multiselect.maintenanceStatusOptions');

        $responsibility[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $responsibility = $responsibility + trans(\Locales::getNamespace() . '/multiselect.maintenanceResponsibilityOptions');

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.add', compact('table', 'apartment', 'status', 'responsibility'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function save(DataTable $datatable, ApartmentMaintenanceIssueRequest $request)
    {
        $apartment = Apartment::findOrFail([$request->input('apartment')])->first();

        $request->merge([
            'apartment_id' => $apartment->id,
        ]);

        $issue = MaintenanceIssue::create($request->all());

        if ($issue->id) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.savedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityMaintenanceIssues', 1)]);

            $datatable->setup(MaintenanceIssue::where('apartment_id', $apartment->id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, true));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.createError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityMaintenanceIssues', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function remove(Request $request)
    {
        $table = $request->input('table');

        $apartment = $request->input('apartment') ?: null;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.remove', compact('table', 'apartment'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function destroy(DataTable $datatable, Request $request)
    {
        $apartment = Apartment::findOrFail([$request->input('apartment')])->first();

        $count = count($request->input('id'));

        if ($count > 0) {
            MaintenanceIssue::whereIn('id', $request->input('id'))->delete();

            $datatable->setup(MaintenanceIssue::where('apartment_id', $apartment->id), $request->input('table'), $this->datatables[$request->input('table')], true);
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

    public function edit(Request $request, $id = null)
    {
        $maintenance = MaintenanceIssue::findOrFail($id);
        $table = $request->input('table');

        $status = trans(\Locales::getNamespace() . '/multiselect.maintenanceStatusOptions');

        $responsibility[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $responsibility = $responsibility + trans(\Locales::getNamespace() . '/multiselect.maintenanceResponsibilityOptions');

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.add', compact('table', 'maintenance', 'status', 'responsibility'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, ApartmentMaintenanceIssueRequest $request)
    {
        $maintenance = MaintenanceIssue::findOrFail($request->input('id'))->first();

        if ($maintenance->update($request->all())) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityMaintenanceIssues', 1)]);

            $datatable->setup(MaintenanceIssue::where('apartment_id', $maintenance->apartment_id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, true));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityMaintenanceIssues', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function changeStatus($id, $status)
    {
        $maintenance = MaintenanceIssue::findOrFail($id);

        $maintenance->is_visible = $status;
        $maintenance->save();

        $href = '';
        $img = '';
        foreach ($this->datatables[$this->route]['columns'] as $column) {
            if ($column['id'] == 'is_visible') {
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

}
