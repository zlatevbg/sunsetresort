<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Models\Sky\Project;
use App\Models\Sky\Building;
use App\Http\Requests\Sky\BuildingRequest;

class BuildingController extends Controller {

    protected $route = 'buildings';
    protected $datatables;

    public function __construct()
    {
        $this->datatables = [
            $this->route => [
                'translation' => true,
                'title' => trans(\Locales::getNamespace() . '/datatables.titleBuildings'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover table-dropdown',
                'selectors' => [$this->route . '.project_id'],
                'columns' => [
                    [
                        'selector' => $this->route . '.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center',
                        'join' => [
                            'table' => 'building_translations',
                            'localColumn' => 'building_translations.building_id',
                            'constrain' => '=',
                            'foreignColumn' => $this->route . '.id',
                            'whereColumn' => 'building_translations.locale',
                            'whereConstrain' => '=',
                            'whereValue' => \Locales::getCurrent(),
                        ],
                    ],
                    [
                        'id' => 'dropdown',
                        'name' => '',
                        'order' => false,
                        'class' => 'text-center datatables-dropdown',
                        'width' => '1.25em',
                        'dropdown' => [
                            'route' => $this->route,
                            'routeParameters' => ['project_id', 'id'],
                            'routeParametersPrepend' => ['buildings', ''],
                            'title' => trans(\Locales::getNamespace() . '/messages.menu'),
                            'menu' => trans(\Locales::getNamespace() . '/multiselect.buildingProperties'),
                        ],
                    ],
                    [
                        'selector' => 'building_translations.name',
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.name'),
                        'search' => true,
                    ],
                ],
                'orderByColumn' => 2,
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
            ],
        ];
    }

    public function index(DataTable $datatable, Building $buildings, Request $request, $project = null, $buildingsSlug = null, $building = null)
    {
        $breadcrumbs = [];
        $project = Project::findOrFail($project);
        $breadcrumbs[] = ['id' => 'projects', 'slug' => $project->id . '/' . $buildingsSlug, 'name' => $project->name];

        if ($building) {
            $building = Building::findOrFail($building);
            $breadcrumbs[] = ['id' => 'building', 'slug' => $building->id, 'name' => $building->name];

            $this->datatables['properties']['data'] = [
                [
                    'name' => '<a href="' . \Locales::route($this->route, true) . '/floors"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/multiselect.buildingProperties.floors') . '</a>',
                ],
                [
                    'name' => '<a href="' . \Locales::route($this->route, true) . '/mm"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/multiselect.buildingProperties.mm') . '</a>',
                ],
                [
                    'name' => '<a href="' . \Locales::route($this->route, true) . '/condominium"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/multiselect.buildingProperties.condominium') . '</a>',
                ],
            ];
            $datatable->setup(null, 'properties', $this->datatables['properties']);
        } else {
            $datatable->setup($buildings->where('project_id', $project->id), $this->route, $this->datatables[$this->route]);
        }

        $datatable->setOption('project', $project->id);

        $datatables = $datatable->getTables();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($datatables);
        } else {
            return view(\Locales::getNamespace() . '.' . $this->route . '.index', compact('datatables', 'breadcrumbs'));
        }
    }

    public function create(Request $request)
    {
        $table = $request->input('table') ?: $this->route;

        $project = $request->input('project') ?: null;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('table', 'project'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function store(DataTable $datatable, Building $building, BuildingRequest $request)
    {
        $request->merge([
            'project_id' => $request->input('project'),
        ]);

        $data = \Locales::prepareTranslations($request);

        $newBuilding = Building::create($data);

        if ($newBuilding->id) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.storedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityBuildings', 1)]);

            $datatable->setup($building->where('project_id', $request->input('project')), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, [$request->input('project')]));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'reset' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.createError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityBuildings', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function delete(Request $request)
    {
        $table = $request->input('table') ?: $this->route;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.delete', compact('table'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function destroy(DataTable $datatable, Building $building, Request $request)
    {
        $count = count($request->input('id'));

        $project = Building::select('project_id')->whereIn('id', $request->input('id'))->first()->project_id;

        if ($count > 0 && $building->destroy($request->input('id'))) {
            $datatable->setup($building->where('project_id', $project), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, [$project]));
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
        $building = Building::findOrFail($id);

        $table = $request->input('table') ?: $this->route;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('building', 'table'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, BuildingRequest $request)
    {
        $building = Building::findOrFail($request->input('id'))->first();

        $data = \Locales::prepareTranslations($request);

        if ($building->update($data)) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityBuildings', 1)]);

            $datatable->setup($building->where('project_id', $building->project_id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, [$building->project_id]));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityBuildings', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

}
