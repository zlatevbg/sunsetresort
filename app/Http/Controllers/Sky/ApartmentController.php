<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Models\Sky\Apartment;
use App\Models\Sky\Project;
use App\Models\Sky\Building;
use App\Models\Sky\Floor;
use App\Models\Sky\Room;
use App\Models\Sky\Furniture;
use App\Models\Sky\View;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Http\Requests\Sky\ApartmentRequest;
use Storage;
use Carbon\Carbon;

class ApartmentController extends Controller {

    protected $route = 'apartments';
    protected $uploadDirectory = 'apartments';
    protected $datatables;
    protected $multiselect;

    public function __construct()
    {
        $this->datatables = [
            $this->route => [
                'title' => trans(\Locales::getNamespace() . '/datatables.titleApartments'),
                'url' => \Locales::route($this->route),
                'class' => 'table-checkbox table-striped table-bordered table-hover table-dropdown',
                'joins' => [
                    [
                        'table' => 'poa',
                        'localColumn' => 'poa.apartment_id',
                        'constrain' => '=',
                        'foreignColumn' => $this->route . '.id',
                        'whereNull' => 'poa.deleted_at',
                        'whereColumn' => 'poa.is_active',
                        'whereConstrain' => '=',
                        'whereValue' => 1,
                        'andWhereColumn' => 'poa.to',
                        'andWhereConstrain' => '>=',
                        'andWhereValue' => Carbon::now()->year,
                        'group' => $this->route . '.id',
                    ],
                    [
                        'table' => 'proxy_translations',
                        'localColumn' => 'proxy_translations.proxy_id',
                        'constrain' => '=',
                        'foreignColumn' => 'poa.proxy_id',
                        'whereColumn' => 'proxy_translations.locale',
                        'whereConstrain' => '=',
                        'whereValue' => \Locales::getCurrent(),
                    ],
                ],
                'selectors' => [$this->route . '.comments'],
                'columns' => [
                    [
                        'selector' => $this->route . '.id',
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
                            'route' => $this->route,
                            'routeParameter' => 'id',
                            'title' => trans(\Locales::getNamespace() . '/messages.menu'),
                            'menu' => trans(\Locales::getNamespace() . '/multiselect.apartmentProperties'),
                        ],
                    ],
                    [
                        'selector' => $this->route . '.number',
                        'id' => 'number',
                        'name' => trans(\Locales::getNamespace() . '/datatables.number'),
                        'search' => true,
                        'order' => false,
                        'selectRaw' => 'GROUP_CONCAT(proxy_translations.name SEPARATOR ", ") AS proxies',
                        'info' => ['comments' => 'comments', 'poa' => 'proxies'],
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
                            'foreignColumn' => $this->route . '.room_id',
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
                            'foreignColumn' => $this->route . '.view_id',
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
                            'foreignColumn' => $this->route . '.furniture_id',
                            'whereColumn' => 'furniture_translations.locale',
                            'whereConstrain' => '=',
                            'whereValue' => \Locales::getCurrent(),
                        ],
                    ],
                    [
                        'selector' => $this->route . '.total_area',
                        'id' => 'total_area',
                        'name' => trans(\Locales::getNamespace() . '/datatables.totalArea') . ' ' . trans(\Locales::getNamespace() . '/datatables.m2'),
                        'class' => 'text-right',
                    ],
                ],
                'orderByRaw' => 'CONCAT(SUBSTR(apartments.number, 1, LOCATE("-", apartments.number) - 1), LPAD(SUBSTR(apartments.number, LOCATE("-", apartments.number) + 1), 3, "0"))',
                'orderByColumn' => 'number',
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
            'properties' => [
                'dom' => 'tr',
                'url' => \Locales::route($this->route),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'columns' => [
                    [
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.properties'),
                        'order' => false,
                    ],
                ],
                'data' => [
                    [
                        'name' => '<a href="' . \Locales::route($this->route, true) . '/contracts"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/multiselect.apartmentProperties.contracts') . '</a>',
                    ],
                    [
                        'name' => '<a href="' . \Locales::route($this->route, true) . '/mm-fees"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/multiselect.apartmentProperties.mm-fees') . '</a>',
                    ],
                    [
                        'name' => '<a href="' . \Locales::route($this->route, true) . '/communal-fees"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/multiselect.apartmentProperties.communal-fees') . '</a>',
                    ],
                    [
                        'name' => '<a href="' . \Locales::route($this->route, true) . '/pool-usage"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/multiselect.apartmentProperties.pool-usage') . '</a>',
                    ],
                    [
                        'name' => '<a href="' . \Locales::route($this->route, true) . '/owners"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/multiselect.apartmentProperties.owners') . '</a>',
                    ],
                    [
                        'name' => '<a href="' . \Locales::route($this->route, true) . '/former-owners"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/multiselect.apartmentProperties.former-owners') . '</a>',
                    ],
                    [
                        'name' => '<a href="' . \Locales::route($this->route, true) . '/agents"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/multiselect.apartmentProperties.agents') . '</a>',
                    ],
                    [
                        'name' => '<a href="' . \Locales::route($this->route, true) . '/keyholders"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/multiselect.apartmentProperties.keyholders') . '</a>',
                    ],
                    [
                        'name' => '<a href="' . \Locales::route($this->route, true) . '/maintenance"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/multiselect.apartmentProperties.maintenance-issues') . '</a>',
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
        ];
    }

    public function index(DataTable $datatable, Apartment $apartment, Request $request, $id = null)
    {
        $breadcrumbs = [];

        if ($id) {
            $apartment = Apartment::findOrFail($id);
            $breadcrumbs[] = ['id' => $apartment->id, 'slug' => $apartment->id, 'name' => $apartment->number];
            $datatable->setup(null, 'properties', $this->datatables['properties']);
        } else {
            $datatable->setup($apartment, $this->route, $this->datatables[$this->route]);

            if (!Storage::disk('local-public')->exists($this->uploadDirectory)) {
                Storage::disk('local-public')->makeDirectory($this->uploadDirectory);
            }
        }

        $datatables = $datatable->getTables();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($datatables);
        } else {
            return view(\Locales::getNamespace() . '.' . $this->route . '.index', compact('datatables', 'breadcrumbs'));
        }
    }

    public function indexProject(DataTable $datatable, Apartment $apartment, Request $request, $project = null, $buildingsSlug = null, $building = null, $floorsSlug = null, $floor = null)
    {
        $breadcrumbs = [];
        $project = Project::findOrFail($project);
        $building = Building::findOrFail($building);
        $floor = Floor::findOrFail($floor);
        $breadcrumbs[] = ['id' => 'projects', 'slug' => $project->id . '/' . $buildingsSlug, 'name' => $project->name];
        $breadcrumbs[] = ['id' => 'buildings', 'slug' => $building->id, 'name' => $building->name];
        $breadcrumbs[] = ['id' => 'floors', 'slug' => $floorsSlug, 'name' => trans(\Locales::getNamespace() . '/multiselect.buildingProperties.' . $floorsSlug)];
        $breadcrumbs[] = ['id' => 'floor', 'slug' => $floor->id, 'name' => $floor->name];

        $datatable->setup($apartment->where('project_id', $project->id)->where('building_id', $building->id)->where('floor_id', $floor->id), $this->route, $this->datatables[$this->route]);
        $datatable->setOption('project', $project->id);
        $datatable->setOption('building', $building->id);
        $datatable->setOption('floor', $floor->id);

        $datatables = $datatable->getTables();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($datatables);
        } else {
            return view(\Locales::getNamespace() . '.' . $this->route . '.index', compact('datatables', 'breadcrumbs'));
        }
    }

    public function create(Request $request)
    {
        $table = $this->route;
        if ($request->input('table')) { // magnific popup request
            $table = $request->input('table');
        }

        $project = $request->input('project') ?: '';
        $building = $request->input('building') ?: '';
        $floor = $request->input('floor') ?: '';

        $projects[] = ['id' => 0, 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        $projects = array_merge($projects, Project::withTranslation()->select('project_translations.name', 'projects.id')->leftJoin('project_translations', 'project_translations.project_id', '=', 'projects.id')->where('project_translations.locale', \Locales::getCurrent())->orderBy('project_translations.name')->get()->toArray());
        $this->multiselect['projects']['options'] = $projects;
        $this->multiselect['projects']['selected'] = $project;

        $buildings[] = ['id' => 0, 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        if ($building) {
            $buildings = array_merge($buildings, Building::withTranslation()->select('building_translations.name', 'buildings.id')->leftJoin('building_translations', 'building_translations.building_id', '=', 'buildings.id')->where('building_translations.locale', \Locales::getCurrent())->orderBy('building_translations.name')->get()->toArray());
        }
        $this->multiselect['buildings']['options'] = $buildings;
        $this->multiselect['buildings']['selected'] = $building;

        $floors[] = ['id' => 0, 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        if ($floor) {
            $floors = array_merge($floors, Floor::withTranslation()->select('floor_translations.name', 'floors.id')->leftJoin('floor_translations', 'floor_translations.floor_id', '=', 'floors.id')->where('floor_translations.locale', \Locales::getCurrent())->orderBy('floor_translations.name')->get()->toArray());
        }
        $this->multiselect['floors']['options'] = $floors;
        $this->multiselect['floors']['selected'] = $floor;

        $multiselect = $this->multiselect;

        $rooms[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $rooms = $rooms + Room::withTranslation()->select('room_translations.name', 'rooms.id')->leftJoin('room_translations', 'room_translations.room_id', '=', 'rooms.id')->where('room_translations.locale', \Locales::getCurrent())->orderBy('room_translations.name')->get()->pluck('name', 'id')->toArray();

        $furniture[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $furniture = $furniture + Furniture::withTranslation()->select('furniture_translations.name', 'furniture.id')->leftJoin('furniture_translations', 'furniture_translations.furniture_id', '=', 'furniture.id')->where('furniture_translations.locale', \Locales::getCurrent())->orderBy('furniture_translations.name')->get()->pluck('name', 'id')->toArray();

        $views[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $views = $views + View::withTranslation()->select('view_translations.name', 'views.id')->leftJoin('view_translations', 'view_translations.view_id', '=', 'views.id')->where('view_translations.locale', \Locales::getCurrent())->orderBy('view_translations.name')->get()->pluck('name', 'id')->toArray();

        $mmTaxFormula[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $mmTaxFormula = $mmTaxFormula + trans(\Locales::getNamespace() . '/multiselect.mmTaxFormula');

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('table', 'project', 'building', 'floor', 'multiselect', 'rooms', 'furniture', 'views', 'mmTaxFormula'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function store(DataTable $datatable, Apartment $apartment, ApartmentRequest $request)
    {
        $newApartment = Apartment::create($request->all());

        if ($newApartment->id) {
            $uploadDirectory = $this->uploadDirectory . DIRECTORY_SEPARATOR . $newApartment->id;
            if (!Storage::disk('local-public')->exists($uploadDirectory)) {
                Storage::disk('local-public')->makeDirectory($uploadDirectory);
            }

            $successMessage = trans(\Locales::getNamespace() . '/forms.storedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityApartments', 1)]);

            $project = $request->input('project') ?: '';
            $building = $request->input('building') ?: '';
            $floor = $request->input('floor') ?: '';

            if ($project) {
                $apartment = $apartment->where('project_id', $project);
            }

            if ($building) {
                $apartment = $apartment->where('building_id', $building);
            }

            if ($floor) {
                $apartment = $apartment->where('floor_id', $floor);
            }

            $datatable->setup($apartment, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'reset' => true,
                'resetMultiselect' => [
                    'input-project_id' => ['refresh'],
                    'input-building_id' => ['empty', 'refresh', 'disable'],
                    'input-floor_id' => ['empty', 'refresh', 'disable'],
                ],
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.createError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityApartments', 1)]);
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

    public function destroy(DataTable $datatable, Apartment $apartment, Request $request)
    {
        $count = count($request->input('id'));

        if ($count > 0 && $apartment->destroy($request->input('id'))) {
            // softDelete
            /*foreach ($request->input('id') as $id) {
                Storage::disk('local-public')->deleteDirectory($this->uploadDirectory . DIRECTORY_SEPARATOR . $id);
            }*/

            $datatable->setup($apartment, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route));
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

    public function edit(Request $request, $id = null)
    {
        $apartment = Apartment::findOrFail($id);
        $project = $request->input('project') ?: '';
        $building = $request->input('building') ?: '';
        $floor = $request->input('floor') ?: '';

        $project_id = $apartment->project_id;
        $building_id = $apartment->building_id;
        $floor_id = $apartment->floor_id;

        $table = $this->route;
        if ($request->input('table')) { // magnific popup request
            $table = $request->input('table');
        }

        $projects[] = ['id' => 0, 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        $projects = array_merge($projects, Project::withTranslation()->select('project_translations.name', 'projects.id')->leftJoin('project_translations', 'project_translations.project_id', '=', 'projects.id')->where('project_translations.locale', \Locales::getCurrent())->orderBy('project_translations.name')->get()->toArray());
        $this->multiselect['projects']['options'] = $projects;
        $this->multiselect['projects']['selected'] = $project_id;

        $buildings[] = ['id' => 0, 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        $buildings = array_merge($buildings, Building::withTranslation()->select('building_translations.name', 'buildings.id')->leftJoin('building_translations', 'building_translations.building_id', '=', 'buildings.id')->where('building_translations.locale', \Locales::getCurrent())->where('buildings.project_id', $project_id)->orderBy('building_translations.name')->get()->toArray());
        $this->multiselect['buildings']['options'] = $buildings;
        $this->multiselect['buildings']['selected'] = $building_id;

        $floors[] = ['id' => 0, 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        $floors = array_merge($floors, Floor::withTranslation()->select('floor_translations.name', 'floors.id')->leftJoin('floor_translations', 'floor_translations.floor_id', '=', 'floors.id')->where('floor_translations.locale', \Locales::getCurrent())->where('floors.building_id', $building_id)->orderBy('floor_translations.name')->get()->toArray());
        $this->multiselect['floors']['options'] = $floors;
        $this->multiselect['floors']['selected'] = $floor_id;

        $multiselect = $this->multiselect;

        $rooms[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $rooms = $rooms + Room::withTranslation()->select('room_translations.name', 'rooms.id')->leftJoin('room_translations', 'room_translations.room_id', '=', 'rooms.id')->where('room_translations.locale', \Locales::getCurrent())->orderBy('room_translations.name')->get()->pluck('name', 'id')->toArray();

        $furniture[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $furniture = $furniture + Furniture::withTranslation()->select('furniture_translations.name', 'furniture.id')->leftJoin('furniture_translations', 'furniture_translations.furniture_id', '=', 'furniture.id')->where('furniture_translations.locale', \Locales::getCurrent())->orderBy('furniture_translations.name')->get()->pluck('name', 'id')->toArray();

        $views[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $views = $views + View::withTranslation()->select('view_translations.name', 'views.id')->leftJoin('view_translations', 'view_translations.view_id', '=', 'views.id')->where('view_translations.locale', \Locales::getCurrent())->orderBy('view_translations.name')->get()->pluck('name', 'id')->toArray();

        $mmTaxFormula[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $mmTaxFormula = $mmTaxFormula + trans(\Locales::getNamespace() . '/multiselect.mmTaxFormula');

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('apartment', 'table', 'project', 'building', 'floor', 'multiselect', 'rooms', 'furniture', 'views', 'mmTaxFormula'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, ApartmentRequest $request)
    {
        $apartment = Apartment::findOrFail($request->input('id'))->first();
        $project = $request->input('project') ?: '';
        $building = $request->input('building') ?: '';
        $floor = $request->input('floor') ?: '';

        if ($apartment->update($request->all())) {
            $uploadDirectory = $this->uploadDirectory . DIRECTORY_SEPARATOR . $apartment->id;
            if (!Storage::disk('local-public')->exists($uploadDirectory)) {
                Storage::disk('local-public')->makeDirectory($uploadDirectory);
            }

            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityApartments', 1)]);

            if ($project) {
                $apartment = $apartment->where('project_id', $project);
            }

            if ($building) {
                $apartment = $apartment->where('building_id', $building);
            }

            if ($floor) {
                $apartment = $apartment->where('floor_id', $floor);
            }

            $datatable->setup($apartment, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityApartments', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function getBuildings($project = null)
    {
        $buildings[] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $buildings = $buildings + Building::withTranslation()->select('building_translations.name', 'buildings.id')->leftJoin('building_translations', 'building_translations.building_id', '=', 'buildings.id')->where('building_translations.locale', \Locales::getCurrent())->where('buildings.project_id', $project)->orderBy('building_translations.name')->get()->pluck('name', 'id')->toArray();

        return response()->json([
            'success' => true,
            'buildings' => $buildings,
            'project' => $project,
        ]);
    }

    public function getFloors($building = null)
    {
        $floors[] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $floors = $floors + Floor::withTranslation()->select('floor_translations.name', 'floors.id')->leftJoin('floor_translations', 'floor_translations.floor_id', '=', 'floors.id')->where('floor_translations.locale', \Locales::getCurrent())->where('floors.building_id', $building)->orderBy('floor_translations.name')->get()->pluck('name', 'id')->toArray();

        return response()->json([
            'success' => true,
            'floors' => $floors,
            'building' => $building,
        ]);
    }

}
