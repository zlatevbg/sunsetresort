<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Models\Sky\Apartment;
use App\Models\Sky\Year;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Models\Sky\MmFeePayment;
use App\Models\Sky\RentalCompany;
use App\Http\Requests\Sky\PayMmFeeRequest;
use Carbon\Carbon;

class MmFeeController extends Controller
{

    protected $route = 'mm-fees';
    protected $datatables;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->datatables = [
            'years' => [
                'dom' => 'tr',
                'title' => trans(\Locales::getNamespace() . '/datatables.titleMMFees'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'columns' => [
                    [
                        'id' => 'year',
                        'name' => trans(\Locales::getNamespace() . '/datatables.year'),
                        'class' => 'vertical-center',
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
        ];
    }

    public function index(DataTable $datatable, Request $request, Year $years, $apartment = null, $mmFeesSlug = null)
    {
        $breadcrumbs = [];

        $apartment = Apartment::with(['buildingMM', 'mmFeesPayments', 'room'])->findOrFail($apartment);
        $breadcrumbs[] = ['id' => 'apartments', 'slug' => $apartment->id, 'name' => $apartment->number];
        $breadcrumbs[] = ['id' => $mmFeesSlug, 'slug' => $mmFeesSlug, 'name' => trans(\Locales::getNamespace() . '/multiselect.apartmentProperties.' . $mmFeesSlug)];

        $datatable->setup(null, 'years', $this->datatables['years']);
        $data = [];
        foreach ($years->orderBy('year', 'desc')->get() as $year) {
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

                $balance = round($mmFeeTax, 2) - round($apartment->mmFeesPayments->where('year_id', $year->id)->sum('amount'), 2);

                $data[] = [
                    'year' => '<a href="' . \Locales::route('mm-fees-payments', [$apartment->id . '/' . $mmFeesSlug, $year->year]) . '"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . $year->year . '</a>',
                    'fees' => '&euro; ' . number_format($mmFeeTax, 2),
                    'balance' => '
<table class="table table-bordered">
    <tbody>
        <tr class="' . ($balance ? (Carbon::now() > Carbon::parse($mm->deadline_at)->year($year->year) ? 'bg-danger' : 'bg-warning') : 'bg-success') . '">
            <td>&euro; ' . number_format($balance, 2) . '</td>
        </tr>
    </tbody>
</table>',
                ];
            }
        }
        $datatable->setOption('data', $data);

        $datatables = $datatable->getTables();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($datatables);
        } else {
            return view(\Locales::getNamespace() . '.' . $this->route . '.index', compact('datatables', 'breadcrumbs'));
        }
    }

    public function selectYear(Request $request)
    {
        $years[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $years = $years + Year::select('year', 'id')->where('year', '<=', date('Y'))->orderBy('year', 'desc')->get()->lists('year', 'id')->toArray();

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.select-year', compact('years'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function payMmFees(PayMmFeeRequest $request)
    {
        $contractYear = Year::findOrFail($request->input('year'));

        $apartments = Apartment::select('id', 'number', 'mm_tax_formula', 'apartment_area', 'common_area', 'balcony_area', 'extra_balcony_area', 'total_area', 'building_id', 'room_id')
            ->with(['buildingMM' => function ($query) use ($contractYear) {
                // $query->where('building_mm.year_id', $contractYear->id); // I need all BuildingMM years, not just the selected one!!!
            }, 'mmFeesPayments', 'contracts' => function ($query) use ($contractYear) {
                if ($contractYear->year < date('Y')) {
                    $query->withTrashed();
                }
            }, 'contracts.contractYears' => function ($query) use ($contractYear) {
                $query->where('contract_years.year', $contractYear->year);

                if ($contractYear->year < date('Y')) {
                    $query->withTrashed();
                }
            }, 'contracts.rentalContract' => function ($query) {
                $query->withTrashed();
            }, 'rooms'])
            ->groupBy('apartments.id')
            ->get();

        // dd($apartments->pluck('number'));
        $apartmentsMM = DataTable::calculateMmFees($apartments, 'due-by-rental', $contractYear->year)->keyBy('id');
        // dd($apartmentsMM->pluck('number'));

        $now = Carbon::now();
        $company = RentalCompany::where('is_active', 1)->firstOrFail();
        $allYears = Year::all();
        $insert = [];

        foreach ($apartmentsMM as $apartment) {
            $years = explode(', ', $apartment->mmYears);

            foreach ($years as $year) {
                if ($apartment->mmFees[$year]) {
                    $insert[] = [
                        'created_at' => $now,
                        'amount' => $apartment->mmFees[$year],
                        'paid_at' => Carbon::parse($apartment->buildingMM->where('year_id', $contractYear->id)->first()->deadline_at),
                        'apartment_id' => $apartment->id,
                        'year_id' => $allYears->where('year', $year)->first()->id,
                        'payment_method_id' => 2, // bank transfer
                        'rental_company_id' => $company->id,
                    ];
                }
            }
        }

        if ($insert) {
            MmFeePayment::insert($insert);
            return response()->json(['success' => ['All MM Fees paid successfully!']]);
        } else {
            return response()->json(['errors' => ['All MM Fees are already paid!']]);
        }
    }
}
