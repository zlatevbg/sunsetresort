<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Models\Sky\Apartment;
use App\Models\Sky\Keyholder;
use App\Models\Sky\KeyholderAccess;
use App\Services\DataTable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\Sky\ApartmentKeyholderRequest;

class ApartmentKeyholdersController extends Controller {

    protected $route = 'apartment-keyholders';
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
                'dom' => 'tr',
                'title' => trans(\Locales::getNamespace() . '/datatables.titleKeyholders'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'selectors' => ['keyholders.id as keyholder', 'keyholder_access.comments'],
                'columns' => [
                    [
                        'selector' => 'keyholder_access.id',
                        'id' => 'checkbox',
                        'order' => false,
                        'class' => 'text-center',
                        'replace' => [
                            'id' => 'id',
                            'rules' => [
                                0 => [
                                    'column' => 'deleted_at',
                                    'value' => null,
                                    'checkbox' => true,
                                ],
                            ],
                        ],
                    ],
                    [
                        'selector' => 'keyholders.name',
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.name'),
                        'order' => false,
                        'info' => ['comments' => 'comments'],
                    ],
                    [
                        'selector' => 'keyholder_access.created_at',
                        'id' => 'created_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.dateFrom'),
                        'order' => false,
                    ],
                    [
                        'selector' => 'keyholder_access.deleted_at',
                        'id' => 'deleted_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.dateTo'),
                        'order' => false,
                    ],
                ],
                'orderByColumn' => 'id',
                'order' => 'asc',
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

        $this->multiselect = [
            'keyholders' => [
                'id' => 'id',
                'name' => 'name',
            ],
        ];
    }

    public function index(DataTable $datatable, Request $request, $id = null)
    {
        $breadcrumbs = [];

        $apartment = Apartment::findOrFail($id);
        $breadcrumbs[] = ['id' => $apartment->id, 'slug' => $apartment->id, 'name' => $apartment->number];
        $breadcrumbs[] = ['id' => 'keyholders', 'slug' => 'keyholders', 'name' => trans(\Locales::getNamespace() . '/multiselect.apartmentProperties.keyholders')];

        $datatable->setup(KeyholderAccess::withTrashed()->leftJoin('keyholders', 'keyholder_access.keyholder_id', '=', 'keyholders.id')->where('keyholder_access.apartment_id', $apartment->id), $this->route, $this->datatables[$this->route]);
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

        $this->multiselect['keyholders']['options'] = Keyholder::select('keyholders.id', 'keyholders.name')->whereNotExists(function ($query) use ($apartment) {
            $query->from('keyholder_access')->whereRaw('keyholder_access.keyholder_id = keyholders.id')->where('keyholder_access.apartment_id', $apartment)->whereNull('keyholder_access.deleted_at');
        })->orderBy('name')->get()->toarray();
        $this->multiselect['keyholders']['selected'] = '';

        $multiselect = $this->multiselect;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.add', compact('table', 'apartment', 'multiselect'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function save(DataTable $datatable, ApartmentKeyholderRequest $request)
    {
        $apartment = Apartment::findOrFail([$request->input('apartment')])->first();

        $now = Carbon::now();

        $keyholders = [];
        foreach ($request->input('keyholders') as $keyholder) {
            array_push($keyholders, [
                'created_at' => $now,
                'apartment_id' => $apartment->id,
                'keyholder_id' => $keyholder,
                'comments' => $request->input('comments'),
            ]);
        }
        KeyholderAccess::insert($keyholders);

        $successMessage = trans(\Locales::getNamespace() . '/forms.savedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityKeyholders', 1)]);

        $datatable->setup(KeyholderAccess::withTrashed()->leftJoin('keyholders', 'keyholder_access.keyholder_id', '=', 'keyholders.id')->where('keyholder_access.apartment_id', $apartment->id), $request->input('table'), $this->datatables[$request->input('table')], true);
        $datatable->setOption('url', \Locales::route($this->route, true));
        $datatables = $datatable->getTables();

        return response()->json($datatables + [
            'success' => $successMessage,
            'closePopup' => true,
        ]);
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
            KeyholderAccess::whereIn('id', $request->input('id'))->delete();

            $datatable->setup(KeyholderAccess::withTrashed()->leftJoin('keyholders', 'keyholder_access.keyholder_id', '=', 'keyholders.id')->where('keyholder_access.apartment_id', $apartment->id), $request->input('table'), $this->datatables[$request->input('table')], true);
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
        $keyholder = KeyholderAccess::withTrashed()->findOrFail($id);
        $table = $request->input('table');

        $this->multiselect['keyholders']['options'] = Keyholder::select('keyholders.id', 'keyholders.name')->where('id', $keyholder->keyholder_id)->get()->toarray();
        $this->multiselect['keyholders']['selected'] = $keyholder->keyholder_id;

        $multiselect = $this->multiselect;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.add', compact('table', 'keyholder', 'multiselect'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, ApartmentKeyholderRequest $request)
    {
        $keyholder = KeyholderAccess::withTrashed()->findOrFail($request->input('id'))->first();

        if ($keyholder->update($request->all())) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityKeyholders', 1)]);

            $datatable->setup(KeyholderAccess::withTrashed()->leftJoin('keyholders', 'keyholder_access.keyholder_id', '=', 'keyholders.id')->where('keyholder_access.apartment_id', $keyholder->apartment_id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, true));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityKeyholders', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

}
