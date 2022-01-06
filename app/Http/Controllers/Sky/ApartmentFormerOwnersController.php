<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Models\Sky\Apartment;
use App\Models\Sky\Owner;
use App\Models\Sky\Ownership;
use App\Services\DataTable;
use Illuminate\Http\Request;

class ApartmentFormerOwnersController extends Controller {

    protected $route = 'apartment-former-owners';
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
                'title' => trans(\Locales::getNamespace() . '/datatables.titleFormerOwners'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'selectors' => ['owners.id as owner', 'owners.comments'],
                'columns' => [
                    [
                        'selector' => 'ownership.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center',
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
                        'link' => [
                            'icon' => 'folder-open',
                            'route' => 'owners',
                            'routeParameter' => 'owner',
                        ],
                        'info' => 'comments',
                    ],
                    [
                        'selector' => 'ownership.created_at',
                        'id' => 'created_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.dateFrom'),
                        'order' => false,
                    ],
                    [
                        'selector' => 'ownership.deleted_at',
                        'id' => 'deleted_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.dateTo'),
                        'order' => false,
                    ],
                ],
                'orderByColumn' => 'id',
                'order' => 'asc',
                'buttons' => [
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

        $apartment = Apartment::findOrFail($id);
        $breadcrumbs[] = ['id' => $apartment->id, 'slug' => $apartment->id, 'name' => $apartment->number];
        $breadcrumbs[] = ['id' => 'former-owners', 'slug' => 'former-owners', 'name' => trans(\Locales::getNamespace() . '/multiselect.apartmentProperties.former-owners')];

        $datatable->setup(Ownership::onlyTrashed()->leftJoin('owners', 'ownership.owner_id', '=', 'owners.id')->where('ownership.apartment_id', $apartment->id), $this->route, $this->datatables[$this->route]);
        $datatable->setOption('apartment', $apartment->id);

        $datatables = $datatable->getTables();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($datatables);
        } else {
            return view(\Locales::getNamespace() . '.' . $this->route . '.index', compact('datatables', 'breadcrumbs'));
        }
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
            Ownership::whereIn('id', $request->input('id'))->forceDelete();

            $datatable->setup(Ownership::onlyTrashed()->leftJoin('owners', 'ownership.owner_id', '=', 'owners.id')->where('ownership.apartment_id', $apartment->id), $request->input('table'), $this->datatables[$request->input('table')], true);
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
