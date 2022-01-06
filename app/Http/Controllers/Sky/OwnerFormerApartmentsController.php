<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Models\Sky\Apartment;
use App\Models\Sky\Owner;
use App\Models\Sky\Ownership;
use App\Services\DataTable;
use Illuminate\Http\Request;

class OwnerFormerApartmentsController extends Controller {

    protected $route = 'owner-former-apartments';
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
                'title' => trans(\Locales::getNamespace() . '/datatables.titleFormerApartments'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
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
                        'selector' => 'apartments.number',
                        'id' => 'number',
                        'name' => trans(\Locales::getNamespace() . '/datatables.number'),
                        'order' => false,
                        'link' => [
                            'icon' => 'folder-open',
                            'route' => 'apartments',
                            'routeParameter' => 'apartment',
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

        $owner = Owner::findOrFail($id);
        $breadcrumbs[] = ['id' => $owner->id, 'slug' => $owner->id, 'name' => $owner->full_name];
        $breadcrumbs[] = ['id' => 'former-apartments', 'slug' => 'former-apartments', 'name' => trans(\Locales::getNamespace() . '/multiselect.ownerProperties.former-apartments')];

        $datatable->setup(Ownership::onlyTrashed()->leftJoin('apartments', 'ownership.apartment_id', '=', 'apartments.id')->where('ownership.owner_id', $owner->id), $this->route, $this->datatables[$this->route]);
        $datatable->setOption('owner', $owner->id);

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
            Ownership::whereIn('id', $request->input('id'))->forceDelete();

            $datatable->setup(Ownership::onlyTrashed()->leftJoin('apartments', 'ownership.apartment_id', '=', 'apartments.id')->where('ownership.owner_id', $owner->id), $request->input('table'), $this->datatables[$request->input('table')], true);
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
