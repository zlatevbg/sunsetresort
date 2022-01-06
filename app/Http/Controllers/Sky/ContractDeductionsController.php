<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Models\Sky\Apartment;
use App\Models\Sky\Contract;
use App\Models\Sky\ContractYear;
use App\Models\Sky\Year;
use App\Models\Sky\Deduction;
use App\Models\Sky\ContractDeduction;
use App\Models\Sky\RentalRatesPeriod;
use App\Models\Sky\KeyLog;
use App\Http\Requests\Sky\ContractDeductionRequest;
use Carbon\Carbon;

class ContractDeductionsController extends Controller {

    protected $route = 'contract-deductions';
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
                'title' => trans(\Locales::getNamespace() . '/datatables.titleContractDeductions'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'selectors' => ['contract_deductions.comments', 'contract_deductions.deduction_id', 'contract_deductions.amount AS cleanAmount'],
                'columns' => [
                    [
                        'selector' => 'contract_deductions.id',
                        'id' => 'checkbox',
                        'order' => false,
                        'class' => 'text-center',
                        'width' => '1.25em',
                        'replace' => [
                            'id' => 'id',
                            'rules' => [
                                0 => [
                                    'column' => 'id',
                                    'valueNot' => null,
                                    'checkbox' => true,
                                ],
                            ],
                        ],
                    ],
                    [
                        'selector' => 'deduction_translations.name',
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.deduction'),
                        'order' => false,
                        'join' => [
                            'table' => 'deduction_translations',
                            'localColumn' => 'deduction_translations.deduction_id',
                            'constrain' => '=',
                            'foreignColumn' => 'contract_deductions.deduction_id',
                            'whereColumn' => 'deduction_translations.locale',
                            'whereConstrain' => '=',
                            'whereValue' => \Locales::getCurrent(),
                        ],
                        'info' => 'comments',
                        'deductions' => true,
                    ],
                    [
                        'selector' => 'contract_deductions.amount',
                        'id' => 'amount',
                        'name' => trans(\Locales::getNamespace() . '/datatables.amount'),
                        'order' => false,
                        'class' => 'text-right',
                        'prepend' => [
                            'simpleText' => '&euro; ',
                        ],
                    ],
                    [
                        'selector' => 'contract_deductions.signed_at',
                        'id' => 'signed_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.signedAt'),
                        'order' => false,
                        'class' => 'text-center',
                    ],
                ],
                'orderByColumn' => 'signed_at',
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
            'deductions' => [
                'id' => 'id',
                'name' => 'name',
            ],
        ];
    }

    public function index(DataTable $datatable, Request $request, $apartment = null, $contractsSlug = null, $contract = null, $yearsSlug = null, $contractYear = null, $deductionsSlug = null)
    {
        $breadcrumbs = [];

        $apartment = Apartment::select('apartments.*', 'project_translations.slug AS projectSlug', 'room_translations.slug AS roomSlug', 'view_translations.slug AS viewSlug')->with([/*'mmFeesPayments', */'buildingMM', 'room'])->leftJoin('project_translations', function($join) {
            $join->on('project_translations.project_id', '=', 'apartments.project_id')->where('project_translations.locale', '=', \Locales::getCurrent());
        })->leftJoin('room_translations', function($join) {
            $join->on('room_translations.room_id', '=', 'apartments.room_id')->where('room_translations.locale', '=', \Locales::getCurrent());
        })->leftJoin('view_translations', function($join) {
            $join->on('view_translations.view_id', '=', 'apartments.view_id')->where('view_translations.locale', '=', \Locales::getCurrent());
        })->findOrFail($apartment);
        // $apply_wt = $apartment->owners()->first()->owner->apply_wt;
        // $contract = Contract::withTrashed()->findOrFail($contract);
        $contract = $apartment->contracts()->withTrashed()->where('id', $contract)->firstOrFail();
        $rentalContract = $contract->rentalContract;
        $contractYear = ContractYear::withTrashed()->findOrFail($contractYear);

        $breadcrumbs[] = ['id' => 'apartments', 'slug' => $apartment->id, 'name' => $apartment->number];
        $breadcrumbs[] = ['id' => $contractsSlug, 'slug' => $contractsSlug, 'name' => trans(\Locales::getNamespace() . '/multiselect.apartmentProperties.' . $contractsSlug)];
        $breadcrumbs[] = ['id' => 'contract', 'slug' => $contract->id . '/' . $yearsSlug, 'name' => $rentalContract->name];
        $breadcrumbs[] = ['id' => 'year', 'slug' => $contractYear->id, 'name' => $contractYear->year];
        $breadcrumbs[] = ['id' => $deductionsSlug, 'slug' => $deductionsSlug, 'name' => trans(\Locales::getNamespace() . '/multiselect.contractProperties.' . $deductionsSlug)];

        $datatable->setup(ContractDeduction::where('contract_year_id', $contractYear->id), $this->route, $this->datatables[$this->route]);
        $datatable->setOption('year', $contractYear->id);
        $datatables = $datatable->getTables();

        $allYears = Year::all();
        $currentYear = $allYears->where('year', $contractYear->year)->first();
        if (!$currentYear) {
            abort(404);
        }

        if ($contractYear->mm_for_years) {
            $years = explode(',', $contractYear->mm_for_years);
        } else {
            $years = [$contractYear->mm_for_year]; // [$currentYear->year];
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
                $mm = $apartment->buildingMM()->where('year_id', $year->id)->first();
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
                    // $MMpayments = $contractYear->deleted_at ? 0 : $apartment->mmFeesPayments->where('year_id', $year->id)->sum('amount'); // substract MM payments only for active contract years

                    $currentFee = (($mmFeeTax - $MMpayments) * $rentalContract->mm_covered) / 100;
                    $mmFee += $currentFee;

                    array_unshift($datatables[$this->route]['data'], [
                        'id' => null,
                        'name' => trans(\Locales::getNamespace() . '/datatables.deductionMMFees') . ' (' . $year->year . ')<div class="tooltip-spacer"></div>',
                        'amount' => '&euro; ' . number_format($currentFee, 2),
                        'signed_at' => null,
                        'comments' => null,
                        'checkbox' => null,
                    ]);
                }
            }
        }

        $total = $rentAmount + $contractYear->price + $contractYear->price_tc + $mmFee;

        $deductions = ContractDeduction::leftJoin('deductions', 'deductions.id', '=', 'contract_deductions.deduction_id')->where('contract_deductions.contract_year_id', $contractYear->id)->get();
        $allDeductions = $deductions->pluck('amount')->sum();
        $deductionsAmount = $mmFee + $allDeductions;
        $owner = $apartment->owners()->withTrashed()->whereYear('created_at', '<=', $contractYear->year)->orderBy('created_at', 'desc')->first();
        $tax = 0;

        if ($owner->owner->apply_wt) {
            $corporate_tax = $contractYear->withTrashed()->leftJoin('years', 'years.year', '=', 'contract_years.year')->where('contract_years.year', $contractYear->year)->firstOrFail()->corporate_tax;

            if ($rentAmount + $contractYear->price + $contractYear->price_tc > 0) {
                $realTax = round($total / 100  * $corporate_tax, 2);
                if (number_format($total - $deductionsAmount - $realTax, 2) == 0) {
                    $tax = $realTax;
                } else {
                    $deductionsNotTaxable = $deductions->where('is_taxable', 0)->pluck('amount')->sum();
                    $tax = (($rentAmount + $contractYear->price + $contractYear->price_tc - $deductionsNotTaxable) > 0 ? round((($total - $deductionsNotTaxable) / 100) * $corporate_tax, 2) : 0);
                }
            } else {
                $tax = $total / 10; // -10%
                $total += $tax; // + 10%
            }

            array_push($datatables[$this->route]['data'], [
                'id' => null,
                'name' => trans(\Locales::getNamespace() . '/datatables.deductionWithholdingTax') . ' (' . $corporate_tax . '%)<div class="tooltip-spacer"></div>',
                'amount' => '&euro; ' . number_format($tax, 2),
                'signed_at' => null,
                'comments' => null,
                'checkbox' => null,
            ]);
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

        $year = $request->input('year') ?: null;

        $deductions[] = ['id' => '', 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        $deductions = array_merge($deductions, Deduction::withTranslation()->select('deduction_translations.name', 'deductions.id')->leftJoin('deduction_translations', 'deduction_translations.deduction_id', '=', 'deductions.id')->where('deduction_translations.locale', \Locales::getCurrent())->orderBy('deduction_translations.name')->get()->toArray());

        $this->multiselect['deductions']['options'] = $deductions;
        $this->multiselect['deductions']['selected'] = '';

        $multiselect = $this->multiselect;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.add', compact('table', 'year', 'multiselect'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function save(DataTable $datatable, ContractDeductionRequest $request)
    {
        $year = ContractYear::withTrashed()->findOrFail([$request->input('year')])->first();

        $request->merge([
            'contract_year_id' => $year->id,
        ]);

        $deduction = ContractDeduction::create($request->all());

        if ($deduction->id) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.savedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityDeductions', 1)]);

            $datatable->setup(ContractDeduction::where('contract_year_id', $year->id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, true));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.createError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityDeductions', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function remove(Request $request)
    {
        $table = $request->input('table');

        $year = $request->input('year') ?: null;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.remove', compact('table', 'year'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function destroy(DataTable $datatable, ContractDeduction $deduction, Request $request)
    {
        $year = ContractYear::withTrashed()->findOrFail([$request->input('year')])->first();

        $count = count($request->input('id'));

        if ($count > 0 && $deduction->destroy($request->input('id'))) {
            $datatable->setup(ContractDeduction::where('contract_year_id', $year->id), $request->input('table'), $this->datatables[$request->input('table')], true);
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
        $deduction = ContractDeduction::findOrFail($id);

        $table = $request->input('table');

        $deductions = Deduction::withTrashed()->withTranslation()->select('deduction_translations.name', 'deductions.id')->leftJoin('deduction_translations', 'deduction_translations.deduction_id', '=', 'deductions.id')->where('deduction_translations.locale', \Locales::getCurrent())->orderBy('deduction_translations.name')->get()->toArray();

        $this->multiselect['deductions']['options'] = $deductions;
        $this->multiselect['deductions']['selected'] = $deduction->deduction_id;

        $multiselect = $this->multiselect;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.add', compact('table', 'deduction', 'multiselect'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, ContractDeductionRequest $request)
    {
        $deduction = ContractDeduction::findOrFail($request->input('id'))->first();

        if ($deduction->update($request->all())) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityDeductions', 1)]);

            $datatable->setup(ContractDeduction::where('contract_year_id', $deduction->contract_year_id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, true));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityDeductions', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

}
