<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Models\Sky\Apartment;
use App\Models\Sky\RentalContract;
use App\Models\Sky\RentalPaymentPrices;
use App\Models\Sky\Poa;
use App\Models\Sky\Contract;
use App\Models\Sky\ContractYear;
use App\Services\DataTable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\Sky\ContractRequest;

class ContractController extends Controller {

    protected $route = 'contracts';
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
                'title' => trans(\Locales::getNamespace() . '/datatables.titleRentalContracts'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'selectors' => [$this->route . '.deleted_at AS deleted', $this->route . '.comments', $this->route . '.is_exception', 'rental_contracts.rental_payment_id'],
                'joins' => [
                    [
                        'table' => 'rental_contracts',
                        'localColumn' => 'rental_contracts.id',
                        'constrain' => '=',
                        'foreignColumn' => $this->route . '.rental_contract_id',
                    ],
                    [
                        'table' => 'rental_contract_translations',
                        'localColumn' => 'rental_contract_translations.rental_contract_id',
                        'constrain' => '=',
                        'foreignColumn' => 'rental_contracts.id',
                        'whereColumn' => 'rental_contract_translations.locale',
                        'whereConstrain' => '=',
                        'whereValue' => \Locales::getCurrent(),
                    ],
                ],
                'columns' => [
                    [
                        'selector' => $this->route . '.id',
                        'id' => 'id',
                        'checkbox' => true,
                        // 'id' => 'checkbox',
                        'order' => false,
                        'class' => 'text-center',
                        'width' => '1.25em',
                        /*'replace' => [
                            'id' => 'id',
                            'rules' => [
                                0 => [
                                    'column' => 'deleted_at',
                                    'value' => null,
                                    'checkbox' => true,
                                ],
                            ],
                        ],*/
                    ],
                    [
                        'selector' => 'rental_contract_translations.name',
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.contract'),
                        'search' => true,
                        'order' => false,
                        'info' => 'comments',
                        'append' => [
                            'rules' => [
                                'is_exception' => 1,
                            ],
                            'text' => '<span title="Exception" class="glyphicon glyphicon-right glyphicon-color-red glyphicon-large glyphicon-top glyphicon-alert"></span>',
                        ],
                        'link' => [
                            'selector' => ['apartment_id'],
                            'icon' => 'folder-open',
                            'route' => 'contract-years',
                            'routeParametersPrepend' => ['apartment_id' => 'contracts', 'id' => 'years'],
                            /*'rules' => [
                                [
                                    'column' => 'rental_payment_id',
                                    'icon' => 'folder-open',
                                ],
                            ],*/
                        ],
                    ],
                    [
                        'selector' => $this->route . '.signed_at',
                        'id' => 'signed_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.signedAt'),
                        'search' => true,
                    ],
                    [
                        'selector' => $this->route . '.duration',
                        'id' => 'duration',
                        'name' => trans(\Locales::getNamespace() . '/datatables.duration'),
                        'append' => [
                            'trans_choice' => 'datatables.choiceYears',
                        ],
                    ],
                    [
                        'selector' => $this->route . '.deleted_at',
                        'id' => 'deleted_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.status'),
                        'class' => 'text-center',
                        'order' => false,
                        'width' => '2.50em',
                        'status' => [
                            'test' => null,
                            'rules' => [
                                0 => [
                                    'icon' => 'off.gif',
                                    'title' => trans(\Locales::getNamespace() . '/datatables.statusOff'),
                                    'appendDate' => 'deleted_at',
                                ],
                                1 => [
                                    'icon' => 'on.gif',
                                    'title' => trans(\Locales::getNamespace() . '/datatables.statusOn'),
                                ],
                            ],
                        ],
                    ],
                ],
                'orderByColumn' => 'signed_at',
                'orderByColumnExtra' => ['deleted_at' => 'asc'],
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
                        'url' => \Locales::route($this->route . '/cancel'),
                        'class' => 'btn-danger disabled hidden js-destroy js-cancel-rental-contract',
                        'icon' => 'trash',
                        'name' => trans(\Locales::getNamespace() . '/forms.cancelButton'),
                    ],
                ],
            ],
        ];

        $this->multiselect = [
            'contracts' => [
                'id' => 'id',
                'name' => 'name',
                'data' => [
                    'min-duration' => 'min_duration',
                    'max-duration' => 'max_duration',
                    'start-year' => 'start_year',
                ],
            ],
            'duration' => [
                'id' => 'id',
                'name' => 'name',
            ],
        ];
    }

    public function index(DataTable $datatable, Request $request, $apartment = null, $contractsSlug = null)
    {
        $breadcrumbs = [];

        $apartment = Apartment::findOrFail($apartment);
        $breadcrumbs[] = ['id' => 'apartments', 'slug' => $apartment->id, 'name' => $apartment->number];
        $breadcrumbs[] = ['id' => $contractsSlug, 'slug' => $contractsSlug, 'name' => trans(\Locales::getNamespace() . '/multiselect.apartmentProperties.' . $contractsSlug)];

        $datatable->setup(Contract::withTrashed()->where('apartment_id', $apartment->id), $this->route, $this->datatables[$this->route]);
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

        $contracts[] = ['id' => '', 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        $contracts = array_merge($contracts, RentalContract::selectRaw('rental_contract_translations.name, rental_contracts.id, rental_contracts.min_duration, rental_contracts.max_duration, YEAR(LEAST(rental_contracts.contract_dfrom1, IFNULL(rental_contracts.contract_dfrom2, "9999-01-01 00:00:00"))) as start_year')->leftJoin('rental_contract_translations', 'rental_contract_translations.rental_contract_id', '=', 'rental_contracts.id')->where('rental_contract_translations.locale', \Locales::getCurrent())->whereNotExists(function ($query) use ($apartment) {
            $query->from('contracts')->whereRaw('contracts.rental_contract_id = rental_contracts.id')->where('contracts.apartment_id', $apartment)->whereNull('contracts.deleted_at');
        })->orderBy('rental_contract_translations.name')->get()->toArray());

        $this->multiselect['contracts']['options'] = $contracts;
        $this->multiselect['contracts']['selected'] = '';

        $duration[] = ['id' => '', 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];

        $this->multiselect['duration']['options'] = $duration;
        $this->multiselect['duration']['selected'] = '';

        $multiselect = $this->multiselect;

        $exceptions = trans(\Locales::getNamespace() . '/multiselect.exceptions');

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.add', compact('table', 'apartment', 'multiselect', 'exceptions'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function save(DataTable $datatable, ContractRequest $request)
    {
        $apartment = Apartment::findOrFail([$request->input('apartment')])->first();

        $request->merge([
            'apartment_id' => $apartment->id,
        ]);

        $newContract = Contract::create($request->all());

        if ($newContract->id) {
            $rentalContract = $newContract->rentalContract()->first();

            $price = null;
            if ($rentalContract->rental_payment_id) {
                $price = RentalPaymentPrices::leftJoin('rental_contracts', function ($join) {
                    $join->on('rental_contracts.rental_payment_id', '=', 'rental_payment_prices.rental_payment_id');
                })->leftJoin('contracts', function ($join) {
                    $join->on('contracts.rental_contract_id', '=', 'rental_contracts.id');
                })->leftJoin('apartments', function ($join) {
                    $join->on('apartments.id', '=', 'contracts.apartment_id');
                })->where('contracts.id', $newContract->id)->whereRaw('apartments.room_id = rental_payment_prices.room_id')->whereRaw('apartments.furniture_id = rental_payment_prices.furniture_id')->whereRaw('apartments.view_id = rental_payment_prices.view_id')->where('apartments.id', $apartment->id)->first()->price;
            }

            $now = Carbon::now();
            if ($rentalContract->contract_dfrom2) {
                $year = (Carbon::parse($rentalContract->contract_dfrom1) > Carbon::parse($rentalContract->contract_dfrom2)) ? Carbon::parse($rentalContract->contract_dfrom2)->year : Carbon::parse($rentalContract->contract_dfrom1)->year;
            } else {
                $year = Carbon::parse($rentalContract->contract_dfrom1)->year;
            }

            $years = [];
            for ($i = 0; $i < $request->input('duration'); $i++) {
                array_push($years, [
                    'created_at' => $now,
                    'contract_id' => $newContract->id,
                    'year' => $year + $i,
                    'mm_for_year' => $request->input('mm_for_year') + $i,
                    'price' => $price,
                    'is_exception' => $request->input('is_exception') ?: 0,
                    'contract_dfrom1' => $rentalContract->contract_dfrom1 ? Carbon::parse($rentalContract->contract_dfrom1)->addYear($i) : null,
                    'contract_dto1' => $rentalContract->contract_dto1 ? Carbon::parse($rentalContract->contract_dto1)->addYear($i) : null,
                    'contract_dfrom2' => $rentalContract->contract_dfrom2 ? Carbon::parse($rentalContract->contract_dfrom2)->addYear($i) : null,
                    'contract_dto2' => $rentalContract->contract_dto2 ? Carbon::parse($rentalContract->contract_dto2)->addYear($i) : null,
                    'personal_dfrom1' => $rentalContract->personal_dfrom1 ? Carbon::parse($rentalContract->personal_dfrom1)->addYear($i) : null,
                    'personal_dto1' => $rentalContract->personal_dto1 ? Carbon::parse($rentalContract->personal_dto1)->addYear($i) : null,
                    'personal_dfrom2' => $rentalContract->personal_dfrom2 ? Carbon::parse($rentalContract->personal_dfrom2)->addYear($i) : null,
                    'personal_dto2' => $rentalContract->personal_dto2 ? Carbon::parse($rentalContract->personal_dto2)->addYear($i) : null,
                ]);
            }

            ContractYear::insert($years);

            $successMessage = trans(\Locales::getNamespace() . '/forms.savedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityRentalContracts', 1)]);

            $datatable->setup(Contract::withTrashed()->where('apartment_id', $apartment->id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, true));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.createError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityRentalContracts', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function cancel(Request $request)
    {
        $table = $request->input('table');

        $apartment = $request->input('apartment') ?: null;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.cancel', compact('table', 'apartment'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function destroy(DataTable $datatable, Contract $contract, Request $request)
    {
        $apartment = Apartment::findOrFail([$request->input('apartment')])->first();

        $count = count($request->input('id'));

        if ($count > 0 && $contract->destroy($request->input('id'))) {
            // Poa::where('apartment_id', $apartment->id)->whereIn('contract_id', $request->input('id'))->delete();

            $datatable->setup(Contract::withTrashed()->where('apartment_id', $apartment->id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, true));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => trans(\Locales::getNamespace() . '/forms.cancelledSuccessfully'),
                'closePopup' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.countError');

            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function edit(Request $request, $id = null)
    {
        // $contract = Contract::findOrFail($id);
        $contract = Contract::withTrashed()->findOrFail($id);

        $table = $request->input('table');

        $duration = [];
        for ($i = $contract->rentalContract->min_duration; $i <= $contract->rentalContract->max_duration; $i++) {
            array_push($duration, ['id' => $i, 'name' => $i]);
        }
        $this->multiselect['duration']['options'] = $duration;
        $this->multiselect['duration']['selected'] = $contract->duration;

        $multiselect = $this->multiselect;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.add', compact('table', 'contract', 'multiselect'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, ContractRequest $request)
    {
        // $contract = Contract::findOrFail($request->input('id'))->first();
        $contract = Contract::withTrashed()->findOrFail($request->input('id'))->first();
        $duration = $contract->duration;

        if ($contract->update($request->all())) {
            if ($contract->duration != $duration) {
                $rentalContract = $contract->rentalContract()->first();
                $year = Carbon::parse($rentalContract->contract_dfrom1) > Carbon::parse($rentalContract->contract_dfrom2) ? Carbon::parse($rentalContract->contract_dfrom2)->year : Carbon::parse($rentalContract->contract_dfrom1)->year;

                if ($contract->duration > $duration) { // add years
                    $price = null;
                    if ($rentalContract->rental_payment_id) {
                        $price = RentalPaymentPrices::leftJoin('rental_contracts', function ($join) {
                            $join->on('rental_contracts.rental_payment_id', '=', 'rental_payment_prices.rental_payment_id');
                        })->leftJoin('contracts', function ($join) {
                            $join->on('contracts.rental_contract_id', '=', 'rental_contracts.id');
                        })->leftJoin('apartments', function ($join) {
                            $join->on('apartments.id', '=', 'contracts.apartment_id');
                        })->where('contracts.id', $contract->id)->whereRaw('apartments.room_id = rental_payment_prices.room_id')->whereRaw('apartments.furniture_id = rental_payment_prices.furniture_id')->whereRaw('apartments.view_id = rental_payment_prices.view_id')->where('apartments.id', $contract->apartment_id)->first()->price;
                    }

                    $now = Carbon::now();
                    $years = [];
                    for ($i = $duration; $i < $contract->duration; $i++) {
                        array_push($years, [
                            'created_at' => $now,
                            'contract_id' => $contract->id,
                            'year' => $year + $i,
                            'mm_for_year' => $year + $i,
                            'price' => $price,
                            'contract_dfrom1' => $rentalContract->contract_dfrom1 ? Carbon::parse($rentalContract->contract_dfrom1)->addYear($i) : null,
                            'contract_dto1' => $rentalContract->contract_dto1 ? Carbon::parse($rentalContract->contract_dto1)->addYear($i) : null,
                            'contract_dfrom2' => $rentalContract->contract_dfrom2 ? Carbon::parse($rentalContract->contract_dfrom2)->addYear($i) : null,
                            'contract_dto2' => $rentalContract->contract_dto2 ? Carbon::parse($rentalContract->contract_dto2)->addYear($i) : null,
                            'personal_dfrom1' => $rentalContract->personal_dfrom1 ? Carbon::parse($rentalContract->personal_dfrom1)->addYear($i) : null,
                            'personal_dto1' => $rentalContract->personal_dto1 ? Carbon::parse($rentalContract->personal_dto1)->addYear($i) : null,
                            'personal_dfrom2' => $rentalContract->personal_dfrom2 ? Carbon::parse($rentalContract->personal_dfrom2)->addYear($i) : null,
                            'personal_dto2' => $rentalContract->personal_dto2 ? Carbon::parse($rentalContract->personal_dto2)->addYear($i) : null,
                        ]);
                    }

                    ContractYear::insert($years);
                } else { // cancel years
                    ContractYear::where('contract_id', $contract->id)->where('year', '>=', ($year + $contract->duration))->delete();
                }
            }

            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityRentalContracts', 1)]);

            $datatable->setup(Contract::withTrashed()->where('apartment_id', $contract->apartment_id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, true));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityRentalContracts', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

}
