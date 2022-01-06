<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Models\Sky\Apartment;
use App\Models\Sky\Year;
use App\Models\Sky\PaymentMethod;
use App\Models\Sky\RentalCompany;
use App\Models\Sky\Owner;
use App\Models\Sky\PoolUsagePayment;
use App\Http\Requests\Sky\PoolUsagePaymentRequest;

class PoolUsagePaymentController extends Controller {

    protected $route = 'pool-usage-payments';
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
                'title' => trans(\Locales::getNamespace() . '/datatables.titlePoolUsagePayments'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'joins' => [
                    [
                        'table' => 'rental_company_translations',
                        'localColumn' => 'rental_company_translations.rental_company_id',
                        'constrain' => '=',
                        'foreignColumn' => 'pool_usage_payments.rental_company_id',
                        'whereColumn' => 'rental_company_translations.locale',
                        'whereConstrain' => '=',
                        'whereValue' => \Locales::getCurrent(),
                    ],
                    [
                        'table' => 'owners',
                        'localColumn' => 'owners.id',
                        'constrain' => '=',
                        'foreignColumn' => 'pool_usage_payments.owner_id',
                    ],
                ],
                'selectors' => ['pool_usage_payments.comments', 'rental_company_translations.name as company'],
                'columns' => [
                    [
                        'selector' => 'pool_usage_payments.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center',
                    ],
                    [
                        'selector' => 'pool_usage_payments.paid_at',
                        'id' => 'paid_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.paidAt'),
                        'order' => false,
                        'info' => 'comments',
                        'join' => [
                            'table' => 'years',
                            'localColumn' => 'years.id',
                            'constrain' => '=',
                            'foreignColumn' => 'pool_usage_payments.year_id',
                        ],
                        'link' => [
                            'selector' => ['years.year', 'pool_usage_payments.apartment_id'],
                            'icon' => 'folder-open',
                            'route' => 'pool-usage-payment-documents',
                            'routeParametersPrepend' => ['apartment_id' => 'pool-usage', 'year' => '', 'id' => ''],
                        ],
                    ],
                    [
                        'selector' => 'pool_usage_payments.amount',
                        'id' => 'amount',
                        'name' => trans(\Locales::getNamespace() . '/datatables.amount'),
                        'order' => false,
                        'class' => 'text-right',
                        'prepend' => [
                            'simpleText' => '&euro; ',
                        ],
                    ],
                    [
                        'selector' => 'payment_method_translations.name',
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.paymentMethod'),
                        'order' => false,
                        'join' => [
                            'table' => 'payment_method_translations',
                            'localColumn' => 'payment_method_translations.payment_method_id',
                            'constrain' => '=',
                            'foreignColumn' => 'pool_usage_payments.payment_method_id',
                            'whereColumn' => 'payment_method_translations.locale',
                            'whereConstrain' => '=',
                            'whereValue' => \Locales::getCurrent(),
                        ],
                    ],
                    [
                        'selectRaw' => 'CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as owner',
                        'id' => 'paidBy',
                        'name' => trans(\Locales::getNamespace() . '/datatables.paidBy'),
                        'order' => false,
                    ],
                ],
                'orderByColumn' => 'paid_at',
                'order' => 'desc',
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
            'paymentMethods' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'companies' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'owners' => [
                'id' => 'id',
                'name' => 'name',
            ],
        ];
    }

    public function index(DataTable $datatable, Request $request, $apartment = null, $poolUsageSlug = null, $year = null)
    {
        $breadcrumbs = [];

        $apartment = Apartment::findOrFail($apartment);
        $year = Year::where('year', $year)->firstOrFail();

        $breadcrumbs[] = ['id' => 'apartments', 'slug' => $apartment->id, 'name' => $apartment->number];
        $breadcrumbs[] = ['id' => $poolUsageSlug, 'slug' => $poolUsageSlug, 'name' => trans(\Locales::getNamespace() . '/multiselect.apartmentProperties.' . $poolUsageSlug)];
        $breadcrumbs[] = ['id' => 'year', 'slug' => $year->year, 'name' => $year->year];

        $datatable->setup(PoolUsagePayment::where('apartment_id', $apartment->id)->where('year_id', $year->id), $this->route, $this->datatables[$this->route]);
        $datatable->setOption('year', $year->id);
        $datatable->setOption('apartment', $apartment->id);
        $datatables = $datatable->getTables();

        foreach ($datatables[$this->route]['data'] as $key => $value) {
            $datatables[$this->route]['data'][$key]['paidBy'] = $value['company'] ?: $value['owner'];
        }

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

        $apartment = Apartment::findOrFail([$request->input('apartment')])->first()->id;
        $year = Year::findOrFail([$request->input('year')])->first()->id;

        $paymentMethods[] = ['id' => '', 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        $paymentMethods = array_merge($paymentMethods, PaymentMethod::withTranslation()->select('payment_method_translations.name', 'payment_methods.id')->leftJoin('payment_method_translations', 'payment_method_translations.payment_method_id', '=', 'payment_methods.id')->where('payment_method_translations.locale', \Locales::getCurrent())->orderBy('payment_method_translations.name')->get()->toArray());

        $this->multiselect['paymentMethods']['options'] = $paymentMethods;
        $this->multiselect['paymentMethods']['selected'] = '';

        $companies[] = ['id' => '', 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        $companies = array_merge($companies, RentalCompany::withTranslation()->select('rental_company_translations.name', 'rental_companies.id')->leftJoin('rental_company_translations', 'rental_company_translations.rental_company_id', '=', 'rental_companies.id')->where('rental_company_translations.locale', \Locales::getCurrent())->orderBy('rental_company_translations.name')->get()->toArray());

        $this->multiselect['companies']['options'] = $companies;
        $this->multiselect['companies']['selected'] = '';

        $owners[] = ['id' => '', 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        $owners = array_merge($owners, Owner::selectRaw('owners.id, CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as name')->leftJoin('ownership', 'ownership.owner_id', '=', 'owners.id')->whereNull('ownership.deleted_at')->where('ownership.apartment_id', $apartment)->orderBy('name')->get()->toArray());

        $this->multiselect['owners']['options'] = $owners;
        $this->multiselect['owners']['selected'] = '';

        $multiselect = $this->multiselect;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.add', compact('table', 'year', 'apartment', 'multiselect'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function save(DataTable $datatable, PoolUsagePaymentRequest $request)
    {
        $apartment = Apartment::findOrFail([$request->input('apartment')])->first();
        $year = Year::findOrFail([$request->input('year')])->first();

        $request->merge([
            'apartment_id' => $apartment->id,
            'year_id' => $year->id,
        ]);

        $payment = PoolUsagePayment::create($request->all());

        if ($payment->id) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.savedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityPayments', 1)]);

            $datatable->setup(PoolUsagePayment::where('apartment_id', $apartment->id)->where('year_id', $year->id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, true));
            $datatables = $datatable->getTables();

            foreach ($datatables[$this->route]['data'] as $key => $value) {
                $datatables[$this->route]['data'][$key]['paidBy'] = $value['company'] ?: $value['owner'];
            }

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.createError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityPayments', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function remove(Request $request)
    {
        $table = $request->input('table');

        $apartment = $request->input('apartment') ?: null;
        $year = $request->input('year') ?: null;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.remove', compact('table', 'apartment', 'year'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function destroy(DataTable $datatable, PoolUsagePayment $payment, Request $request)
    {
        $apartment = Apartment::findOrFail([$request->input('apartment')])->first();
        $year = Year::findOrFail([$request->input('year')])->first();

        $count = count($request->input('id'));

        if ($count > 0 && $payment->whereIn('id', $request->input('id'))->where('apartment_id', $apartment->id)->where('year_id', $year->id)->delete()) {
            $datatable->setup(PoolUsagePayment::where('apartment_id', $apartment->id)->where('year_id', $year->id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, true));
            $datatables = $datatable->getTables();

            foreach ($datatables[$this->route]['data'] as $key => $value) {
                $datatables[$this->route]['data'][$key]['paidBy'] = $value['company'] ?: $value['owner'];
            }

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
        $payment = PoolUsagePayment::findOrFail($id);

        $table = $request->input('table');

        $paymentMethods = PaymentMethod::withTranslation()->select('payment_method_translations.name', 'payment_methods.id')->leftJoin('payment_method_translations', 'payment_method_translations.payment_method_id', '=', 'payment_methods.id')->where('payment_method_translations.locale', \Locales::getCurrent())->orderBy('payment_method_translations.name')->get()->toArray();

        $this->multiselect['paymentMethods']['options'] = $paymentMethods;
        $this->multiselect['paymentMethods']['selected'] = $payment->payment_method_id;

        $companies[] = ['id' => '', 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        $companies = array_merge($companies, RentalCompany::withTranslation()->select('rental_company_translations.name', 'rental_companies.id')->leftJoin('rental_company_translations', 'rental_company_translations.rental_company_id', '=', 'rental_companies.id')->where('rental_company_translations.locale', \Locales::getCurrent())->orderBy('rental_company_translations.name')->get()->toArray());

        $this->multiselect['companies']['options'] = $companies;
        $this->multiselect['companies']['selected'] = $payment->rental_company_id;

        $owners[] = ['id' => '', 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        $owners = array_merge($owners, Owner::selectRaw('owners.id, CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as name')->leftJoin('ownership', 'ownership.owner_id', '=', 'owners.id')->whereNull('ownership.deleted_at')->where('ownership.apartment_id', $payment->apartment_id)->orderBy('name')->get()->toArray());

        $this->multiselect['owners']['options'] = $owners;
        $this->multiselect['owners']['selected'] = $payment->owner_id;

        $multiselect = $this->multiselect;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.add', compact('table', 'payment', 'multiselect'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, PoolUsagePaymentRequest $request)
    {
        $payment = PoolUsagePayment::findOrFail($request->input('id'))->first();

        if ($payment->update($request->all())) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityPayments', 1)]);

            $datatable->setup(PoolUsagePayment::where('apartment_id', $payment->apartment_id)->where('year_id', $payment->year_id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, true));
            $datatables = $datatable->getTables();

            foreach ($datatables[$this->route]['data'] as $key => $value) {
                $datatables[$this->route]['data'][$key]['paidBy'] = $value['company'] ?: $value['owner'];
            }

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityPayments', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

}
