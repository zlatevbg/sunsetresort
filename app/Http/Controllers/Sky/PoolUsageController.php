<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Models\Sky\Apartment;
use App\Models\Sky\Year;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Models\Sky\RentalCompany;
use App\Models\Sky\PoolUsagePayment;
use App\Http\Requests\Sky\PayPoolUsageRequest;
use Carbon\Carbon;

class PoolUsageController extends Controller
{

    protected $route = 'pool-usage';
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
                'title' => trans(\Locales::getNamespace() . '/datatables.titlePoolUsage'),
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
                        'name' => trans(\Locales::getNamespace() . '/datatables.poolUsage'),
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

    public function index(DataTable $datatable, Request $request, Year $years, $apartment = null, $poolUsageSlug = null)
    {
        $breadcrumbs = [];

        $apartment = Apartment::with(['buildingMM', 'poolUsagePayments', 'poolUsageContracts', 'room'])->findOrFail($apartment);
        $breadcrumbs[] = ['id' => 'apartments', 'slug' => $apartment->id, 'name' => $apartment->number];
        $breadcrumbs[] = ['id' => $poolUsageSlug, 'slug' => $poolUsageSlug, 'name' => trans(\Locales::getNamespace() . '/multiselect.apartmentProperties.' . $poolUsageSlug)];

        $datatable->setup(null, 'years', $this->datatables['years']);
        $data = [];
        foreach ($years->where('year', '>', 2020)->orderBy('year', 'desc')->get() as $year) {
            $contract = $apartment->poolUsageContracts->where('year_id', $year->id)->first();
            if ($contract && $contract->is_active) {
                $mm = $apartment->buildingMM->where('year_id', $year->id)->first();
                $fees = $year->fees->where('room_id', $apartment->room_id)->first();
                if ($mm && $fees) {
                    $poolUsageTax = round($fees->pool_tax / 1.95583);

                    $balance = round($poolUsageTax, 2) - round($apartment->poolUsagePayments->where('year_id', $year->id)->sum('amount'), 2);

                    $data[] = [
                        'year' => '<a href="' . \Locales::route('pool-usage-payments', [$apartment->id . '/' . $poolUsageSlug, $year->year]) . '"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . $year->year . '</a>',
                        'fees' => '&euro; ' . number_format($poolUsageTax, 2),
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
        $years = $years + Year::select('year', 'id')->where('year', '>', 2020)->orderBy('year', 'desc')->get()->lists('year', 'id')->toArray();

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.select-year', compact('years'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function payPoolUsage(PayPoolUsageRequest $request)
    {
        $year = Year::findOrFail($request->input('year'));

        $apartments = Apartment::select('id', 'number', 'building_id', 'room_id')
            ->with(['buildingMM' => function ($query) use ($year) {
                // $query->where('building_mm.year_id', $year->id); // I need all BuildingMM years, not just the selected one!!!
            }, 'poolUsagePayments', 'poolUsageContracts', 'contracts', 'contracts.contractYears' => function ($query) use ($year) {
                $query->where('contract_years.year', $year->year);
            }, 'contracts.rentalContract' => function ($query) {
                $query->withTrashed();
            }])
            ->groupBy('apartments.id')
            ->get();

        // dd($apartments->pluck('number'));
        $apartmentsFees = DataTable::calculatePoolUsage($apartments, 'due-by-rental', $year->year)->keyBy('id');
        // dd($apartmentsFees->pluck('number'));

        $now = Carbon::now();
        $company = RentalCompany::where('is_active', 1)->firstOrFail();
        $insert = [];

        foreach ($apartmentsFees as $apartment) {
            $insert[] = [
                'created_at' => $now,
                'amount' => str_replace(',', '', $apartment->amount),
                'paid_at' => ($year->year == '2021' ? $now : Carbon::parse($apartment->buildingMM->where('year_id', $year->id)->first()->deadline_at)),
                'apartment_id' => $apartment->id,
                'year_id' => $year->id,
                'payment_method_id' => 2, // bank transfer
                'rental_company_id' => $company->id,
            ];
        }

        if ($insert) {
            PoolUsagePayment::insert($insert);
            return response()->json(['success' => ['All Pool Usage Taxes paid successfully!']]);
        } else {
            return response()->json(['errors' => ['All Pool Usage Taxes are already paid!']]);
        }
    }
}
