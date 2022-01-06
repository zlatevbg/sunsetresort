<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Models\Sky\RentalContract;
use App\Models\Sky\RentalPayment;
use App\Models\Sky\Apartment;
use App\Models\Sky\Year;
use App\Http\Requests\Sky\RentalContractRequest;
use App\Http\Requests\Sky\CancelRentalContractRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Sky\Deduction;
use App\Models\Sky\ContractYear;
use App\Models\Sky\ContractDeduction;
use App\Models\Sky\ContractPayment;
use App\Models\Sky\RentalCompany;
use App\Services\FineUploader;
use Storage;

class RentalContractController extends Controller {

    protected $route = 'rental-contracts';
    protected $uploadDirectory = 'rental-payments';
    protected $datatables;

    public function __construct()
    {
        $this->datatables = [
            $this->route => [
                'translation' => true,
                'title' => trans(\Locales::getNamespace() . '/datatables.titleRentalContracts'),
                'url' => \Locales::route($this->route),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'columns' => [
                    [
                        'selector' => 'rental_contracts.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center',
                    ],
                    [
                        'selector' => 'rental_contract_translations.name',
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.name'),
                        'search' => true,
                        'join' => [
                            'table' => 'rental_contract_translations',
                            'localColumn' => 'rental_contract_translations.rental_contract_id',
                            'constrain' => '=',
                            'foreignColumn' => 'rental_contracts.id',
                            'whereColumn' => 'rental_contract_translations.locale',
                            'whereConstrain' => '=',
                            'whereValue' => \Locales::getCurrent(),
                        ],
                    ],
                ],
                'orderByColumn' => 1,
                'order' => 'asc',
                'buttons' => [
                    [
                        'url' => \Locales::route($this->route . '/create'),
                        'class' => 'btn-primary js-create',
                        'icon' => 'plus',
                        'name' => trans(\Locales::getNamespace() . '/forms.createButton'),
                    ],
                    [
                        'url' => \Locales::route($this->route . '/edit'),
                        'class' => 'btn-warning disabled js-edit',
                        'icon' => 'edit',
                        'name' => trans(\Locales::getNamespace() . '/forms.editButton'),
                    ],
                    [
                        'url' => \Locales::route($this->route . '/delete'),
                        'class' => 'btn-danger disabled js-destroy',
                        'icon' => 'trash',
                        'name' => trans(\Locales::getNamespace() . '/forms.deleteButton'),
                    ],
                ],
            ],
        ];
    }

    public function index(DataTable $datatable, RentalContract $contract, Request $request)
    {
        $breadcrumbs = [];

        $datatable->setup($contract, $this->route, $this->datatables[$this->route]);
        $datatables = $datatable->getTables();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($datatables);
        } else {
            return view(\Locales::getNamespace() . '.' . $this->route . '.index', compact('datatables', 'breadcrumbs'));
        }
    }

    public function create(RentalContract $contract, Request $request)
    {
        $table = $request->input('table') ?: $this->route;

        $mmCovered[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $mmCovered = $mmCovered + trans(\Locales::getNamespace() . '/multiselect.mmCoveredOptions');

        $rentalPayments[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $rentalPayments = $rentalPayments + RentalPayment::select('id', 'name')->orderBy('name')->get()->pluck('name', 'id')->toArray();

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('table', 'mmCovered', 'rentalPayments'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function store(DataTable $datatable, RentalContract $contract, RentalContractRequest $request)
    {
        $data = \Locales::prepareTranslations($request);

        $newRentalContract = RentalContract::create($data);

        if ($newRentalContract->id) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.storedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityRentalContracts', 1)]);

            $datatable->setup($contract, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'reset' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.createError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityRentalContracts', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function delete(Request $request)
    {
        $table = $request->input('table') ?: $this->route;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.delete', compact('table'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function destroy(DataTable $datatable, RentalContract $contract, Request $request)
    {
        $count = count($request->input('id'));

        if ($count > 0 && $contract->destroy($request->input('id'))) {
            $datatable->setup($contract, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => trans(\Locales::getNamespace() . '/forms.destroyedSuccessfully'),
                'closePopup' => true
            ]);
        } else {
            if ($count > 0) {
                $errorMessage = trans(\Locales::getNamespace() . '/forms.deleteError');
            } else {
                $errorMessage = trans(\Locales::getNamespace() . '/forms.countError');
            }

            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function edit(Request $request, $id = null)
    {
        $contract = RentalContract::findOrFail($id);

        $table = $request->input('table') ?: $this->route;

        $mmCovered[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $mmCovered = $mmCovered + trans(\Locales::getNamespace() . '/multiselect.mmCoveredOptions');

        $rentalPayments[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $rentalPayments = $rentalPayments + RentalPayment::select('id', 'name')->orderBy('name')->get()->pluck('name', 'id')->toArray();

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('contract', 'table', 'mmCovered', 'rentalPayments'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, RentalContractRequest $request)
    {
        $contract = RentalContract::findOrFail($request->input('id'))->first();

        $data = \Locales::prepareTranslations($request);

        if ($contract->update($data)) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityRentalContracts', 1)]);

            $datatable->setup($contract, $request->input('table'), $this->datatables[$request->input('table')], true);
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

    public function cancelRental(Request $request)
    {
        $years[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $years = $years + Year::select('year', 'id')->orderBy('year', 'desc')->get()->lists('year', 'id')->toArray();

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.cancel-rental', compact('years'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function payRental(Request $request)
    {
        $years[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $years = $years + Year::select('year', 'id')->orderBy('year', 'desc')->get()->lists('year', 'id')->toArray();

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.pay-rental', compact('years'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function cancelRentalContracts(CancelRentalContractRequest $request)
    {
        $year = Year::findOrFail($request->input('year'));

        $msg = '';

        $result = DB::table('contracts')->leftJoin('rental_contracts', 'rental_contracts.id', '=', 'contracts.rental_contract_id')->whereNull('contracts.deleted_at')->whereRaw('YEAR(rental_contracts.contract_dfrom1) + contracts.duration <= ?', [$year->year + 1])->update(['contracts.deleted_at' => DB::raw('CONCAT(YEAR(rental_contracts.contract_dfrom1) + contracts.duration - 1, "-12-31 00:00:00")'), 'contracts.updated_at' => Carbon::now(), 'contracts.is_cancelled' => true]);
        if ($result) {
            $msg .= 'All Contracts cancelled successfully!<br>';
        }

        $result = DB::table('contract_years')->whereNull('deleted_at')->where('year', '<', ($year->year + 1))->update(['deleted_at' => DB::raw('CONCAT(contract_years.year, "-12-31 00:00:00")'), 'updated_at' => Carbon::now()]);
        if ($result) {
            $msg .= 'All Contract Years cancelled successfully!<br>';
        }

        if ($msg) {
            return response()->json(['success' => [$msg]]);
        } else {
            return response()->json(['errors' => ['All Contracts are already cancelled!']]);
        }
    }

    public function upload(Request $request, FineUploader $uploader, $chunk = null)
    {
        $errorsFound = [];
        $now = Carbon::now();
        $year = Year::find($request->input('year'));

        if (!$year) {
            return response()->json([
                'error' => trans(\Locales::getNamespace() . '/forms.yearError'),
                'preventRetry' => true,
            ]);
        }

        $company = RentalCompany::where('is_active', 1)->firstOrFail();

        $uploader->isImage = false;
        $uploader->isFile = true;
        $uploader->allowedExtensions = \Config::get('upload.fileExtensions');

        $uploader->uploadDirectory = $this->uploadDirectory;
        if (!Storage::disk('local-public')->exists($uploader->uploadDirectory)) {
            Storage::disk('local-public')->makeDirectory($uploader->uploadDirectory);
        }

        if ($chunk) {
            $response = $uploader->combineChunks(false);
        } else {
            $response = $uploader->handleUpload(null, false);
        }

        if (isset($response['success']) && $response['success'] && isset($response['fileName'])) {
            $rows = array_map(function($row) {
                return str_getcsv($row, ';');
            }, file(public_path('upload') . DIRECTORY_SEPARATOR . $this->uploadDirectory . DIRECTORY_SEPARATOR . $response['fileName']));

            Storage::disk('local-public')->delete($this->uploadDirectory . DIRECTORY_SEPARATOR . $response['fileName']);

            $apartments = Apartment::with(['mmFeesPayments', 'buildingMM' => function ($query) use ($year) {
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
            }, 'rooms'])->selectRaw('apartments.id, apartments.number, apartments.mm_tax_formula, apartments.apartment_area, apartments.common_area, apartments.balcony_area, apartments.extra_balcony_area, apartments.total_area, apartments.building_id, apartments.room_id, project_translations.slug AS projectSlug, room_translations.slug AS roomSlug, view_translations.slug AS viewSlug')->leftJoin('project_translations', function($join) {
                $join->on('project_translations.project_id', '=', 'apartments.project_id')->where('project_translations.locale', '=', \Locales::getCurrent());
            })->leftJoin('room_translations', function($join) {
                $join->on('room_translations.room_id', '=', 'apartments.room_id')->where('room_translations.locale', '=', \Locales::getCurrent());
            })->leftJoin('view_translations', function($join) {
                $join->on('view_translations.view_id', '=', 'apartments.view_id')->where('view_translations.locale', '=', \Locales::getCurrent());
            })->get();

            $apartmentsMM = DataTable::calculateRentalOptions($apartments, $year->year, 'rental', 'due', null, false)->keyBy('id');

            foreach($rows as $row) {
                $row = array_filter($row);

                array_walk($row, function(&$value) {
                    $value = trim(html_entity_decode($value), " €\t\n\r\0\x0B\xC2\xA0\xEF\xBB\xBF"); // \xC2\xA0 = &nbsp; | \xEF\xBB\xBF = BOM
                });
                $apartment = $apartmentsMM->where('number', $row[0]);

                if (count($apartment)) {
                    $deductionAmount = str_replace(',', '.', $row[1] ?? 0);
                    $paymentAmount = str_replace(',', '.', $row[2] ?? 0);

                    $deductionAmount = str_replace([' ', ','], '', $deductionAmount);
                    $paymentAmount = str_replace([' ', ','], '', $paymentAmount);

                    $contract = $apartment->first()->contracts->first();
                    $contractYear = $contract->contractYears->where('year', $year->year)->first();

                    if ($deductionAmount > 0) {
                        $deduction = ContractDeduction::create([
                            'amount' => $deductionAmount,
                            'signed_at' => $now,
                            'deduction_id' => Deduction::first()->id,
                            'contract_year_id' => $contractYear->id,
                        ]);
                    }

                    if ($paymentAmount > 0) {
                        $payment = ContractPayment::create([
                            'amount' => $paymentAmount,
                            'paid_at' => $now,
                            'payment_method_id' => 2, // bank transfer
                            'contract_year_id' => $contractYear->id,
                            'rental_company_id' => $company->id,
                        ]);
                    }
                } else {
                    array_push($errorsFound, $row[0]);
                }
            }

            if ($errorsFound) {
                return response()->json([
                    'error' => trans(\Locales::getNamespace() . '/forms.importApartmentsNotFoundError') . '<br>' . implode('<br>', $errorsFound) . '<br>',
                    'preventRetry' => true,
                ]);
            } else {
                return response()->json([
                    'success' => trans(\Locales::getNamespace() . '/forms.uploadedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityFiles', 1)]),
                ]);
            }
        }

        return response()->json($response, $uploader->getStatus())->header('Content-Type', 'text/plain');
    }
}
