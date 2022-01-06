<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Models\Sky\Apartment;
use App\Models\Sky\Owner;
use App\Models\Sky\Ownership;
use App\Models\Sky\Project;
use App\Models\Sky\Building;
use App\Models\Sky\Floor;
use Carbon\Carbon;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Http\Requests\Sky\OwnerApartmentRequest;

class OwnerApartmentsController extends Controller {

    protected $route = 'owner-apartments';
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
                'title' => trans(\Locales::getNamespace() . '/datatables.titleApartments'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover table-dropdown',
                'selectors' => ['apartments.id as apartment', 'apartments.comments'],
                'columns' => [
                    [
                        'selector' => 'ownership.id',
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
                            'route' => 'apartments',
                            'routeParameter' => 'apartment',
                            'title' => trans(\Locales::getNamespace() . '/messages.menu'),
                            'menu' => trans(\Locales::getNamespace() . '/multiselect.apartmentProperties'),
                        ],
                    ],
                    [
                        'selector' => 'apartments.number',
                        'id' => 'number',
                        'name' => trans(\Locales::getNamespace() . '/datatables.number'),
                        'search' => true,
                        'info' => 'comments',
                    ],
                    [
                        'selector' => 'room_translations.name as room',
                        'id' => 'room',
                        'name' => trans(\Locales::getNamespace() . '/datatables.room'),
                        'search' => true,
                        'join' => [
                            'table' => 'room_translations',
                            'localColumn' => 'room_translations.room_id',
                            'constrain' => '=',
                            'foreignColumn' => 'apartments.room_id',
                            'whereColumn' => 'room_translations.locale',
                            'whereConstrain' => '=',
                            'whereValue' => \Locales::getCurrent(),
                        ],
                    ],
                    [
                        'selector' => 'view_translations.name as view',
                        'id' => 'view',
                        'name' => trans(\Locales::getNamespace() . '/datatables.view'),
                        'search' => true,
                        'join' => [
                            'table' => 'view_translations',
                            'localColumn' => 'view_translations.view_id',
                            'constrain' => '=',
                            'foreignColumn' => 'apartments.view_id',
                            'whereColumn' => 'view_translations.locale',
                            'whereConstrain' => '=',
                            'whereValue' => \Locales::getCurrent(),
                        ],
                    ],
                    [
                        'selector' => 'furniture_translations.name as furniture',
                        'id' => 'furniture',
                        'name' => trans(\Locales::getNamespace() . '/datatables.furniture'),
                        'search' => true,
                        'join' => [
                            'table' => 'furniture_translations',
                            'localColumn' => 'furniture_translations.furniture_id',
                            'constrain' => '=',
                            'foreignColumn' => 'apartments.furniture_id',
                            'whereColumn' => 'furniture_translations.locale',
                            'whereConstrain' => '=',
                            'whereValue' => \Locales::getCurrent(),
                        ],
                    ],
                    [
                        'selector' => 'apartments.total_area',
                        'id' => 'total_area',
                        'name' => trans(\Locales::getNamespace() . '/datatables.totalArea') . ' ' . trans(\Locales::getNamespace() . '/datatables.m2'),
                        'class' => 'text-right',
                    ],
                ],
                'orderByColumn' => 'number',
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
            'projects' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'buildings' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'floors' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'apartments' => [
                'id' => 'id',
                'name' => 'number',
            ],
        ];
    }

    public function index(DataTable $datatable, Request $request, $id = null)
    {
        $breadcrumbs = [];

        $owner = Owner::findOrFail($id);
        $breadcrumbs[] = ['id' => $owner->id, 'slug' => $owner->id, 'name' => $owner->full_name];
        $breadcrumbs[] = ['id' => 'apartments', 'slug' => 'apartments', 'name' => trans(\Locales::getNamespace() . '/multiselect.ownerProperties.apartments')];

        $datatable->setup(Ownership::leftJoin('apartments', 'ownership.apartment_id', '=', 'apartments.id')->where('ownership.owner_id', $owner->id), $this->route, $this->datatables[$this->route]);
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

        $owner = $request->input('owner') ?: null;

        $projects[] = ['id' => 0, 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        $projects = array_merge($projects, Project::withTranslation()->select('project_translations.name', 'projects.id')->leftJoin('project_translations', 'project_translations.project_id', '=', 'projects.id')->where('project_translations.locale', \Locales::getCurrent())->orderBy('project_translations.name')->get()->toArray());
        $this->multiselect['projects']['options'] = $projects;
        $this->multiselect['projects']['selected'] = '';

        $buildings[] = ['id' => 0, 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        $this->multiselect['buildings']['options'] = $buildings;
        $this->multiselect['buildings']['selected'] = '';

        $floors[] = ['id' => 0, 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        $this->multiselect['floors']['options'] = $floors;
        $this->multiselect['floors']['selected'] = '';

        $this->multiselect['apartments']['options'] = Apartment::select('id', 'number')->whereNotExists(function ($query) use ($owner) {
            $query->from('ownership')->whereRaw('ownership.apartment_id = apartments.id')->where('ownership.owner_id', $owner)->whereNull('ownership.deleted_at');
        })->orderBy('number')->get()->toArray();
        $this->multiselect['apartments']['selected'] = '';

        $multiselect = $this->multiselect;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.add', compact('table', 'owner', 'multiselect'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function save(DataTable $datatable, OwnerApartmentRequest $request)
    {
        $owner = Owner::findOrFail([$request->input('owner')])->first();

        $now = Carbon::now();

        $apartments = [];
        foreach ($request->input('apartments') as $apartment) {
            array_push($apartments, [
                'created_at' => $now,
                'apartment_id' => $apartment,
                'owner_id' => $owner->id,
            ]);
        }
        Ownership::insert($apartments);

        $successMessage = trans(\Locales::getNamespace() . '/forms.savedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityApartments', 1)]);

        $datatable->setup(Ownership::leftJoin('apartments', 'ownership.apartment_id', '=', 'apartments.id')->where('ownership.owner_id', $owner->id), $request->input('table'), $this->datatables[$request->input('table')], true);
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

        $owner = $request->input('owner') ?: null;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.remove', compact('table', 'owner'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function destroy(DataTable $datatable, Request $request)
    {
        $owner = Owner::findOrFail([$request->input('owner')])->first();

        $count = count($request->input('id'));

        if ($count > 0) {
            Ownership::whereIn('id', $request->input('id'))->delete();

            $datatable->setup(Ownership::leftJoin('apartments', 'ownership.apartment_id', '=', 'apartments.id')->where('ownership.owner_id', $owner->id), $request->input('table'), $this->datatables[$request->input('table')], true);
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

    public function getBuildings($owner = null, $project = null)
    {
        $buildings[] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $buildings = $buildings + Building::withTranslation()->select('building_translations.name', 'buildings.id')->leftJoin('building_translations', 'building_translations.building_id', '=', 'buildings.id')->where('building_translations.locale', \Locales::getCurrent())->where('buildings.project_id', $project)->orderBy('building_translations.name')->get()->pluck('name', 'id')->toArray();

        $apartments = $this->getApartments($owner, null, $project, null, false);

        return response()->json([
            'success' => true,
            'buildings' => $buildings,
            'apartments' => $apartments,
            'project' => $project,
        ]);
    }

    public function getFloors($owner = null, $building = null)
    {
        $floors[] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $floors = $floors + Floor::withTranslation()->select('floor_translations.name', 'floors.id')->leftJoin('floor_translations', 'floor_translations.floor_id', '=', 'floors.id')->where('floor_translations.locale', \Locales::getCurrent())->where('floors.building_id', $building)->orderBy('floor_translations.name')->get()->pluck('name', 'id')->toArray();

        $apartments = $this->getApartments($owner, null, null, $building, false);

        return response()->json([
            'success' => true,
            'floors' => $floors,
            'apartments' => $apartments,
            'building' => $building,
        ]);
    }

    public function getApartments($owner = null, $floor = null, $project = null, $building = null, $json = true)
    {
        $apartments = Apartment::select('apartments.id', 'apartments.number')->join('ownership', 'ownership.apartment_id', '=', 'apartments.id')->whereNotExists(function($query) use ($owner) {
            $query->from('ownership')->whereRaw('ownership.apartment_id = apartments.id')->where('ownership.owner_id', $owner)->whereNull('ownership.deleted_at');
        });

        if ($project) {
            $apartments = $apartments->where('apartments.project_id', $project);
        }

        if ($building) {
            $apartments = $apartments->where('apartments.building_id', $building);
        }

        if ($floor) {
            $apartments = $apartments->where('apartments.floor_id', $floor);
        }

        $apartments = $apartments->orderBy('apartments.number')->get()->pluck('number', 'id')->toArray();

        if ($json) {
            return response()->json([
                'success' => true,
                'apartments' => $apartments,
                'floor' => $floor,
            ]);
        } else {
            return $apartments;
        }
    }

}
