<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Models\Sky\Project;
use App\Models\Sky\Building;
use App\Models\Sky\Year;
use App\Models\Sky\Condominium;
use App\Http\Requests\Sky\CondominiumRequest;

class CondominiumController extends Controller {

    protected $route = 'condominium';
    protected $datatables;

    public function __construct()
    {
        $this->datatables = [
            'years' => [
                'dom' => 'tr',
                'title' => trans(\Locales::getNamespace() . '/datatables.titleCondominium'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'columns' => [
                    [
                        'id' => 'year',
                        'name' => trans(\Locales::getNamespace() . '/datatables.year'),
                    ],
                ],
            ],
            $this->route => [
                'title' => trans(\Locales::getNamespace() . '/datatables.titleCondominium'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'joins' => [
                    [
                        'table' => 'projects',
                        'localColumn' => 'projects.id',
                        'constrain' => '=',
                        'foreignColumn' => 'buildings.project_id',
                    ],
                    [
                        'table' => 'years',
                        'localColumn' => 'years.id',
                        'constrain' => '=',
                        'foreignColumn' => 'condominium.year_id',
                    ]
                ],
                'columns' => [
                    [
                        'selector' => 'condominium.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center',
                        'join' => [
                            'table' => 'buildings',
                            'localColumn' => 'buildings.id',
                            'constrain' => '=',
                            'foreignColumn' => 'condominium.building_id',
                        ],
                    ],
                    [
                        'selector' => 'condominium.assembly_at',
                        'id' => 'assembly_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.generalAssembly'),
                        'link' => [
                            'selector' => ['buildings.project_id', 'condominium.building_id', 'years.year'],
                            'icon' => 'folder-open',
                            'route' => $this->route,
                            'routeParametersPrepend' => ['project_id' => 'buildings', 'building_id' => 'condominium', 'year' => '', 'id' => ''],
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
                        'name' => trans(\Locales::getNamespace() . '/forms.addButton'),
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

    public function index(DataTable $datatable, Condominium $condominium, Request $request, Year $years, $project = null, $buildingsSlug = null, $building = null, $cSlug = null, $year = null)
    {
        $breadcrumbs = [];
        $project = Project::findOrFail($project);
        $building = Building::findOrFail($building);
        $breadcrumbs[] = ['id' => 'projects', 'slug' => $project->id . '/' . $buildingsSlug, 'name' => $project->name];
        $breadcrumbs[] = ['id' => 'buildings', 'slug' => $building->id, 'name' => $building->name];
        $breadcrumbs[] = ['id' => 'condominium', 'slug' => $cSlug, 'name' => trans(\Locales::getNamespace() . '/multiselect.buildingProperties.' . $cSlug)];

        if ($year) {
            $year = $years->where('year', $year)->firstOrFail();
            $breadcrumbs[] = ['id' => 'year', 'slug' => $year->year, 'name' => $year->year];

            $datatable->setup($condominium->where('building_id', $building->id)->where('year_id', $year->id), $this->route, $this->datatables[$this->route]);
            $datatable->setOption('project', $project->id);
            $datatable->setOption('building', $building->id);
            $datatable->setOption('year', $year->id);
        } else {
            $datatable->setup(null, 'years', $this->datatables['years']);
            $data = [];
            foreach ($years->orderBy('year', 'desc')->get() as $year) {
                $data[] = ['year' => '<a href="' . \Locales::route($this->route, [$project->id . '/' . $buildingsSlug . '/' . $building->id . '/' . $cSlug, $year->year]) . '"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . $year->year . '</a>'];
            }
            $datatable->setOption('data', $data);
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
        $table = $request->input('table') ?: $this->route;

        $project = $request->input('project') ?: null;
        $building = $request->input('building') ?: null;
        $year = $request->input('year') ?: null;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('table', 'project', 'building', 'year'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function store(DataTable $datatable, Condominium $condominium, CondominiumRequest $request)
    {
        $project = Project::findOrFail([$request->input('project')])->first();
        $building = Building::findOrFail([$request->input('building')])->first();
        $year = Year::findOrFail([$request->input('year')])->first();

        $request->merge([
            'building_id' => $building->id,
            'year_id' => $year->id,
        ]);

        $newCondominium = Condominium::create($request->all());

        if ($newCondominium->id) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.storedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityCondominiumInfo', 1)]);

            $datatable->setup($condominium->where('building_id', $building->id)->where('year_id', $year->id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'reset' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.createError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityCondominiumInfo', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function delete(Request $request)
    {
        $table = $request->input('table') ?: $this->route;

        $project = $request->input('project') ?: null;
        $building = $request->input('building') ?: null;
        $year = $request->input('year') ?: null;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.delete', compact('table', 'project', 'building', 'year'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function destroy(DataTable $datatable, Condominium $condominium, Request $request)
    {
        $project = Project::findOrFail([$request->input('project')])->first();
        $building = Building::findOrFail([$request->input('building')])->first();
        $year = Year::findOrFail([$request->input('year')])->first();

        $count = count($request->input('id'));

        if ($count > 0 && $condominium->destroy($request->input('id'))) {
            $datatable->setup($condominium->where('building_id', $building->id)->where('year_id', $year->id), $request->input('table'), $this->datatables[$request->input('table')], true);
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
        $condominium = Condominium::findOrFail($id);

        $table = $request->input('table') ?: $this->route;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('condominium', 'table'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, CondominiumRequest $request)
    {
        $condominium = Condominium::findOrFail($request->input('id'))->first();

        if ($condominium->update($request->all())) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityCondominiumInfo', 1)]);

            $datatable->setup($condominium->where('building_id', $condominium->building_id)->where('year_id', $condominium->year_id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityCondominiumInfo', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

}
