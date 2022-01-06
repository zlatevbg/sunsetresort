<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Models\Sky\Apartment;
use App\Models\Sky\LegalRepresentative;
use App\Models\Sky\LegalRepresentativeAccess;
use App\Services\DataTable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\Sky\ApartmentLegalRepresentativeRequest;

class ApartmentLegalRepresentativesController extends Controller {

    protected $route = 'apartment-legal-representatives';
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
                'title' => trans(\Locales::getNamespace() . '/datatables.titleLegalRepresentatives'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'selectors' => ['legal_representatives.id as representative', 'legal_representatives.comments'],
                'columns' => [
                    [
                        'selector' => 'legal_representative_access.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center',
                    ],
                    [
                        'selector' => 'legal_representatives.name',
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.name'),
                        'order' => false,
                        'info' => ['comments' => 'comments'],
                    ],
                    [
                        'selector' => 'legal_representatives.phone',
                        'id' => 'phone',
                        'name' => trans(\Locales::getNamespace() . '/datatables.phone'),
                        'search' => true,
                    ],
                    [
                        'selector' => 'legal_representatives.email',
                        'id' => 'email',
                        'name' => trans(\Locales::getNamespace() . '/datatables.email'),
                        'search' => true,
                    ],
                    [
                        'selector' => 'legal_representative_access.dfrom',
                        'id' => 'dfrom',
                        'name' => trans(\Locales::getNamespace() . '/datatables.dateFrom'),
                        'data' => [
                            'type' => 'sort',
                            'id' => 'dfrom',
                            'date' => 'YYmmdd',
                        ],
                    ],
                    [
                        'selector' => 'legal_representative_access.dto',
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
            'representatives' => [
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
        $breadcrumbs[] = ['id' => 'legal-representatives', 'slug' => 'legal-representatives', 'name' => trans(\Locales::getNamespace() . '/multiselect.apartmentProperties.legal-representatives')];

        $datatable->setup(LegalRepresentativeAccess::leftJoin('legal_representatives', 'legal_representative_access.legal_representative_id', '=', 'legal_representatives.id')->where('legal_representative_access.apartment_id', $apartment->id), $this->route, $this->datatables[$this->route]);
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

        $this->multiselect['representatives']['options'] = LegalRepresentative::select('legal_representatives.id', 'legal_representatives.name')->whereNotExists(function ($query) use ($apartment) {
            $query->from('legal_representative_access')->whereRaw('legal_representative_access.legal_representative_id = legal_representatives.id')->where('legal_representative_access.apartment_id', $apartment)->whereNull('legal_representative_access.deleted_at');
        })->orderBy('name')->get()->toarray();
        $this->multiselect['representatives']['selected'] = '';

        $multiselect = $this->multiselect;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.add', compact('table', 'apartment', 'multiselect'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function save(DataTable $datatable, ApartmentLegalRepresentativeRequest $request)
    {
        $apartment = Apartment::findOrFail([$request->input('apartment')])->first();

        $now = Carbon::now();

        $legalRepresentatives = [];
        foreach ($request->input('representatives') as $legalRepresentative) {
            array_push($legalRepresentatives, [
                'created_at' => $now,
                'apartment_id' => $apartment->id,
                'legal_representative_id' => $legalRepresentative,
                'dfrom' => Carbon::parse($request->input('dfrom'))->toDateTimeString(),
                'dto' => Carbon::parse($request->input('dto'))->toDateTimeString(),
            ]);
        }
        LegalRepresentativeAccess::insert($legalRepresentatives);

        $successMessage = trans(\Locales::getNamespace() . '/forms.savedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityLegalRepresentatives', 1)]);

        $datatable->setup(LegalRepresentativeAccess::leftJoin('legal_representatives', 'legal_representative_access.legal_representative_id', '=', 'legal_representatives.id')->where('legal_representative_access.apartment_id', $apartment->id), $request->input('table'), $this->datatables[$request->input('table')], true);
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
            LegalRepresentativeAccess::whereIn('id', $request->input('id'))->delete();

            $datatable->setup(LegalRepresentativeAccess::leftJoin('legal_representatives', 'legal_representative_access.legal_representative_id', '=', 'legal_representatives.id')->where('legal_representative_access.apartment_id', $apartment->id), $request->input('table'), $this->datatables[$request->input('table')], true);
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
        $legalRepresentative = LegalRepresentativeAccess::findOrFail($id);
        $table = $request->input('table');

        $this->multiselect['representatives']['options'] = LegalRepresentative::select('legal_representatives.id', 'legal_representatives.name')->where('id', $legalRepresentative->legal_representative_id)->get()->toarray();
        $this->multiselect['representatives']['selected'] = $legalRepresentative->legal_representative_id;

        $multiselect = $this->multiselect;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.add', compact('table', 'legalRepresentative', 'multiselect'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, ApartmentLegalRepresentativeRequest $request)
    {
        $legalRepresentative = LegalRepresentativeAccess::findOrFail($request->input('id'))->first();

        if ($legalRepresentative->update($request->all())) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityLegalRepresentatives', 1)]);

            $datatable->setup(LegalRepresentativeAccess::leftJoin('legal_representatives', 'legal_representative_access.legal_representative_id', '=', 'legal_representatives.id')->where('legal_representative_access.apartment_id', $legalRepresentative->apartment_id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, true));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityLegalRepresentatives', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

}
