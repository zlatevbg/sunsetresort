<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Models\Sky\CouncilTax;
use App\Models\Sky\Owner;
use App\Models\Sky\Apartment;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Http\Requests\Sky\CouncilTaxRequest;

class CouncilTaxController extends Controller {

    protected $route = 'council-tax';
    protected $datatables;

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
                'title' => trans(\Locales::getNamespace() . '/datatables.titleCouncilTax'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'columns' => [
                    [
                        'selector' => 'council_tax.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center',
                    ],
                    [
                        'selector' => 'apartments.number',
                        'id' => 'number',
                        'name' => trans(\Locales::getNamespace() . '/datatables.apartment'),
                        'search' => true,
                        'class' => 'vertical-center',
                        'join' => [
                            'table' => 'apartments',
                            'localColumn' => 'apartments.id',
                            'constrain' => '=',
                            'foreignColumn' => 'council_tax.apartment_id',
                        ],
                    ],
                    [
                        'selector' => 'council_tax.tax',
                        'id' => 'tax',
                        'name' => trans(\Locales::getNamespace() . '/datatables.tax'),
                        'order' => false,
                    ],
                    [
                        'selector' => 'council_tax.checked_at',
                        'id' => 'checked_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.checkedAt'),
                        'order' => false,
                    ],
                ],
                'orderByColumn' => 'id',
                'order' => 'asc',
                'buttons' => [
                    'add' => [
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
    }

    public function index(DataTable $datatable, Request $request, $id = null)
    {
        $breadcrumbs = [];

        $owner = Owner::findOrFail($id);
        $breadcrumbs[] = ['id' => $owner->id, 'slug' => $owner->id, 'name' => $owner->full_name];
        $breadcrumbs[] = ['id' => 'council-tax', 'slug' => 'council-tax', 'name' => trans(\Locales::getNamespace() . '/multiselect.ownerProperties.council-tax')];

        $datatable->setup(CouncilTax::where('council_tax.owner_id', $owner->id), $this->route, $this->datatables[$this->route]);
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

        $apartments[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $apartments = $apartments + Apartment::select('apartments.id', 'apartments.number')->join('ownership', 'ownership.apartment_id', '=', 'apartments.id')->join('owners', 'owners.id', '=', 'ownership.owner_id')->whereNotExists(function ($query) use ($owner) {
            $query->from('council_tax')->whereRaw('council_tax.apartment_id = apartments.id AND council_tax.owner_id = owners.id')->where('council_tax.owner_id', $owner);
        })->whereNull('ownership.deleted_at')->where('owners.id', $owner)->orderBy('number')->get()->pluck('number', 'id')->toArray();

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.add', compact('table', 'owner', 'apartments'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function save(DataTable $datatable, CouncilTaxRequest $request)
    {
        $owner = Owner::findOrFail([$request->input('owner')])->first();

        $request->merge([
            'owner_id' => $owner->id,
        ]);

        $newTax = CouncilTax::create($request->all());

        if ($newTax->id) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.savedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityCouncilTax', 1)]);

            $datatable->setup(CouncilTax::where('owner_id', $owner->id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, true));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.createError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityCouncilTax', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
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
            CouncilTax::whereIn('id', $request->input('id'))->delete();

            $datatable->setup(CouncilTax::where('owner_id', $owner->id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, true));
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
        $tax = CouncilTax::findOrFail($id);

        $table = $request->input('table');

        $apartments = Apartment::where('id', $tax->apartment_id)->pluck('number', 'id')->toArray();

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.add', compact('table', 'tax', 'apartments'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, CouncilTaxRequest $request)
    {
        $tax = CouncilTax::findOrFail($request->input('id'))->first();

        if ($tax->update($request->all())) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityCouncilTax', 1)]);

            $datatable->setup(CouncilTax::where('owner_id', $tax->owner_id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, true));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityCouncilTax', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

}
