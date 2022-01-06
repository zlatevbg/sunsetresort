<?php namespace App\Http\Controllers\Owners;

use App\Http\Controllers\Controller;
use App\Services\DataTable;
use App\Models\Owners\Apartment;
use App\Models\Owners\Year;
use App\Models\Owners\ContractYear;
use App\Models\Owners\ContractPayment;
use App\Models\Owners\ContractPaymentDocuments;
use App\Models\Owners\ContractDeduction;
use App\Models\Owners\RentalRatesPeriod;
use App\Models\Owners\KeyLog;
use Carbon\Carbon;

class RentalContractController extends Controller {

    protected $route = 'rental-contract';
    protected $datatables;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->datatables = [
            'payments' => [
                'dom' => 'tr',
                'title' => trans(\Locales::getNamespace() . '/datatables.titlePayments'),
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'columns' => [
                    [
                        'id' => 'paid_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.paidAt'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'amount',
                        'name' => trans(\Locales::getNamespace() . '/datatables.amount'),
                        'order' => false,
                        'class' => 'vertical-center text-right',
                    ],
                    [
                        'id' => 'method',
                        'name' => trans(\Locales::getNamespace() . '/datatables.paymentMethod'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'company',
                        'name' => trans(\Locales::getNamespace() . '/datatables.rentalCompany'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'owner',
                        'name' => trans(\Locales::getNamespace() . '/datatables.owner'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'documents',
                        'name' => trans(\Locales::getNamespace() . '/datatables.documents'),
                        'order' => false,
                        'class' => 'text-center vertical-center',
                    ],
                ],
            ],
            'deductions' => [
                'dom' => 'tr',
                'title' => trans(\Locales::getNamespace() . '/datatables.titleDeductions'),
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'columns' => [
                    [
                        'id' => 'deduction',
                        'name' => trans(\Locales::getNamespace() . '/datatables.deduction'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'amount',
                        'name' => trans(\Locales::getNamespace() . '/datatables.amount'),
                        'order' => false,
                        'class' => 'vertical-center text-right',
                    ],
                    [
                        'id' => 'signed_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.signedAt'),
                        'order' => false,
                        'class' => 'text-center vertical-center',
                    ],
                ],
            ],
        ];
    }

    public function index(DataTable $datatable, $id = null)
    {
        $contractYear = ContractYear::withTrashed()->findOrFail($id);
        $contract = $contractYear->contract;
        $rentalContract = $contract->rentalContract;

        $owner = \Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user();

        $apartment = Apartment::with([/*'mmFeesPayments', */'buildingMM', 'room'])->selectRaw('apartments.*, YEAR(ownership.created_at) as year, project_translations.slug AS projectSlug, room_translations.slug AS roomSlug, view_translations.slug AS viewSlug')->leftJoin('ownership', 'ownership.apartment_id', '=', 'apartments.id')->leftJoin('project_translations', function($join) {
            $join->on('project_translations.project_id', '=', 'apartments.project_id')->where('project_translations.locale', '=', \Locales::getCurrent());
        })->leftJoin('room_translations', function($join) {
            $join->on('room_translations.room_id', '=', 'apartments.room_id')->where('room_translations.locale', '=', \Locales::getCurrent());
        })->leftJoin('view_translations', function($join) {
            $join->on('view_translations.view_id', '=', 'apartments.view_id')->where('view_translations.locale', '=', \Locales::getCurrent());
        })->where('ownership.owner_id', '=', $owner->id)->whereNull('ownership.deleted_at')->whereNull('apartments.deleted_at')->findOrFail($contract->apartment_id);

        $allYears = Year::all();
        $currentYear = $allYears->where('year', $contractYear->year)/*->where('year', '>=', $apartment->year)*/->first();
        if (!$currentYear) {
            abort(404);
        }

        $payments = ContractPayment::with('documents')->selectRaw('contract_payments.id, contract_payments.paid_at, contract_payments.amount, payment_method_translations.name as method, CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as owner_name, rental_company_translations.name as company_name')->leftJoin('owners', 'owners.id', '=', 'contract_payments.owner_id')->leftJoin('rental_company_translations', function($join) {
            $join->on('rental_company_translations.rental_company_id', '=', 'contract_payments.rental_company_id')->where('rental_company_translations.locale', '=', \Locales::getCurrent());
        })->leftJoin('payment_method_translations', function($join) {
            $join->on('payment_method_translations.payment_method_id', '=', 'contract_payments.payment_method_id')->where('payment_method_translations.locale', '=', \Locales::getCurrent());
        })->where('contract_payments.contract_year_id', $contractYear->id)->orderBy('contract_payments.paid_at', 'desc')->get();

        $deductions = ContractDeduction::select('contract_deductions.amount', 'contract_deductions.signed_at', 'deductions.is_taxable', 'deduction_translations.name as deduction')->leftJoin('deductions', 'deductions.id', '=', 'contract_deductions.deduction_id')->leftJoin('deduction_translations', function($join) {
            $join->on('deduction_translations.deduction_id', '=', 'deductions.id')->where('deduction_translations.locale', '=', \Locales::getCurrent());
        })->where('contract_year_id', $contractYear->id)->orderBy('contract_deductions.signed_at', 'desc')->get();

        $data = [];
        foreach ($payments as $payment) {
            $documents = '';
            foreach ($payment->documents as $document) {
                $documents .= '<a title="' . trans(\Locales::getNamespace() . '/multiselect.contractPaymentDocuments.' . $document->type) . ', ' . \App\Helpers\formatBytes($document->size) . '" href="' . \Locales::route('download-payment-document', $document->id) . '">' . \HTML::image(\App\Helpers\autover('/img/' . \Locales::getNamespace() . '/ext/' . $document->extension . '.png')) . '</a>';
            }

            array_push($data, [
                'paid_at' => $payment->paid_at,
                'amount' => $owner->email == 'dummy@sunsetresort.bg' ? '' : '&euro; ' . $payment->amount,
                'method' => $payment->method,
                'company' => $payment->company_name,
                'owner' => $owner->email == 'dummy@sunsetresort.bg' ? 'Dummy Name' : $payment->owner_name,
                'documents' => $owner->email == 'dummy@sunsetresort.bg' ? '' : $documents,
            ]);
        }

        $datatable->setup(null, 'payments', $this->datatables['payments']);
        $datatable->setOption('data', $data);

        $data = [];
        foreach ($deductions as $deduction) {
            array_push($data, [
                'deduction' => $deduction->deduction,
                'amount' => '&euro; ' . number_format($deduction->amount, 2),
                'signed_at' => $deduction->signed_at,
            ]);
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

                    $currentFee = (($mmFeeTax - $MMpayments) * $rentalContract->mm_covered) / 100;
                    $mmFee += $currentFee;

                    array_unshift($data, [
                        'deduction' => trans(\Locales::getNamespace() . '/datatables.deductionMMFees') . ' (' . $year->year . ')',
                        'amount' => '&euro; ' . number_format($currentFee, 2),
                        'signed_at' => null,
                    ]);
                }
            }
        }

        $total = $rentAmount + $contractYear->price + $contractYear->price_tc + $mmFee;

        $allDeductions = $deductions->pluck('amount')->sum();
        $deductionsAmount = $mmFee + $allDeductions;
        $tax = 0;

        if ($owner->apply_wt) {
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

            array_push($data, [
                'deduction' => trans(\Locales::getNamespace() . '/datatables.deductionWithholdingTax') . ' (' . $corporate_tax . '%)',
                'amount' => '&euro; ' . number_format($tax, 2),
                'signed_at' => null,
            ]);
        }

        $datatable->setup(null, 'deductions', $this->datatables['deductions']);
        $datatable->setOption('data', $data);

        $datatables = $datatable->getTables();

        $datatables['payments']['ajax'] = false;
        $datatables['deductions']['ajax'] = false;

        return view(\Locales::getNamespace() . '.' . $this->route . '.index', compact('datatables'));
    }

    public function download($id = null)
    {
        $file = ContractPaymentDocuments::findOrFail($id);

        $owner_id = \Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user()->id;

        $apartment = Apartment::select('apartments.id')->leftJoin('ownership', 'ownership.apartment_id', '=', 'apartments.id')->where('ownership.owner_id', '=', $owner_id)->whereNull('ownership.deleted_at')->whereNull('apartments.deleted_at')->findOrFail($file->contractPayment->contractYear->contract->apartment_id);

        $uploadDirectory = public_path('upload') . DIRECTORY_SEPARATOR . 'apartments' . DIRECTORY_SEPARATOR . $apartment->id . DIRECTORY_SEPARATOR . 'payments' . DIRECTORY_SEPARATOR . $file->uuid . DIRECTORY_SEPARATOR . $file->file;

        return response()->download($uploadDirectory);
    }

}
