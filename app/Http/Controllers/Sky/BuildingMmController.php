<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Models\Sky\Project;
use App\Models\Sky\Building;
use App\Models\Sky\Year;
use App\Models\Sky\ManagementCompany;
use App\Models\Sky\BuildingMm;
use App\Http\Requests\Sky\BuildingMmRequest;

class BuildingMmController extends Controller {

    protected $route = 'buildings-mm';
    protected $datatables;

    public function __construct()
    {
        $this->datatables = [
            'years' => [
                'dom' => 'tr',
                'title' => trans(\Locales::getNamespace() . '/datatables.titleBuildingsMM'),
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
                'title' => trans(\Locales::getNamespace() . '/datatables.titleBuildingsMM'),
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
                        'foreignColumn' => 'building_mm.year_id',
                    ]
                ],
                'columns' => [
                    [
                        'selector' => 'building_mm.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center',
                        'join' => [
                            'table' => 'buildings',
                            'localColumn' => 'buildings.id',
                            'constrain' => '=',
                            'foreignColumn' => 'building_mm.building_id',
                        ],
                    ],
                    [
                        'selector' => 'management_company_translations.name',
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.managementCompany'),
                        'search' => true,
                        'join' => [
                            'table' => 'management_company_translations',
                            'localColumn' => 'management_company_translations.management_company_id',
                            'constrain' => '=',
                            'foreignColumn' => 'building_mm.management_company_id',
                            'whereColumn' => 'management_company_translations.locale',
                            'whereConstrain' => '=',
                            'whereValue' => \Locales::getCurrent(),
                        ],
                        'link' => [
                            'selector' => ['buildings.project_id', 'building_mm.building_id', 'years.year'],
                            'icon' => 'folder-open',
                            'route' => $this->route,
                            'routeParametersPrepend' => ['project_id' => 'buildings', 'building_id' => 'mm', 'year' => '', 'id' => ''],
                        ],
                    ],
                    [
                        'selector' => 'building_mm.mm_tax',
                        'id' => 'mm_tax',
                        'name' => trans(\Locales::getNamespace() . '/datatables.mmTax'),
                        'search' => true,
                        (last(\Slug::getSlugs()) >= 2021 ? 'append' : 'prepend') => [
                            'simpleText' => (last(\Slug::getSlugs()) >= 2021 ? ' лв.' : '€ '),
                        ],
                    ],
                    [
                        'selector' => 'building_mm.deadline_at',
                        'id' => 'deadline_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.mmFeesDeadlineAt'),
                        'order' => false,
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

    public function index(DataTable $datatable, BuildingMm $mm, Request $request, Year $years, $project = null, $buildingsSlug = null, $building = null, $mmSlug = null, $year = null)
    {
        $breadcrumbs = [];
        $project = Project::findOrFail($project);
        $building = Building::findOrFail($building);
        $breadcrumbs[] = ['id' => 'projects', 'slug' => $project->id . '/' . $buildingsSlug, 'name' => $project->name];
        $breadcrumbs[] = ['id' => 'buildings', 'slug' => $building->id, 'name' => $building->name];
        $breadcrumbs[] = ['id' => 'mm', 'slug' => $mmSlug, 'name' => trans(\Locales::getNamespace() . '/multiselect.buildingProperties.' . $mmSlug)];

        if ($year) {
            $year = $years->where('year', $year)->firstOrFail();
            $breadcrumbs[] = ['id' => 'year', 'slug' => $year->year, 'name' => $year->year];

            $datatable->setup($mm->where('building_id', $building->id)->where('year_id', $year->id), $this->route, $this->datatables[$this->route]);
            $datatable->setOption('project', $project->id);
            $datatable->setOption('building', $building->id);
            $datatable->setOption('year', $year->id);
        } else {
            $datatable->setup(null, 'years', $this->datatables['years']);
            $data = [];
            foreach ($years->orderBy('year', 'desc')->get() as $year) {
                $data[] = ['year' => '<a href="' . \Locales::route($this->route, [$project->id . '/' . $buildingsSlug . '/' . $building->id . '/' . $mmSlug, $year->year]) . '"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . $year->year . '</a>'];
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

        $companies[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $companies = $companies + ManagementCompany::withTranslation()->select('management_company_translations.name', 'management_companies.id')->leftJoin('management_company_translations', 'management_company_translations.management_company_id', '=', 'management_companies.id')->where('management_company_translations.locale', \Locales::getCurrent())->orderBy('management_company_translations.name')->get()->pluck('name', 'id')->toArray();

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('table', 'project', 'building', 'year', 'companies'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function store(DataTable $datatable, BuildingMm $mm, BuildingMmRequest $request)
    {
        $project = Project::findOrFail([$request->input('project')])->first();
        $building = Building::findOrFail([$request->input('building')])->first();
        $year = Year::findOrFail([$request->input('year')])->first();

        $request->merge([
            'building_id' => $building->id,
            'year_id' => $year->id,
        ]);

        $newBuildingMm = BuildingMm::create($request->all());

        if ($newBuildingMm->id) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.storedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityMMInfo', 1)]);

            $datatable->setup($mm->where('building_id', $building->id)->where('year_id', $year->id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'reset' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.createError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityMMInfo', 1)]);
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

    public function destroy(DataTable $datatable, BuildingMm $mm, Request $request)
    {
        $project = Project::findOrFail([$request->input('project')])->first();
        $building = Building::findOrFail([$request->input('building')])->first();
        $year = Year::findOrFail([$request->input('year')])->first();

        $count = count($request->input('id'));

        if ($count > 0 && $mm->destroy($request->input('id'))) {
            $datatable->setup($mm->where('building_id', $building->id)->where('year_id', $year->id), $request->input('table'), $this->datatables[$request->input('table')], true);
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
        $mm = BuildingMm::findOrFail($id);

        $table = $request->input('table') ?: $this->route;

        $companies = ManagementCompany::withTranslation()->select('management_company_translations.name', 'management_companies.id')->leftJoin('management_company_translations', 'management_company_translations.management_company_id', '=', 'management_companies.id')->where('management_company_translations.locale', \Locales::getCurrent())->orderBy('management_company_translations.name')->get()->pluck('name', 'id')->toArray();

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('mm', 'table', 'companies'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, BuildingMmRequest $request)
    {
        $mm = BuildingMm::findOrFail($request->input('id'))->first();

        if ($mm->update($request->all())) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityMMInfo', 1)]);

            $datatable->setup($mm->where('building_id', $mm->building_id)->where('year_id', $mm->year_id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityMMInfo', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

}
