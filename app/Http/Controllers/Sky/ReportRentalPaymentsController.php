<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Models\Sky\Project;
use App\Models\Sky\Building;
use App\Models\Sky\Apartment;
use App\Models\Sky\Room;
use App\Models\Sky\Furniture;
use App\Models\Sky\View;
use App\Models\Sky\Year;
use App\Models\Sky\RentalContract;
use App\Services\DataTable;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportRentalPaymentsController extends Controller
{

    protected $route = 'reports';
    protected $datatables;
    protected $multiselect;

    public function __construct()
    {
        $this->datatables = [
            $this->route => [
                'wrapperClass' => 'table-invisible',
                'dom' => "<'dataTableFull'l>tr<'clearfix'<'dataTableI'i><'dataTableP'p>>",
                'url' => \Locales::route('reports/rental-payments'),
                'class' => 'table-checkbox table-striped table-bordered table-hover table-report',
                'joins' => [
                    [
                        'table' => 'locales',
                        'localColumn' => 'locales.id',
                        'constrain' => '=',
                        'foreignColumn' => 'owners.locale_id',
                    ],
                    [
                        'table' => 'project_translations',
                        'localColumn' => 'project_translations.project_id',
                        'constrain' => '=',
                        'foreignColumn' => 'apartments.project_id',
                        'whereColumn' => 'project_translations.locale',
                        'whereConstrain' => '=',
                        'whereValue' => \Locales::getCurrent(),
                    ],
                    [
                        'table' => 'building_translations',
                        'localColumn' => 'building_translations.building_id',
                        'constrain' => '=',
                        'foreignColumn' => 'apartments.building_id',
                        'whereColumn' => 'building_translations.locale',
                        'whereConstrain' => '=',
                        'whereValue' => \Locales::getCurrent(),
                    ],
                    [
                        'table' => 'room_translations',
                        'localColumn' => 'room_translations.room_id',
                        'constrain' => '=',
                        'foreignColumn' => 'apartments.room_id',
                        'whereColumn' => 'room_translations.locale',
                        'whereConstrain' => '=',
                        'whereValue' => \Locales::getCurrent(),
                    ],
                    [
                        'table' => 'furniture_translations',
                        'localColumn' => 'furniture_translations.furniture_id',
                        'constrain' => '=',
                        'foreignColumn' => 'apartments.furniture_id',
                        'whereColumn' => 'furniture_translations.locale',
                        'whereConstrain' => '=',
                        'whereValue' => \Locales::getCurrent(),
                    ],
                    [
                        'table' => 'view_translations',
                        'localColumn' => 'view_translations.view_id',
                        'constrain' => '=',
                        'foreignColumn' => 'apartments.view_id',
                        'whereColumn' => 'view_translations.locale',
                        'whereConstrain' => '=',
                        'whereValue' => \Locales::getCurrent(),
                    ],
                ],
                'columns' => [
                    [
                        'selector' => 'apartments.number',
                        'id' => 'number',
                        'name' => trans(\Locales::getNamespace() . '/datatables.apartment'),
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'options',
                        'name' => trans(\Locales::getNamespace() . '/datatables.rentalOption'),
                        'class' => 'vertical-center',
                        'order' => false,
                        'calculateRentalOptions' => [
                            'deductPayments' => false,
                        ],
                    ],
                    [
                        'id' => 'mmYears',
                        'name' => trans(\Locales::getNamespace() . '/datatables.mmYears'),
                        'class' => 'text-right vertical-center',
                        'order' => false,
                    ],
                    [
                        'id' => 'mmFeeValue',
                        'name' => trans(\Locales::getNamespace() . '/datatables.mmFees'),
                        'class' => 'text-right vertical-center',
                        'order' => false,
                    ],
                    [
                        'id' => 'rentAmountValue',
                        'name' => trans(\Locales::getNamespace() . '/datatables.rentalAmount'),
                        'class' => 'text-right vertical-center',
                        'order' => false,
                    ],
                    [
                        'id' => 'deductionsValue',
                        'name' => trans(\Locales::getNamespace() . '/datatables.deductions'),
                        'class' => 'text-right vertical-center',
                        'order' => false,
                    ],
                    [
                        'id' => 'taxValue',
                        'name' => trans(\Locales::getNamespace() . '/datatables.deductionWithholdingTax'),
                        'class' => 'text-right vertical-center',
                        'order' => false,
                    ],
                    [
                        'id' => 'netRentValue',
                        'name' => trans(\Locales::getNamespace() . '/datatables.netRent'),
                        'class' => 'text-right vertical-center',
                        'order' => false,
                    ],
                    [
                        'id' => 'paymentsValue',
                        'name' => trans(\Locales::getNamespace() . '/datatables.payments'),
                        'class' => 'text-right vertical-center',
                        'order' => false,
                    ],
                    [
                        'id' => 'amount',
                        'name' => trans(\Locales::getNamespace() . '/datatables.balance'),
                        'class' => 'text-right vertical-center',
                        'order' => false,
                    ],
                    'bank' => [
                        'selectRaw' => 'CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) AS owner, apartments.room_id',
                        'id' => 'owner',
                        'name' => trans(\Locales::getNamespace() . '/datatables.owner'),
                        'order' => false,
                        'class' => 'vertical-center',
                        'info' => [
                            'array' => [
                                'phone' => trans(\Locales::getNamespace() . '/datatables.phone'),
                                'mobile' => trans(\Locales::getNamespace() . '/datatables.mobile'),
                                'email' => trans(\Locales::getNamespace() . '/datatables.email'),
                            ],
                        ],
                    ],
                    [
                        'selector' => 'locales.name AS language',
                        'id' => 'language',
                        'name' => trans(\Locales::getNamespace() . '/datatables.language'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'selector' => 'room_translations.name AS room',
                        'id' => 'room',
                        'name' => trans(\Locales::getNamespace() . '/datatables.room'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'selector' => 'view_translations.name AS view',
                        'id' => 'view',
                        'name' => trans(\Locales::getNamespace() . '/datatables.view'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'selector' => 'furniture_translations.name AS furniture',
                        'id' => 'furniture',
                        'name' => trans(\Locales::getNamespace() . '/datatables.furniture'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                ],
                'orderByColumn' => 'number',
                'orderByRaw' => 'CONCAT(SUBSTR(apartments.number, 1, LOCATE("-", apartments.number) - 1), LPAD(SUBSTR(apartments.number, LOCATE("-", apartments.number) + 1), 3, "0"))',
                'order' => 'asc',
                'footer' => [
                    'class' => 'bg-info',
                    'columns' => [
                        [ // apartment
                            'data' => '',
                        ],
                        [ // options
                            'data' => '',
                        ],
                        [ // MM Years
                            'data' => '',
                        ],
                        [ // MM Fees
                            'data' => '<span></span>',
                            'sum' => true,
                        ],
                        [ // Rent
                            'data' => '<span></span>',
                            'sum' => true,
                        ],
                        [ // Deductions
                            'data' => '<span></span>',
                            'sum' => true,
                        ],
                        [ // Tax
                            'data' => '<span></span>',
                            'sum' => true,
                        ],
                        [ // Net Rent
                            'data' => '<span></span>',
                            'sum' => true,
                        ],
                        [ // Payments
                            'data' => '<span></span>',
                            'sum' => true,
                        ],
                        [ // amount
                            'data' => '<span></span>',
                            'sum' => true,
                        ],
                        [ // owner
                            'data' => '',
                        ],
                        [ // language
                            'data' => '',
                        ],
                        [ // room
                            'data' => '',
                        ],
                        [ // view
                            'data' => '',
                        ],
                        [ // furniture
                            'data' => '',
                        ],
                    ],
                ],
            ],
        ];

        $this->multiselect = [
            'projects' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'buildings' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'apartments' => [
                'id' => 'id',
                'name' => 'number',
            ],
            'rooms' => [
                'id' => 'id',
                'name' => 'room',
            ],
            'furniture' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'views' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'years' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'languages' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'type' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'group' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'rental' => [
                'id' => 'id',
                'name' => 'name',
            ],
        ];
    }

    public function index(DataTable $datatable, Request $request, Apartment $apartments)
    {
        $datatable->setOption('skipLoading', true, $this->route);
        $datatable->setup($apartments, $this->route, $this->datatables[$this->route]);

        $datatables = $datatable->getTables();

        $this->multiselect['projects']['options'] = Project::withTranslation()->select('project_translations.name', 'projects.id')->leftJoin('project_translations', 'project_translations.project_id', '=', 'projects.id')->where('project_translations.locale', \Locales::getCurrent())->orderBy('project_translations.name')->get()->toArray();
        $this->multiselect['projects']['selected'] = '';

        $this->multiselect['buildings']['options'] = Building::withTranslation()->select('building_translations.name', 'buildings.id')->leftJoin('building_translations', 'building_translations.building_id', '=', 'buildings.id')->where('building_translations.locale', \Locales::getCurrent())->orderBy('building_translations.name')->get()->toArray();
        $this->multiselect['buildings']['selected'] = '';

        $this->multiselect['apartments']['options'] = Apartment::distinct()->select('apartments.id', 'apartments.number')->join('ownership', 'ownership.apartment_id', '=', 'apartments.id')->whereNull('ownership.deleted_at')->orderBy('number')->get()->toArray();
        $this->multiselect['apartments']['selected'] = '';

        $this->multiselect['rooms']['options'] = Room::withTranslation()->selectRaw('CONCAT(room_translations.name, " (", room_translations.description, ")") as room, rooms.id')->leftJoin('room_translations', 'room_translations.room_id', '=', 'rooms.id')->where('room_translations.locale', \Locales::getCurrent())->orderBy('room_translations.name')->get()->toArray();
        $this->multiselect['rooms']['selected'] = '';

        $this->multiselect['furniture']['options'] = Furniture::withTranslation()->select('furniture_translations.name', 'furniture.id')->leftJoin('furniture_translations', 'furniture_translations.furniture_id', '=', 'furniture.id')->where('furniture_translations.locale', \Locales::getCurrent())->orderBy('furniture_translations.name')->get()->toArray();
        $this->multiselect['furniture']['selected'] = '';

        $this->multiselect['views']['options'] = View::withTranslation()->select('view_translations.name', 'views.id')->leftJoin('view_translations', 'view_translations.view_id', '=', 'views.id')->where('view_translations.locale', \Locales::getCurrent())->orderBy('view_translations.name')->get()->toArray();
        $this->multiselect['views']['selected'] = '';

        $type = [];
        foreach (trans(\Locales::getNamespace() . '/multiselect.reportRentalPaymentsType') as $key => $value) {
            $type[] = ['id' => $key, 'name' => $value];
        }
        $this->multiselect['type']['options'] = $type;
        $this->multiselect['type']['selected'] = 'due';

        $group = [];
        foreach (trans(\Locales::getNamespace() . '/multiselect.reportGroupBy') as $key => $value) {
            $group[] = ['id' => $key, 'name' => $value];
        }
        $this->multiselect['group']['options'] = $group;
        $this->multiselect['group']['selected'] = '';

        $rental = [0 => ['id' => 'rental', 'name' => trans(\Locales::getNamespace() . '/multiselect.reportRentalOptions.rental')]];
        $rental = array_merge($rental, RentalContract::withTrashed()->withTranslation()->select('rental_contract_translations.name', 'rental_contracts.id')->leftJoin('rental_contract_translations', 'rental_contract_translations.rental_contract_id', '=', 'rental_contracts.id')->where('rental_contract_translations.locale', \Locales::getCurrent())->where(\DB::raw('YEAR(contract_dfrom1) + max_duration'), '>=', date('Y'))->orderByRaw('YEAR(rental_contracts.created_at) desc')->orderBy('rental_contract_translations.name')->get()->toArray());
        $this->multiselect['rental']['options'] = $rental;
        $this->multiselect['rental']['selected'] = 'rental';

        $years = [];
        foreach (Year::orderBy('year', 'desc')->get() as $key => $value) {
            $years[$key]['id'] = $value['year'];
            $years[$key]['name'] = $value['year'];
        }

        $this->multiselect['years']['options'] = $years;
        $this->multiselect['years']['selected'] = date('Y');

        $languages = [];
        foreach (\Locales::getPublicDomain()->locales->toArray() as $key => $value) {
            $languages[$key]['id'] = $value['id'];
            $languages[$key]['name'] = $value['name'];
        }

        $this->multiselect['languages']['options'] = $languages;
        $this->multiselect['languages']['selected'] = '';

        $multiselect = $this->multiselect;

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($datatables);
        } else {
            return view(\Locales::getNamespace() . '.' . $this->route . '.rental-payments', compact('datatables', 'multiselect'));
        }
    }

    public function getBuildings(Request $request, $projects = null)
    {
        $projects = $request->input('projects', $projects);

        $buildings = Building::withTranslation()->select('building_translations.name', 'buildings.id')->leftJoin('building_translations', 'building_translations.building_id', '=', 'buildings.id')->where('building_translations.locale', \Locales::getCurrent())->whereIn('buildings.project_id', $projects)->orderBy('building_translations.name')->get()->pluck('id', 'name')->toArray();

        $apartments = $this->getApartments($request, false, $request->input('projects'), array_values($buildings));

        return response()->json([
            'success' => true,
            'buildings' => $buildings,
            'apartments' => $apartments,
        ]);
    }

    public function getApartments(Request $request, $json = true, $projects = null, $buildings = null)
    {
        $projects = $request->input('projects', $projects);
        $buildings = $request->input('buildings', $buildings);

        $apartments = Apartment::distinct()->select('apartments.id', 'apartments.number')->join('ownership', 'ownership.apartment_id', '=', 'apartments.id')->whereNull('ownership.deleted_at');

        if ($projects) {
            $apartments = $apartments->whereIn('apartments.project_id', $projects);
        }

        if ($buildings) {
            $apartments = $apartments->whereIn('apartments.building_id', $buildings);
        }

        $apartments = $apartments->orderBy('apartments.number')->get()->pluck('id', 'number')->toArray();

        if ($json) {
            return response()->json([
                'success' => true,
                'apartments' => $apartments,
            ]);
        } else {
            return $apartments;
        }
    }

    public function getRentalOptions(Request $request)
    {
        $rental = [0 => ['id' => 'rental', 'name' => trans(\Locales::getNamespace() . '/multiselect.reportRentalOptions.rental')]];
        $rental = collect($rental)->pluck('id', 'name')->toArray();

        $options = array_merge($rental, RentalContract::withTrashed()->withTranslation()->select('rental_contract_translations.name', 'rental_contracts.id')->leftJoin('rental_contract_translations', 'rental_contract_translations.rental_contract_id', '=', 'rental_contracts.id')->where('rental_contract_translations.locale', \Locales::getCurrent())->where(\DB::raw('YEAR(contract_dfrom1) + max_duration'), '>=', $request->input('year'))->orderBy('rental_contract_translations.name')->get()->pluck('id', 'name')->toArray());

        return response()->json([
            'success' => true,
            'options' => $options,
        ]);
    }

    public function generate(DataTable $datatable, Request $request, Apartment $apartments)
    {
        set_time_limit(0);

        $year = Year::where('year', $request->input('year'))->firstOrFail();

        $apartments = $apartments->with(['mmFeesPayments', 'buildingMM' => function ($query) use ($year) {
            // $query->where('building_mm.year_id', $year->id); // I need all BuildingMM years, not just the selected one!!!
        }, 'contracts' => function ($query) use ($year) {
            if ($year->year < date('Y')) {
                $query->withTrashed();
            }
        }, 'contracts.contractYears' => function ($query) use ($year) {
            $query->where('contract_years.year', $year->year);

            if ($year->year < date('Y')) {
                $query->withTrashed();
            }
        }, 'contracts.rentalContract' => function ($query) {
            $query->withTrashed()->withTranslation();
        }, 'contracts.contractYears.payments', 'contracts.contractYears.deductions', 'owners' => function ($query) use ($year) {
            if ($year->year < date('Y')) {
                $query->withTrashed();
            }
        }, 'rooms' => function ($query) {
            $query->withTranslation();
        }])->join('ownership', 'ownership.apartment_id', '=', 'apartments.id')->join('owners', 'owners.id', '=', 'ownership.owner_id')->leftJoin('bank_accounts', function($join) {
            $join->on('bank_accounts.id', '=', 'ownership.bank_account_id')->where('bank_accounts.rental', '>', 0);
        })->whereYear('ownership.created_at', '<=', $year->year)->where(function ($query) use ($year) {
            $query->whereYear('ownership.deleted_at', '>=', $year->year)->orWhereNull('ownership.deleted_at');
        })->groupBy('apartments.id');

        if ($request->input('projects')) {
            $apartments = $apartments->whereIn('apartments.project_id', $request->input('projects'));
        }

        if ($request->input('buildings')) {
            $apartments = $apartments->whereIn('apartments.building_id', $request->input('buildings'));
        }

        if ($request->input('apartments')) {
            $apartments = $apartments->whereIn('apartments.id', $request->input('apartments'));
        }

        if ($request->input('rooms')) {
            $apartments = $apartments->whereIn('apartments.room_id', $request->input('rooms'));
        }

        if ($request->input('furniture')) {
            $apartments = $apartments->whereIn('apartments.furniture_id', $request->input('furniture'));
        }

        if ($request->input('views')) {
            $apartments = $apartments->whereIn('apartments.view_id', $request->input('views'));
        }

        if ($request->input('languages')) {
            $apartments = $apartments->whereIn('owners.locale_id', $request->input('languages'));
        }

        $selectors = ['apartments.id', 'apartments.mm_tax_formula', 'apartments.apartment_area', 'apartments.common_area', 'apartments.balcony_area', 'apartments.extra_balcony_area', 'apartments.total_area', 'apartments.building_id', 'owners.phone', 'owners.mobile', 'owners.email', 'project_translations.slug AS projectSlug', 'room_translations.slug AS roomSlug', 'view_translations.slug AS viewSlug'];
        if ($request->input('group') === 'project') {
            $selectors = array_merge($selectors, ['project_translations.name AS project', 'apartments.project_id AS project_id1']);
        } elseif ($request->input('group') === 'building') {
            $selectors = array_merge($selectors, ['building_translations.name AS building', 'apartments.building_id AS building_id1']);
        }

        if ($request->input('generate')) {
            $datatable->setOption('skipInfo', true, $this->route);
            $this->datatables[$this->route]['columns']['bank']['selectRaw'] .= ', GROUP_CONCAT(DISTINCT CONCAT(TRIM(bank_accounts.bank_beneficiary), ";;", TRIM(bank_accounts.bank_iban), ";;", TRIM(bank_accounts.bank_bic), ";;", TRIM(bank_accounts.bank_name), ";;", TRIM(bank_accounts.rental)) SEPARATOR "|") AS bank';
        }

        $datatable->setOption('selectors', $selectors, $this->route);

        $datatable->setup($apartments, $this->route, $this->datatables[$this->route], true);
        $datatables = $datatable->getTables();

        if ($request->input('generate')) {
            $method = 'generate' . ucfirst($request->input('generate'));

            return response()->json([
                'success' => true,
                'uuid' => $this->$method($datatables[$this->route]['data'], $request->input('projects'), $request->input('buildings'), $request->input('group'), $request),
            ]);
        } else {
            $enable = ['button-reset-report', 'button-download-report'];

            return response()->json($datatables + [
                'success' => true,
                'showTable' => true,
                'enable' => $enable,
            ]);
        }
    }

    public function generateExcel($rawData, $projects, $buildings, $group, $request)
    {
        $columns = ['number', 'options', 'mmYears', 'mmFee', 'rentAmountValue', 'deductions', 'tax', 'netRent', 'payments', 'amount', 'owner', 'language', 'room', 'furniture', 'view', 'phone', 'mobile', 'email'];
        /*if ($request->input('rental') == 78 && $request->input('type') == 'due') { // Flexi 2021 with Rental Rates
            array_splice($columns, 3, 0, 'amount2');
        }*/

        $maxBankFields = 0;
        foreach ($rawData as $key => $value) {
            $data[$key] = collect(array_replace(array_flip($columns), $rawData[$key]->toArray()))->only(array_merge($columns, ['project', 'project_id1', 'building', 'building_id1']))->map(function ($item, $key) {
                if ($key === 'amount' || $key === 'rentAmountValue') {
                    $item = (float)str_replace(',', '', $item);
                }

                return $item;
            })->toArray();

            $bankFields = explode('|', $value->bank);
            if (count($bankFields) > $maxBankFields) {
                $maxBankFields = count($bankFields);
            }

            $next = 0;
            foreach ($bankFields as $k => $v) {
                $bank = explode(';;', $v);

                $prev = $next;
                if (isset($data[$key]['bank_beneficiary_' . $prev]) && $bank[0] == $data[$key]['bank_beneficiary_' . $prev] && $bank[1] == $data[$key]['bank_iban_' . $prev] && $bank[2] == $data[$key]['bank_bic_' . $prev] && $bank[3] == $data[$key]['bank_name_' . $prev]) {
                    continue;
                } else {
                    $next++;
                }

                $data[$key]['bank_beneficiary_' . $next] = $bank[0] ?? null;
                $data[$key]['bank_iban_' . $next] = $bank[1] ?? null;
                $data[$key]['bank_bic_' . $next] = $bank[2] ?? null;
                $data[$key]['bank_name_' . $next] = $bank[3] ?? null;
                $data[$key]['rental_' . $next] = $bank[4] ?? null;
            }
        }

        $uuid = (string)\Uuid::generate();
        $filename = 'rental-payments-' . $uuid;

        $firstColumn = 'A';
        $firstRow = 1;
        $firstCell = $firstColumn . $firstRow;

        if (!isset($data)) {
            $data = [];
        }

        $columns = [
            'number' => trans(\Locales::getNamespace() . '/datatables.apartment'),
            'options' => trans(\Locales::getNamespace() . '/datatables.rentalOption'),
            'mmYears' => trans(\Locales::getNamespace() . '/datatables.mmYears'),
            'mmFee' => trans(\Locales::getNamespace() . '/datatables.mmFees'),
            'rentAmountValue' => trans(\Locales::getNamespace() . '/datatables.rentalAmount'),
            'deductions' => trans(\Locales::getNamespace() . '/datatables.deductions'),
            'tax' => trans(\Locales::getNamespace() . '/datatables.deductionWithholdingTax'),
            'netRent' => trans(\Locales::getNamespace() . '/datatables.netRent'),
            'payments' => trans(\Locales::getNamespace() . '/datatables.payments'),
            'amount' => trans(\Locales::getNamespace() . '/datatables.balance'),
            'owner' => trans(\Locales::getNamespace() . '/datatables.owner'),
            'language' => trans(\Locales::getNamespace() . '/datatables.language'),
            'room' => trans(\Locales::getNamespace() . '/datatables.room'),
            'furniture' => trans(\Locales::getNamespace() . '/datatables.furniture'),
            'view' => trans(\Locales::getNamespace() . '/datatables.view'),
            'phone' => trans(\Locales::getNamespace() . '/datatables.phone'),
            'mobile' => trans(\Locales::getNamespace() . '/datatables.mobile'),
            'email' => trans(\Locales::getNamespace() . '/datatables.email'),
        ];

        for ($i = 1; $i <= $maxBankFields; $i++) {
            $columns['bank_beneficiary_' . $i] = trans(\Locales::getNamespace() . '/datatables.beneficiary') . ' ' . $i;
            $columns['bank_iban_' . $i] = trans(\Locales::getNamespace() . '/datatables.iban') . ' ' . $i;
            $columns['bank_bic_' . $i] = trans(\Locales::getNamespace() . '/datatables.bic') . ' ' . $i;
            $columns['bank_name_' . $i] = trans(\Locales::getNamespace() . '/datatables.bank') . ' ' . $i;
            $columns['rental_' . $i] = trans(\Locales::getNamespace() . '/datatables.rentalPercent') . ' ' . $i;
        }

        /*if ($request->input('rental') == 78 && $request->input('type') == 'due') { // Flexi 2021 with Rental Rates
            array_splice($columns, 3, 0, ['amount2' => trans(\Locales::getNamespace() . '/datatables.amount')]);
        }*/

        array_unshift($data, $columns);

        $data = collect($data);

        if ($group) {
            $content = $data->map(function ($item, $key) use ($group) {
                if (isset($item[$group])) {
                    unset($item[$group]);
                }

                if (isset($item[$group . '_id1'])) {
                    unset($item[$group . '_id1']);
                }

                return $item;
            });
        } else {
            $content = $data;
        }

        $columnWidth = [
            'A' => -1,
            'B' => -1,
            'C' => -1,
            'D' => -1,
            'E' => -1,
            'F' => -1,
            'G' => -1,
            'H' => -1,
            'I' => -1,
            'J' => -1,
            'K' => -1,
            'L' => -1,
            'M' => -1,
            'N' => -1,
            'O' => -1,
            'P' => -1,
            'Q' => -1,
            'R' => -1,
            'S' => -1,
            'T' => -1,
            'U' => -1,
            'V' => -1,
            'W' => -1,
            'X' => -1,
            'Y' => -1,
            'Z' => -1,
            'AA' => -1,
            'AB' => -1,
            'AC' => -1,
            'AD' => -1,
            'AE' => -1,
            'AF' => -1,
            'AG' => -1,
            'AH' => -1,
            'AI' => -1,
            'AJ' => -1,
            'AK' => -1,
            'AL' => -1,
            'AM' => -1,
            'AN' => -1,
        ];
        $columnWidthLast = 'AN';

        foreach ($content as $id => $row) {
            $col = $firstColumn;
            foreach ($row as $value) {
                $width = mb_strlen($value);
                if ($width > $columnWidth[$col]) {
                    $columnWidth[$col] = $width;
                }

                $col++;
            }
        }

        \Excel::create('Report', function ($excel) use ($data, $content, $projects, $buildings, $group, $columnWidth, $columnWidthLast, $firstColumn, $firstRow, $firstCell, $request) {

            $reportName = 'Report';
            if ($group) {
                $reportName = 'All';
            }

            $excel->sheet($reportName, function ($sheet) use ($content, $columnWidth, $columnWidthLast, $firstColumn, $firstRow, $firstCell, $request) {

                $sheet->setColumnFormat([
                    'A' => '@',
                    'B' => '@',
                    'C' => '@',
                    'D' => '#,##0.00',
                    'E' => '#,##0.00',
                    'F' => '#,##0.00',
                    'G' => '#,##0.00',
                    'H' => '#,##0.00',
                    'I' => '#,##0.00',
                    'J' => '#,##0.00',
                    'K' => '@',
                    'L' => '@',
                    'M' => '@',
                    'N' => '@',
                    'O' => '@',
                    'P' => '@',
                    'Q' => '@',
                    'R' => '@',
                    'S' => '@',
                    'T' => '@',
                    'U' => '@',
                    'V' => '@',
                    'W' => '@',
                    'X' => '@',
                    'Y' => '@',
                    'Z' => '@',
                    'AA' => '@',
                    'AB' => '@',
                    'AC' => '@',
                    'AD' => '@',
                    'AE' => '@',
                    'AF' => '@',
                    'AG' => '@',
                    'AH' => '@',
                    'AI' => '@',
                    'AJ' => '@',
                    'AK' => '@',
                    'AL' => '@',
                    'AM' => '@',
                    'AN' => '@',
                ]);

                $sheet->fromArray($content, null, $firstCell, false, false);

                $lastRow = $sheet->getHighestDataRow() + 1; // plus footer
                $lastColumn = $sheet->getHighestDataColumn();
                $lastCell = $lastColumn . $lastRow;

                $sheet->setShowGridlines(false);

                $sheet->getStyle($firstCell . ':' . $lastCell)->applyFromArray([
                    'font' => [
                        'size' => 12,
                    ],
                    'alignment' => [
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allborders' => [
                            'style' => \PHPExcel_Style_Border::BORDER_THIN,
                            'color' => [
                                'rgb' => 'DDDDDD',
                            ]
                        ]
                    ]
                ]);

                $mmFees = chr(ord($firstColumn) + array_search('mmFee', array_keys($content[0])));
                $amount = chr(ord($firstColumn) + array_search('amount', array_keys($content[0])));
                $sheet->getStyle($mmFees . ($firstRow + 1) . ':' . $amount . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                    ]
                ]);

                // $sheet->getStyle($firstCell . ':' . $lastCell)->getAlignment()->setWrapText(true);

                $sheet->getStyle($firstCell . ':' . $lastColumn . $firstRow)->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'style' => \PHPExcel_Style_Border::BORDER_THICK,
                            'color' => [
                                'rgb' => 'DDDDDD',
                            ]
                        ]
                    ]
                ]);

                $sheet->getStyle($firstColumn . ($firstRow + 1) . ':' . $lastColumn . ($firstRow + 1))->applyFromArray([
                    'borders' => [
                        'top' => [
                            'style' => \PHPExcel_Style_Border::BORDER_THICK,
                            'color' => [
                                'rgb' => 'DDDDDD',
                            ]
                        ]
                    ]
                ]);

                // Footer

                $sheet->getStyle($firstColumn . $lastRow . ':' . $lastColumn . $lastRow)->applyFromArray([
                    'borders' => [
                        'top' => [
                            'style' => \PHPExcel_Style_Border::BORDER_THICK,
                            'color' => [
                                'rgb' => 'DDDDDD',
                            ]
                        ]
                    ]
                ]);

                $sheet->getStyle($firstColumn . ($lastRow - 1) . ':' . $lastColumn . ($lastRow - 1))->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'style' => \PHPExcel_Style_Border::BORDER_THICK,
                            'color' => [
                                'rgb' => 'DDDDDD',
                            ]
                        ]
                    ]
                ]);

                $sheet->freezePane('A' . ($firstRow + 1));

                $sheet->setAutoFilter($firstCell . ':' . $lastColumn . $firstRow);

                /* Zebra styles
                foreach ($sheet->getRowIterator() as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                    foreach ($cellIterator as $key => $cell) {
                        if ($cell->getRow() % 2 == 0) {
                            $sheet->cell($cell->getCoordinate() . ':' . $lastColumn . $cell->getRow(), function ($cells) {
                                $cells->setBackground('#F9F9F9');
                            });
                        }

                        break;
                    }
                }*/

                /* Set font color for specific column
                foreach ($sheet->getColumnIterator($statusColumn) as $column) {
                    $cellIterator = $column->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                    foreach ($cellIterator as $key => $cell) {
                        if ($cell->getValue() == trans(\Locales::getNamespace() . '/datatables.statusSold')) {
                            $sheet->cell($cell->getCoordinate(), function ($cell) {
                                $cell->setFontColor('#ff0000');
                            });
                        }
                    }
                }*/

                $sheet->cells($firstCell . ':' . $lastColumn . $firstRow, function ($cells) {
                    $cells->setFontWeight('bold');
                    $cells->setBackground('#D9EDF7');
                });

                // bold footer

                $sheet->cells($firstColumn . $lastRow . ':' . $lastCell, function ($cells) {
                    $cells->setFontWeight('bold');
                    $cells->setBackground('#D9EDF7');
                });

                /* FORMULAS START */

                for ($col = 'D'; $col <= 'J'; $col++) {
                    $sheet->cell($col . $lastRow, function ($cell) use ($firstRow, $lastRow, $col) {
                        $cell->setValue('=SUM(' . $col . ($firstRow + 1) . ':' . $col . ($lastRow - 1) . ')');
                    });
                }

                /*if ($request->input('rental') == 78 && $request->input('type') == 'due') { // Flexi 2021 with Rental Rates
                    $sheet->cell('D' . $lastRow, function ($cell) use ($firstRow, $lastRow) {
                        $cell->setValue('=SUM(D' . ($firstRow + 1) . ':D' . ($lastRow - 1) . ')');
                    });
                }*/

                /* FORMULAS END */

                $sheet->setAutoSize(false);
                for ($col = 'A'; $col != $columnWidthLast; $col++) {
                    $sheet->getColumnDimension($col)->setWidth($columnWidth[$col] + 8.43);
                }

                // $sheet->getDefaultRowDimension()->setRowHeight(-1); // auto row height but it doesn't work on merged cells or cells with new lines in content

                // $sheet->setSelectedCell('A1'); // doesn;t work with frozen panes
            });

            if ($group) {
                if ($group === 'project') {
                    $items = $projects;
                    if (!$items) {
                        $items = [1, 2];
                    }
                } elseif ($group === 'building') {
                    $items = $buildings;
                    if (!$items) {
                        $items = [1, 2, 3, 4, 5, 6, 7, 8];
                    }
                }

                foreach ($items as $item) {
                    $content = $data->filter(function ($value, $key) use ($group, $item) {
                        if (isset($value[$group . '_id1'])) {
                            return $value[$group . '_id1'] == $item;
                        } else {
                            return true;
                        }
                    });

                    if (isset($content->last()[$group])) {
                        $reportName = $content->last()[$group];

                        $content->transform(function ($item, $key) use ($group) {
                            if (isset($item[$group])) {
                                unset($item[$group]);
                            }

                            if (isset($item[$group . '_id1'])) {
                                unset($item[$group . '_id1']);
                            }

                            return $item;
                        });

                        $excel->sheet($reportName, function ($sheet) use ($content, $columnWidth, $columnWidthLast, $firstColumn, $firstRow, $firstCell, $request) {

                            $sheet->setColumnFormat([
                                'A' => '@',
                                'B' => '@',
                                'C' => '@',
                                'D' => '#,##0.00',
                                'E' => '#,##0.00',
                                'F' => '#,##0.00',
                                'G' => '#,##0.00',
                                'H' => '#,##0.00',
                                'I' => '#,##0.00',
                                'J' => '#,##0.00',
                                'K' => '@',
                                'L' => '@',
                                'M' => '@',
                                'N' => '@',
                                'O' => '@',
                                'P' => '@',
                                'Q' => '@',
                                'R' => '@',
                                'S' => '@',
                                'T' => '@',
                                'U' => '@',
                                'V' => '@',
                                'W' => '@',
                                'X' => '@',
                                'Y' => '@',
                                'Z' => '@',
                                'AA' => '@',
                                'AB' => '@',
                                'AC' => '@',
                                'AD' => '@',
                                'AE' => '@',
                                'AF' => '@',
                                'AG' => '@',
                                'AH' => '@',
                                'AI' => '@',
                                'AJ' => '@',
                                'AK' => '@',
                                'AL' => '@',
                                'AM' => '@',
                                'AN' => '@',
                            ]);

                            $sheet->fromArray($content, null, $firstCell, false, false);

                            $lastRow = $sheet->getHighestDataRow() + 1; // plus footer
                            $lastColumn = $sheet->getHighestDataColumn();
                            $lastCell = $lastColumn . $lastRow;

                            $sheet->setShowGridlines(false);

                            $sheet->getStyle($firstCell . ':' . $lastCell)->applyFromArray([
                                'font' => [
                                    'size' => 12,
                                ],
                                'alignment' => [
                                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                    'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                                ],
                                'borders' => [
                                    'allborders' => [
                                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                                        'color' => [
                                            'rgb' => 'DDDDDD',
                                        ]
                                    ]
                                ]
                            ]);

                            $mmFees = chr(ord($firstColumn) + array_search('mmFee', array_keys($content[0])));
                            $amount = chr(ord($firstColumn) + array_search('amount', array_keys($content[0])));
                            $sheet->getStyle($mmFees . ($firstRow + 1) . ':' . $amount . $lastRow)->applyFromArray([
                                'alignment' => [
                                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                                ]
                            ]);

                            // $sheet->getStyle($firstCell . ':' . $lastCell)->getAlignment()->setWrapText(true);

                            $sheet->getStyle($firstCell . ':' . $lastColumn . $firstRow)->applyFromArray([
                                'borders' => [
                                    'bottom' => [
                                        'style' => \PHPExcel_Style_Border::BORDER_THICK,
                                        'color' => [
                                            'rgb' => 'DDDDDD',
                                        ]
                                    ]
                                ]
                            ]);

                            $sheet->getStyle($firstColumn . ($firstRow + 1) . ':' . $lastColumn . ($firstRow + 1))->applyFromArray([
                                'borders' => [
                                    'top' => [
                                        'style' => \PHPExcel_Style_Border::BORDER_THICK,
                                        'color' => [
                                            'rgb' => 'DDDDDD',
                                        ]
                                    ]
                                ]
                            ]);

                            // Footer

                            $sheet->getStyle($firstColumn . $lastRow . ':' . $lastColumn . $lastRow)->applyFromArray([
                                'borders' => [
                                    'top' => [
                                        'style' => \PHPExcel_Style_Border::BORDER_THICK,
                                        'color' => [
                                            'rgb' => 'DDDDDD',
                                        ]
                                    ]
                                ]
                            ]);

                            $sheet->getStyle($firstColumn . ($lastRow - 1) . ':' . $lastColumn . ($lastRow - 1))->applyFromArray([
                                'borders' => [
                                    'bottom' => [
                                        'style' => \PHPExcel_Style_Border::BORDER_THICK,
                                        'color' => [
                                            'rgb' => 'DDDDDD',
                                        ]
                                    ]
                                ]
                            ]);

                            $sheet->freezePane('A' . ($firstRow + 1));

                            $sheet->setAutoFilter($firstCell . ':' . $lastColumn . $firstRow);

                            /* Zebra styles
                            foreach ($sheet->getRowIterator() as $row) {
                                $cellIterator = $row->getCellIterator();
                                $cellIterator->setIterateOnlyExistingCells(false);
                                foreach ($cellIterator as $key => $cell) {
                                    if ($cell->getRow() % 2 == 0) {
                                        $sheet->cell($cell->getCoordinate() . ':' . $lastColumn . $cell->getRow(), function ($cells) {
                                            $cells->setBackground('#F9F9F9');
                                        });
                                    }

                                    break;
                                }
                            }*/

                            /* Set font color for specific column
                            foreach ($sheet->getColumnIterator($statusColumn) as $column) {
                                $cellIterator = $column->getCellIterator();
                                $cellIterator->setIterateOnlyExistingCells(false);
                                foreach ($cellIterator as $key => $cell) {
                                    if ($cell->getValue() == trans(\Locales::getNamespace() . '/datatables.statusSold')) {
                                        $sheet->cell($cell->getCoordinate(), function ($cell) {
                                            $cell->setFontColor('#ff0000');
                                        });
                                    }
                                }
                            }*/

                            $sheet->cells($firstCell . ':' . $lastColumn . $firstRow, function ($cells) {
                                $cells->setFontWeight('bold');
                                $cells->setBackground('#D9EDF7');
                            });

                            // bold footer

                            $sheet->cells($firstColumn . $lastRow . ':' . $lastCell, function ($cells) {
                                $cells->setFontWeight('bold');
                                $cells->setBackground('#D9EDF7');
                            });

                            /* FORMULAS START */

                            for ($col = 'D'; $col <= 'J'; $col++) {
                                $sheet->cell($col . $lastRow, function ($cell) use ($firstRow, $lastRow, $col) {
                                    $cell->setValue('=SUM(' . $col . ($firstRow + 1) . ':' . $col . ($lastRow - 1) . ')');
                                });
                            }

                            /*if ($request->input('rental') == 78 && $request->input('type') == 'due') { // Flexi 2021 with Rental Rates
                                $sheet->cell('D' . $lastRow, function ($cell) use ($firstRow, $lastRow) {
                                    $cell->setValue('=SUM(D' . ($firstRow + 1) . ':D' . ($lastRow - 1) . ')');
                                });
                            }*/

                            /* FORMULAS END */

                            $sheet->setAutoSize(false);
                            for ($col = 'A'; $col != $columnWidthLast; $col++) {
                                $sheet->getColumnDimension($col)->setWidth($columnWidth[$col] + 8.43);
                            }

                            // $sheet->getDefaultRowDimension()->setRowHeight(-1); // auto row height but it doesn't work on merged cells or cells with new lines in content

                            // $sheet->setSelectedCell('A1'); // doesn;t work with frozen panes
                        });
                    }
                }
            }
        })->setFilename($filename)->store('xlsx');

        return $uuid;
    }

    public function download(Request $request)
    {
        return response()->download(storage_path('app/reports/rental-payments-' . $request->input('uuid') . '.xlsx'))->deleteFileAfterSend(true);
    }
}
