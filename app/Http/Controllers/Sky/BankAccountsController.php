<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Models\Sky\BankAccount;
use App\Models\Sky\Apartment;
use App\Models\Sky\Owner;
use App\Models\Sky\Ownership;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Http\Requests\Sky\BankAccountRequest;

class BankAccountsController extends Controller {

    protected $route = 'bank-accounts';
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
                'title' => trans(\Locales::getNamespace() . '/datatables.titleBankAccounts'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'joins' => [
                    [
                        'table' => 'ownership',
                        'localColumn' => 'ownership.bank_account_id',
                        'constrain' => '=',
                        'foreignColumn' => 'bank_accounts.id',
                        'whereNull' => 'ownership.deleted_at',
                        'group' => 'bank_accounts.id',
                    ],
                    [
                        'table' => 'apartments',
                        'localColumn' => 'apartments.id',
                        'constrain' => '=',
                        'foreignColumn' => 'ownership.apartment_id',
                    ],
                ],
                'selectors' => ['bank_accounts.comments'],
                'columns' => [
                    [
                        'selector' => 'bank_accounts.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center',
                    ],
                    [
                        'selector' => 'bank_accounts.bank_iban',
                        'id' => 'bank_iban',
                        'name' => trans(\Locales::getNamespace() . '/datatables.iban'),
                        'order' => false,
                        'info' => 'comments',
                    ],
                    [
                        'selector' => 'bank_accounts.bank_name',
                        'id' => 'bank_name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.bank'),
                        'order' => false,
                    ],
                    [
                        'selector' => 'bank_accounts.bank_beneficiary',
                        'id' => 'bank_beneficiary',
                        'name' => trans(\Locales::getNamespace() . '/datatables.beneficiary'),
                        'order' => false,
                    ],
                    [
                        'selectRaw' => 'GROUP_CONCAT(apartments.number ORDER BY apartments.number SEPARATOR ", ") as apartments',
                        'id' => 'apartments',
                        'name' => trans(\Locales::getNamespace() . '/datatables.apartments'),
                        'order' => false,
                    ],
                    [
                        'selector' => 'bank_accounts.rental',
                        'id' => 'rental',
                        'name' => trans(\Locales::getNamespace() . '/datatables.rentalAmount'),
                        'order' => false,
                        'replace' => [
                            'array' => trans(\Locales::getNamespace() . '/multiselect.rentalAmountOptions')
                        ],
                        'class' => 'text-center',
                    ],
                ],
                'orderByColumn' => 'bank_accounts.id',
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
            'apartments' => [
                'id' => 'id',
                'name' => 'number',
            ],
        ];
    }

    public function index(DataTable $datatable, Request $request, $id = null)
    {
        $breadcrumbs = [];

        $owner = Owner::findOrFail($id);
        $breadcrumbs[] = ['id' => $owner->id, 'slug' => $owner->id, 'name' => $owner->full_name];
        $breadcrumbs[] = ['id' => 'bank-accounts', 'slug' => 'bank-accounts', 'name' => trans(\Locales::getNamespace() . '/multiselect.ownerProperties.bank-accounts')];

        $datatable->setup(BankAccount::where('bank_accounts.owner_id', $owner->id), $this->route, $this->datatables[$this->route]);
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

        $amount = trans(\Locales::getNamespace() . '/multiselect.rentalAmountOptions');

        $this->multiselect['apartments']['options'] = Apartment::select('apartments.id', 'apartments.number')->leftJoin('ownership', 'ownership.apartment_id', '=', 'apartments.id')->leftJoin('bank_accounts', 'bank_accounts.id', '=', 'ownership.bank_account_id')->where('ownership.owner_id', $owner)->whereNull('ownership.deleted_at')->whereNotExists(function ($query) {
            $query->from('ownership')->whereRaw('ownership.apartment_id = apartments.id')->whereRaw('ownership.bank_account_id = bank_accounts.id');
        })->orderBy('apartments.number')->get()->toarray();
        $this->multiselect['apartments']['selected'] = '';

        $multiselect = $this->multiselect;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.add', compact('table', 'owner', 'multiselect', 'amount'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function save(DataTable $datatable, BankAccountRequest $request)
    {
        $owner = Owner::findOrFail([$request->input('owner')])->first();

        $request->merge([
            'owner_id' => $owner->id,
        ]);

        $newAccount = BankAccount::create($request->all());

        if ($newAccount->id) {
            foreach ($request->input('apartments') as $apartment) {
                Ownership::where('owner_id', $owner->id)->where('apartment_id', $apartment)->update(['bank_account_id' => $newAccount->id]);
            }

            $successMessage = trans(\Locales::getNamespace() . '/forms.savedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityBankAccounts', 1)]);

            $datatable->setup(BankAccount::where('bank_accounts.owner_id', $owner->id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, true));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.createError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityBankAccounts', 1)]);
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
            BankAccount::whereIn('id', $request->input('id'))->delete();

            $datatable->setup(BankAccount::where('bank_accounts.owner_id', $owner->id), $request->input('table'), $this->datatables[$request->input('table')], true);
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
        $account = BankAccount::findOrFail($id);

        $table = $request->input('table');

        $amount = trans(\Locales::getNamespace() . '/multiselect.rentalAmountOptions');

        $apartments = Apartment::select('apartments.id', 'apartments.number', 'ownership.bank_account_id')->leftJoin('ownership', 'ownership.apartment_id', '=', 'apartments.id')->where('ownership.owner_id', $account->owner_id)->whereNull('ownership.deleted_at')->where(function ($query) use ($account) {
            $query->where('ownership.bank_account_id', $account->id)->orWhereNull('ownership.bank_account_id');
        })->orderBy('apartments.number')->get();

        $selected = $apartments->filter(function ($value, $key) {
            if ($value->bank_account_id) {
                return true;
            }
        });

        $this->multiselect['apartments']['options'] = $apartments->toArray();
        $this->multiselect['apartments']['selected'] = $selected->pluck('id')->toArray();

        $multiselect = $this->multiselect;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.add', compact('table', 'account', 'multiselect', 'amount'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, BankAccountRequest $request)
    {
        $account = BankAccount::findOrFail($request->input('id'))->first();

        if ($account->update($request->all())) {
            $apartments = $account->ownership->pluck('apartment_id')->diff($request->input('apartments'));
            if ($apartments) {
                Ownership::where('owner_id', $account->owner_id)->whereIn('apartment_id', $apartments)->update(['bank_account_id' => null]);
            }

            Ownership::where('owner_id', $account->owner_id)->whereIn('apartment_id', $request->input('apartments'))->update(['bank_account_id' => $account->id]);

            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityBankAccounts', 1)]);

            $datatable->setup(BankAccount::where('bank_accounts.owner_id', $account->owner_id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, true));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityBankAccounts', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

}
