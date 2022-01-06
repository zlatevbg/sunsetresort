<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Models\Sky\Apartment;
use App\Models\Sky\Owner;
use App\Models\Sky\Ownership;
use App\Services\DataTable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\Sky\ApartmentOwnerRequest;

class ApartmentOwnersController extends Controller {

    protected $route = 'apartment-owners';
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
                'title' => trans(\Locales::getNamespace() . '/datatables.titleOwners'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover table-dropdown',
                'selectors' => ['owners.id as owner', 'owners.comments'],
                'columns' => [
                    [
                        'selector' => 'owners.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center',
                    ],
                    [
                        'id' => 'dropdown',
                        'name' => '',
                        'order' => false,
                        'class' => 'text-center datatables-dropdown',
                        'width' => '1.25em',
                        'dropdown' => [
                            'route' => 'owners',
                            'routeParameter' => 'owner',
                            'title' => trans(\Locales::getNamespace() . '/messages.menu'),
                            'menu' => trans(\Locales::getNamespace() . '/multiselect.ownerProperties'),
                        ],
                        'impersonate' => [
                            'slug' => 'impersonate',
                            'name' => trans(\Locales::getNamespace() . '/messages.impersonateOwner'),
                        ]
                    ],
                    [
                        'selector' => 'owners.first_name',
                        'id' => 'first_name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.name'),
                        'order' => false,
                        'append' => [
                            'selector' => ['owners.last_name'],
                            'text' => 'last_name',
                        ],
                        'info' => 'comments',
                    ],
                    [
                        'selector' => 'country_translations.name',
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.country'),
                        'order' => false,
                        'join' => [
                            'table' => 'country_translations',
                            'localColumn' => 'country_translations.country_id',
                            'constrain' => '=',
                            'foreignColumn' => 'owners.country_id',
                            'whereColumn' => 'country_translations.locale',
                            'whereConstrain' => '=',
                            'whereValue' => \Locales::getCurrent(),
                        ],
                    ],
                    [
                        'selector' => 'owners.email',
                        'id' => 'email',
                        'name' => trans(\Locales::getNamespace() . '/datatables.email'),
                        'order' => false,
                    ],
                    [
                        'selectRaw' => 'CONCAT(COALESCE(owners.phone, ""), " / ", COALESCE(owners.mobile, "")) as phones',
                        'id' => 'phones',
                        'name' => trans(\Locales::getNamespace() . '/datatables.phone'),
                        'order' => false,
                    ],
                    [
                        'selector' => 'owners.is_active',
                        'id' => 'is_active',
                        'name' => trans(\Locales::getNamespace() . '/datatables.status'),
                        'class' => 'text-center',
                        'order' => false,
                        'status' => [
                            'class' => 'change-status',
                            'queue' => 'async-change-status',
                            'route' => 'owners/change-status',
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
            'owners' => [
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
        $breadcrumbs[] = ['id' => 'owners', 'slug' => 'owners', 'name' => trans(\Locales::getNamespace() . '/multiselect.apartmentProperties.owners')];

        $datatable->setup(Ownership::leftJoin('owners', 'ownership.owner_id', '=', 'owners.id')->where('ownership.apartment_id', $apartment->id), $this->route, $this->datatables[$this->route]);
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

        $this->multiselect['owners']['options'] = Owner::selectRaw('owners.id, CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as name')->whereNotExists(function ($query) use ($apartment) {
            $query->from('ownership')->whereRaw('ownership.owner_id = owners.id')->where('ownership.apartment_id', $apartment)->whereNull('ownership.deleted_at');
        })->orderBy('name')->get()->toarray();
        $this->multiselect['owners']['selected'] = '';

        $multiselect = $this->multiselect;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.add', compact('table', 'apartment', 'multiselect'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function save(DataTable $datatable, ApartmentOwnerRequest $request)
    {
        $apartment = Apartment::findOrFail([$request->input('apartment')])->first();

        $now = Carbon::now();

        $owners = [];
        foreach ($request->input('owners') as $owner) {
            array_push($owners, [
                'created_at' => $now,
                'apartment_id' => $apartment->id,
                'owner_id' => $owner,
            ]);
        }
        Ownership::insert($owners);

        $successMessage = trans(\Locales::getNamespace() . '/forms.savedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityOwners', 1)]);

        $datatable->setup(Ownership::leftJoin('owners', 'ownership.owner_id', '=', 'owners.id')->where('ownership.apartment_id', $apartment->id), $request->input('table'), $this->datatables[$request->input('table')], true);
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
            Ownership::where('apartment_id', $apartment->id)->whereIn('owner_id', $request->input('id'))->delete();

            $datatable->setup(Ownership::leftJoin('owners', 'ownership.owner_id', '=', 'owners.id')->where('ownership.apartment_id', $apartment->id), $request->input('table'), $this->datatables[$request->input('table')], true);
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
