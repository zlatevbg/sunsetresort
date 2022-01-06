<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Models\Sky\Domain;
use App\Models\Sky\Locale;
use App\Http\Requests\Sky\DomainRequest;

class DomainController extends Controller {

    protected $route = 'domains';
    protected $datatables;
    protected $multiselect;

    public function __construct()
    {
        $this->datatables = [
            $this->route => [
                'title' => trans(\Locales::getNamespace() . '/datatables.titleDomains'),
                'url' => \Locales::route($this->route),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'columns' => [
                    [
                        'selector' => $this->route . '.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center',
                    ],
                    [
                        'selector' => $this->route . '.name',
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.name'),
                        'search' => true,
                        'append' => [
                            'selector' => [$this->route . '.description'],
                            'text' => 'description',
                        ],
                    ],
                    [
                        'selector' => $this->route . '.domain',
                        'id' => 'domain',
                        'name' => trans(\Locales::getNamespace() . '/datatables.domain'),
                        'search' => true,
                    ],
                    [
                        'selector' => $this->route . '.route',
                        'id' => 'route',
                        'name' => trans(\Locales::getNamespace() . '/datatables.defaultRoute'),
                    ],
                    [
                        'selector' => '',
                        'id' => 'locales',
                        'name' => trans(\Locales::getNamespace() . '/datatables.locales'),
                        'aggregate' => 'localesCount',
                    ],
                    [
                        'selector' => 'locales.name as defaultLocale',
                        'id' => 'defaultLocale',
                        'name' => trans(\Locales::getNamespace() . '/datatables.defaultLocale'),
                        'append' => [
                            'selector' => [$this->route . '.hide_default_locale'],
                            'rules' => [
                                'hide_default_locale' => 1,
                            ],
                            'text' => trans(\Locales::getNamespace() . '/messages.localeIsHidden'),
                        ],
                        'join' => [
                            'table' => 'locales',
                            'localColumn' => 'locales.id',
                            'constrain' => '=',
                            'foreignColumn' => $this->route . '.default_locale_id',
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

        $this->multiselect = [
            'locales' => [
                'id' => 'id',
                'name' => 'native',
                'subText' => 'name',
            ],
            'default_locale_id' => [
                'id' => 'id',
                'name' => 'native',
                'subText' => 'name',
            ],
        ];
    }

    public function index(DataTable $datatable, Domain $domain, Request $request)
    {
        $datatable->setup($domain, $this->route, $this->datatables[$this->route]);

        $datatables = $datatable->getTables();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($datatables);
        } else {
            return view(\Locales::getNamespace() . '.' . $this->route . '.index', compact('datatables'));
        }
    }

    public function create(Domain $domain, Locale $locale, Request $request)
    {
        $this->multiselect['locales']['options'] = $locale->select($this->multiselect['locales']['id'], $this->multiselect['locales']['name'], $this->multiselect['locales']['subText'])->get()->toArray();
        $this->multiselect['locales']['selected'] = $domain->locales->pluck('id')->toArray();

        $this->multiselect['default_locale_id']['options'] = [];
        $this->multiselect['default_locale_id']['selected'] = [];

        $multiselect = $this->multiselect;

        $table = $request->input('table') ?: $this->route;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('multiselect', 'table'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function store(DataTable $datatable, Domain $domain, DomainRequest $request)
    {
        $newDomain = Domain::create($request->all());

        if ($newDomain->id) {
            $newDomain->locales()->sync($request->input('locales'));

            $successMessage = trans(\Locales::getNamespace() . '/forms.storedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityDomains', 1)]);

            $datatable->setup($domain, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'reset' => true,
                'resetMultiselect' => [
                    'input-locales' => ['refresh'],
                    'input-default_locale_id' => ['empty', 'disable', 'refresh'],
                ],
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.createError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityDomains', 1)]);
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

    public function destroy(DataTable $datatable, Domain $domain, Request $request)
    {
        $count = count($request->input('id'));

        if ($count > 0 && $domain->destroy($request->input('id'))) {
            $datatable->setup($domain, $request->input('table'), $this->datatables[$request->input('table')], true);
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

    public function edit(Locale $locale, Request $request, $id = null)
    {
        $domain = Domain::findOrFail($id);

        $this->multiselect['locales']['options'] = $locale->select($this->multiselect['locales']['id'], $this->multiselect['locales']['name'], $this->multiselect['locales']['subText'])->get()->toArray();
        $this->multiselect['locales']['selected'] = $domain->locales->pluck('id')->toArray();

        $this->multiselect['default_locale_id']['options'] = array_where($this->multiselect['locales']['options'], function($key, $value) {
            if (in_array($value['id'], $this->multiselect['locales']['selected'])) {
                return true;
            }

            return false;
        });
        $this->multiselect['default_locale_id']['selected'] = $domain->default_locale_id;

        $multiselect = $this->multiselect;

        $table = $request->input('table') ?: $this->route;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('multiselect', 'domain', 'table'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, DomainRequest $request)
    {
        $domain = Domain::findOrFail($request->input('id'))->first();

        if ($domain->update($request->all())) {
            $domain->locales()->sync($request->input('locales'));

            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityDomains', 1)]);

            $datatable->setup($domain, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityDomains', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

}
