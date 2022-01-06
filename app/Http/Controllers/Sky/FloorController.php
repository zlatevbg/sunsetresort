<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Models\Sky\Project;
use App\Models\Sky\Building;
use App\Models\Sky\Floor;
use App\Http\Requests\Sky\FloorRequest;

class FloorController extends Controller {

    protected $route = 'floors';
    protected $datatables;

    public function __construct()
    {
        $this->datatables = [
            $this->route => [
                'translation' => true,
                'title' => trans(\Locales::getNamespace() . '/datatables.titleFloors'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'joins' => [
                    [
                        'table' => 'projects',
                        'localColumn' => 'projects.id',
                        'constrain' => '=',
                        'foreignColumn' => 'buildings.project_id',
                    ]
                ],
                'columns' => [
                    [
                        'selector' => $this->route . '.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center',
                        'join' => [
                            'table' => 'buildings',
                            'localColumn' => 'buildings.id',
                            'constrain' => '=',
                            'foreignColumn' => $this->route . '.building_id',
                        ],
                    ],
                    [
                        'selector' => 'floor_translations.name',
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.name'),
                        'search' => true,
                        'join' => [
                            'table' => 'floor_translations',
                            'localColumn' => 'floor_translations.floor_id',
                            'constrain' => '=',
                            'foreignColumn' => $this->route . '.id',
                            'whereColumn' => 'floor_translations.locale',
                            'whereConstrain' => '=',
                            'whereValue' => \Locales::getCurrent(),
                        ],
                        'data' => [
                            'type' => 'sort',
                            'id' => 'name',
                            'cast' => 'int',
                        ],
                        'link' => [
                            'selector' => ['buildings.project_id', $this->route . '.building_id'],
                            'icon' => 'folder-open',
                            'route' => 'project-apartments',
                            'routeParametersPrepend' => ['project_id' => 'buildings', 'building_id' => 'floors' , 'id' => ''],
                        ],
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
    }

    public function index(DataTable $datatable, Floor $floor, Request $request, $project = null, $buildingsSlug = null, $building = null, $floorsSlug = null)
    {
        $breadcrumbs = [];
        $project = Project::findOrFail($project);
        $building = Building::findOrFail($building);
        $breadcrumbs[] = ['id' => 'projects', 'slug' => $project->id . '/' . $buildingsSlug, 'name' => $project->name];
        $breadcrumbs[] = ['id' => 'buildings', 'slug' => $building->id, 'name' => $building->name];
        $breadcrumbs[] = ['id' => 'floors', 'slug' => $floorsSlug, 'name' => trans(\Locales::getNamespace() . '/multiselect.buildingProperties.' . $floorsSlug)];

        $datatable->setup($floor->where('building_id', $building->id), $this->route, $this->datatables[$this->route]);
        $datatable->setOption('project', $project->id);
        $datatable->setOption('building', $building->id);

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
        $building = $request->input('building') ?: null;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('table', 'project', 'building'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function store(DataTable $datatable, Floor $floor, FloorRequest $request)
    {
        $request->merge([
            'building_id' => $request->input('building'),
        ]);

        $data = \Locales::prepareTranslations($request);

        $newFloor = Floor::create($data);

        if ($newFloor->id) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.storedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityFloors', 1)]);

            $datatable->setup($floor->where('building_id', $request->input('building')), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, [$request->input('project'), $request->input('building')]));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'reset' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.createError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityFloors', 1)]);
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

    public function destroy(DataTable $datatable, Floor $floor, Request $request)
    {
        $count = count($request->input('id'));

        $building = Floor::select('building_id')->whereIn('id', $request->input('id'))->first()->building_id;

        if ($count > 0 && $floor->destroy($request->input('id'))) {
            $datatable->setup($floor->where('building_id', $building), $request->input('table'), $this->datatables[$request->input('table')], true);
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
        $floor = Floor::findOrFail($id);

        $table = $request->input('table') ?: $this->route;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('floor', 'table'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, FloorRequest $request)
    {
        $floor = Floor::findOrFail($request->input('id'))->first();

        $data = \Locales::prepareTranslations($request);

        if ($floor->update($data)) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityFloors', 1)]);

            $datatable->setup($floor->where('building_id', $floor->building_id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, [$floor->project_id, $floor->building_id]));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityFloors', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

}
