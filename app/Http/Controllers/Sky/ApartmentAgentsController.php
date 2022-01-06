<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Models\Sky\Apartment;
use App\Models\Sky\Agent;
use App\Models\Sky\AgentAccess;
use App\Services\DataTable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\Sky\ApartmentAgentRequest;

class ApartmentAgentsController extends Controller {

    protected $route = 'apartment-agents';
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
                'title' => trans(\Locales::getNamespace() . '/datatables.titleAgents'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'selectors' => ['agents.id as agent', 'agents.comments'],
                'columns' => [
                    [
                        'selector' => 'agent_access.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center',
                    ],
                    [
                        'selector' => 'agents.name',
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.name'),
                        'order' => false,
                        'info' => ['comments' => 'comments'],
                    ],
                    [
                        'selector' => 'agents.phone',
                        'id' => 'phone',
                        'name' => trans(\Locales::getNamespace() . '/datatables.phone'),
                        'search' => true,
                    ],
                    [
                        'selector' => 'agents.email',
                        'id' => 'email',
                        'name' => trans(\Locales::getNamespace() . '/datatables.email'),
                        'search' => true,
                    ],
                    [
                        'selector' => 'agent_access.dfrom',
                        'id' => 'dfrom',
                        'name' => trans(\Locales::getNamespace() . '/datatables.dateFrom'),
                        'data' => [
                            'type' => 'sort',
                            'id' => 'dfrom',
                            'date' => 'YYmmdd',
                        ],
                    ],
                    [
                        'selector' => 'agent_access.dto',
                        'id' => 'dto',
                        'name' => trans(\Locales::getNamespace() . '/datatables.dateTo'),
                        'data' => [
                            'type' => 'sort',
                            'id' => 'dto',
                            'date' => 'YYmmdd',
                            'expire' => [
                                'color' => 'red',
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
            'agents' => [
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
        $breadcrumbs[] = ['id' => 'agents', 'slug' => 'agents', 'name' => trans(\Locales::getNamespace() . '/multiselect.apartmentProperties.agents')];

        $datatable->setup(AgentAccess::leftJoin('agents', 'agent_access.agent_id', '=', 'agents.id')->where('agent_access.apartment_id', $apartment->id), $this->route, $this->datatables[$this->route]);
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

        $this->multiselect['agents']['options'] = Agent::select('agents.id', 'agents.name')->whereNotExists(function ($query) use ($apartment) {
            $query->from('agent_access')->whereRaw('agent_access.agent_id = agents.id')->where('agent_access.apartment_id', $apartment)->whereNull('agent_access.deleted_at');
        })->orderBy('name')->get()->toarray();
        $this->multiselect['agents']['selected'] = '';

        $multiselect = $this->multiselect;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.add', compact('table', 'apartment', 'multiselect'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function save(DataTable $datatable, ApartmentAgentRequest $request)
    {
        $apartment = Apartment::findOrFail([$request->input('apartment')])->first();

        $now = Carbon::now();

        $agents = [];
        foreach ($request->input('agents') as $agent) {
            array_push($agents, [
                'created_at' => $now,
                'apartment_id' => $apartment->id,
                'agent_id' => $agent,
                'dfrom' => Carbon::parse($request->input('dfrom'))->toDateTimeString(),
                'dto' => Carbon::parse($request->input('dto'))->toDateTimeString(),
            ]);
        }
        AgentAccess::insert($agents);

        $successMessage = trans(\Locales::getNamespace() . '/forms.savedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityAgents', 1)]);

        $datatable->setup(AgentAccess::leftJoin('agents', 'agent_access.agent_id', '=', 'agents.id')->where('agent_access.apartment_id', $apartment->id), $request->input('table'), $this->datatables[$request->input('table')], true);
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
            AgentAccess::whereIn('id', $request->input('id'))->delete();

            $datatable->setup(AgentAccess::leftJoin('agents', 'agent_access.agent_id', '=', 'agents.id')->where('agent_access.apartment_id', $apartment->id), $request->input('table'), $this->datatables[$request->input('table')], true);
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
        $agent = AgentAccess::findOrFail($id);
        $table = $request->input('table');

        $this->multiselect['agents']['options'] = Agent::select('agents.id', 'agents.name')->where('id', $agent->agent_id)->get()->toarray();
        $this->multiselect['agents']['selected'] = $agent->agent_id;

        $multiselect = $this->multiselect;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.add', compact('table', 'agent', 'multiselect'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, ApartmentAgentRequest $request)
    {
        $agent = AgentAccess::findOrFail($request->input('id'))->first();

        if ($agent->update($request->all())) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityAgents', 1)]);

            $datatable->setup(AgentAccess::leftJoin('agents', 'agent_access.agent_id', '=', 'agents.id')->where('agent_access.apartment_id', $agent->apartment_id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, true));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityAgents', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

}
