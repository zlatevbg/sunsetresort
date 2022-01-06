<?php namespace App\Http\Controllers\Owners;

use App\Http\Controllers\Controller;
use App\Services\DataTable;
use App\Models\Owners\Owner;
use App\Models\Owners\Apartment;
use App\Models\Owners\CouncilTax;
use App\Models\Owners\Ownership;
use App\Models\Owners\Year;
use App\Models\Owners\KeyLog;
use App\Models\Owners\Contract;
use Carbon\Carbon;

class ApartmentController extends Controller {

    protected $route = 'apartments';
    protected $datatables;

    public function __construct()
    {
        $this->datatables = [
            $this->route => [
                'dom' => 'tr',
                'title' => \Locales::getMenu(\Slug::getSlug())['title'],
                'class' => 'table-striped table-bordered table-hover',
                'columns' => [
                    [
                        'selector' => 'project_translations.name',
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.project'),
                        'order' => false,
                        'join' => [
                            'table' => 'project_translations',
                            'localColumn' => 'project_translations.project_id',
                            'constrain' => '=',
                            'foreignColumn' => $this->route . '.project_id',
                            'whereColumn' => 'project_translations.locale',
                            'whereConstrain' => '=',
                            'whereValue' => \Locales::getCurrent(),
                        ],
                    ],
                    [
                        'selector' => $this->route . '.number',
                        'id' => 'number',
                        'name' => trans(\Locales::getNamespace() . '/datatables.apartmentNumber'),
                        'order' => false,
                        'link' => [
                            'icon' => 'file',
                            'route' => $this->route,
                            'routeParameter' => 'number',
                        ],
                    ],
                ],
                'orderByColumn' => $this->route . '.number',
                'order' => 'asc',
            ],
            'details' => [
                'dom' => 'tr',
                'subtitle' => trans(\Locales::getNamespace() . '/datatables.titleApartmentDetails'),
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'columns' => [
                    [
                        'id' => 'project',
                        'name' => trans(\Locales::getNamespace() . '/datatables.project'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'building',
                        'name' => trans(\Locales::getNamespace() . '/datatables.building'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'floor',
                        'name' => trans(\Locales::getNamespace() . '/datatables.floor'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'room',
                        'name' => trans(\Locales::getNamespace() . '/datatables.room'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'furniture',
                        'name' => trans(\Locales::getNamespace() . '/datatables.furniture'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'view',
                        'name' => trans(\Locales::getNamespace() . '/datatables.view'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                ],
            ],
            'areas' => [
                'dom' => 'tr',
                'subtitle' => trans(\Locales::getNamespace() . '/datatables.titleApartmentAreas'),
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'columns' => [
                    [
                        'id' => 'total',
                        'name' => trans(\Locales::getNamespace() . '/datatables.totalArea'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'apartment',
                        'name' => trans(\Locales::getNamespace() . '/datatables.apartmentArea'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'common',
                        'name' => trans(\Locales::getNamespace() . '/datatables.commonArea'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'balcony',
                        'name' => trans(\Locales::getNamespace() . '/datatables.balconyArea'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'extra',
                        'name' => trans(\Locales::getNamespace() . '/datatables.extraArea'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                ],
            ],
            'bank' => [
                'dom' => 'tr',
                'subtitle' => trans(\Locales::getNamespace() . '/datatables.titleBankAccountDetails'),
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'columns' => [
                    [
                        'id' => 'iban',
                        'name' => trans(\Locales::getNamespace() . '/datatables.iban'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'bic',
                        'name' => trans(\Locales::getNamespace() . '/datatables.swift'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'beneficiary',
                        'name' => trans(\Locales::getNamespace() . '/datatables.beneficiary'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.bankName'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'rental',
                        'name' => trans(\Locales::getNamespace() . '/datatables.rentalAmount'),
                        'order' => false,
                        'class' => 'text-center vertical-center',
                    ],
                ],
            ],
            'owners' => [
                'dom' => 'tr',
                'subtitle' => trans(\Locales::getNamespace() . '/datatables.titleCoOwners'),
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'columns' => [
                    [
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.name'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'phone',
                        'name' => trans(\Locales::getNamespace() . '/datatables.phone'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'mobile',
                        'name' => trans(\Locales::getNamespace() . '/datatables.mobile'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'email',
                        'name' => trans(\Locales::getNamespace() . '/datatables.email'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                ],
            ],
            'agents' => [
                'dom' => 'tr',
                'subtitle' => trans(\Locales::getNamespace() . '/datatables.titleAgents'),
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'columns' => [
                    [
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.name'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'dfrom',
                        'name' => trans(\Locales::getNamespace() . '/datatables.dfrom'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'dto',
                        'name' => trans(\Locales::getNamespace() . '/datatables.dto'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                ],
            ],
            'keyholders' => [
                'dom' => 'tr',
                'subtitle' => trans(\Locales::getNamespace() . '/datatables.titleKeyholders'),
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'columns' => [
                    [
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.name'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'dfrom',
                        'name' => trans(\Locales::getNamespace() . '/datatables.dfrom'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'dto',
                        'name' => trans(\Locales::getNamespace() . '/datatables.dto'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                ],
            ],
            'taxes' => [
                'dom' => 'tr',
                'subtitle' => trans(\Locales::getNamespace() . '/datatables.titleCouncilTax'),
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'columns' => [
                    [
                        'id' => 'bulstat',
                        'name' => trans(\Locales::getNamespace() . '/datatables.bulstat'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'tax_pin',
                        'name' => trans(\Locales::getNamespace() . '/datatables.pin'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'tax',
                        'name' => trans(\Locales::getNamespace() . '/datatables.amount'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'checked_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.date'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                ],
            ],
            'key-log' => [
                'dom' => 'tr',
                'subtitle' => trans(\Locales::getNamespace() . '/datatables.titleKeyLog'),
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'columns' => [
                    [
                        'id' => 'year',
                        'name' => trans(\Locales::getNamespace() . '/datatables.year'),
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'total',
                        'name' => trans(\Locales::getNamespace() . '/datatables.total'),
                        'order' => false,
                        'class' => 'text-right vertical-center',
                    ],
                ],
            ],
            'mm-fees' => [
                'dom' => 'tr',
                'subtitle' => trans(\Locales::getNamespace() . '/datatables.titleMMFees'),
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'columns' => [
                    [
                        'id' => 'year',
                        'name' => trans(\Locales::getNamespace() . '/datatables.year'),
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'mm_tax',
                        'name' => trans(\Locales::getNamespace() . '/datatables.mmTax'),
                        'order' => false,
                        'class' => 'text-right vertical-center',
                    ],
                    [
                        'id' => 'deadline_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.deadlineAt'),
                        'order' => false,
                        'class' => 'text-right vertical-center',
                    ],
                    [
                        'id' => 'fees',
                        'name' => trans(\Locales::getNamespace() . '/datatables.mmFees'),
                        'order' => false,
                        'class' => 'text-right vertical-center',
                    ],
                    [
                        'id' => 'balance',
                        'name' => trans(\Locales::getNamespace() . '/datatables.balance'),
                        'order' => false,
                        'class' => 'text-right vertical-center payment-info',
                    ],
                ],
            ],
            'communal-fees' => [
                'dom' => 'tr',
                'subtitle' => trans(\Locales::getNamespace() . '/datatables.titleCommunalFees'),
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'columns' => [
                    [
                        'id' => 'year',
                        'name' => trans(\Locales::getNamespace() . '/datatables.year'),
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'deadline_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.deadlineAt'),
                        'order' => false,
                        'class' => 'text-right vertical-center',
                    ],
                    [
                        'id' => 'fees',
                        'name' => trans(\Locales::getNamespace() . '/datatables.communalTax'),
                        'order' => false,
                        'class' => 'text-right vertical-center',
                    ],
                    [
                        'id' => 'balance',
                        'name' => trans(\Locales::getNamespace() . '/datatables.balance'),
                        'order' => false,
                        'class' => 'text-right vertical-center payment-info',
                    ],
                ],
            ],
            'pool-usage' => [
                'dom' => 'tr',
                'subtitle' => trans(\Locales::getNamespace() . '/datatables.titlePoolUsage'),
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'columns' => [
                    [
                        'id' => 'year',
                        'name' => trans(\Locales::getNamespace() . '/datatables.year'),
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'deadline_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.deadlineAt'),
                        'order' => false,
                        'class' => 'text-right vertical-center',
                    ],
                    [
                        'id' => 'fees',
                        'name' => trans(\Locales::getNamespace() . '/datatables.poolTax'),
                        'order' => false,
                        'class' => 'text-right vertical-center',
                    ],
                    [
                        'id' => 'balance',
                        'name' => trans(\Locales::getNamespace() . '/datatables.balance'),
                        'order' => false,
                        'class' => 'text-right vertical-center payment-info',
                    ],
                ],
            ],
            'contracts' => [
                'dom' => 'tr',
                'subtitle' => trans(\Locales::getNamespace() . '/datatables.titleRentalContracts'),
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'columns' => [
                    [
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.contract'),
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'signed_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.signedAt'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'duration',
                        'name' => trans(\Locales::getNamespace() . '/datatables.duration'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'status',
                        'name' => trans(\Locales::getNamespace() . '/datatables.status'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                ],
            ],
        ];
    }

    public function index(DataTable $datatable, Year $year, $number = null)
    {
        $owner_id = \Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user()->id;

        if ($number) {
            $apartment = Apartment::with(['buildingMM', 'mmFeesPayments', 'poolUsageContracts', 'room'])->selectRaw('project_translations.name as project, building_translations.name as building, floor_translations.name as floor, room_translations.name as room_name, room_translations.description as room_description, furniture_translations.name as furniture, view_translations.name as view, apartments.building_id, apartments.room_id, apartments.id, apartments.number, apartments.total_area, apartments.extra_balcony_area, apartments.balcony_area, apartments.common_area, apartments.apartment_area, apartments.total_area, apartments.mm_tax_formula, bank_accounts.bank_iban, bank_accounts.bank_bic, bank_accounts.bank_beneficiary, bank_accounts.bank_name, bank_accounts.rental, YEAR(ownership.created_at) as year')->leftJoin('ownership', 'ownership.apartment_id', '=', 'apartments.id')->leftJoin('project_translations', function ($join) {
                $join->on('project_translations.project_id', '=', 'apartments.project_id')->where('project_translations.locale', '=', \Locales::getCurrent());
            })->leftJoin('building_translations', function ($join) {
                $join->on('building_translations.building_id', '=', 'apartments.building_id')->where('building_translations.locale', '=', \Locales::getCurrent());
            })->leftJoin('floor_translations', function ($join) {
                $join->on('floor_translations.floor_id', '=', 'apartments.floor_id')->where('floor_translations.locale', '=', \Locales::getCurrent());
            })->leftJoin('room_translations', function ($join) {
                $join->on('room_translations.room_id', '=', 'apartments.room_id')->where('room_translations.locale', '=', \Locales::getCurrent());
            })->leftJoin('furniture_translations', function ($join) {
                $join->on('furniture_translations.furniture_id', '=', 'apartments.furniture_id')->where('furniture_translations.locale', '=', \Locales::getCurrent());
            })->leftJoin('view_translations', function ($join) {
                $join->on('view_translations.view_id', '=', 'apartments.view_id')->where('view_translations.locale', '=', \Locales::getCurrent());
            })->leftJoin('bank_accounts', function ($join) use ($owner_id) {
                $join->on('bank_accounts.id', '=', 'ownership.bank_account_id')->where('bank_accounts.owner_id', '=', $owner_id);
            })->where('apartments.number', $number)->where('ownership.owner_id', '=', $owner_id)->whereNull('ownership.deleted_at')->whereNull('apartments.deleted_at')->firstOrFail();

            $owners = Owner::select('owners.first_name', 'owners.last_name', 'owners.phone', 'owners.mobile', 'owners.email', 'owners.email_cc')->leftJoin('ownership', 'owners.id', '=', 'ownership.owner_id')->where('owners.id', '!=', $owner_id)->where('ownership.apartment_id', $apartment->id)->whereNull('ownership.deleted_at')->whereNull('owners.deleted_at')->orderBy('owners.first_name')->get();

            $agents = $apartment->agents()->select('agents.name', 'agent_access.created_at', 'agent_access.deleted_at')->leftJoin('agents', 'agents.id', '=', 'agent_access.agent_id')->orderByRaw('agent_access.deleted_at is null desc')->orderBy('agent_access.deleted_at', 'desc')->get();

            $keyholders = $apartment->keyholders()->select('keyholders.name', 'keyholder_access.created_at', 'keyholder_access.deleted_at')->leftJoin('keyholders', 'keyholders.id', '=', 'keyholder_access.keyholder_id')->orderByRaw('keyholder_access.deleted_at is null desc')->orderBy('keyholder_access.deleted_at', 'desc')->get();

            $taxes = CouncilTax::select('council_tax.tax', 'council_tax.checked_at', 'owners.bulstat', 'owners.tax_pin')->leftJoin('owners', 'owners.id', '=', 'council_tax.owner_id')->where('council_tax.apartment_id', $apartment->id)->where('council_tax.owner_id', $owner_id)->get();

            $years = $year->with('fees')->orderBy('year', 'desc')->get();

            $contracts = Contract::withTrashed()->select('rental_contracts.rental_payment_id', 'rental_contract_translations.name', 'contracts.id', 'contracts.signed_at', 'contracts.duration', 'contracts.deleted_at')->leftJoin('rental_contracts', function ($join) {
                $join->on('rental_contracts.id', '=', 'contracts.rental_contract_id');
            })->leftJoin('rental_contract_translations', function ($join) {
                $join->on('rental_contract_translations.rental_contract_id', '=', 'rental_contracts.id')->where('rental_contract_translations.locale', '=', \Locales::getCurrent());
            })->where('contracts.apartment_id', $apartment->id)->whereRaw('YEAR(rental_contracts.contract_dfrom1) + contracts.duration >= ?', [$apartment->year])->orderBy('contracts.signed_at', 'desc')->orderBy('contracts.deleted_at', 'asc')->get();

            $breadcrumbs = [];
            $breadcrumbs[] = ['id' => 'apartment', 'slug' => $apartment->number, 'name' => $apartment->number];

            $metaTitle = trans(\Locales::getNamespace() . '/datatables.apartment') . ': ' . $apartment->number;
            $metaDescription = trans(\Locales::getNamespace() . '/datatables.apartment') . ': ' . $apartment->number;

            $datatable->setup(null, 'details', $this->datatables['details']);
            $datatable->setOption('data', [
                [
                    'project' => $apartment->project,
                    'building' => $apartment->building,
                    'floor' => $apartment->floor,
                    'room' => $apartment->room_name . ' (' . $apartment->room_description . ')',
                    'furniture' => $apartment->furniture,
                    'view' => $apartment->view,
                ],
            ]);

            $datatable->setup(null, 'areas', $this->datatables['areas']);
            $datatable->setOption('data', [
                [
                    'total' => $apartment->total_area . ' ' . trans(\Locales::getNamespace() . '/datatables.m2'),
                    'apartment' => $apartment->apartment_area . ' ' . trans(\Locales::getNamespace() . '/datatables.m2'),
                    'common' => $apartment->common_area . ' ' . trans(\Locales::getNamespace() . '/datatables.m2'),
                    'balcony' => $apartment->balcony_area . ' ' . trans(\Locales::getNamespace() . '/datatables.m2'),
                    'extra' => $apartment->extra_balcony_area . ' ' . trans(\Locales::getNamespace() . '/datatables.m2'),
                ],
            ]);

            if ($apartment->bank_iban) {
                $datatable->setup(null, 'bank', $this->datatables['bank']);
                $datatable->setOption('data', [
                    [
                        'iban' => \Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user()->email == 'dummy@sunsetresort.bg' ? 'IE51AIBM99339719275021' : $apartment->bank_iban,
                        'bic' => $apartment->bank_bic,
                        'beneficiary' => \Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user()->email == 'dummy@sunsetresort.bg' ? 'Dummy Name' : $apartment->bank_beneficiary,
                        'name' => $apartment->bank_name,
                        'rental' => trans(\Locales::getNamespace() . '/multiselect.rentalAmountOptions')[$apartment->rental],
                    ]
                ]);
            }

            if ($owners->count()) {
                $data = [];
                foreach ($owners as $owner) {
                    array_push($data, [
                        'name' => $owner->full_name,
                        'phone' => $owner->phone,
                        'mobile' => $owner->mobile,
                        'email' => $owner->email . ($owner->email_cc ? '<br>' . $owner->email_cc : ''),
                    ]);
                }
                $datatable->setup(null, 'owners', $this->datatables['owners']);
                $datatable->setOption('data', $data);
            }

            if ($agents->count()) {
                $data = [];
                foreach ($agents as $agent) {
                    array_push($data, [
                        'name' => $agent->name,
                        'dfrom' => $agent->created_at,
                        'dto' => $agent->deleted_at ?: trans(\Locales::getNamespace() . '/datatables.present'),
                    ]);
                }
                $datatable->setup(null, 'agents', $this->datatables['agents']);
                $datatable->setOption('data', $data);
            }

            if ($keyholders->count()) {
                $data = [];
                foreach ($keyholders as $keyholder) {
                    array_push($data, [
                        'name' => $keyholder->name,
                        'dfrom' => $keyholder->created_at,
                        'dto' => $keyholder->deleted_at ?: trans(\Locales::getNamespace() . '/datatables.present'),
                    ]);
                }

                $datatable->setup(null, 'keyholders', $this->datatables['keyholders']);
                $datatable->setOption('data', $data);
            }

            if ($taxes->count()) {
                $data = [];
                foreach ($taxes as $tax) {
                    array_push($data, [
                        'bulstat' => $tax->bulstat,
                        'tax_pin' => $tax->tax_pin,
                        'tax' => $tax->tax,
                        'checked_at' => $tax->checked_at,
                    ]);
                }

                $datatable->setup(null, 'taxes', $this->datatables['taxes']);
                $datatable->setOption('data', $data);
            }

            if ($years->count()) {
                $data = [];
                $log = KeyLog::selectRaw('YEAR(occupied_at) AS `year`, COUNT(*) AS total')->where('apartment_id', $apartment->id)->groupBy('year')->pluck('total', 'year');
                foreach ($years as $year) {
                    if ($year->year >= 2021 && $year->year >= $apartment->year && $year->year <= date('Y')) {

                        array_push($data, [
                            'year' => '<a class="js-popup" href="' . \Locales::route('key-log', [$apartment->id, $year->id]) . '"><span class="glyphicon glyphicon-plus glyphicon-left"></span>' . $year->year . '</a>',
                            'total' => $log[$year->year] ?? 0,
                        ]);
                    }
                }
                $datatable->setup(null, 'key-log', $this->datatables['key-log']);
                $datatable->setOption('data', $data);
            }

            if ($years->count()) {
                $data = [];
                foreach ($years as $year) {
                    if ($year->year >= $apartment->year) {
                        $mm = $apartment->buildingMM->where('year_id', $year->id)->first();
                        if ($mm) {
                            if ($year->year > 2020) {
                                $mmFeeTax = round(($apartment->room->capacity * $mm->mm_tax) / 1.95583);
                                $mmTax = '&euro; ' . number_format(ceil($mm->mm_tax / 1.95583), 2) . ' / ' . trans(\Locales::getNamespace() . '/datatables.person');
                            } else {
                                if ($apartment->mm_tax_formula == 0) {
                                    $mmFeeTax = (($apartment->apartment_area + $apartment->common_area + $apartment->balcony_area) * $mm->mm_tax) + ($apartment->extra_balcony_area * ($mm->mm_tax / 2));
                                } elseif ($apartment->mm_tax_formula == 1) {
                                    $mmFeeTax = $apartment->total_area * $mm->mm_tax;
                                }

                                $mmTax = '&euro; ' . number_format($mm->mm_tax, 2) . ' / ' . trans(\Locales::getNamespace() . '/datatables.m2');
                            }

                            $balance = round($mmFeeTax, 2) - round($apartment->mmFeesPayments->where('year_id', $year->id)->sum('amount'), 2);

                            array_push($data, [
                                'year' => '<a class="js-popup" href="' . \Locales::route('mm-fees', [$apartment->id, $year->id]) . '"><span class="glyphicon glyphicon-plus glyphicon-left"></span>' . $year->year . '</a>',
                                'mm_tax' => $mmTax,
                                'deadline_at' => $mm->deadline_at,
                                'fees' => '&euro; ' . number_format($mmFeeTax, 2),
                                'balance' => '
    <table class="table table-bordered">
        <tbody>
            <tr class="' . ($balance ? (Carbon::now() > Carbon::parse($mm->deadline_at)->year($year->year) ? 'bg-danger' : 'bg-warning') : 'bg-success') . '">
                <td>&euro; ' . number_format($balance, 2) . '</td>
            </tr>
        </tbody>
    </table>',
                            ]);
                        }
                    }
                }
                $datatable->setup(null, 'mm-fees', $this->datatables['mm-fees']);
                $datatable->setOption('data', $data);

                $data = [];
                foreach ($years as $year) {
                    if ($year->year > 2020 && $year->year >= $apartment->year) {
                        $mm = $apartment->buildingMM->where('year_id', $year->id)->first();
                        $fees = $year->fees->where('room_id', $apartment->room_id)->first();
                        if ($mm && $fees) {
                            $communalFeeTax = round($fees->annual_communal_tax / 1.95583);

                            $balance = round($communalFeeTax, 2) - round($apartment->communalFeesPayments->where('year_id', $year->id)->sum('amount'), 2);

                            array_push($data, [
                                'year' => '<a class="js-popup" href="' . \Locales::route('communal-fees', [$apartment->id, $year->id]) . '"><span class="glyphicon glyphicon-plus glyphicon-left"></span>' . $year->year . '</a>',
                                'deadline_at' => ($year->year == 2021 ? '15.06.2021' : $mm->deadline_at),
                                'fees' => '&euro; ' . number_format($communalFeeTax, 2),
                                'balance' => '
    <table class="table table-bordered">
        <tbody>
            <tr class="' . ($balance ? (Carbon::now() > Carbon::parse($year->year == 2021 ? '15.06.2021' : $mm->deadline_at)->year($year->year) ? 'bg-danger' : 'bg-warning') : 'bg-success') . '">
                <td>&euro; ' . number_format($balance, 2) . '</td>
            </tr>
        </tbody>
    </table>',
                            ]);
                        }
                    }
                }
                $datatable->setup(null, 'communal-fees', $this->datatables['communal-fees']);
                $datatable->setOption('data', $data);

                $data = [];
                foreach ($years as $year) {
                    if ($year->year > 2020 && $year->year >= $apartment->year) {
                        $contract = $apartment->poolUsageContracts->where('year_id', $year->id)->first();
                        if ($contract && $contract->is_active) {
                            $mm = $apartment->buildingMM->where('year_id', $year->id)->first();
                            $fees = $year->fees->where('room_id', $apartment->room_id)->first();
                            if ($mm && $fees) {
                                $poolUsageTax = round($fees->pool_tax / 1.95583);

                                $balance = round($poolUsageTax, 2) - round($apartment->poolUsagePayments->where('year_id', $year->id)->sum('amount'), 2);

                                array_push($data, [
                                    'year' => '<a class="js-popup" href="' . \Locales::route('pool-usage', [$apartment->id, $year->id]) . '"><span class="glyphicon glyphicon-plus glyphicon-left"></span>' . $year->year . '</a>',
                                    'deadline_at' => ($year->year == 2021 ? '20.05.2021' : $mm->deadline_at),
                                    'fees' => '&euro; ' . number_format($poolUsageTax, 2),
                                    'balance' => '
        <table class="table table-bordered">
            <tbody>
                <tr class="' . ($balance ? (Carbon::now() > Carbon::parse($year->year == 2021 ? '20.05.2021' : $mm->deadline_at)->year($year->year) ? 'bg-danger' : 'bg-warning') : 'bg-success') . '">
                    <td>&euro; ' . number_format($balance, 2) . '</td>
                </tr>
            </tbody>
        </table>',
                                ]);
                            }
                        }
                    }
                }
                $datatable->setup(null, 'pool-usage', $this->datatables['pool-usage']);
                $datatable->setOption('data', $data);
            }

            if ($contracts->count()) {
                $data = [];
                foreach ($contracts as $contract) {
                    array_push($data, [
                        'name' => '<a href="' . \Locales::route('contract', $contract->id) . '"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . $contract->name . '</a>',
                        'signed_at' => $contract->signed_at,
                        'duration' => $contract->duration . ' ' . trans_choice(\Locales::getNamespace() . '/datatables.choiceYears', $contract->duration),
                        'status' => $contract->deleted_at ? trans(\Locales::getNamespace() . '/datatables.canceled') . ': ' . Carbon::parse($contract->deleted_at)->format('d.m.Y') : trans(\Locales::getNamespace() . '/datatables.active'),
                    ]);
                }

                $datatable->setup(null, 'contracts', $this->datatables['contracts']);
                $datatable->setOption('data', $data);
            }

            $datatables = $datatable->getTables();

            return view(\Locales::getNamespace() . '/' . $this->route . '.apartment', compact('datatables', 'breadcrumbs', 'apartment', 'metaTitle', 'metaDescription'));
        } else {
            $datatable->setup(Ownership::leftJoin('apartments', 'ownership.apartment_id', '=', 'apartments.id')->where('ownership.owner_id', $owner_id), $this->route, $this->datatables[$this->route]);
            $datatables = $datatable->getTables();

            return view(\Locales::getNamespace() . '/' . $this->route . '.index', compact('datatables'));
        }
    }

}
