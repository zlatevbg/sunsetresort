<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Models\Sky\Year;
use App\Models\Sky\Apartment;
use App\Models\Sky\Contract;
use App\Models\Sky\ContractYear;
use App\Models\Sky\ContractDeduction;
use App\Models\Sky\RentalRatesPeriod;
use App\Models\Sky\KeyLog;
use App\Services\DataTable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\Sky\ContractYearRequest;

class ContractYearController extends Controller {

    protected $route = 'contract-years';
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
                'title' => trans(\Locales::getNamespace() . '/datatables.titleRentalContract'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover table-dropdown',
                'joins' => [
                    [
                        'table' => 'contracts',
                        'localColumn' => 'contracts.id',
                        'constrain' => '=',
                        'foreignColumn' => 'contract_years.contract_id',
                    ],
                    [
                        'table' => 'contract_payments',
                        'localColumn' => 'contract_payments.contract_year_id',
                        'constrain' => '=',
                        'foreignColumn' => 'contract_years.id',
                        'group' => 'contract_years.year',
                    ],
                    [
                        'table' => 'years',
                        'localColumn' => 'years.year',
                        'constrain' => '=',
                        'foreignColumn' => 'contract_years.year',
                    ],
                ],
                'selectors' => ['contracts.apartment_id', 'contract_years.mm_for_years', 'contract_years.year as currentYear', 'contract_years.is_exception', 'contract_years.contract_id', 'contract_years.comments', 'contract_years.price', 'contract_years.price_tc', 'years.corporate_tax', 'years.id as year_id'],
                'columns' => [
                    [
                        'selector' => 'contract_years.id',
                        'id' => 'id',
                        'checkbox' => true,
                        // 'id' => 'checkbox',
                        'order' => false,
                        'class' => 'text-center vertical-center',
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
                        'id' => 'dropdown',
                        'name' => '',
                        'order' => false,
                        'class' => 'text-center vertical-center datatables-dropdown',
                        'width' => '1.25em',
                        'dropdown' => [
                            'route' => 'contract-years',
                            'routeParameters' => ['apartment_id', 'contract_id', 'id'],
                            'routeParametersPrepend' => ['contracts', 'years', ''],
                            'title' => trans(\Locales::getNamespace() . '/messages.menu'),
                            'menu' => trans(\Locales::getNamespace() . '/multiselect.contractProperties'),
                        ],
                    ],
                    [
                        'selector' => 'contract_years.year',
                        'id' => 'year',
                        'name' => trans(\Locales::getNamespace() . '/datatables.year'),
                        'info' => 'comments',
                        'class' => 'text-center vertical-center',
                        'prepend' => [
                            'rules' => [
                                'is_exception' => 1,
                            ],
                            'text' => '<span title="Exception" class="glyphicon glyphicon-left glyphicon-color-red glyphicon-large glyphicon-top glyphicon-alert"></span>',
                        ],
                    ],
                    [
                        'selector' => 'contract_years.mm_for_year',
                        'id' => 'mm_for_year',
                        'name' => trans(\Locales::getNamespace() . '/datatables.mmYear'),
                        'class' => 'text-center vertical-center',
                        'order' => false,
                        'prefer' => 'mm_for_years',
                    ],
                    [
                        'selectRaw' => 'SUM(contract_payments.amount) as balance',
                        'id' => 'info',
                        'name' => trans(\Locales::getNamespace() . '/datatables.paymentInfo'),
                        'class' => 'text-center vertical-center payment-info',
                        'order' => false,
                    ],
                    [
                        'id' => 'contractDuration',
                        'name' => trans(\Locales::getNamespace() . '/datatables.contractDuration'),
                        'multiDates' => [
                            'selector' => ['contract_dfrom1', 'contract_dto1', 'contract_dfrom2', 'contract_dto2'],
                        ],
                        'class' => 'text-center vertical-center',
                        'order' => false,
                    ],
                    [
                        'id' => 'personalUsage',
                        'name' => trans(\Locales::getNamespace() . '/datatables.personalUsage'),
                        'multiDates' => [
                            'selector' => ['personal_dfrom1', 'personal_dto1', 'personal_dfrom2', 'personal_dto2'],
                        ],
                        'class' => 'text-center vertical-center',
                        'order' => false,
                    ],
                    [
                        'selector' => 'contract_years.deleted_at',
                        'id' => 'deleted_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.status'),
                        'class' => 'text-center vertical-center',
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
                'orderByColumn' => 2,
                'order' => 'asc',
                'buttons' => [
                    [
                        'url' => \Locales::route($this->route . '/edit'),
                        'class' => 'btn-warning disabled js-edit',
                        'icon' => 'edit',
                        'name' => trans(\Locales::getNamespace() . '/forms.editButton'),
                    ],
                ],
            ],
            'properties' => [
                'dom' => 'tr',
                'url' => \Locales::route($this->route),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'columns' => [
                    [
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.properties'),
                        'order' => false,
                    ],
                ],
                'data' => [
                    [
                        'name' => '<a href="' . \Locales::route($this->route, true) . '/documents"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/multiselect.contractProperties.documents') . '</a>',
                    ],
                    [
                        'name' => '<a href="' . \Locales::route($this->route, true) . '/deductions"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/multiselect.contractProperties.deductions') . '</a>',
                    ],
                    [
                        'name' => '<a href="' . \Locales::route($this->route, true) . '/payments"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/multiselect.contractProperties.payments') . '</a>',
                    ],
                ],
            ],
        ];
    }

    public function index(DataTable $datatable, Request $request, $apartment = null, $contractsSlug = null, $contract = null, $yearsSlug = null, $contractYear = null)
    {
        $breadcrumbs = [];

        $apartment = Apartment::select('apartments.*', 'project_translations.slug AS projectSlug', 'room_translations.slug AS roomSlug', 'view_translations.slug AS viewSlug')->with([/*'mmFeesPayments', */'buildingMM', 'allowners' => function ($query) {
            $query->withTrashed()->orderBy('created_at', 'desc');
        }, 'room'])->leftJoin('project_translations', function($join) {
            $join->on('project_translations.project_id', '=', 'apartments.project_id')->where('project_translations.locale', '=', \Locales::getCurrent());
        })->leftJoin('room_translations', function($join) {
            $join->on('room_translations.room_id', '=', 'apartments.room_id')->where('room_translations.locale', '=', \Locales::getCurrent());
        })->leftJoin('view_translations', function($join) {
            $join->on('view_translations.view_id', '=', 'apartments.view_id')->where('view_translations.locale', '=', \Locales::getCurrent());
        })->findOrFail($apartment);
        // $apply_wt = $apartment->owners()->first()->owner->apply_wt;
        // $contract = Contract::withTrashed()->findOrFail($contract);
        $contract = $apartment->contracts()->withTrashed()->with(['contractYears' => function ($query) {
            $query->withTrashed();
        }, 'contractYears.deductions'])->where('id', $contract)->firstOrFail();
        $rentalContract = $contract->rentalContract;

        $breadcrumbs[] = ['id' => 'apartments', 'slug' => $apartment->id, 'name' => $apartment->number];
        $breadcrumbs[] = ['id' => $contractsSlug, 'slug' => $contractsSlug, 'name' => trans(\Locales::getNamespace() . '/multiselect.apartmentProperties.' . $contractsSlug)];
        $breadcrumbs[] = ['id' => 'contract', 'slug' => $contract->id . '/' . $yearsSlug, 'name' => $rentalContract->name];

        if ($contractYear) {
            $contractYear = ContractYear::withTrashed()->findOrFail($contractYear);
            $breadcrumbs[] = ['id' => 'year', 'slug' => $contractYear->id, 'name' => $contractYear->year];
            $datatable->setup(null, 'properties', $this->datatables['properties']);
        } else {
            $datatable->setup(ContractYear::withTrashed()->where('contract_id', $contract->id), $this->route, $this->datatables[$this->route]);
            $datatable->setOption('contract', $contract->id);
            $datatable->setOption('title', $datatable->getOption('title') . ' ' . $rentalContract->name);
        }

        $datatables = $datatable->getTables();

        if (!$contractYear) {
            $allYears = Year::all();
            foreach ($datatables[$this->route]['data'] as $key => $value) {
                if ($value['year_id']) {
                    $currentYear = $allYears->where('id', $value['year_id'])->first();
                    if (!$currentYear) {
                        abort(404);
                    }

                    if ($value['mm_for_years']) {
                        $years = explode(',', $value['mm_for_years']);
                    } else {
                        $years = [$value['mm_for_year']]; // [$currentYear->year];
                    }

                    $rentAmount = 0;
                    if ($rentalContract->rental_payment_id == 9) { // Rental Rates
                        $rentalRates = RentalRatesPeriod::with('rates')->whereYear('dfrom', '=', $currentYear->year)->get();
                        foreach ($rentalRates as $period) {
                            $nights = KeyLog::whereBetween('occupied_at', [Carbon::parse($period->dfrom), Carbon::parse($period->dto)])->where('apartment_id', $apartment->id)->count();

                            if ($period->type == 'personal-usage') {
                                $nights = $nights - 53; // personal usage period
                            }

                            if ($nights > 0) {
                                $rates = $period->rates->where('project', $apartment->projectSlug)->where('room', $apartment->roomSlug)->where('view', $apartment->viewSlug)->first();
                                if ($rates) {
                                    $rentAmount += $nights * $rates->rate;
                                }
                            }
                        }
                    }

                    $mmFee = 0;
                    foreach ($years as $year) {
                        $year = $currentYear->year == $year ? $currentYear : $allYears->where('year', $year)->first();
                        if ($year) {
                            $mm = $apartment->buildingMM->where('year_id', $year->id)->first();
                            if ($mm) {
                                if ($year->year > 2020) {
                                    $mmFeeTax = round(($apartment->room->capacity * $mm->mm_tax) / 1.95583);
                                } else {
                                    if ($apartment->mm_tax_formula == 0) {
                                        $mmFeeTax = (($apartment->apartment_area + $apartment->common_area + $apartment->balcony_area) * $mm->mm_tax) + ($apartment->extra_balcony_area * ($mm->mm_tax / 2));
                                    } elseif ($apartment->mm_tax_formula == 1) {
                                        $mmFeeTax = $apartment->total_area * $mm->mm_tax;
                                    }
                                }

                                $MMpayments = 0;
                                // $MMpayments = $value['deleted_at'] ? 0 : $apartment->mmFeesPayments->where('year_id', $year->id)->sum('amount'); // substract MM payments only for active contract years

                                $mmFee += (($mmFeeTax - $MMpayments) * $rentalContract->mm_covered) / 100;
                            }
                        }
                    }

                    $total = $rentAmount + $value['price'] + $value['price_tc'] + $mmFee;
                    // $deductions = ContractDeduction::leftJoin('deductions', 'deductions.id', '=', 'contract_deductions.deduction_id')->where('contract_deductions.contract_year_id', $value['id'])->get();
                    $deductions = $contract->contractYears->where('id', $value['id'])->first();
                    $deductions = $deductions ? $deductions->deductions : collect();
                    $allDeductions = $deductions->pluck('amount')->sum();
                    $deductionsAmount = $mmFee + $allDeductions;
                    $owner = $apartment->allowners->filter(function ($v, $k) use ($value) {
                        if (Carbon::parse($v->created_at)->year <= $value['currentYear']) {
                            return true;
                        }

                        return false;
                    })->first();
                    $tax = 0;

                    if ($owner->owner->apply_wt) {
                        if ($rentAmount + $value['price'] + $value['price_tc'] > 0) {
                            $realTax = round($total / 100  * $value['corporate_tax'], 2);
                            if (number_format($total - $deductionsAmount - $realTax, 2) == 0) {
                                $tax = $realTax;
                            } else {
                                $deductionsNotTaxable = $deductions->where('is_taxable', 0)->pluck('amount')->sum();
                                $tax = (($rentAmount + $value['price'] + $value['price_tc'] - $deductionsNotTaxable) > 0 ? round((($total - $deductionsNotTaxable) / 100) * $value['corporate_tax'], 2) : 0);
                            }
                        } else {
                            $tax = $total / 10; // -10%
                            $total += $tax; // + 10%
                        }
                    }

                    $netRent = round($total - $deductionsAmount - $tax, 2);
                    if ($rentalContract->rental_payment_id == 9 && $netRent < 0) { // Rental Rates
                        $netRent = 0;
                    }

                    $balance = round($netRent - $value['balance'], 2);

                    if ($total > 0) {
                        $datatables[$this->route]['data'][$key]['info'] = '
<table class="table table-bordered">
    <tbody>
        <!-- <tr>
            <td>Total:</td>
            <td>&euro; ' . number_format($total, 2) . '</td>
        </tr> -->
        <tr>
            <td>MM Fees:</td>
            <td>&euro; ' . number_format($mmFee, 2) . '</td>
        </tr>
        <tr>
            <td>Rent:</td>
            <td>&euro; ' . number_format($value['price'] + $value['price_tc'] + $rentAmount, 2) . '</td>
        </tr>
        <tr>
            <td>Deductions:</td>
            <td>&euro; ' . number_format($allDeductions, 2) . '</td>
        </tr>
        <tr>
            <td>Tax:</td>
            <td>&euro; ' . number_format($tax, 2) . '</td>
        </tr>
        <tr class="bg-info">
            <td>Net Rent:</td>
            <td>&euro; ' . number_format($netRent, 2) . '</td>
        </tr>
        <tr class="' . ($balance ? (Carbon::now() > Carbon::parse($rentalContract->deadline_at)->year($value['currentYear']) ? 'bg-danger' : 'bg-warning') : 'bg-success') . '">
            <td>Balance:</td>
            <td>&euro; ' . number_format($balance, 2) . '</td>
        </tr>
    </tbody>
</table>';
                    } else {
                        $datatables[$this->route]['data'][$key]['info'] = '';
                    }
                } else {
                    $datatables[$this->route]['data'][$key]['info'] = '';
                }
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($datatables);
        } else {
            return view(\Locales::getNamespace() . '.' . $this->route . '.index', compact('datatables', 'breadcrumbs'));
        }
    }

    public function edit(Request $request, $id = null)
    {
        // $year = ContractYear::findOrFail($id);
        $year = ContractYear::withTrashed()->findOrFail($id);

        $table = $request->input('table');

        $years[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $years[$year->year] = $year->year;
        $years[($year->year + 1)] = $year->year + 1;

        $exceptions = trans(\Locales::getNamespace() . '/multiselect.exceptions');

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.add', compact('table', 'year', 'years', 'exceptions'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, ContractYearRequest $request)
    {
        // $contractYear = ContractYear::findOrFail($request->input('id'))->first();
        $contractYear = ContractYear::withTrashed()->findOrFail($request->input('id'))->first();

        $contract = $contractYear->contract;
        $apartment = Apartment::select('apartments.*', 'project_translations.slug AS projectSlug', 'room_translations.slug AS roomSlug', 'view_translations.slug AS viewSlug')->with([/*'mmFeesPayments', */'buildingMM', 'room'])->leftJoin('project_translations', function($join) {
            $join->on('project_translations.project_id', '=', 'apartments.project_id')->where('project_translations.locale', '=', \Locales::getCurrent());
        })->leftJoin('room_translations', function($join) {
            $join->on('room_translations.room_id', '=', 'apartments.room_id')->where('room_translations.locale', '=', \Locales::getCurrent());
        })->leftJoin('view_translations', function($join) {
            $join->on('view_translations.view_id', '=', 'apartments.view_id')->where('view_translations.locale', '=', \Locales::getCurrent());
        })->findOrFail($contract->apartment_id);
        // $apply_wt = $apartment->owners()->first()->owner->apply_wt;
        $rentalContract = $contract->rentalContract;

        if ($contractYear->update($request->all())) {
            $exceptions = $contract->contractYears()->where('is_exception', 1)->count();
            if ($exceptions) {
                if (!$contract->is_exception) {
                    $contract->is_exception = 1;
                    $contract->save();
                }
            } else {
                if ($contract->is_exception) {
                    $contract->is_exception = 0;
                    $contract->save();
                }
            }

            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityYears', 1)]);

            $datatable->setup(ContractYear::withTrashed()->where('contract_id', $contractYear->contract_id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, true));
            $datatables = $datatable->getTables();

            foreach ($datatables[$request->input('table')]['data'] as $key => $value) {
                if ($value['year_id']) {
                    $currentYear = Year::findOrFail($value['year_id']);
                    if ($value['mm_for_years']) {
                        $years = explode(',', $value['mm_for_years']);
                    } else {
                        $years = [$value['mm_for_year']]; // [$currentYear->year];
                    }

                    $rentAmount = 0;
                    if ($rentalContract->rental_payment_id == 9) { // Rental Rates
                        $rentalRates = RentalRatesPeriod::with('rates')->whereYear('dfrom', '=', $currentYear->year)->get();
                        foreach ($rentalRates as $period) {
                            $nights = KeyLog::whereBetween('occupied_at', [Carbon::parse($period->dfrom), Carbon::parse($period->dto)])->where('apartment_id', $apartment->id)->count();

                            if ($period->type == 'personal-usage') {
                                $nights = $nights - 53; // personal usage period
                            }

                            if ($nights > 0) {
                                $rates = $period->rates->where('project', $apartment->projectSlug)->where('room', $apartment->roomSlug)->where('view', $apartment->viewSlug)->first();
                                if ($rates) {
                                    $rentAmount += $nights * $rates->rate;
                                }
                            }
                        }
                    }

                    $mmFee = 0;
                    foreach ($years as $year) {
                        $year = $currentYear->year == $year ? $currentYear : Year::where('year', $year)->first();
                        if ($year) {
                            $mm = $apartment->buildingMM->where('year_id', $year->id)->first();
                            if ($mm) {
                                if ($year->year > 2020) {
                                    $mmFeeTax = round(($apartment->room->capacity * $mm->mm_tax) / 1.95583);
                                } else {
                                    if ($apartment->mm_tax_formula == 0) {
                                        $mmFeeTax = (($apartment->apartment_area + $apartment->common_area + $apartment->balcony_area) * $mm->mm_tax) + ($apartment->extra_balcony_area * ($mm->mm_tax / 2));
                                    } elseif ($apartment->mm_tax_formula == 1) {
                                        $mmFeeTax = $apartment->total_area * $mm->mm_tax;
                                    }
                                }

                                $MMpayments = 0;
                                // $MMpayments = $value['deleted_at'] ? 0 : $apartment->mmFeesPayments->where('year_id', $year->id)->sum('amount'); // substract MM payments only for active contract years

                                $mmFee += (($mmFeeTax - $MMpayments) * $rentalContract->mm_covered) / 100;
                            }
                        }
                    }

                    $total = $rentAmount + $value['price'] + $value['price_tc'] + $mmFee;
                    $deductions = ContractDeduction::leftJoin('deductions', 'deductions.id', '=', 'contract_deductions.deduction_id')->where('contract_deductions.contract_year_id', $value['id'])->get();
                    $allDeductions = $deductions->pluck('amount')->sum();
                    $deductionsAmount = $mmFee + $allDeductions;
                    $owner = $apartment->owners()->withTrashed()->whereYear('created_at', '<=', $value['currentYear'])->orderBy('created_at', 'desc')->first();
                    $tax = 0;

                    if ($owner->owner->apply_wt) {
                        if ($rentAmount + $value['price'] + $value['price_tc'] > 0) {
                            $realTax = round($total / 100  * $value['corporate_tax'], 2);
                            if (number_format($total - $deductionsAmount - $realTax, 2) == 0) {
                                $tax = $realTax;
                            } else {
                                $deductionsNotTaxable = $deductions->where('is_taxable', 0)->pluck('amount')->sum();
                                $tax = (($rentAmount + $value['price'] + $value['price_tc'] - $deductionsNotTaxable) > 0 ? round((($total - $deductionsNotTaxable) / 100) * $value['corporate_tax'], 2) : 0);
                            }
                        } else {
                            $tax = $total / 10; // -10%
                            $total += $tax; // + 10%
                        }
                    }

                    $netRent = round($total - $deductionsAmount - $tax, 2);
                    if ($rentalContract->rental_payment_id == 9 && $netRent < 0) { // Rental Rates
                        $netRent = 0;
                    }

                    $balance = round($netRent - $value['balance'], 2);

                    if ($total > 0) {
                        $datatables[$this->route]['data'][$key]['info'] = '
<table class="table table-bordered">
    <tbody>
        <!-- <tr>
            <td>Total:</td>
            <td>&euro; ' . number_format($total, 2) . '</td>
        </tr> -->
        <tr>
            <td>MM Fees:</td>
            <td>&euro; ' . number_format($mmFee, 2) . '</td>
        </tr>
        <tr>
            <td>Rent:</td>
            <td>&euro; ' . number_format($value['price'] + $value['price_tc'] + $rentAmount, 2) . '</td>
        </tr>
        <tr>
            <td>Deductions:</td>
            <td>&euro; ' . number_format($allDeductions, 2) . '</td>
        </tr>
        <tr>
            <td>Tax:</td>
            <td>&euro; ' . number_format($tax, 2) . '</td>
        </tr>
        <tr class="bg-info">
            <td>Net Rent:</td>
            <td>&euro; ' . number_format($netRent, 2) . '</td>
        </tr>
        <tr class="' . ($balance ? (Carbon::now() > Carbon::parse($rentalContract->deadline_at)->year($value['currentYear']) ? 'bg-danger' : 'bg-warning') : 'bg-success') . '">
            <td>Balance:</td>
            <td>&euro; ' . number_format($balance, 2) . '</td>
        </tr>
    </tbody>
</table>';
                    } else {
                        $datatables[$request->input('table')]['data'][$key]['info'] = '';
                    }
                } else {
                    $datatables[$request->input('table')]['data'][$key]['info'] = '';
                }
            }

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityYears', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

}
