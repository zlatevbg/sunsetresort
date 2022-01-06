<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Models\Sky\Admin;
use App\Models\Sky\Calendar;
use App\Http\Requests\Sky\CalendarRequest;

class CalendarController extends Controller {

    protected $route = 'calendar';
    protected $datatables;
    protected $multiselect;

    public function __construct()
    {
        $this->datatables = [
            $this->route => [
                'title' => trans(\Locales::getNamespace() . '/datatables.titleCalendar'),
                'url' => \Locales::route($this->route),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'columns' => [
                    [
                        'selector' => $this->route . '.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center vertical-center',
                    ],
                    [
                        'selector' => 'calendar.date',
                        'id' => 'date',
                        'name' => trans(\Locales::getNamespace() . '/datatables.date'),
                        'class' => 'vertical-center',
                        'search' => true,
                        'data' => [
                            'type' => 'sort',
                            'id' => 'date',
                            'date' => 'YYmmdd',
                            'format' => 'F, d',
                        ],
                        'date' => [
                            'format' => '%B, %d',
                        ],
                    ],
                    [
                        'selector' => 'calendar.description',
                        'id' => 'description',
                        'name' => trans(\Locales::getNamespace() . '/datatables.description'),
                        'search' => true,
                        'class' => 'vertical-center',
                    ],
                    [
                        'selector' => '',
                        'id' => 'users',
                        'name' => trans(\Locales::getNamespace() . '/datatables.reminder'),
                        'order' => false,
                        'class' => 'vertical-center',
                        'aggregate' => 'admins',
                        'aggregateFunction' => 'list-admins',
                    ],
                ],
                'orderByColumn' => 1,
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

        $this->multiselect = [
            'admins' => [
                'id' => 'id',
                'name' => 'name',
            ],
        ];
    }

    public function index(DataTable $datatable, Calendar $calendar, Request $request)
    {
        $datatable->setup($calendar, $this->route, $this->datatables[$this->route]);
        $datatables = $datatable->getTables();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($datatables);
        } else {
            return view(\Locales::getNamespace() . '.' . $this->route . '.index', compact('datatables'));
        }
    }

    public function create(Calendar $calendar, Admin $admin, Request $request)
    {
        $table = $this->route;
        if ($request->input('table')) { // magnific popup request
            $table = $request->input('table');
        }

        $this->multiselect['admins']['options'] = $admin->select($this->multiselect['admins']['id'], $this->multiselect['admins']['name'])->get()->toArray();
        $this->multiselect['admins']['selected'] = $calendar->admins->pluck('id')->toArray();

        $multiselect = $this->multiselect;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('table', 'multiselect'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function store(DataTable $datatable, Calendar $calendar, CalendarRequest $request)
    {
        $newCalendar = Calendar::create($request->all());

        if ($newCalendar->id) {
            $newCalendar->admins()->attach($request->input('admins'));

            $successMessage = trans(\Locales::getNamespace() . '/forms.storedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityEvent', 1)]);

            $datatable->setup($calendar, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.createError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityEvent', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function delete(Request $request)
    {
        $table = $this->route;
        if ($request->input('table')) { // magnific popup request
            $table = $request->input('table');
        }

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.delete', compact('table'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function destroy(DataTable $datatable, Calendar $calendar, Request $request)
    {
        $count = count($request->input('id'));

        if ($count > 0 && $calendar->destroy($request->input('id'))) {
            $datatable->setup($calendar, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => trans(\Locales::getNamespace() . '/forms.destroyedSuccessfully'),
                'closePopup' => true
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

    public function edit(Admin $admin, Request $request, $id = null)
    {
        $calendar = Calendar::findOrFail($id);

        $table = $this->route;
        if ($request->input('table')) { // magnific popup request
            $table = $request->input('table');
        }

        $this->multiselect['admins']['options'] = $admin->select($this->multiselect['admins']['id'], $this->multiselect['admins']['name'])->get()->toArray();
        $this->multiselect['admins']['selected'] = $calendar->admins->pluck('id')->toArray();

        $multiselect = $this->multiselect;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('calendar', 'table', 'multiselect'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, CalendarRequest $request)
    {
        $calendar = Calendar::findOrFail($request->input('id'))->first();

        if ($calendar->update($request->all())) {
            $calendar->admins()->sync($request->input('admins'));

            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityEvent', 1)]);

            $datatable->setup($calendar, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityEvent', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }
}
