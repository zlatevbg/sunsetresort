<?php namespace App\Http\Controllers\Owners;

use App\Http\Controllers\Controller;
use App\Services\DataTable;
use App\Models\Owners\Contract;
use App\Models\Owners\Apartment;
use App\Models\Owners\ContractYear;
use App\Models\Owners\ContractDeduction;
use App\Models\Owners\ContractDocuments;
use App\Models\Owners\RentalRatesPeriod;
use App\Models\Owners\KeyLog;
use App\Models\Owners\Year;
use Carbon\Carbon;

class ContractController extends Controller {

    protected $route = 'contracts';
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
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'columns' => [
                    [
                        'id' => 'year',
                        'name' => trans(\Locales::getNamespace() . '/datatables.year'),
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'info',
                        'name' => trans(\Locales::getNamespace() . '/datatables.paymentInfo'),
                        'order' => false,
                        'class' => 'vertical-center payment-info',
                    ],
                    [
                        'id' => 'duration',
                        'name' => trans(\Locales::getNamespace() . '/datatables.contractDuration'),
                        'order' => false,
                        'class' => 'text-center vertical-center',
                    ],
                    [
                        'id' => 'usage',
                        'name' => trans(\Locales::getNamespace() . '/datatables.personalUsage'),
                        'order' => false,
                        'class' => 'text-center vertical-center',
                    ],
                    [
                        'id' => 'documents',
                        'name' => trans(\Locales::getNamespace() . '/datatables.documents'),
                        'order' => false,
                        'class' => 'text-center vertical-center',
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

    public function index(DataTable $datatable, $contract = null)
    {
        $contract = Contract::withTrashed()->findOrFail($contract);
        $rentalContract = $contract->rentalContract;
        $owner = \Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user();
        $apartment = Apartment::with([/*'mmFeesPayments', */'buildingMM', 'room'])->selectRaw('apartments.*, YEAR(ownership.created_at) AS year, project_translations.slug AS projectSlug, room_translations.slug AS roomSlug, view_translations.slug AS viewSlug')->leftJoin('ownership', 'ownership.apartment_id', '=', 'apartments.id')->leftJoin('project_translations', function($join) {
            $join->on('project_translations.project_id', '=', 'apartments.project_id')->where('project_translations.locale', '=', \Locales::getCurrent());
        })->leftJoin('room_translations', function($join) {
            $join->on('room_translations.room_id', '=', 'apartments.room_id')->where('room_translations.locale', '=', \Locales::getCurrent());
        })->leftJoin('view_translations', function($join) {
            $join->on('view_translations.view_id', '=', 'apartments.view_id')->where('view_translations.locale', '=', \Locales::getCurrent());
        })->where('ownership.owner_id', '=', $owner->id)->whereYear('ownership.created_at', '<', $rentalContract->contract_year + $contract->duration)->whereNull('ownership.deleted_at')->whereNull('apartments.deleted_at')->findOrFail($contract->apartment_id);
        $name = $rentalContract->name;

        $breadcrumbs = [];
        $breadcrumbs[] = ['id' => 'apartments', 'slug' => 'apartments', 'name' => \Locales::getMenu('apartments')['title']];
        $breadcrumbs[] = ['id' => 'apartment', 'slug' => 'apartments/' . $apartment->number, 'name' => $apartment->number];
        $breadcrumbs[] = ['id' => 'contract', 'slug' => 'contract/' . $contract->id, 'name' => trans(\Locales::getNamespace() . '/datatables.contract') . ': ' . $name];

        $metaTitle = trans(\Locales::getNamespace() . '/datatables.contract') . ': ' . $name;
        $metaDescription = trans(\Locales::getNamespace() . '/datatables.contract') . ': ' . $name;

        $contractYears = ContractYear::withTrashed()->with(['deductions', 'documents'])->selectRaw('contract_years.*, years.corporate_tax, years.id as year_id, SUM(contract_payments.amount) as balance')->leftJoin('contract_payments', 'contract_payments.contract_year_id', '=', 'contract_years.id')->leftJoin('years', 'years.year', '=', 'contract_years.year')->where('years.year', '<=', date('Y'))->where('contract_id', $contract->id)->groupBy('contract_years.year')->orderBy('contract_years.year', 'desc')->get();
        $allYears = Year::all();

        if ($contractYears->count()) {
            $data = [];
            foreach ($contractYears as $contractYear) {
                if ($contractYear->year >= $apartment->year) {
                    $contractDates = '';
                    if ($contractYear->contract_dfrom1) {
                        $contractDates .= $contractYear->contract_dfrom1;

                        if ($contractYear->contract_dto1) {
                            $contractDates .= ' / ' . $contractYear->contract_dto1;
                        }

                        if ($contractYear->contract_dfrom2) {
                            $contractDates .= '<br>' . $contractYear->contract_dfrom2;
                        }

                        if ($contractYear->contract_dto2) {
                            $contractDates .= ' / ' . $contractYear->contract_dto2;
                        }
                    }

                    $usageDates = '';
                    if ($contractYear->personal_dfrom1) {
                        $usageDates .= $contractYear->personal_dfrom1;

                        if ($contractYear->personal_dto1) {
                            $usageDates .= ' / ' . $contractYear->personal_dto1;
                        }

                        if ($contractYear->personal_dfrom2) {
                            $usageDates .= '<br>' . $contractYear->personal_dfrom2;
                        }

                        if ($contractYear->personal_dto2) {
                            $usageDates .= ' / ' . $contractYear->personal_dto2;
                        }
                    } else {
                        $usageDates = trans(\Locales::getNamespace() . '/datatables.outsideContractDates');
                    }

                    $info = '';

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
                                // $MMpayments = $contractYear->deleted_at ? 0 : $apartment->mmFeesPayments->where('year_id', $year->id)->sum('amount'); // substract MM payments only for active contract years

                                $mmFee += (($mmFeeTax - $MMpayments) * $rentalContract->mm_covered) / 100;
                            }
                        }
                    }

                    $total = $rentAmount + $contractYear->price + $contractYear->price_tc + $mmFee;
                    // $deductions = ContractDeduction::leftJoin('deductions', 'deductions.id', '=', 'contract_deductions.deduction_id')->where('contract_deductions.contract_year_id', $contractYear->id)->get();
                    $deductions = $contractYear->deductions;
                    $allDeductions = $deductions->pluck('amount')->sum();
                    $deductionsAmount = $mmFee + $allDeductions;
                    $tax = 0;

                    if ($owner->apply_wt) {
                        if ($rentAmount + $contractYear->price + $contractYear->price_tc > 0) {
                            $realTax = round($total / 100  * $contractYear->corporate_tax, 2);
                            if (number_format($total - $deductionsAmount - $realTax, 2) == 0) {
                                $tax = $realTax;
                            } else {
                                $deductionsNotTaxable = $deductions->where('is_taxable', 0)->pluck('amount')->sum();
                                $tax = (($rentAmount + $contractYear->price + $contractYear->price_tc - $deductionsNotTaxable) > 0 ? round((($total - $deductionsNotTaxable) / 100) * $contractYear->corporate_tax, 2) : 0);
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

                    $balance = round($netRent - $contractYear->balance, 2);

                    if ($total > 0) {
                        $info = '
    <table class="table table-bordered">
        <tbody>
            <!-- <tr>
                <td>' . trans(\Locales::getNamespace() . '/datatables.total') . ':</td>
                <td>&euro; ' . number_format($total, 2) . '</td>
            </tr> -->
            <tr>
                <td>' . trans(\Locales::getNamespace() . '/datatables.mmFees') . ':</td>
                <td>&euro; ' . number_format($mmFee, 2) . '</td>
            </tr>
            <tr>
                <td>' . trans(\Locales::getNamespace() . '/datatables.rent') . ':</td>
                <td>&euro; ' . number_format($contractYear->price + $contractYear->price_tc + $rentAmount, 2) . '</td>
            </tr>
            <tr>
                <td>' . trans(\Locales::getNamespace() . '/datatables.deductions') . ':</td>
                <td>&euro; ' . number_format($allDeductions, 2) . '</td>
            </tr>
            <tr>
                <td>' . trans(\Locales::getNamespace() . '/datatables.deductionWithholdingTax') . ':</td>
                <td>&euro; ' . number_format($tax, 2) . '</td>
            </tr>
            <tr class="bg-info">
                <td>' . trans(\Locales::getNamespace() . '/datatables.netRent') . ':</td>
                <td>&euro; ' . number_format($netRent, 2) . '</td>
            </tr>
            <tr class="' . ($balance ? (Carbon::now() > Carbon::parse($rentalContract->deadline_at)->year($contractYear->year) ? 'bg-danger' : 'bg-warning') : 'bg-success') . '">
                <td>' . trans(\Locales::getNamespace() . '/datatables.balance') . ':</td>
                <td>&euro; ' . number_format($balance, 2) . '</td>
            </tr>
        </tbody>
    </table>';
                    }

                    $documents = '';
                    foreach ($contractYear->documents as $document) {
                        $documents .= '<a title="' . trans(\Locales::getNamespace() . '/multiselect.contractDocuments.' . $document->type) . ', ' . \App\Helpers\formatBytes($document->size) . '" href="' . \Locales::route('download-contract-document', $document->id) . '">' . \HTML::image(\App\Helpers\autover('/img/' . \Locales::getNamespace() . '/ext/' . $document->extension . '.png')) . '</a>';
                    }

                    $status = '';
                    if ($contractYear->deleted_at) {
                        $status = trans(\Locales::getNamespace() . '/datatables.canceled') . ': ' . Carbon::parse($contractYear->deleted_at)->format('d.m.Y');
                    } else {
                        if ($contractYear->year == date('Y')) {
                            $status = trans(\Locales::getNamespace() . '/datatables.active');
                        } elseif ($contractYear->year < date('Y')) {
                            $status = trans(\Locales::getNamespace() . '/datatables.ended');
                        }
                    }

                    array_push($data, [
                        'year' => '<a class="js-popup" href="' . \Locales::route('rental-contract', $contractYear->id) . '"><span class="glyphicon glyphicon-plus glyphicon-left"></span>' . $contractYear->year . '</a>',
                        'info' => $owner->email == 'dummy@sunsetresort.bg' ? '' : $info,
                        'duration' => $contractDates,
                        'usage' => $usageDates,
                        'documents' => $owner->email == 'dummy@sunsetresort.bg' ? '' : $documents,
                        'status' => $status,
                    ]);
                }
            }

            $datatable->setup(null, $this->route, $this->datatables[$this->route]);
            $datatable->setOption('data', $data);
        }

        $datatables = $datatable->getTables();

        return view(\Locales::getNamespace() . '.' . $this->route . '.index', compact('datatables', 'breadcrumbs', 'metaTitle', 'metaDescription'));
    }

    public function download($id = null)
    {
        $file = ContractDocuments::findOrFail($id);

        $owner_id = \Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user()->id;

        $apartment = Apartment::select('apartments.id')->leftJoin('ownership', 'ownership.apartment_id', '=', 'apartments.id')->where('ownership.owner_id', '=', $owner_id)->whereNull('ownership.deleted_at')->whereNull('apartments.deleted_at')->findOrFail($file->contractYear->contract->apartment_id);

        $uploadDirectory = public_path('upload') . DIRECTORY_SEPARATOR . 'apartments' . DIRECTORY_SEPARATOR . $apartment->id . DIRECTORY_SEPARATOR . 'documents' . DIRECTORY_SEPARATOR . $file->uuid . DIRECTORY_SEPARATOR . $file->file;

        return response()->download($uploadDirectory);
    }

}
