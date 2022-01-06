<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Models\Sky\RentalContractTracker;
use App\Models\Sky\Apartment;
use App\Models\Sky\Year;
use App\Models\Sky\Poa;
use App\Models\Sky\Proxy;
use App\Models\Sky\Owner;
use App\Models\Sky\Signature;
use App\Models\Sky\RentalContract;
use App\Models\Sky\RentalPaymentPrices;
use App\Models\Sky\RentalRatesPeriod;
use App\Models\Sky\NewsletterTemplates;
use App\Models\Sky\Contract;
use App\Models\Sky\ContractYear;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Http\Requests\Sky\RentalContractTrackerRequest;
use App\Services\Newsletter as NewsletterService;
use Mail;
use File;
use Carbon\Carbon;
use Dompdf\Dompdf;

class RentalContractTrackerController extends Controller
{
    protected $route = 'rental-contracts-tracker';
    protected $datatables;
    protected $multiselect;

    public function __construct()
    {
        $this->datatables = [
            $this->route => [
                'title' => trans(\Locales::getNamespace() . '/datatables.titleRentalContractsTracker'),
                'url' => \Locales::route($this->route),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'selectors' => ['rental_contracts_tracker.comments', 'rental_contracts_tracker.is_exception'],
                'joins' => [
                    [
                        'table' => 'owners',
                        'localColumn' => 'owners.id',
                        'constrain' => '=',
                        'foreignColumn' => 'rental_contracts_tracker.owner_id',
                        'whereNull' => 'owners.deleted_at',
                    ],
                    [
                        'table' => 'poa',
                        'localColumn' => 'poa.id',
                        'constrain' => '=',
                        'foreignColumn' => 'rental_contracts_tracker.poa_id',
                        'whereNull' => 'poa.deleted_at',
                    ],
                    [
                        'table' => 'proxy_translations',
                        'localColumn' => 'proxy_translations.proxy_id',
                        'constrain' => '=',
                        'foreignColumn' => 'poa.proxy_id',
                        'whereColumn' => 'proxy_translations.locale',
                        'whereConstrain' => '=',
                        'whereValue' => \Locales::getCurrent(),
                    ],
                ],
                'columns' => [
                    [
                        'selector' => 'rental_contracts_tracker.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center vertical-center',
                    ],
                    [
                        'selector' => 'apartments.number',
                        'id' => 'number',
                        'name' => trans(\Locales::getNamespace() . '/datatables.apartment'),
                        'search' => true,
                        'class' => 'vertical-center',
                        'join' => [
                            'table' => 'apartments',
                            'localColumn' => 'apartments.id',
                            'constrain' => '=',
                            'foreignColumn' => 'rental_contracts_tracker.apartment_id',
                        ],
                        'info' => 'comments',
                    ],
                    [
                        'selector' => 'owners.first_name',
                        'id' => 'first_name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.owner'),
                        'search' => true,
                        'class' => 'vertical-center',
                        'append' => [
                            'selector' => ['owners.last_name'],
                            'text' => 'last_name',
                        ],
                    ],
                    [
                        'selector' => 'rental_contract_translations.name',
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.contract'),
                        'search' => true,
                        'class' => 'vertical-center',
                        'join' => [
                            'table' => 'rental_contract_translations',
                            'localColumn' => 'rental_contract_translations.rental_contract_id',
                            'constrain' => '=',
                            'foreignColumn' => 'rental_contracts_tracker.rental_contract_id',
                            'whereColumn' => 'rental_contract_translations.locale',
                            'whereConstrain' => '=',
                            'whereValue' => \Locales::getCurrent(),
                        ],
                        'append' => [
                            'rules' => [
                                'is_exception' => 1,
                            ],
                            'text' => '<span title="Exception" class="glyphicon glyphicon-right glyphicon-color-red glyphicon-large glyphicon-top glyphicon-alert"></span>',
                        ],
                    ],
                    [
                        'selector' => 'proxy_translations.name as proxy',
                        'id' => 'proxy',
                        'name' => trans(\Locales::getNamespace() . '/datatables.proxy'),
                        'search' => true,
                        'class' => 'vertical-center',
                    ],
                    [
                        'selector' => 'rental_contracts_tracker.sent_at',
                        'id' => 'sent_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.dateSent'),
                        'class' => 'text-center vertical-center sent-at',
                        'search' => true,
                        'date' => [
                            'format' => '%d.%m.%Y',
                        ],
                    ],
                    [
                        'selector' => 'rental_contracts_tracker.is_active',
                        'id' => 'is_active',
                        'name' => trans(\Locales::getNamespace() . '/datatables.contractReceivedStatus'),
                        'class' => 'text-center',
                        'order' => false,
                        'status' => [
                            'class' => 'change-status',
                            'queue' => 'async-change-status',
                            'route' => $this->route . '/change-status',
                            'rules' => [
                                0 => [
                                    'status' => 1,
                                    'icon' => 'off.gif',
                                    'title' => trans(\Locales::getNamespace() . '/datatables.statusOff'),
                                ],
                                1 => [
                                    'status' => 0,
                                    'icon' => 'on.gif',
                                    'title' => trans(\Locales::getNamespace() . '/datatables.statusOn'),
                                ],
                            ],
                        ],
                    ],
                ],
                'orderByColumn' => 'rental_contracts_tracker.sent_at',
                'orderByRaw' => 'NOT ISNULL(rental_contracts_tracker.sent_at)',
                'order' => 'desc',
                'buttons' => [
                    [
                        'url' => \Locales::route($this->route . '/confirm-activate'),
                        'class' => 'btn-secondary js-activate hidden',
                        'icon' => 'ok',
                        'name' => trans(\Locales::getNamespace() . '/forms.activateButton'),
                    ],
                    [
                        'url' => \Locales::route($this->route . '/confirm-send-to-all'),
                        'class' => 'btn-success js-multiple hidden',
                        'icon' => 'send',
                        'name' => trans(\Locales::getNamespace() . '/forms.sendToAllButton'),
                    ],
                    [
                        'url' => \Locales::route($this->route . '/send'),
                        'class' => 'btn-success js-send hidden',
                        'icon' => 'send',
                        'name' => trans(\Locales::getNamespace() . '/forms.sendButton'),
                    ],
                    [
                        'url' => \Locales::route($this->route . '/test'),
                        'class' => 'btn-info js-test hidden',
                        'icon' => 'repeat',
                        'name' => trans(\Locales::getNamespace() . '/forms.testButton'),
                    ],
                    [
                        'url' => \Locales::route($this->route . '/print'),
                        'class' => 'btn-default js-print hidden',
                        'icon' => 'print',
                        'name' => trans(\Locales::getNamespace() . '/forms.printButton'),
                    ],
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

        $this->multiselect = [
            'apartments' => [
                'id' => 'id',
                'name' => 'number',
            ],
            'contracts' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'mm_for_years' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'proxies' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'owners' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'duration' => [
                'id' => 'id',
                'name' => 'name',
            ],
        ];
    }

    public function index(DataTable $datatable, RentalContractTracker $rentalContract, Request $request)
    {
        $datatable->setup($rentalContract, $this->route, $this->datatables[$this->route]);
        $datatables = $datatable->getTables();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($datatables);
        } else {
            return view(\Locales::getNamespace() . '.' . $this->route . '.index', compact('datatables'));
        }
    }

    public function create(Request $request)
    {
        $table = $this->route;
        if ($request->input('table')) { // magnific popup request
            $table = $request->input('table');
        }

        $this->multiselect['apartments']['options'] = array_merge([['id' => '', 'number' => trans(\Locales::getNamespace() . '/forms.selectOption')]], Apartment::distinct()->select('apartments.id', 'apartments.number')->join('ownership', 'ownership.apartment_id', '=', 'apartments.id')->whereNull('ownership.deleted_at')->orderBy('number')->get()->toArray());
        $this->multiselect['apartments']['selected'] = '';

        $this->multiselect['contracts']['options'] = [];
        $this->multiselect['contracts']['selected'] = '';

        $this->multiselect['mm_for_years']['options'] = [];
        $this->multiselect['mm_for_years']['selected'] = '';

        $this->multiselect['proxies']['options'] = [];
        $this->multiselect['proxies']['selected'] = '';

        $this->multiselect['owners']['options'] = [];
        $this->multiselect['owners']['selected'] = '';

        $this->multiselect['duration']['options'] = [];
        $this->multiselect['duration']['selected'] = '';

        $multiselect = $this->multiselect;

        $years = [];
        $exceptions = trans(\Locales::getNamespace() . '/multiselect.exceptions');

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('table', 'multiselect', 'years', 'exceptions'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function store(DataTable $datatable, RentalContractTracker $rentalContract, RentalContractTrackerRequest $request)
    {
        if ($request->input('mm_for_years') && (count($request->input('mm_for_years')) / 2 != $request->input('duration'))) {
            return response()->json(['errors' => [trans(\Locales::getNamespace() . '/forms.MMYearsCountError')]]);
        }

        $newRentalContract = RentalContractTracker::create($request->all());

        if ($newRentalContract->id) {
            if ($request->input('proxy_id') && $request->input('from') && $request->input('to')) {
                $apartment = $newRentalContract->apartment;

                $poa = Poa::create([
                    'from' => $request->input('from'),
                    'to' => $request->input('to'),
                    'apartment_id' => $apartment->id,
                    'owner_id' => $request->input('owner_id'), // $apartment->ownership->first()->owner_id,
                    'proxy_id' => $request->input('proxy_id'),
                ]);

                $newRentalContract->poa_id = $poa->id;
                $newRentalContract->save();
            }

            $successMessage = trans(\Locales::getNamespace() . '/forms.storedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityRentalContracts', 1)]);

            $datatable->setup($rentalContract, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route));
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

    public function delete(Request $request)
    {
        $table = $this->route;
        if ($request->input('table')) { // magnific popup request
            $table = $request->input('table');
        }

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.delete', compact('table'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function destroy(DataTable $datatable, RentalContractTracker $rentalContract, Request $request)
    {
        $count = count($request->input('id'));

        $poas = RentalContractTracker::find($request->input('id'))->pluck('poa_id');
        Poa::whereIn('id', $poas)->forceDelete();

        if ($count > 0 && $rentalContract->destroy($request->input('id'))) {
            $datatable->setup($rentalContract, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route));
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
        $rentalContract = RentalContractTracker::findOrFail($id);
        $rc = $rentalContract->rentalContract;

        $table = $this->route;
        if ($request->input('table')) { // magnific popup request
            $table = $request->input('table');
        }

        $this->multiselect['apartments']['options'] = Apartment::distinct()->select('apartments.id', 'apartments.number')->join('ownership', 'ownership.apartment_id', '=', 'apartments.id')->whereNull('ownership.deleted_at')->orderBy('number')->get()->toArray();
        $this->multiselect['apartments']['selected'] = $rentalContract->apartment_id;

        $this->multiselect['owners']['options'] = $this->getOwners($rentalContract->apartment_id)->toArray();
        $this->multiselect['owners']['selected'] = $rentalContract->owner_id;

        $this->multiselect['contracts']['options'] = $this->getRentalContracts($rentalContract->apartment_id, $rentalContract->id)->toArray();
        $this->multiselect['contracts']['selected'] = $rentalContract->rental_contract_id;

        $flexiOverdue = $rc ? str_contains($rc->name, 'Flexi Overdue') : null;
        $tc = $rc ? starts_with($rc->name, 'Thomas Cook') : null;
        $covid = $rc ? str_contains($rc->name, 'COVID') : null;

        $years = [];
        foreach (array_reverse($this->getMMYears($rentalContract->apartment_id)) as $year) {
            array_push($years, ['id' => $year, 'name' => $year]);
        }
        $this->multiselect['mm_for_years']['options'] = $years;
        $this->multiselect['mm_for_years']['selected'] = explode(',', $rentalContract->mm_for_years);

        $poa = $rentalContract->poa;
        if ($poa) {
            $this->multiselect['proxies']['options'] = $this->getProxies($rentalContract->apartment_id, $rentalContract->owner_id, $poa->proxy_id)->toArray();
            $this->multiselect['proxies']['selected'] = $poa->proxy_id;
        } else {
            $this->multiselect['proxies']['options'] = array_merge([['id' => '', 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')]], $this->getProxies($rentalContract->apartment_id, $rentalContract->owner_id)->toArray());
            $this->multiselect['proxies']['selected'] = '';
        }

        $duration = [];
        if ($rc) {
            for ($i = $rentalContract->rentalContract->min_duration; $i <= $rentalContract->rentalContract->max_duration; $i++) {
                $duration[] = ['id' => $i, 'name' => $i];
            }
        }

        $this->multiselect['duration']['options'] = $duration;
        $this->multiselect['duration']['selected'] = $rentalContract->duration;

        $multiselect = $this->multiselect;

        $year = $this->getStartYear($rentalContract->contract_dfrom1, $rentalContract->contract_dfrom2);
        $years = [];
        $years[$year] = $year;
        $years[++$year] = $year;

        $exceptions = trans(\Locales::getNamespace() . '/multiselect.exceptions');

        $maxDate = Carbon::now()->year($year)->endOfYear()->addYear()->format('d.m.Y');

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('rentalContract', 'table', 'multiselect', 'years', 'exceptions', 'maxDate', 'poa', 'tc', 'covid', 'flexiOverdue'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, RentalContractTracker $rentalContracts, RentalContractTrackerRequest $request)
    {
        $rentalContract = RentalContractTracker::findOrFail($request->input('id'))->first();

        if ($rentalContract->update($request->all())) {
            if ($request->input('proxy_id') && $request->input('from') && $request->input('to')) {
                $apartment = $rentalContract->apartment;
                $newPoa = Poa::updateOrCreate(
                    [
                        'id' => $rentalContract->poa_id,
                    ],
                    [
                        'from' => $request->input('from'),
                        'to' => $request->input('to'),
                        'apartment_id' => $rentalContract->apartment_id,
                        'owner_id' => $request->input('owner_id'), // $apartment->ownership->first()->owner_id,
                        'proxy_id' => $request->input('proxy_id'),
                    ]
                );

                if ($newPoa->id != $rentalContract->poa_id) {
                    $rentalContract->poa_id = $newPoa->id;
                    $rentalContract->save();
                }
            }

            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityRentalContracts', 1)]);

            $datatable->setup($rentalContracts, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route));
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

    public function print(Request $request, $id)
    {
        $contract = RentalContractTracker::findOrFail($id);
        $rentalContract = RentalContract::findOrFail($contract->rental_contract_id);

        $option = null;
        $modifier = null;
        $tc = null;
        $flexi = null;
        $flexiOverdue = null;
        $summer = null;

        $covid = str_contains($rentalContract->name, 'COVID');

        $poa = $contract->poa;
        /*if (!$poa && !$covid) {
            return redirect()->back()->withErrors([trans(\Locales::getNamespace() . '/forms.contractProxyError')]);
        }*/

        $apartment = $contract->apartment;
        $building = $apartment->building;
        $owners = $apartment->owners;
        $owner = $contract->owner; // $owners->first()->owner;
        $locale = $owner->locale->locale;
        $isCustomFurniture = $apartment->furniture_id == 4;

        if (preg_match('/(option\s*\d*)\s*(.+)\s*\//i', $rentalContract->name, $matches) != false) {
            $option = strtolower(preg_replace('/\s+\/?/', '', $matches[1]));
            $modifier = strtolower(preg_replace('/\s+\/?/', '', $matches[2]));
            $tc = starts_with($rentalContract->name, 'Thomas Cook') ? true : false;
            if ($tc) {
                if (str_contains($rentalContract->name, 'UPGRADE')) {
                    $modifier = 'upgraded';
                }

                if ($contract->price_tc) {
                    $tc_years = 3;
                    $amounts = array_fill(0, $tc_years - 1, round($contract->price_tc / $tc_years, 2));
                    $amounts[$tc_years - 1] = round($contract->price_tc - array_sum($amounts), 2);
                }
            }
        } elseif ($flexi = str_contains($rentalContract->name, 'Flexi')) {
            $flexiOverdue = str_contains($rentalContract->name, 'Overdue');
            $periods = RentalRatesPeriod::with('rates')->orderBy('dfrom')->get();
        } elseif ($summer = str_contains($rentalContract->name, 'Summer')) {

        }

        if ($contract->contract_dfrom2) {
            $from = min(Carbon::parse($contract->contract_dfrom1), Carbon::parse($contract->contract_dfrom2));
        } else {
            $from = Carbon::parse($contract->contract_dfrom1);
        }

        if ($contract->contract_dfrom2) {
            $to = max(Carbon::parse($contract->contract_dto1), Carbon::parse($contract->contract_dto2));
        } else {
            $to = Carbon::parse($contract->contract_dto1);
        }

        $personal_dfrom1 = $contract->personal_dfrom1 ? Carbon::parse($contract->personal_dfrom1)->format('d.m') : null;
        $personal_dto1 = $contract->personal_dto1 ? Carbon::parse($contract->personal_dto1)->format('d.m') : null;
        $personal_dfrom2 = $contract->personal_dfrom2 ? Carbon::parse($contract->personal_dfrom2)->format('d.m') : null;
        $personal_dto2 = $contract->personal_dto2 ? Carbon::parse($contract->personal_dto2)->format('d.m') : null;

        $year = Year::with(['rentalCompanies' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->where('year', $from->year)->firstOrFail();

        $company = $year->rentalCompanies->first();

        $isCurrentYear = $year->year == $contract->mm_for_year;

        if (!$flexi) {
            $mmFee = 0;
            $mmFeeSum = 0;
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

                $mmFeeSum = ($mmFeeTax * $rentalContract->mm_covered) / 100;
                $mmFee = number_format($mmFeeSum, 2);
            }

            $rent = $contract->price + $mmFeeSum;
        }

        $names = [];
        $addresses = [];
        $emails = [];
        foreach ($owners as $o) {
            array_push($names, $o->owner->full_name);
            array_push($addresses, $o->owner->full_address);
            array_push($emails, $o->owner->email);
            array_push($emails, $o->owner->email_cc);
        }

        \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);

        if ($flexiOverdue) {
            $contractSlug = 'flexi-overdue-';
        } elseif ($flexi) {
            $contractSlug = 'flexi-';
        } elseif ($summer) {
            $contractSlug = 'summer-';
        } elseif ($tc) {
            $contractSlug = 'tc-';
        } elseif ($covid) {
            $contractSlug = 'covid-';
        } else {
            $contractSlug = '';
        }

        $templateProcessor = new \App\Extensions\PhpOffice\PhpWord\CustomTemplateProcessor(storage_path('app/templates/rental-contract-' . $contractSlug . $locale . '.docx'));

        $templateProcessor->setValue('DATE', Carbon::now('Europe/Sofia')->format('d.m.Y'));
        $templateProcessor->setValue('NAMES', implode('; ', $names));
        $templateProcessor->setValue('OWNER-NAME', $owner->full_name);
        $templateProcessor->setValue('ADDRESSES', implode('; ', array_unique($addresses)));
        $templateProcessor->setValue('OWNER-ADDRESS', $owner->full_address);
        $templateProcessor->setValue('BULSTAT', $company->bulstat);
        $templateProcessor->setValue('EGN', $company->egn);
        $templateProcessor->setValue('PASSPORT', $company->id_card);
        $templateProcessor->setValue('COMPANY-BG', $company->translate('bg')->name);
        $templateProcessor->setValue('COMPANY', $company->translate($locale)->name);
        $templateProcessor->setValue('ADDRESS-BG', $company->translate('bg')->address);
        $templateProcessor->setValue('ADDRESS', $company->translate($locale)->address);
        $templateProcessor->setValue('MANAGER-BG', $company->translate('bg')->manager);
        $templateProcessor->setValue('MANAGER', $company->translate($locale)->manager);
        $templateProcessor->setValue('APARTMENT', $apartment->number);
        $templateProcessor->setValue('PRICE_TC', $contract->price_tc);
        $templateProcessor->setValue('EMAILS', implode('; ', array_unique(array_filter($emails))));
        $templateProcessor->setValue('BUILDINGNAME-BG', $building->translate('bg')->name);
        $templateProcessor->setValue('BUILDINGNAME', $building->translate($locale)->name);
        $templateProcessor->setValue('BUILDINGDESCRIPTION-BG', $building->translate('bg')->description);
        $templateProcessor->setValue('BUILDINGDESCRIPTION', $building->translate($locale)->description);
        $templateProcessor->setValue('EXPIRY', $to->copy()->addYear($contract->duration - 1)->endOfYear()->format('d.m.Y'));

        if ($poa) {
            $proxy = $poa->proxy;
            $templateProcessor->setValue('PROXYNAME-BG', $proxy->translate('bg')->name);
            $templateProcessor->setValue('PROXYNAME', $proxy->translate($locale)->name);

            if ($proxy->is_company) {
                $templateProcessor->setValue('PROXYDETAILS-BG', \Lang::get('contracts.proxy-company', ['bulstat' => $proxy->bulstat, 'address' => $company->translate('bg')->address], 'bg'));
                $templateProcessor->setValue('PROXYDETAILS', \Lang::get('contracts.proxy-company', ['bulstat' => $proxy->bulstat, 'address' => $company->translate($locale)->address], $locale));
            } else {
                $templateProcessor->setValue('PROXYDETAILS-BG', \Lang::get('contracts.proxy-person', ['egn' => $proxy->egn, 'passport' => $proxy->id_card, 'issuedat' => $proxy->issued_at, 'issuedby' => $proxy->translate('bg')->issued_by, 'address' => $proxy->translate('bg')->address], 'bg'));
                $templateProcessor->setValue('PROXYDETAILS', \Lang::get('contracts.proxy-person', ['egn' => $proxy->egn, 'passport' => $proxy->id_card, 'issuedat' => $proxy->issued_at, 'issuedby' => $proxy->translate($locale)->issued_by, 'address' => $proxy->translate($locale)->address], $locale));
            }
        }

        /*$nf = new \NumberFormatter('bg', \NumberFormatter::SPELLOUT);
        $templateProcessor->setValue('MMFEE-BG', number_format($mm->mm_tax) . ' (' . $nf->format($mm->mm_tax) . ')');
        $nf = new \NumberFormatter($locale, \NumberFormatter::SPELLOUT);
        $templateProcessor->setValue('MMFEE', number_format($mm->mm_tax) . ' (' . $nf->format($mm->mm_tax) . ')');*/

        if ($flexi || $flexiOverdue || $summer) {
            if ($flexi) {
                $view = $apartment->view()->leftJoin('view_translations', 'view_translations.view_id', '=', 'views.id')->where('view_translations.locale', $locale)->first()->slug;
                $room = $apartment->room()->leftJoin('room_translations', 'room_translations.room_id', '=', 'rooms.id')->where('room_translations.locale', $locale)->first()->slug;
                $project = $apartment->project()->leftJoin('project_translations', 'project_translations.project_id', '=', 'projects.id')->where('project_translations.locale', $locale)->first()->slug;

                $templateProcessor->setValue('PERSONAL-USAGE', '01.07 - 05.09');
                $templateProcessor->setValue('RATE-OPEN-DATE', $periods->where('type', 'open')->first()->dtoWithoutYear);
                $templateProcessor->setValue('RATE-CLOSE-DATE', $periods->where('type', 'close')->first()->dfromWithoutYear);
                $templateProcessor->setValue('RATE-OPEN-CLOSE', $periods->where('type', 'open')->first()->rates->where('view', $view)->where('room', $room)->where('project', $project)->first()->rate);
                $templateProcessor->setValue('RATE-BEFORE-DATES', $periods->where('type', null)->first()->dfromWithoutYear . ' - ' . $periods->where('type', null)->first()->dtoWithoutYear);
                $templateProcessor->setValue('RATE-AFTER-DATES', $periods->where('type', null)->last()->dfromWithoutYear . ' - ' . $periods->where('type', null)->last()->dtoWithoutYear);
                $templateProcessor->setValue('RATE-BEFORE-AFTER', $periods->where('type', null)->first()->rates->where('view', $view)->where('room', $room)->where('project', $project)->first()->rate);
                $templateProcessor->setValue('RATE-PERSONAL-USAGE-DATES', $periods->where('type', 'personal-usage')->first()->dfromWithoutYear . ' - ' . $periods->where('type', 'personal-usage')->first()->dtoWithoutYear);
                $templateProcessor->setValue('RATE-PERSONAL-USAGE', $periods->where('type', 'personal-usage')->first()->rates->where('view', $view)->where('room', $room)->where('project', $project)->first()->rate);
                $templateProcessor->setValue('PERSONAL-USAGE-FROM', $personal_dfrom1);
                $templateProcessor->setValue('PERSONAL-USAGE-TO', $personal_dto1);
            }

            if ($flexiOverdue || $summer || ($flexi && $contract->duration == 1)) {
                $mmYears = $contract->mm_for_years ? explode(',', $contract->mm_for_years) : [$contract->mm_for_year];
                $templateProcessor->setValue('MM-YEAR-1', $mmYears[0] ?? '-');
                $templateProcessor->setValue('MM-YEAR-2', implode(array_slice($mmYears, 1), ', '));
                $templateProcessor->setValue('MM-YEAR-1-1-BG', '');
                $templateProcessor->setValue('MM-YEAR-1-1', '');
            } elseif ($flexi && $contract->duration > 1) {
                $templateProcessor->setValue('MM-YEAR-1-1-BG', \Lang::get('contracts.mm-year-1-1', [], 'bg'));
                $templateProcessor->setValue('MM-YEAR-1-1', \Lang::get('contracts.mm-year-1-1', [], $locale));
                $templateProcessor->setValue('MM-YEAR-1', '');
                $templateProcessor->setValue('MM-YEAR-2', '');
            }

            $templateProcessor->setValue('CONTRACT-DURATION', $contract->duration);
            $templateProcessor->setValue('CONTRACT-FROM', (($flexiOverdue || $summer) ? $from->copy()->format('d.m.Y') : $from->copy()->startOfYear()->format('d.m.Y')));
            $templateProcessor->setValue('CONTRACT-TO', (($flexiOverdue || $summer) ? $to->copy()->addYear($contract->duration - 1)->format('d.m.Y') : $to->copy()->addYear($contract->duration - 1)->endOfYear()->format('d.m.Y')));
        } else {
            $templateProcessor->setValue('RENT', number_format((($contract->price + $mmFeeSum) * $contract->duration) + $contract->price_tc, 2));

            // Custom furniture, All Years, All Options
            $templateProcessor->setValue('1.1-BG', $isCustomFurniture ? \Lang::get('contracts.custom-1-1', [], 'bg') : \Lang::get('contracts.1-1', [], 'bg'));
            $templateProcessor->setValue('1.1', $isCustomFurniture ? \Lang::get('contracts.custom-1-1', [], $locale) : \Lang::get('contracts.1-1', [], $locale));
            $templateProcessor->setValue('1.3-BG', $isCustomFurniture ? \Lang::get('contracts.custom-1-3', [], 'bg') : \Lang::get('contracts.1-3', [], 'bg'));
            $templateProcessor->setValue('1.3', $isCustomFurniture ? \Lang::get('contracts.custom-1-3', [], $locale) : \Lang::get('contracts.1-3', [], $locale));
            $templateProcessor->setValue('4.4-BG', $isCustomFurniture ? \Lang::get('contracts.custom-4-4', [], 'bg') : \Lang::get('contracts.4-4', [], 'bg'));
            $templateProcessor->setValue('4.4', $isCustomFurniture ? \Lang::get('contracts.custom-4-4', [], $locale) : \Lang::get('contracts.4-4', [], $locale));
            $templateProcessor->setValue('4.5-BG', $isCustomFurniture ? \Lang::get('contracts.custom-4-5', [], 'bg') : \Lang::get('contracts.4-5', [], 'bg'));
            $templateProcessor->setValue('4.5', $isCustomFurniture ? \Lang::get('contracts.custom-4-5', [], $locale) : \Lang::get('contracts.4-5', [], $locale));
            $templateProcessor->setValue('5-4-5-6-BG', $isCustomFurniture ? \Lang::get('contracts.custom-5-4-5-6', [], 'bg') : \Lang::get('contracts.5-4-5-6', [], 'bg'));
            $templateProcessor->setValue('5-4-5-6', $isCustomFurniture ? \Lang::get('contracts.custom-5-4-5-6', [], $locale) : \Lang::get('contracts.5-4-5-6', [], $locale));

            // All Years, All Options, MM Fees For Current Year
            $templateProcessor->setValue('6.3-BG', $isCurrentYear ? \Lang::get('contracts.6-3', [], 'bg') : '');
            $templateProcessor->setValue('6.3', $isCurrentYear ? \Lang::get('contracts.6-3', [], $locale) : '');

            // 1 Year: Option 1
            // 3 Years: All Options
            if ($contract->duration > 1 || ($contract->duration == 1 && $option == 'option1')) {
                $placeholderFrom = $from->copy()->startOfYear()->format('d.m.Y');
                $placeholderTo = $to->copy()->addYear($contract->duration - 1)->endOfYear()->format('d.m.Y');

                $placeholderYears = $contract->duration . ' ' . \Lang::choice('contracts.years', $contract->duration, [], 'bg');
                $templateProcessor->setValue('3.1-BG', \Lang::get('contracts.1-3-1', ['period' => $placeholderYears, 'from' => $placeholderFrom, 'to' => $placeholderTo], 'bg'));

                $placeholderYears = $contract->duration . ' ' . \Lang::choice('contracts.years', $contract->duration, [], $locale);
                $templateProcessor->setValue('3.1', \Lang::get('contracts.1-3-1', ['period' => $placeholderYears, 'from' => $placeholderFrom, 'to' => $placeholderTo], $locale));
            } else { // 1 Year: Option 2 - Option 6
                $placeholderFrom = $from->copy()->format('d.m.Y');
                $placeholderTo = $to->copy()->format('d.m.Y');

                $templateProcessor->setValue('3.1-BG', \Lang::get('contracts.2-3-1', ['from' => $placeholderFrom, 'to' => $placeholderTo], 'bg'));
                $templateProcessor->setValue('3.1', \Lang::get('contracts.2-3-1', ['from' => $placeholderFrom, 'to' => $placeholderTo], $locale));
            }

            if ($option == 'option1') { // All Years: Option 1
                $placeholderPeriod = \Lang::get('contracts.1-4-6-period2', ['from2' => $personal_dfrom2, 'to2' => $personal_dto2], 'bg');
                $templateProcessor->setValue('4.6-BG', \Lang::get('contracts.1-4-6', ['from1' => $personal_dfrom1, 'to1' => $personal_dto1, 'period2' => $placeholderPeriod], 'bg'));

                $placeholderPeriod = \Lang::get('contracts.1-4-6-period2', ['from2' => $personal_dfrom2, 'to2' => $personal_dto2], $locale);
                $templateProcessor->setValue('4.6', \Lang::get('contracts.1-4-6', ['from1' => $personal_dfrom1, 'to1' => $personal_dto1, 'period2' => $placeholderPeriod], $locale));

                if ($modifier == 'upgraded') {
                    $templateProcessor->setValue('4.7-BG', \Lang::get('contracts.1-4-7', [], 'bg'));
                    $templateProcessor->setValue('4.7', \Lang::get('contracts.1-4-7', [], $locale));
                } else {
                    $templateProcessor->setValue('4.7-BG', '');
                    $templateProcessor->setValue('4.7', '');
                }
            } elseif ($option == 'option2') { // All Years: Option 2
                if ($modifier == 'upgraded') {
                    $templateProcessor->setValue('4.7-BG', \Lang::get('contracts.1-4-7', [], 'bg'));
                    $templateProcessor->setValue('4.7', \Lang::get('contracts.1-4-7', [], $locale));
                } else {
                    $templateProcessor->setValue('4.7-BG', '');
                    $templateProcessor->setValue('4.7', '');
                }
            } elseif ($option == 'option5' || $option == 'option6') { // All Years: Option 5 - Option 6
                if ($covid) {
                    $text = \Lang::get('contracts.2-4-6-covid', [], 'bg');
                    $templateProcessor->setValue('4.6-BG', $text);

                    $text = \Lang::get('contracts.2-4-6-covid', [], $locale);
                    $templateProcessor->setValue('4.6', $text);
                } else {
                    $placeholderDuration = $option == 'option5' ? 3 : 5;
                    $nf1 = new \NumberFormatter('bg', \NumberFormatter::SPELLOUT);
                    $nf2 = new \NumberFormatter($locale, \NumberFormatter::SPELLOUT);

                    $text = \Lang::get('contracts.2-4-6', ['period' => $placeholderDuration . ' (' . $nf1->format($placeholderDuration) . ')'], 'bg');
                    $templateProcessor->setValue('4.6-BG', $text);

                    $text = \Lang::get('contracts.2-4-6', ['period' => $placeholderDuration . ' (' . $nf2->format($placeholderDuration) . ')'], $locale);
                    $templateProcessor->setValue('4.6', $text);
                }

                $templateProcessor->setValue('4.7-BG', '');
                $templateProcessor->setValue('4.7', '');
            } else {
                $templateProcessor->setValue('4.6-BG', '');
                $templateProcessor->setValue('4.6', '');
                $templateProcessor->setValue('4.7-BG', '');
                $templateProcessor->setValue('4.7', '');
            }

            if ($contract->duration == 1) {
                // All Years: Option 1 - Option 3
                if ($option == 'option1' || $option == 'option2' || $option == 'option3') {
                    $text = '2.1.1. ' . \Lang::get('contracts.1-2-1', ['mmfee' => $mmFee, 'year' => $contract->mm_for_year], 'bg') . '2.1.2. ' . \Lang::get('contracts.1-2-2', ['year' => $year->year, 'deadline' => $rentalContract->deadline_at], 'bg') . '2.2. ' . \Lang::get('contracts.1-2-3', [], 'bg') . '2.3. ' . \Lang::get('contracts.1-2-4', [], 'bg');
                    $templateProcessor->setValue('2-BG', $text);

                    $text = '2.1.1. ' . \Lang::get('contracts.1-2-1', ['mmfee' => $mmFee, 'year' => $contract->mm_for_year], $locale) . '2.1.2. ' . \Lang::get('contracts.1-2-2', ['year' => $year->year, 'deadline' => $rentalContract->deadline_at], $locale) . '2.2. ' . \Lang::get('contracts.1-2-3', [], $locale) . '2.3. ' . \Lang::get('contracts.1-2-4', [], $locale);
                    $templateProcessor->setValue('2', $text);
                } elseif ($option == 'option4' || $option == 'option5' || $option == 'option6') { // 1 Year: Option 4 - Option 6
                    $text = '2.2. ' . \Lang::get('contracts.2-2-1', ['year' => $contract->mm_for_year], 'bg') . '2.3. ' . \Lang::get('contracts.2-2-2', [], 'bg');
                    $templateProcessor->setValue('2-BG', $text);

                    $text = '2.2. ' . \Lang::get('contracts.2-2-1', ['year' => $contract->mm_for_year], $locale) . '2.3. ' . \Lang::get('contracts.2-2-2', [], $locale);
                    $templateProcessor->setValue('2', $text);
                }
            } elseif ($contract->duration > 1) {
                // All Years: Option 1 - Option 3
                if ($option == 'option1' || $option == 'option2' || $option == 'option3') {
                    $placeholderFrom = $from->copy()->startOfYear();
                    $placeholderTo = $to->copy()->endOfYear();
                    $placeholderDeadline = Carbon::parse($rentalContract->deadline_at);
                    $text1 = '';
                    $text2 = '';

                    for ($i = 0; $i < $contract->duration; $i++) {
                        $j = (2 + $i);

                        $price_tc = 0;
                        if ($tc && $contract->price_tc) {
                            if ($contract->duration == 3) {
                                $price_tc = $amounts[$i];
                            } elseif ($contract->duration == 5 && $i >= 2) {
                                $price_tc = $amounts[$i - 2];
                            }
                        }

                        $text1 .= '2.' . $j . '. ' . \Lang::get('contracts.3-2-1', ['from' => $placeholderFrom->format('d.m.Y'), 'to' => $placeholderTo->format('d.m.Y'), 'rent' => number_format($rent + $price_tc, 2)], 'bg') . '\n2.' . $j . '.1. ' . \Lang::get('contracts.1-2-1', ['mmfee' => $mmFee, 'year' => ($contract->mm_for_year + $i)], 'bg') . '2.' . $j . '.2. ' . \Lang::get('contracts.1-2-2', ['year' => ($year->year + $i), 'deadline' => $placeholderDeadline->format('d.m.Y')], 'bg');
                        $text2 .= '2.' . $j . '. ' . \Lang::get('contracts.3-2-1', ['from' => $placeholderFrom->format('d.m.Y'), 'to' => $placeholderTo->format('d.m.Y'), 'rent' => number_format($rent + $price_tc, 2)], $locale) . '\n2.' . $j . '.1. ' . \Lang::get('contracts.1-2-1', ['mmfee' => $mmFee, 'year' => ($contract->mm_for_year + $i)], $locale) . '2.' . $j . '.2. ' . \Lang::get('contracts.1-2-2', ['year' => ($year->year + $i), 'deadline' => $placeholderDeadline->format('d.m.Y')], $locale);

                        $placeholderFrom->addYear();
                        $placeholderTo->addYear();
                        $placeholderDeadline->addYear();
                    }

                    $text1 .= '2.' . ++$j . '. ' . \Lang::get('contracts.1-2-3', [], 'bg');
                    $text2 .= '2.' . $j . '. ' . \Lang::get('contracts.1-2-3', [], $locale);

                    $text1 .= '2.' . ++$j . '. ' . \Lang::get('contracts.1-2-4', [], 'bg');
                    $text2 .= '2.' . $j . '. ' . \Lang::get('contracts.1-2-4', [], $locale);

                    $templateProcessor->setValue('2-BG', $text1);
                    $templateProcessor->setValue('2', $text2);
                } else { // 3 Years: Option 4 - Option 6
                    $placeholderFrom = $from->copy()->startOfYear();
                    $placeholderTo = $to->copy()->endOfYear();

                    $text1 = '';
                    $text2 = '';

                    for ($i = 0; $i < $contract->duration; $i++) {
                        $j = (2 + $i);

                        $price_tc = 0;
                        if ($tc && $contract->price_tc) {
                            if ($contract->duration == 3) {
                                $price_tc = $amounts[$i];
                            } elseif ($contract->duration == 5 && $i >= 2) {
                                $price_tc = $amounts[$i - 2];
                            }
                        }

                        $text1 .= '2.' . $j . '. ' . \Lang::get('contracts.3-2-1', ['from' => $placeholderFrom->format('d.m.Y'), 'to' => $placeholderTo->format('d.m.Y'), 'rent' => number_format($rent + $price_tc, 2)], 'bg') . ' ' . \Lang::get('contracts.4-2-1', ['year' => ($contract->mm_for_year + $i)], 'bg');
                        $text2 .= '2.' . $j . '. ' . \Lang::get('contracts.3-2-1', ['from' => $placeholderFrom->format('d.m.Y'), 'to' => $placeholderTo->format('d.m.Y'), 'rent' => number_format($rent + $price_tc, 2)], $locale) . ' ' . \Lang::get('contracts.4-2-1', ['year' => ($contract->mm_for_year + $i)], $locale);

                        $placeholderFrom->addYear();
                        $placeholderTo->addYear();
                    }

                    $text1 .= '2.' . ++$j . '. ' . \Lang::get('contracts.1-2-3', [], 'bg');
                    $text2 .= '2.' . $j . '. ' . \Lang::get('contracts.1-2-3', [], $locale);

                    $templateProcessor->setValue('2-BG', $text1);
                    $templateProcessor->setValue('2', $text2);
                }

                // 3 Years: Option 2 - Option 6
                if ($option == 'option2' || $option == 'option3' || $option == 'option4' || $option == 'option5' || $option == 'option6') {
                    // current locale is: setlocale(LC_ALL, 0);
                    setlocale(LC_TIME, 'bg'); // change locale to BG
                    $placeholderDateFrom = \App\Helpers\displayWindowsDate($from->copy()->formatLocalized('%d %B'));
                    $placeholderDateTo = \App\Helpers\displayWindowsDate($to->copy()->formatLocalized('%d %B'));
                    $templateProcessor->setValue('3.2-BG', \Lang::get('contracts.3-2', ['from' => $placeholderDateFrom, 'to' => $placeholderDateTo], 'bg'));

                    setlocale(LC_TIME, $locale); // restore locale
                    $placeholderDateFrom = \App\Helpers\displayWindowsDate($from->copy()->formatLocalized('%d %B'));
                    $placeholderDateTo = \App\Helpers\displayWindowsDate($to->copy()->formatLocalized('%d %B'));
                    $templateProcessor->setValue('3.2', \Lang::get('contracts.3-2', ['from' => $placeholderDateFrom, 'to' => $placeholderDateTo], $locale));
                }
            }

            // reset 3.2
            $templateProcessor->setValue('3.2-BG', '');
            $templateProcessor->setValue('3.2', '');
        }

        return response()->stream(function () use ($templateProcessor) {
            $templateProcessor->saveAs("php://output");
        }, 200, [
            'Content-Disposition' => 'attachment; filename="rental-contract.docx"',
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Transfer-Encoding' => 'binary',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => 0,
        ]);
    }

    public function test(Request $request, NewsletterService $newsletterService, $id)
    {
        set_time_limit(300); // 5 mins.

        $contract = RentalContractTracker::findOrFail($id);
        $rentalContract = RentalContract::findOrFail($contract->rental_contract_id);
        $covid = str_contains($rentalContract->name, 'COVID');

        $poa = $contract->poa;
        /*if (!$poa && !$covid) {
            return response()->json([
                'errors' => [trans(\Locales::getNamespace() . '/forms.contractProxyError')],
            ]);
        }*/

        $apartment = $contract->apartment;
        $owners = $apartment->ownership;
        $owner = $contract->owner; // $owners->first()->owner;
        $locale = $owner->locale->locale;

        $data = $this->getData($contract, $poa, $apartment, $owners, $locale, $rentalContract);

        $template = NewsletterTemplates::where('template', 'rental-contract-no-poa')->where('locale_id', $owner->locale_id)->firstOrFail();

        $directory = public_path('upload') . DIRECTORY_SEPARATOR . 'newsletter-templates' . DIRECTORY_SEPARATOR . $template->id . DIRECTORY_SEPARATOR;

        $attachments = [];
        foreach ($template->attachments as $attachment) {
            array_push($attachments, $directory . \Config::get('upload.attachmentsDirectory') . DIRECTORY_SEPARATOR . $attachment->uuid . DIRECTORY_SEPARATOR . $attachment->file);
        }

        $body = $newsletterService->replaceHtml($template->body);

        foreach ($newsletterService->patterns() as $key => $pattern) {
            if (strpos($body, $pattern) !== false) {
                $body = preg_replace('/' . $pattern . '/', '<span style="background-color: #ff0;">' . $owner->{$newsletterService->columns()[$key]} . '</span>', $body);
            }
        }

        $body = preg_replace('/\[\[CONTRACT_NAME\]\]/', '<span style="background-color: #ff0;">' . $rentalContract->translate($locale)->name . '</span>', $body);

        $signature = $template->signature->translate($locale)->content;

        $onlineView = '';
        $links = \Lang::get(\Locales::getPublicNamespace() . '/newsletters.links', [], $locale);
        $copyright = \Lang::get(\Locales::getPublicNamespace() . '/newsletters.copyright', [], $locale);
        $disclaimer = \Lang::get(\Locales::getPublicNamespace() . '/newsletters.disclaimer', [], $locale);

        $html = \View::make(\Locales::getNamespace() . '.newsletters.templates.default', compact('body', 'signature', 'onlineView', 'links', 'copyright', 'disclaimer'))->with('newsletter', $template)->render();
        $text = preg_replace('/{IMAGE}/', '', $body);
        $text = $newsletterService->replaceText($text);

        $images = [storage_path('app/images/newsletter-logo.png')];
        foreach ($template->images as $image) {
            $path = $directory . \Config::get('upload.imagesDirectory') . DIRECTORY_SEPARATOR . $image->uuid . DIRECTORY_SEPARATOR . $image->file;
            if (strpos($html, '{IMAGE}') !== false) {
                if (strpos($html, '<td class="leftColumnContent">{IMAGE}</td>') !== false || strpos($html, '<td class="rightColumnContent">{IMAGE}</td>') !== false) {
                    $html = preg_replace('/{IMAGE}/', '<img src="cid:' . $image->file . '" class="columnImage" style="height:auto !important;max-width:260px !important;" />', $html, 1);
                } else {
                    $html = preg_replace('/{IMAGE}/', '<img src="cid:' . $image->file . '" class="responsiveImage" style="height:auto !important;max-width:' . \Image::make($path)->width() . 'px !important;" />', $html, 1);
                }

                array_push($images, $path);
            }
        }

        $directorySignatures = public_path('upload') . DIRECTORY_SEPARATOR . 'signatures' . DIRECTORY_SEPARATOR . $template->signature->id . DIRECTORY_SEPARATOR;
        foreach ($template->signature->images as $image) {
            $path = $directorySignatures . $image->uuid . DIRECTORY_SEPARATOR . $image->file;
            if (strpos($html, '{SIGNATURE}') !== false) {
                $html = preg_replace('/{SIGNATURE}/', '<img src="cid:' . $image->file . '" class="responsiveImage" style="height:auto !important;max-width:' . \Image::make($path)->width() . 'px !important;" />', $html, 1);
                array_push($images, $path);
            }
        }

        Mail::send([], [], function ($message) use ($data, $owner, $template, $images, $attachments, $locale, $html, $text) {
            $message->from($template->signature->email, $template->signature->translate($locale)->name);
            $message->sender($template->signature->email, $template->signature->translate($locale)->name);
            $message->replyTo($template->signature->email, $template->signature->translate($locale)->name);
            $message->returnPath('mitko@sunsetresort.bg');
            // https://github.com/swiftmailer/swiftmailer/issues/58#issuecomment-210032031
            $message->to((\Config::get('mail.to.address') ? \Config::get('mail.to.address') : \Auth::user()->email), "=?UTF-8?B?" . base64_encode($owner->full_name) . "?=");
            $message->subject($template->subject);

            foreach ($images as $image) {
                $html = preg_replace('/' . preg_quote('cid:' . File::name($image) . '.' . File::extension($image)) . '/', $message->embed($image), $html, 1);
            }

            foreach ($attachments as $attachment) {
                $message->attach($attachment);
            }

            $message->setBody($html, 'text/html');
            $message->addPart($text, 'text/plain');
            $message->attachData($data, 'rental-contract.pdf');
        });

        if (count(Mail::failures()) > 0) {
            $msg = '';
            foreach (Mail::failures() as $email) {
                $msg .= '<br />' . $email;
            }
            return response()->json([
                'errors' => [trans(\Locales::getNamespace() . '/forms.contractSentError') . $msg],
            ]);
        } else {
            return response()->json([
                'success' => [trans(\Locales::getNamespace() . '/forms.contractSentSuccessfully')],
            ]);
        }
    }

    public function send(Request $request, NewsletterService $newsletterService, $id)
    {
        set_time_limit(300); // 5 mins.

        $contract = RentalContractTracker::findOrFail($id);
        $rentalContract = RentalContract::findOrFail($contract->rental_contract_id);
        $covid = str_contains($rentalContract->name, 'COVID');

        $poa = $contract->poa;
        if (!$poa && !$covid) {
            return response()->json([
                'errors' => [trans(\Locales::getNamespace() . '/forms.contractProxyError')],
            ]);
        }

        $apartment = $contract->apartment;
        $owners = $apartment->ownership;
        $owner = $contract->owner; /*$owners->filter(function ($value, $key) {
            return $value->owner->email;
        })->first()->owner;*/
        $locale = $owner->locale->locale;

        $data = $this->getData($contract, $poa, $apartment, $owners, $locale, $rentalContract);

        $template = NewsletterTemplates::where('template', 'rental-contract-no-poa')->where('locale_id', $owner->locale_id)->firstOrFail();

        $directory = public_path('upload') . DIRECTORY_SEPARATOR . 'newsletter-templates' . DIRECTORY_SEPARATOR . $template->id . DIRECTORY_SEPARATOR;

        $attachments = [];
        foreach ($template->attachments as $attachment) {
            array_push($attachments, $directory . \Config::get('upload.attachmentsDirectory') . DIRECTORY_SEPARATOR . $attachment->uuid . DIRECTORY_SEPARATOR . $attachment->file);
        }

        $body = $newsletterService->replaceHtml($template->body);

        foreach ($newsletterService->patterns() as $key => $pattern) {
            if (strpos($body, $pattern) !== false) {
                $body = preg_replace('/' . $pattern . '/', $owner->{$newsletterService->columns()[$key]}, $body);
            }
        }

        $body = preg_replace('/\[\[CONTRACT_NAME\]\]/', $rentalContract->translate($locale)->name, $body);

        $signature = $template->signature->translate($locale)->content;

        $onlineView = '';
        $links = \Lang::get(\Locales::getPublicNamespace() . '/newsletters.links', [], $locale);
        $copyright = \Lang::get(\Locales::getPublicNamespace() . '/newsletters.copyright', [], $locale);
        $disclaimer = \Lang::get(\Locales::getPublicNamespace() . '/newsletters.disclaimer', [], $locale);

        $html = \View::make(\Locales::getNamespace() . '.newsletters.templates.default', compact('body', 'signature', 'onlineView', 'links', 'copyright', 'disclaimer'))->with('newsletter', $template)->render();
        $text = preg_replace('/{IMAGE}/', '', $body);
        $text = $newsletterService->replaceText($text);

        $images = [storage_path('app/images/newsletter-logo.png')];
        foreach ($template->images as $image) {
            $path = $directory . \Config::get('upload.imagesDirectory') . DIRECTORY_SEPARATOR . $image->uuid . DIRECTORY_SEPARATOR . $image->file;
            if (strpos($html, '{IMAGE}') !== false) {
                if (strpos($html, '<td class="leftColumnContent">{IMAGE}</td>') !== false || strpos($html, '<td class="rightColumnContent">{IMAGE}</td>') !== false) {
                    $html = preg_replace('/{IMAGE}/', '<img src="cid:' . $image->file . '" class="columnImage" style="height:auto !important;max-width:260px !important;" />', $html, 1);
                } else {
                    $html = preg_replace('/{IMAGE}/', '<img src="cid:' . $image->file . '" class="responsiveImage" style="height:auto !important;max-width:' . \Image::make($path)->width() . 'px !important;" />', $html, 1);
                }

                array_push($images, $path);
            }
        }

        $directorySignatures = public_path('upload') . DIRECTORY_SEPARATOR . 'signatures' . DIRECTORY_SEPARATOR . $template->signature->id . DIRECTORY_SEPARATOR;
        foreach ($template->signature->images as $image) {
            $path = $directorySignatures . $image->uuid . DIRECTORY_SEPARATOR . $image->file;
            if (strpos($html, '{SIGNATURE}') !== false) {
                $html = preg_replace('/{SIGNATURE}/', '<img src="cid:' . $image->file . '" class="responsiveImage" style="height:auto !important;max-width:' . \Image::make($path)->width() . 'px !important;" />', $html, 1);
                array_push($images, $path);
            }
        }

        Mail::send([], [], function ($message) use ($data, $apartment, $owner, $template, $images, $attachments, $locale, $html, $text) {
            $message->from($template->signature->email, $template->signature->translate($locale)->name);
            $message->sender($template->signature->email, $template->signature->translate($locale)->name);
            $message->replyTo($template->signature->email, $template->signature->translate($locale)->name);
            $message->returnPath('mitko@sunsetresort.bg');

            // https://github.com/swiftmailer/swiftmailer/issues/58#issuecomment-210032031
            $message->to((\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $owner->email), "=?UTF-8?B?" . base64_encode($owner->full_name) . "?=");
            if ($owner->email_cc) {
                $message->to((\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $owner->email_cc), "=?UTF-8?B?" . base64_encode($owner->full_name) . "?=");
            }

            foreach ($apartment->owners as $o) {
                if ($owner->id != $o->owner->id && $o->owner->email) {
                    $message->cc((\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $o->owner->email), "=?UTF-8?B?" . base64_encode($o->owner->full_name) . "?=");
                    if ($o->owner->email_cc) {
                        $message->cc((\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $o->owner->email_cc), "=?UTF-8?B?" . base64_encode($o->owner->full_name) . "?=");
                    }
                }
            }

            $message->subject($template->subject);

            foreach ($images as $image) {
                $html = preg_replace('/' . preg_quote('cid:' . File::name($image) . '.' . File::extension($image)) . '/', $message->embed($image), $html, 1);
            }

            foreach ($attachments as $attachment) {
                $message->attach($attachment);
            }

            $message->setBody($html, 'text/html');
            $message->addPart($text, 'text/plain');
            $message->attachData($data, 'rental-contract.pdf');
        });

        if (count(Mail::failures()) > 0) {
            $msg = '';
            foreach (Mail::failures() as $email) {
                $msg .= '<br />' . $email;
            }
            return response()->json([
                'errors' => [trans(\Locales::getNamespace() . '/forms.contractSentError') . $msg],
            ]);
        } else {
            $contract->sent_at = Carbon::now();
            $contract->save();

            if ($poa) {
                $poa->sent_at = Carbon::now();
                $poa->save();
            }

            Mail::send([], [], function ($message) use ($data, $html, $text, $images, $template, $attachments, $locale) {
                $message->from($template->signature->email, $template->signature->translate($locale)->name);
                $message->sender($template->signature->email, $template->signature->translate($locale)->name);
                $message->replyTo($template->signature->email, $template->signature->translate($locale)->name);
                $message->returnPath('mitko@sunsetresort.bg');

                // https://github.com/swiftmailer/swiftmailer/issues/58#issuecomment-210032031
                $message->to((\Config::get('mail.to.address') ? \Config::get('mail.to.address') : \Auth::user()->email), "=?UTF-8?B?" . base64_encode(\Auth::user()->name) . "?=");

                $message->subject($template->subject);

                foreach ($attachments as $attachment) {
                    $message->attach($attachment);
                }

                foreach ($images as $image) {
                    $html = preg_replace('/' . preg_quote('cid:' . File::name($image) . '.' . File::extension($image)) . '/', $message->embed($image), $html, 1);
                }

                $message->setBody($html, 'text/html');
                $message->addPart($text, 'text/plain');
                $message->attachData($data, 'rental-contract.pdf');
            });

            \Session::flash('success', [trans(\Locales::getNamespace() . '/forms.contractSentSuccessfully')]);

            return response()->json([
                'refresh' => true,
            ]);
        }
    }

    public function confirmSendToAll(Request $request)
    {
        $table = $this->route;
        if ($request->input('table')) { // magnific popup request
            $table = $request->input('table');
        }

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.send-to-all', compact('table'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function sendToAll(Request $request, NewsletterService $newsletterService)
    {
        set_time_limit(300); // 5 mins.

        $contracts = RentalContractTracker::findOrFail($request->input('id'));
        $errors = [];

        foreach ($contracts as $contract) {
            $apartment = $contract->apartment;
            $rentalContract = RentalContract::findOrFail($contract->rental_contract_id);
            $covid = str_contains($rentalContract->name, 'COVID');

            $poa = $contract->poa;
            if (!$poa && !$covid) {
                array_push($errors, trans(\Locales::getNamespace() . '/forms.contractsProxyError', ['apartment' => $apartment->number]));
            }

            $owners = $apartment->ownership;
            $owner = $contract->owner; // $owners->first()->owner;
            $locale = $owner->locale->locale;

            $data = $this->getData($contract, $poa, $apartment, $owners, $locale, $rentalContract);

            $template = NewsletterTemplates::where('template', 'rental-contract-no-poa')->where('locale_id', $owner->locale_id)->firstOrFail();

            $directory = public_path('upload') . DIRECTORY_SEPARATOR . 'newsletter-templates' . DIRECTORY_SEPARATOR . $template->id . DIRECTORY_SEPARATOR;

            $attachments = [];
            foreach ($template->attachments as $attachment) {
                array_push($attachments, $directory . \Config::get('upload.attachmentsDirectory') . DIRECTORY_SEPARATOR . $attachment->uuid . DIRECTORY_SEPARATOR . $attachment->file);
            }

            $body = $newsletterService->replaceHtml($template->body);

            foreach ($newsletterService->patterns() as $key => $pattern) {
                if (strpos($body, $pattern) !== false) {
                    $body = preg_replace('/' . $pattern . '/', $owner->{$newsletterService->columns()[$key]}, $body);
                }
            }

            $body = preg_replace('/\[\[CONTRACT_NAME\]\]/', $rentalContract->translate($locale)->name, $body);

            $signature = $template->signature->translate($locale)->content;

            $onlineView = '';
            $links = \Lang::get(\Locales::getPublicNamespace() . '/newsletters.links', [], $locale);
            $copyright = \Lang::get(\Locales::getPublicNamespace() . '/newsletters.copyright', [], $locale);
            $disclaimer = \Lang::get(\Locales::getPublicNamespace() . '/newsletters.disclaimer', [], $locale);

            $html = \View::make(\Locales::getNamespace() . '.newsletters.templates.default', compact('body', 'signature', 'onlineView', 'links', 'copyright', 'disclaimer'))->with('newsletter', $template)->render();
            $text = preg_replace('/{IMAGE}/', '', $body);
            $text = $newsletterService->replaceText($text);

            $images = [storage_path('app/images/newsletter-logo.png')];
            foreach ($template->images as $image) {
                $path = $directory . \Config::get('upload.imagesDirectory') . DIRECTORY_SEPARATOR . $image->uuid . DIRECTORY_SEPARATOR . $image->file;
                if (strpos($html, '{IMAGE}') !== false) {
                    if (strpos($html, '<td class="leftColumnContent">{IMAGE}</td>') !== false || strpos($html, '<td class="rightColumnContent">{IMAGE}</td>') !== false) {
                        $html = preg_replace('/{IMAGE}/', '<img src="cid:' . $image->file . '" class="columnImage" style="height:auto !important;max-width:260px !important;" />', $html, 1);
                    } else {
                        $html = preg_replace('/{IMAGE}/', '<img src="cid:' . $image->file . '" class="responsiveImage" style="height:auto !important;max-width:' . \Image::make($path)->width() . 'px !important;" />', $html, 1);
                    }

                    array_push($images, $path);
                }
            }

            $directorySignatures = public_path('upload') . DIRECTORY_SEPARATOR . 'signatures' . DIRECTORY_SEPARATOR . $template->signature->id . DIRECTORY_SEPARATOR;
            foreach ($template->signature->images as $image) {
                $path = $directorySignatures . $image->uuid . DIRECTORY_SEPARATOR . $image->file;
                if (strpos($html, '{SIGNATURE}') !== false) {
                    $html = preg_replace('/{SIGNATURE}/', '<img src="cid:' . $image->file . '" class="responsiveImage" style="height:auto !important;max-width:' . \Image::make($path)->width() . 'px !important;" />', $html, 1);
                    array_push($images, $path);
                }
            }

            Mail::send([], [], function ($message) use ($data, $apartment, $owner, $template, $images, $attachments, $locale, $html, $text) {
                $message->from($template->signature->email, $template->signature->translate($locale)->name);
                $message->sender($template->signature->email, $template->signature->translate($locale)->name);
                $message->replyTo($template->signature->email, $template->signature->translate($locale)->name);
                $message->returnPath('mitko@sunsetresort.bg');

                // https://github.com/swiftmailer/swiftmailer/issues/58#issuecomment-210032031
                $message->to((\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $owner->email), "=?UTF-8?B?" . base64_encode($owner->full_name) . "?=");
                if ($owner->email_cc) {
                    $message->to((\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $owner->email_cc), "=?UTF-8?B?" . base64_encode($owner->full_name) . "?=");
                }

                foreach ($apartment->owners as $o) {
                    if ($owner->id != $o->owner->id && $o->owner->email) {
                        $message->cc((\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $o->owner->email), "=?UTF-8?B?" . base64_encode($o->owner->full_name) . "?=");
                        if ($o->owner->email_cc) {
                            $message->cc((\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $o->owner->email_cc), "=?UTF-8?B?" . base64_encode($o->owner->full_name) . "?=");
                        }
                    }
                }

                $message->subject($template->subject);

                foreach ($images as $image) {
                    $html = preg_replace('/' . preg_quote('cid:' . File::name($image) . '.' . File::extension($image)) . '/', $message->embed($image), $html, 1);
                }

                foreach ($attachments as $attachment) {
                    $message->attach($attachment);
                }

                $message->setBody($html, 'text/html');
                $message->addPart($text, 'text/plain');
                $message->attachData($data, 'rental-contract.pdf');
            });

            if (count(Mail::failures()) > 0) {
                $msg = '';
                foreach (Mail::failures() as $email) {
                    $msg .= '<br />' . $email;
                }

                array_push($errors, trans(\Locales::getNamespace() . '/forms.contractSentError') . $msg);
            } else {
                $contract->sent_at = Carbon::now();
                $contract->save();

                if ($poa) {
                    $poa->sent_at = Carbon::now();
                    $poa->save();
                }

                Mail::send([], [], function ($message) use ($data, $html, $text, $images, $template, $attachments, $locale) {
                    $message->from($template->signature->email, $template->signature->translate($locale)->name);
                    $message->sender($template->signature->email, $template->signature->translate($locale)->name);
                    $message->replyTo($template->signature->email, $template->signature->translate($locale)->name);
                    $message->returnPath('mitko@sunsetresort.bg');

                    // https://github.com/swiftmailer/swiftmailer/issues/58#issuecomment-210032031
                    $message->to((\Config::get('mail.to.address') ? \Config::get('mail.to.address') : \Auth::user()->email), "=?UTF-8?B?" . base64_encode(\Auth::user()->name) . "?=");

                    $message->subject($template->subject);

                    foreach ($attachments as $attachment) {
                        $message->attach($attachment);
                    }

                    foreach ($images as $image) {
                        $html = preg_replace('/' . preg_quote('cid:' . File::name($image) . '.' . File::extension($image)) . '/', $message->embed($image), $html, 1);
                    }

                    $message->setBody($html, 'text/html');
                    $message->addPart($text, 'text/plain');
                    $message->attachData($data, 'rental-contract.pdf');
                });
            }
        }

        if ($errors) {
            $errors = new \Illuminate\Support\MessageBag($errors);
            \Session::flash('errors', $errors);
        }

        \Session::flash('success', [trans(\Locales::getNamespace() . '/forms.contractSentSuccessfully')]);

        return response()->json([
            'refresh' => true,
        ]);
    }

    public function confirmActivate(Request $request)
    {
        $table = $this->route;
        if ($request->input('table')) { // magnific popup request
            $table = $request->input('table');
        }

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.activate', compact('table'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function activate(Request $request)
    {
        $now = Carbon::now();

        $contracts = RentalContractTracker::findOrFail($request->input('id'));

        Poa::whereIn('id', $contracts->pluck('poa_id'))->update(['is_active' => 1]);

        foreach ($contracts as $contract) {
            $newContract = Contract::create([
                'duration' => $contract->duration,
                'signed_at' => $now,
                'comments' => $contract->comments,
                'apartment_id' => $contract->apartment_id,
                'rental_contract_id' => $contract->rental_contract_id,
                'is_exception' => $contract->is_exception,
            ]);

            if ($newContract->id) {
                $rentalContract = $newContract->rentalContract()->first();

                if ($rentalContract->contract_dfrom2) {
                    $year = (Carbon::parse($rentalContract->contract_dfrom1) > Carbon::parse($rentalContract->contract_dfrom2)) ? Carbon::parse($rentalContract->contract_dfrom2)->year : Carbon::parse($rentalContract->contract_dfrom1)->year;
                } else {
                    $year = Carbon::parse($rentalContract->contract_dfrom1)->year;
                }

                if ($contract->price_tc) {
                    $tc_years = 3;
                    $amounts = array_fill(0, $tc_years - 1, round($contract->price_tc / $tc_years, 2));
                    $amounts[$tc_years - 1] = round($contract->price_tc - array_sum($amounts), 2);
                }

                if ($contract->mm_for_years) {
                    $yearsMM = explode(',', $contract->mm_for_years);
                    $yearsMM = array_chunk($yearsMM, 2); // each contract for each year covers 2 mm years
                }

                $years = [];
                for ($i = 0; $i < $contract->duration; $i++) {
                    $price_tc = 0;
                    if ($contract->price_tc) {
                        if ($contract->duration == 3) {
                            $price_tc = $amounts[$i];
                        } elseif ($contract->duration == 5 && $i >= 2) {
                            $price_tc = $amounts[$i - 2];
                        }
                    }

                    array_push($years, [
                        'created_at' => $now,
                        'contract_id' => $newContract->id,
                        'year' => $year + $i,
                        'mm_for_year' => $contract->mm_for_year ? $contract->mm_for_year + $i : null,
                        'mm_for_years' => ($contract->mm_for_years ? implode(',', $yearsMM[$i]) : null),
                        'price' => $contract->price,
                        'price_tc' => $price_tc,
                        'is_exception' => $contract->is_exception,
                        'contract_dfrom1' => $contract->contract_dfrom1 ? Carbon::parse($contract->contract_dfrom1)->addYear($i) : null,
                        'contract_dto1' => $contract->contract_dto1 ? Carbon::parse($contract->contract_dto1)->addYear($i) : null,
                        'contract_dfrom2' => $contract->contract_dfrom2 ? Carbon::parse($contract->contract_dfrom2)->addYear($i) : null,
                        'contract_dto2' => $contract->contract_dto2 ? Carbon::parse($contract->contract_dto2)->addYear($i) : null,
                        'personal_dfrom1' => $contract->personal_dfrom1 ? Carbon::parse($contract->personal_dfrom1)->addYear($i) : null,
                        'personal_dto1' => $contract->personal_dto1 ? Carbon::parse($contract->personal_dto1)->addYear($i) : null,
                        'personal_dfrom2' => $contract->personal_dfrom2 ? Carbon::parse($contract->personal_dfrom2)->addYear($i) : null,
                        'personal_dto2' => $contract->personal_dto2 ? Carbon::parse($contract->personal_dto2)->addYear($i) : null,
                    ]);
                }

                ContractYear::insert($years);

                $contract->delete();
            }
        }

        \Session::flash('success', [trans(\Locales::getNamespace() . '/forms.contractActivatedSuccessfully')]);

        return response()->json([
            'refresh' => true,
        ]);
    }

    public function getData($contract, $poa, $apartment, $owners, $locale, $rentalContract)
    {
        $option = null;
        $modifier = null;
        $tc = null;
        $flexi = null;
        $flexiOverdue = null;
        $summer = null;

        $covid = str_contains($rentalContract->name, 'COVID');

        $building = $apartment->building;
        $owner = $contract->owner;
        $isCustomFurniture = $apartment->furniture_id == 4;

        if (preg_match('/(option\s*\d*)\s*(.+)\s*\//i', $rentalContract->name, $matches) != false) {
            $option = strtolower(preg_replace('/\s+\/?/', '', $matches[1]));
            $modifier = strtolower(preg_replace('/\s+\/?/', '', $matches[2]));
            $tc = starts_with($rentalContract->name, 'Thomas Cook') ? true : false;
            if ($tc) {
                if (str_contains($rentalContract->name, 'UPGRADE')) {
                    $modifier = 'upgraded';
                }

                if ($contract->price_tc) {
                    $tc_years = 3;
                    $amounts = array_fill(0, $tc_years - 1, round($contract->price_tc / $tc_years, 2));
                    $amounts[$tc_years - 1] = round($contract->price_tc - array_sum($amounts), 2);
                }
            }
        } elseif ($flexi = str_contains($rentalContract->name, 'Flexi')) {
            $flexiOverdue = str_contains($rentalContract->name, 'Overdue');
            $periods = RentalRatesPeriod::with('rates')->orderBy('dfrom')->get();
        } elseif ($summer = str_contains($rentalContract->name, 'Summer')) {

        }

        if ($contract->contract_dfrom2) {
            $from = min(Carbon::parse($contract->contract_dfrom1), Carbon::parse($contract->contract_dfrom2));
        } else {
            $from = Carbon::parse($contract->contract_dfrom1);
        }

        if ($contract->contract_dfrom2) {
            $to = max(Carbon::parse($contract->contract_dto1), Carbon::parse($contract->contract_dto2));
        } else {
            $to = Carbon::parse($contract->contract_dto1);
        }

        $personal_dfrom1 = $contract->personal_dfrom1 ? Carbon::parse($contract->personal_dfrom1)->format('d.m') : null;
        $personal_dto1 = $contract->personal_dto1 ? Carbon::parse($contract->personal_dto1)->format('d.m') : null;
        $personal_dfrom2 = $contract->personal_dfrom2 ? Carbon::parse($contract->personal_dfrom2)->format('d.m') : null;
        $personal_dto2 = $contract->personal_dto2 ? Carbon::parse($contract->personal_dto2)->format('d.m') : null;

        $year = Year::with(['rentalCompanies' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->where('year', $from->year)->firstOrFail();

        $company = $year->rentalCompanies->first();

        $isCurrentYear = $year->year == $contract->mm_for_year;

        if (!$flexi) {
            $mmFee = 0;
            $mmFeeSum = 0;
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

                $mmFeeSum = ($mmFeeTax * $rentalContract->mm_covered) / 100;
                $mmFee = number_format($mmFeeSum, 2);
            }

            $rent = $contract->price + $mmFeeSum;
        }

        $names = [];
        $addresses = [];
        $emails = [];
        foreach ($owners as $o) {
            array_push($names, $o->owner->full_name);
            array_push($addresses, $o->owner->full_address);
            array_push($emails, $o->owner->email);
            array_push($emails, $o->owner->email_cc);
        }

        if (!defined('DOMPDF_ENABLE_AUTOLOAD')) {
            define("DOMPDF_ENABLE_AUTOLOAD", false);
        }
        $dompdf = new Dompdf();
        $dompdf->set_paper('A4');
        $dompdf->set_option('isFontSubsettingEnabled', true);

        if ($flexiOverdue) {
            $contractSlug = 'flexi-overdue-';
        } elseif ($flexi) {
            $contractSlug = 'flexi-';
        } elseif ($summer) {
            $contractSlug = 'summer-';
        } elseif ($tc) {
            $contractSlug = 'tc-';
        } elseif ($covid) {
            $contractSlug = 'covid-';
        } else {
            $contractSlug = '';
        }

        $html = file_get_contents(storage_path('app/templates/rental-contract-' . $contractSlug . $locale . '.html'));

        $html = preg_replace('/\$\{DATE\}/', Carbon::now('Europe/Sofia')->format('d.m.Y'), $html);
        $html = preg_replace('/\$\{NAMES\}/', implode('; ', $names), $html);
        $html = preg_replace('/\$\{OWNER-NAME\}/', $owner->full_name, $html);
        $html = preg_replace('/\$\{ADDRESSES\}/', implode('; ', array_unique($addresses)), $html);
        $html = preg_replace('/\$\{OWNER-ADDRESS\}/', $owner->full_address, $html);
        $html = preg_replace('/\$\{BULSTAT\}/', $company->bulstat, $html);
        $html = preg_replace('/\$\{EGN\}/', $company->egn, $html);
        $html = preg_replace('/\$\{PASSPORT\}/', $company->id_card, $html);
        $html = preg_replace('/\$\{COMPANY-BG\}/', $company->translate('bg')->name, $html);
        $html = preg_replace('/\$\{COMPANY\}/', $company->translate($locale)->name, $html);
        $html = preg_replace('/\$\{ADDRESS-BG\}/', $company->translate('bg')->address, $html);
        $html = preg_replace('/\$\{ADDRESS\}/', $company->translate($locale)->address, $html);
        $html = preg_replace('/\$\{MANAGER-BG\}/', $company->translate('bg')->manager, $html);
        $html = preg_replace('/\$\{MANAGER\}/', $company->translate($locale)->manager, $html);
        $html = preg_replace('/\$\{APARTMENT\}/', $apartment->number, $html);
        $html = preg_replace('/\$\{PRICE_TC\}/', $contract->price_tc, $html);
        $html = preg_replace('/\$\{EMAILS\}/', implode('; ', array_unique(array_filter($emails))), $html);
        $html = preg_replace('/\$\{BUILDINGNAME-BG\}/', $building->translate('bg')->name, $html);
        $html = preg_replace('/\$\{BUILDINGNAME\}/', $building->translate($locale)->name, $html);
        $html = preg_replace('/\$\{BUILDINGDESCRIPTION-BG\}/', $building->translate('bg')->description, $html);
        $html = preg_replace('/\$\{BUILDINGDESCRIPTION\}/', $building->translate($locale)->description, $html);
        $html = preg_replace('/\$\{EXPIRY\}/', $to->copy()->addYear($contract->duration - 1)->endOfYear()->format('d.m.Y'), $html);

        if ($poa) {
            $proxy = $poa->proxy;
            $html = preg_replace('/\$\{PROXYNAME-BG\}/', $proxy->translate('bg')->name, $html);
            $html = preg_replace('/\$\{PROXYNAME\}/', $proxy->translate($locale)->name, $html);

            if ($proxy->is_company) {
                $html = preg_replace('/\$\{PROXYDETAILS-BG\}/', \Lang::get('contracts.proxy-company', ['bulstat' => $proxy->bulstat, 'address' => $company->translate('bg')->address], 'bg'), $html);
                $html = preg_replace('/\$\{PROXYDETAILS\}/', \Lang::get('contracts.proxy-company', ['bulstat' => $proxy->bulstat, 'address' => $company->translate($locale)->address], $locale), $html);
            } else {
                $html = preg_replace('/\$\{PROXYDETAILS-BG\}/', \Lang::get('contracts.proxy-person', ['egn' => $proxy->egn, 'passport' => $proxy->id_card, 'issuedat' => $proxy->issued_at, 'issuedby' => $proxy->translate('bg')->issued_by, 'address' => $proxy->translate('bg')->address], 'bg'), $html);
                $html = preg_replace('/\$\{PROXYDETAILS\}/', \Lang::get('contracts.proxy-person', ['egn' => $proxy->egn, 'passport' => $proxy->id_card, 'issuedat' => $proxy->issued_at, 'issuedby' => $proxy->translate($locale)->issued_by, 'address' => $proxy->translate($locale)->address], $locale), $html);
            }
        }

        /*$nf = new \NumberFormatter('bg', \NumberFormatter::SPELLOUT);
        $html = preg_replace('/\$\{MMFEE-BG\}/', number_format($mm->mm_tax) . ' (' . $nf->format($mm->mm_tax) . ')', $html);
        $nf = new \NumberFormatter($locale, \NumberFormatter::SPELLOUT);
        $html = preg_replace('/\$\{MMFEE\}/', number_format($mm->mm_tax) . ' (' . $nf->format($mm->mm_tax) . ')', $html);*/

        if ($flexi || $flexiOverdue || $summer) {
            if ($flexi) {
                $view = $apartment->view()->leftJoin('view_translations', 'view_translations.view_id', '=', 'views.id')->where('view_translations.locale', $locale)->first()->slug;
                $room = $apartment->room()->leftJoin('room_translations', 'room_translations.room_id', '=', 'rooms.id')->where('room_translations.locale', $locale)->first()->slug;
                $project = $apartment->project()->leftJoin('project_translations', 'project_translations.project_id', '=', 'projects.id')->where('project_translations.locale', $locale)->first()->slug;

                $html = preg_replace('/\$\{PERSONAL-USAGE\}/', '01.07 - 05.09', $html);
                $html = preg_replace('/\$\{RATE-OPEN-DATE\}/', $periods->where('type', 'open')->first()->dtoWithoutYear, $html);
                $html = preg_replace('/\$\{RATE-CLOSE-DATE\}/', $periods->where('type', 'close')->first()->dfromWithoutYear, $html);
                $html = preg_replace('/\$\{RATE-OPEN-CLOSE\}/', $periods->where('type', 'open')->first()->rates->where('view', $view)->where('room', $room)->where('project', $project)->first()->rate, $html);
                $html = preg_replace('/\$\{RATE-BEFORE-DATES\}/', $periods->where('type', null)->first()->dfromWithoutYear . ' - ' . $periods->where('type', null)->first()->dtoWithoutYear, $html);
                $html = preg_replace('/\$\{RATE-AFTER-DATES\}/', $periods->where('type', null)->last()->dfromWithoutYear . ' - ' . $periods->where('type', null)->last()->dtoWithoutYear, $html);
                $html = preg_replace('/\$\{RATE-BEFORE-AFTER\}/', $periods->where('type', null)->first()->rates->where('view', $view)->where('room', $room)->where('project', $project)->first()->rate, $html);
                $html = preg_replace('/\$\{RATE-PERSONAL-USAGE-DATES\}/', $periods->where('type', 'personal-usage')->first()->dfromWithoutYear . ' - ' . $periods->where('type', 'personal-usage')->first()->dtoWithoutYear, $html);
                $html = preg_replace('/\$\{RATE-PERSONAL-USAGE\}/', $periods->where('type', 'personal-usage')->first()->rates->where('view', $view)->where('room', $room)->where('project', $project)->first()->rate, $html);
                $html = preg_replace('/\$\{PERSONAL-USAGE-FROM\}/', $personal_dfrom1, $html);
                $html = preg_replace('/\$\{PERSONAL-USAGE-TO\}/', $personal_dto1, $html);
            }

            if ($flexiOverdue || $summer || ($flexi && $contract->duration == 1)) {
                $mmYears = $contract->mm_for_years ? explode(',', $contract->mm_for_years) : [$contract->mm_for_year];
                $html = preg_replace('/\$\{MM-YEAR-1\}/', $mmYears[0] ?? '-', $html);
                $html = preg_replace('/\$\{MM-YEAR-2\}/', implode(array_slice($mmYears, 1), ', '), $html);
                $html = preg_replace('/\$\{MM-YEAR-1-1-BG\}/', '', $html);
                $html = preg_replace('/\$\{MM-YEAR-1-1\}/', '', $html);
            } elseif ($flexi && $contract->duration > 1) {
                $html = preg_replace('/\$\{MM-YEAR-1-1-BG\}/', \Lang::get('contracts.mm-year-1-1', [], 'bg'), $html);
                $html = preg_replace('/\$\{MM-YEAR-1-1\}/', \Lang::get('contracts.mm-year-1-1', [], $locale), $html);
                $html = preg_replace('/\$\{MM-YEAR-1\}/', '', $html);
                $html = preg_replace('/\$\{MM-YEAR-2\}/', '', $html);
            }

            $html = preg_replace('/\$\{CONTRACT-DURATION\}/', $contract->duration, $html);
            $html = preg_replace('/\$\{CONTRACT-FROM\}/', (($flexiOverdue || $summer) ? $from->copy()->format('d.m.Y') : $from->copy()->startOfYear()->format('d.m.Y')), $html);
            $html = preg_replace('/\$\{CONTRACT-TO\}/', (($flexiOverdue || $summer) ? $to->copy()->addYear($contract->duration - 1)->format('d.m.Y') : $to->copy()->addYear($contract->duration - 1)->endOfYear()->format('d.m.Y')), $html);
        } else {
            $html = preg_replace('/\$\{RENT\}/', number_format((($contract->price + $mmFeeSum) * $contract->duration) + $contract->price_tc, 2), $html);

            if ($locale == 'bg') {
                $tag = '<td width="100%">';
            } else {
                $tag = '<td class="border-right" width="50%">';
            }

            // Custom furniture, All Years, All Options
            $html = preg_replace('/\$\{1.1-BG\}/', str_replace('\s', ' ', $isCustomFurniture ? \Lang::get('contracts.custom-1-1', [], 'bg') : \Lang::get('contracts.1-1', [], 'bg')), $html);
            $html = preg_replace('/\$\{1.1\}/', str_replace('\s', ' ', $isCustomFurniture ? \Lang::get('contracts.custom-1-1', [], $locale) : \Lang::get('contracts.1-1', [], $locale)), $html);
            $html = preg_replace('/\$\{1.3-BG\}/', str_replace('\s', ' ', $isCustomFurniture ? \Lang::get('contracts.custom-1-3', [], 'bg') : \Lang::get('contracts.1-3', [], 'bg')), $html);
            $html = preg_replace('/\$\{1.3\}/', str_replace('\s', ' ', $isCustomFurniture ? \Lang::get('contracts.custom-1-3', [], $locale) : \Lang::get('contracts.1-3', [], $locale)), $html);
            $html = preg_replace('/\$\{4.4-BG\}/', $isCustomFurniture ? \Lang::get('contracts.custom-4-4', [], 'bg') : \Lang::get('contracts.4-4', [], 'bg'), $html);
            $html = preg_replace('/\$\{4.4\}/', $isCustomFurniture ? \Lang::get('contracts.custom-4-4', [], $locale) : \Lang::get('contracts.4-4', [], $locale), $html);
            $html = preg_replace('/\$\{4.5-BG\}/', $isCustomFurniture ? \Lang::get('contracts.custom-4-5', [], 'bg') : \Lang::get('contracts.4-5', [], 'bg'), $html);
            $html = preg_replace('/\$\{4.5\}/', $isCustomFurniture ? \Lang::get('contracts.custom-4-5', [], $locale) : \Lang::get('contracts.4-5', [], $locale), $html);

            $text = '';
            $strings = explode('\n', trim($isCustomFurniture ? \Lang::get('contracts.custom-5-4-5-6', [], 'bg') : \Lang::get('contracts.5-4-5-6', [], 'bg'), '\n'));
            $strings_locale = explode('\n', trim($isCustomFurniture ? \Lang::get('contracts.custom-5-4-5-6', [], $locale) : \Lang::get('contracts.5-4-5-6', [], $locale), '\n'));
            for ($i = 0; $i < count($strings); $i++) {
                $text .= '<tr>' . $tag . $strings[$i] . '</td>';
                if ($locale != 'bg') {
                    $text .= '<td class="border-left" width="50%">' . $strings_locale[$i] . '</td>';
                }
                $text .= '</tr>';
            }

            $html = preg_replace('/\$\{5-4-5-6\}/', $text, $html);

            // All Years, All Options, MM Fees For Current Year
            $text = '<tr>' . $tag . \Lang::get('contracts.6-3', [], 'bg') . '</td>';
            if ($locale != 'bg') {
                $text .= '<td class="border-left" width="50%">' . \Lang::get('contracts.6-3', [], $locale) . '</td>';
            }
            $text .= '</tr>';

            $html = preg_replace('/\$\{6.3\}/', $isCurrentYear ? str_replace('\n', '', $text) : '', $html);

            // 1 Year: Option 1
            // 3 Years: All Options
            if ($contract->duration > 1 || ($contract->duration == 1 && $option == 'option1')) {
                $placeholderFrom = $from->copy()->startOfYear()->format('d.m.Y');
                $placeholderTo = $to->copy()->addYear($contract->duration - 1)->endOfYear()->format('d.m.Y');

                $placeholderYears = $contract->duration . ' ' . \Lang::choice('contracts.years', $contract->duration, [], 'bg');
                $html = preg_replace('/\$\{3.1-BG\}/', str_replace('\n', '', \Lang::get('contracts.1-3-1', ['period' => $placeholderYears, 'from' => $placeholderFrom, 'to' => $placeholderTo], 'bg')), $html);

                $placeholderYears = $contract->duration . ' ' . \Lang::choice('contracts.years', $contract->duration, [], $locale);
                $html = preg_replace('/\$\{3.1\}/', str_replace('\n', '', \Lang::get('contracts.1-3-1', ['period' => $placeholderYears, 'from' => $placeholderFrom, 'to' => $placeholderTo], $locale)), $html);
            } else { // 1 Year: Option 2 - Option 6
                $placeholderFrom = $from->copy()->format('d.m.Y');
                $placeholderTo = $to->copy()->format('d.m.Y');

                $html = preg_replace('/\$\{3.1-BG\}/', str_replace('\n', '', \Lang::get('contracts.2-3-1', ['from' => $placeholderFrom, 'to' => $placeholderTo], 'bg')), $html);
                $html = preg_replace('/\$\{3.1\}/', str_replace('\n', '', \Lang::get('contracts.2-3-1', ['from' => $placeholderFrom, 'to' => $placeholderTo], $locale)), $html);
            }

            if ($option == 'option1') { // All Years: Option 1
                $placeholderPeriod = \Lang::get('contracts.1-4-6-period2', ['from2' => $personal_dfrom2, 'to2' => $personal_dto2], 'bg');
                $text = '<tr>' . $tag . \Lang::get('contracts.1-4-6', ['from1' => $personal_dfrom1, 'to1' => $personal_dto1, 'period2' => $placeholderPeriod], 'bg') . '</td>';
                if ($locale != 'bg') {
                    $placeholderPeriod = \Lang::get('contracts.1-4-6-period2', ['from2' => $personal_dfrom2, 'to2' => $personal_dto2], $locale);
                    $text .= '<td class="border-left" width="50%">' . \Lang::get('contracts.1-4-6', ['from1' => $personal_dfrom1, 'to1' => $personal_dto1, 'period2' => $placeholderPeriod], $locale) . '</td>';
                }
                $text .= '</tr>';

                $html = preg_replace('/\$\{4.6\}/', str_replace('\n', '', $text), $html);

                if ($modifier == 'upgraded') {
                    $text = '<tr>' . $tag . \Lang::get('contracts.1-4-7', [], 'bg') . '</td>';
                    if ($locale != 'bg') {
                        $text .= '<td class="border-left" width="50%">' . \Lang::get('contracts.1-4-7', [], $locale) . '</td>';
                    }
                    $text .= '</tr>';

                    $html = preg_replace('/\$\{4.7\}/', str_replace('\n', '', $text), $html);
                } else {
                    $html = preg_replace('/\$\{4.7-BG\}/', '', $html);
                    $html = preg_replace('/\$\{4.7\}/', '', $html);
                }
            } elseif ($option == 'option2') { // All Years: Option 2
                if ($modifier == 'upgraded') {
                    $text = '<tr>' . $tag . \Lang::get('contracts.1-4-7', [], 'bg') . '</td>';
                    if ($locale != 'bg') {
                        $text .= '<td class="border-left" width="50%">' . \Lang::get('contracts.1-4-7', [], $locale) . '</td>';
                    }
                    $text .= '</tr>';

                    $html = preg_replace('/\$\{4.7\}/', str_replace('\n', '', $text), $html);
                } else {
                    $html = preg_replace('/\$\{4.7-BG\}/', '', $html);
                    $html = preg_replace('/\$\{4.7\}/', '', $html);
                }
            } elseif ($option == 'option5' || $option == 'option6') { // All Years: Option 5 - Option 6
                if ($covid) {
                    $text = '<tr>' . $tag . \Lang::get('contracts.2-4-6-covid', [], 'bg') . '</td>';
                    if ($locale != 'bg') {
                        $text .= '<td class="border-left" width="50%">' . \Lang::get('contracts.2-4-6-covid', [], $locale) . '</td>';
                    }
                    $text .= '</tr>';
                } else {
                    $placeholderDuration = $option == 'option5' ? 3 : 5;
                    $nf1 = new \NumberFormatter('bg', \NumberFormatter::SPELLOUT);
                    $nf2 = new \NumberFormatter($locale, \NumberFormatter::SPELLOUT);

                    $text = '<tr>' . $tag . \Lang::get('contracts.2-4-6', ['period' => $placeholderDuration . ' (' . $nf1->format($placeholderDuration) . ')'], 'bg') . '</td>';
                    if ($locale != 'bg') {
                        $text .= '<td class="border-left" width="50%">' . \Lang::get('contracts.2-4-6', ['period' => $placeholderDuration . ' (' . $nf2->format($placeholderDuration) . ')'], $locale) . '</td>';
                    }
                    $text .= '</tr>';
                }

                $html = preg_replace('/\$\{4.6\}/', str_replace('\n', '', $text), $html);

                $html = preg_replace('/\$\{4.7-BG\}/', '', $html);
                $html = preg_replace('/\$\{4.7\}/', '', $html);
            } else {
                $html = preg_replace('/\$\{4.6-BG\}/', '', $html);
                $html = preg_replace('/\$\{4.6\}/', '', $html);
                $html = preg_replace('/\$\{4.7-BG\}/', '', $html);
                $html = preg_replace('/\$\{4.7\}/', '', $html);
            }

            if ($contract->duration == 1) {
                if ($locale != 'bg') {
                    $html = preg_replace('/\$\{PAGE-BREAK2-2\}/', '', $html);
                    $html = preg_replace('/\$\{PAGE-BREAK2-1\}/', '${PAGE-BREAK2}', $html);
                }

                // All Years: Option 1 - Option 3
                if ($option == 'option1' || $option == 'option2' || $option == 'option3') {
                    $text = '<tr>' . $tag . '2.1.1. ' . \Lang::get('contracts.1-2-1', ['mmfee' => $mmFee, 'year' => $contract->mm_for_year], 'bg') . '</td>';
                    if ($locale != 'bg') {
                        $text .= '<td class="border-left" width="50%">2.1.1. ' . \Lang::get('contracts.1-2-1', ['mmfee' => $mmFee, 'year' => $contract->mm_for_year], $locale) . '</td>';
                    }
                    $text .= '</tr>';

                    $text .= '<tr>' . $tag . '2.1.2. ' . \Lang::get('contracts.1-2-2', ['year' => $year->year, 'deadline' => $rentalContract->deadline_at], 'bg') . '</td>';
                    if ($locale != 'bg') {
                        $text .= '<td class="border-left" width="50%">2.1.2. ' . \Lang::get('contracts.1-2-2', ['year' => $year->year, 'deadline' => $rentalContract->deadline_at], $locale) . '</td>';
                    }
                    $text .= '</tr>';

                    $text .= '<tr>' . $tag . '2.2. ' . \Lang::get('contracts.1-2-3', [], 'bg') . '</td>';
                    if ($locale != 'bg') {
                        $text .= '<td class="border-left" width="50%">2.2. ' . \Lang::get('contracts.1-2-3', [], $locale) . '</td>';
                    }
                    $text .= '</tr>';

                    $text .= '<tr>' . $tag . '2.3. ' . \Lang::get('contracts.1-2-4', [], 'bg') . '</td>';
                    if ($locale != 'bg') {
                        $text .= '<td class="border-left" width="50%">2.3. ' . \Lang::get('contracts.1-2-4', [], $locale) . '</td>';
                    }
                    $text .= '</tr>';

                    $html = preg_replace('/\$\{2\}/', str_replace('\n', '', $text), $html);
                } elseif ($option == 'option4' || $option == 'option5' || $option == 'option6') { // 1 Year: Option 4 - Option 6
                    $text = '<tr>' . $tag . '2.2. ' . \Lang::get('contracts.2-2-1', ['year' => $contract->mm_for_year], 'bg') . '</td>';
                    if ($locale != 'bg') {
                        $text .= '<td class="border-left" width="50%">2.2. ' . \Lang::get('contracts.2-2-1', ['year' => $contract->mm_for_year], $locale) . '</td>';
                    }
                    $text .= '</tr>';

                    $text .= '<tr>' . $tag . '2.3. ' . \Lang::get('contracts.2-2-2', [], 'bg') . '</td>';
                    if ($locale != 'bg') {
                        $text .= '<td class="border-left" width="50%">2.3. ' . \Lang::get('contracts.2-2-2', [], $locale) . '</td>';
                    }
                    $text .= '</tr>';

                    $html = preg_replace('/\$\{2\}/', str_replace('\n', '', $text), $html);
                }
            } elseif ($contract->duration > 1) {
                if ($locale != 'bg') {
                    $html = preg_replace('/\$\{PAGE-BREAK2-1\}/', '', $html);
                    $html = preg_replace('/\$\{PAGE-BREAK2-2\}/', '${PAGE-BREAK2}', $html);
                }

                // All Years: Option 1 - Option 3
                if ($option == 'option1' || $option == 'option2' || $option == 'option3') {
                    $placeholderFrom = $from->copy()->startOfYear();
                    $placeholderTo = $to->copy()->endOfYear();
                    $placeholderDeadline = Carbon::parse($rentalContract->deadline_at);
                    $text = '';

                    for ($i = 0; $i < $contract->duration; $i++) {
                        $j = (2 + $i);

                        $price_tc = 0;
                        if ($tc && $contract->price_tc) {
                            if ($contract->duration == 3) {
                                $price_tc = $amounts[$i];
                            } elseif ($contract->duration == 5 && $i >= 2) {
                                $price_tc = $amounts[$i - 2];
                            }
                        }

                        $text .= '<tr>' . $tag . '2.' . $j . '. ' . \Lang::get('contracts.3-2-1', ['from' => $placeholderFrom->format('d.m.Y'), 'to' => $placeholderTo->format('d.m.Y'), 'rent' => number_format($rent + $price_tc, 2)], 'bg') . '</td>';
                        if ($locale != 'bg') {
                            $text .= '<td class="border-left" width="50%">2.' . $j . '. ' . \Lang::get('contracts.3-2-1', ['from' => $placeholderFrom->format('d.m.Y'), 'to' => $placeholderTo->format('d.m.Y'), 'rent' => number_format($rent + $price_tc, 2)], $locale) . '</td>';
                        }
                        $text .= '</tr>';

                        $text .= '<tr>' . $tag . '2.' . $j . '.1. ' . \Lang::get('contracts.1-2-1', ['mmfee' => $mmFee, 'year' => ($contract->mm_for_year + $i)], 'bg') . '</td>';
                        if ($locale != 'bg') {
                            $text .= '<td class="border-left" width="50%">2.' . $j . '.1. ' . \Lang::get('contracts.1-2-1', ['mmfee' => $mmFee, 'year' => ($contract->mm_for_year + $i)], $locale) . '</td>';
                        }
                        $text .= '</tr>';

                        $text .= '<tr>' . $tag . '2.' . $j . '.2. ' . \Lang::get('contracts.1-2-2', ['year' => ($year->year + $i), 'deadline' => $placeholderDeadline->format('d.m.Y')], 'bg') . '</td>';
                        if ($locale != 'bg') {
                            $text .= '<td class="border-left" width="50%">2.' . $j . '.2. ' . \Lang::get('contracts.1-2-2', ['year' => ($year->year + $i), 'deadline' => $placeholderDeadline->format('d.m.Y')], $locale) . '</td>';
                        }
                        $text .= '</tr>';

                        $placeholderFrom->addYear();
                        $placeholderTo->addYear();
                        $placeholderDeadline->addYear();

                        if ($locale != 'bg' && $i == 1) {
                            $html = preg_replace('/\$\{PAGE-BREAK1\}/', '', $html);
                            $text .= '${PAGE-BREAK1}';
                        }
                    }

                    $text .= '<tr>' . $tag . '2.' . ++$j . '. ' . \Lang::get('contracts.1-2-3', [], 'bg') . '</td>';
                    if ($locale != 'bg') {
                        $text .= '<td class="border-left" width="50%">2.' . $j . '. ' . \Lang::get('contracts.1-2-3', [], $locale) . '</td>';
                    }
                    $text .= '</tr>';

                    $text .= '<tr>' . $tag . '2.' . ++$j . '. ' . \Lang::get('contracts.1-2-4', [], 'bg') . '</td>';
                    if ($locale != 'bg') {
                        $text .= '<td class="border-left" width="50%">2.' . $j . '. ' . \Lang::get('contracts.1-2-4', [], $locale) . '</td>';
                    }
                    $text .= '</tr>';

                    $html = preg_replace('/\$\{2\}/', str_replace('\n', '', $text), $html);
                } else { // 3 Years: Option 4 - Option 6
                    $placeholderFrom = $from->copy()->startOfYear();
                    $placeholderTo = $to->copy()->endOfYear();
                    $text = '';

                    for ($i = 0; $i < $contract->duration; $i++) {
                        $j = (2 + $i);

                        $price_tc = 0;
                        if ($tc && $contract->price_tc) {
                            if ($contract->duration == 3) {
                                $price_tc = $amounts[$i];
                            } elseif ($contract->duration == 5 && $i >= 2) {
                                $price_tc = $amounts[$i - 2];
                            }
                        }

                        $text .= '<tr>' . $tag . '2.' . $j . '. ' . \Lang::get('contracts.3-2-1', ['from' => $placeholderFrom->format('d.m.Y'), 'to' => $placeholderTo->format('d.m.Y'), 'rent' => number_format($rent + $price_tc, 2)], 'bg') . ' ' . \Lang::get('contracts.4-2-1', ['year' => ($contract->mm_for_year + $i)], 'bg') . '</td>';
                        if ($locale != 'bg') {
                            $text .= '<td class="border-left" width="50%">2.' . $j . '. ' . \Lang::get('contracts.3-2-1', ['from' => $placeholderFrom->format('d.m.Y'), 'to' => $placeholderTo->format('d.m.Y'), 'rent' => number_format($rent + $price_tc, 2)], $locale) . ' ' . \Lang::get('contracts.4-2-1', ['year' => ($contract->mm_for_year + $i)], $locale) . '</td>';
                        }
                        $text .= '</tr>';

                        $placeholderFrom->addYear();
                        $placeholderTo->addYear();
                    }

                    $text .= '<tr>' . $tag . '2.' . ++$j . '. ' . \Lang::get('contracts.1-2-3', [], 'bg') . '</td>';
                    if ($locale != 'bg') {
                        $text .= '<td class="border-left" width="50%">2.' . $j . '. ' . \Lang::get('contracts.1-2-3', [], $locale) . '</td>';
                    }
                    $text .= '</tr>';

                    $html = preg_replace('/\$\{2\}/', str_replace('\n', '', $text), $html);
                }

                // 3 Years: Option 2 - Option 6
                if ($option == 'option2' || $option == 'option3' || $option == 'option4' || $option == 'option5' || $option == 'option6') {
                    // current locale is: setlocale(LC_ALL, 0);
                    setlocale(LC_TIME, 'bg'); // change locale to BG
                    $placeholderDateFrom = \App\Helpers\displayWindowsDate($from->copy()->formatLocalized('%d %B'));
                    $placeholderDateTo = \App\Helpers\displayWindowsDate($to->copy()->formatLocalized('%d %B'));
                    $text = '<tr>' . $tag . \Lang::get('contracts.3-2', ['from' => $placeholderDateFrom, 'to' => $placeholderDateTo], 'bg') . '</td>';
                    if ($locale != 'bg') {
                        setlocale(LC_TIME, $locale); // restore locale
                        $placeholderDateFrom = \App\Helpers\displayWindowsDate($from->copy()->formatLocalized('%d %B'));
                        $placeholderDateTo = \App\Helpers\displayWindowsDate($to->copy()->formatLocalized('%d %B'));
                        $text .= '<td class="border-left" width="50%">' . \Lang::get('contracts.3-2', ['from' => $placeholderDateFrom, 'to' => $placeholderDateTo], $locale) . '</td>';
                    }
                    $text .= '</tr>';

                    $html = preg_replace('/\$\{3.2\}/', str_replace('\n', '', $text), $html);
                }
            }

            // reset 3.2
            $html = preg_replace('/\$\{3.2\}/', '', $html);
        }

        $placeholderSignatures = \Lang::get('contracts.signatures', [], $locale);
        $placeholderTenant = \Lang::get('contracts.tenant', [], $locale);
        $placeholderLandlord = \Lang::get('contracts.landlord', [], $locale);
        $placeholderPageBG = \Lang::get('contracts.page', [], 'bg');
        $placeholderPage = \Lang::get('contracts.page', [], $locale);

        if ($locale == 'bg') {
            $pageBreak = '
        <tr>
            <td width="100%">&nbsp;</td>
        </tr>
    </tbody>
</table>
<table class="break" cellspacing="0" cellpadding="0" width="100%">
    <tbody>
        <tr>
            <td colspan="2" class="padding text-center" width="100%"><strong class="border-bottom">' . $placeholderSignatures . '</strong></td>
        </tr>
        <tr>
            <td class="padding text-center" width="50%">----------------------------------------------------------<br>' . $placeholderTenant . '</td>
            <td class="padding text-center" width="50%">----------------------------------------------------------<br>' . $placeholderLandlord . '</td>
        </tr>
        <tr>
            <td colspan="2" class="text-center font-size-8" width="100%">' . $placeholderPageBG . '</td>
        </tr>
    </tbody>
</table>
<table class="border" cellspacing="0" cellpadding="0" width="100%">
    <tbody>';

            $pageBreakLast = '
        <tr>
            <td width="100%">&nbsp;</td>
        </tr>
    </tbody>
</table>
<table cellspacing="0" cellpadding="0" width="100%">
    <tbody>
        <tr>
            <td colspan="2" class="padding text-center" width="100%"><strong class="border-bottom">' . $placeholderSignatures . '</strong></td>
        </tr>
        <tr>
            <td class="padding text-center" width="50%">----------------------------------------------------------<br>' . $placeholderTenant . '</td>
            <td class="padding text-center" width="50%">----------------------------------------------------------<br>' . $placeholderLandlord . '</td>
        </tr>
        <tr>
            <td colspan="2" class="text-center font-size-8" width="100%">' . $placeholderPageBG . '</td>
        </tr>';
        } else {
            $pageBreak = '
        <tr>
            <td class="border-right" width="50%">&nbsp;</td>
            <td class="border-left" width="50%">&nbsp;</td>
        </tr>
    </tbody>
</table>
<table class="break" cellspacing="0" cellpadding="0" width="100%">
    <tbody>
        <tr>
            <td colspan="2" class="padding text-center" width="100%"><strong class="border-bottom">' . $placeholderSignatures . '</strong></td>
        </tr>
        <tr>
            <td class="padding text-center" width="50%">----------------------------------------------------------<br>' . $placeholderTenant . '</td>
            <td class="padding text-center" width="50%">----------------------------------------------------------<br>' . $placeholderLandlord . '</td>
        </tr>
        <tr>
            <td class="text-center font-size-8" width="50%">' . $placeholderPageBG . '</td>
            <td class="text-center font-size-8" width="50%">' . $placeholderPage . '</td>
        </tr>
    </tbody>
</table>
<table class="border" cellspacing="0" cellpadding="0" width="100%">
    <tbody>';

            $pageBreakLast = '
        <tr>
            <td class="border-right" width="50%">&nbsp;</td>
            <td class="border-left" width="50%">&nbsp;</td>
        </tr>
    </tbody>
</table>
<table cellspacing="0" cellpadding="0" width="100%">
    <tbody>
        <tr>
            <td colspan="2" class="padding text-center" width="100%"><strong class="border-bottom">' . $placeholderSignatures . '</strong></td>
        </tr>
        <tr>
            <td class="padding text-center" width="50%">----------------------------------------------------------<br>' . $placeholderTenant . '</td>
            <td class="padding text-center" width="50%">----------------------------------------------------------<br>' . $placeholderLandlord . '</td>
        </tr>
        <tr>
            <td class="text-center font-size-8" width="50%">' . $placeholderPageBG . '</td>
            <td class="text-center font-size-8" width="50%">' . $placeholderPage . '</td>
        </tr>';
        }

        if ($flexi || $flexiOverdue || $summer) {

        } else {
            if (!$isCustomFurniture) {
                $html = preg_replace('/\$\{PAGE-BREAK3\}/', '', $html);
                $html = preg_replace('/\$\{PAGE-BREAK4\}/', '${PAGE-BREAK3}', $html);
            }

            for ($i = 1, $n = ($locale == 'bg' ? 3 : ($isCustomFurniture ? 4 : 3)); $i <= $n; $i++) {
                $html = preg_replace('/\$\{PAGE-BREAK' . $i . '\}/', str_replace(':pagefrom', $i, str_replace(':pageto', $n, ($i == $n ? $pageBreakLast : $pageBreak))), $html);
            }
        }

        $dompdf->load_html($html);
        $dompdf->render();
        return $dompdf->output();
    }

    public function getContracts($apartment = null)
    {
        $apartment = Apartment::find($apartment);
        if ($apartment) {
            $owners = $this->getOwners($apartment);
            $contracts = $this->getRentalContracts($apartment);

            return response()->json([
                'success' => true,
                'owners' => array_merge([trans(\Locales::getNamespace() . '/forms.selectOption') => ''], $owners->pluck('id', 'name')->toArray()),
                'contracts' => array_merge([trans(\Locales::getNamespace() . '/forms.selectOption') => ''], $contracts->pluck('id', 'name')->toArray()),
            ]);
        } else {
            return response()->json([
                'success' => true,
            ]);
        }
    }

    public function getProxies($apartment = null, $owner = null, $proxy = null, $from = null, $to = null)
    {
        if (gettype($apartment) != 'object') {
            $apartment = Apartment::findOrFail($apartment);
        }

        Poa::where('to', '<', Carbon::now()->year)->delete();

        $exclude1 = Poa::select('poa.proxy_id') // exclude proxies with more than 2 POAs already
            ->leftJoin('apartments', 'apartments.id', '=', 'poa.apartment_id')
            ->where('apartments.building_id', $apartment->building_id)
            ->where('poa.owner_id', '!=', $owner) // Exclude the selected owner as the owner can give multiple poas to the same proxy for different apartments they own
            // ->where('poa.is_active', 1)
            ->when($proxy, function ($query) use ($proxy) { // exclude the currently edited item
                return $query->where('poa.proxy_id', '!=', $proxy);
            })
            ->when($from && $to, function ($query) use ($from, $to) {
                return $query->where('poa.to', '>=', $from)->where('poa.from', '<=', $to);
            })
            ->groupBy('poa.proxy_id');

        if ($apartment->building_id == 3) { // Sigma
            $exclude1 = $exclude1->havingRaw('COUNT(DISTINCT poa.owner_id) > 5');
        } else {
            $exclude1 = $exclude1->havingRaw('COUNT(DISTINCT poa.owner_id) > 2');
        }

        $exclude1 = $exclude1->get()->pluck('proxy_id');

        $exclude2 = Poa::select('poa.proxy_id') // exclude proxy for the same apartment, owner and years
            ->leftJoin('apartments', 'apartments.id', '=', 'poa.apartment_id')
            ->where('apartments.building_id', $apartment->building_id)
            ->where('poa.apartment_id', '=', $apartment->id)
            ->where('poa.owner_id', '=', $owner)
            // ->where('poa.is_active', 1)
            ->when($proxy, function ($query) use ($proxy) { // exclude the currently edited item
                return $query->where('poa.proxy_id', '!=', $proxy);
            })
            ->when($from && $to, function ($query) use ($from, $to) {
                return $query->where('poa.to', '>=', $from)->where('poa.from', '<=', $to);
            })
            ->value('proxy_id');

        $exclude3 = Poa::select('poa.proxy_id') // exclude proxy for the same apartment with POAs given to other co-owners
            ->leftJoin('apartments', 'apartments.id', '=', 'poa.apartment_id')
            ->where('apartments.building_id', $apartment->building_id)
            ->where('poa.apartment_id', '=', $apartment->id)
            ->where('poa.owner_id', '!=', $owner)
            // ->where('poa.is_active', 1)
            ->when($proxy, function ($query) use ($proxy) { // exclude the currently edited item
                return $query->where('poa.proxy_id', '!=', $proxy);
            })
            ->when($from && $to, function ($query) use ($from, $to) {
                return $query->where('poa.to', '>=', $from)->where('poa.from', '<=', $to);
            })
            ->value('proxy_id');

        $exclude = $exclude1->filter()->merge($exclude2)->merge($exclude3);

        return Proxy::withTranslation()
            ->distinct()
            ->select('proxy_translations.name', 'proxies.id')
            ->leftJoin('proxy_translations', 'proxy_translations.proxy_id', '=', 'proxies.id')
            ->leftJoin('poa', function ($join) use ($from, $to, $owner) { // if there is already a poa from the same owner - prefer the same proxy
                $join->on('poa.proxy_id', '=', 'proxies.id')
                    ->whereNull('poa.deleted_at')
                    ->where('poa.owner_id', '=', $owner);

                    if ($from && $to) {
                        $join->where('poa.to', '>=', $from)->where('poa.from', '<=', $to);
                    }
            })
            ->where('proxy_translations.locale', \Locales::getCurrent())
            ->whereNotIn('proxies.id', $exclude)
            ->orderBy('poa.proxy_id', 'desc')
            ->orderBy('proxy_translations.name')
            ->get();
    }

    public function getMMYears($apartment = null)
    {
        if (gettype($apartment) != 'object') {
            $apartment = Apartment::with(['buildingMM', 'mmFeesPayments', 'room'])->findOrFail($apartment);
        }

        $years = [];

        foreach (Year::orderBy('year', 'desc')->get() as $year) {
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
            } else {
                $balance = 0;
            }

            if ($balance) {
               $years[$year->year] = $year->year;
            }
        }

        return $years;
    }

    public function getRentalContracts($apartment = null, $rct = null)
    {
        if (gettype($apartment) != 'object') {
            $apartment = Apartment::findOrFail($apartment);
        }

        return RentalContract::withTranslation()
            ->select('rental_contract_translations.name', 'rental_contracts.id')
            ->leftJoin('rental_contract_translations', 'rental_contract_translations.rental_contract_id', '=', 'rental_contracts.id')
            ->where('rental_contract_translations.locale', \Locales::getCurrent())
            ->whereNotExists(function ($query) use ($apartment) {
                $query->from('contracts')
                    ->whereRaw('contracts.rental_contract_id = rental_contracts.id')
                    ->where('contracts.apartment_id', $apartment->id)
                    ->whereNull('contracts.deleted_at');
            })
            ->whereNotExists(function ($query) use ($apartment, $rct) {
                $query->from('rental_contracts_tracker')
                    ->whereRaw('rental_contracts_tracker.rental_contract_id = rental_contracts.id')
                    ->where('rental_contracts_tracker.apartment_id', $apartment->id)
                    ->when($rct, function($query) use ($rct) {
                        return $query->where('rental_contracts_tracker.id', '!=', $rct);
                    });
            })
            ->orderBy('rental_contract_translations.name')
            ->get();
    }

    public function getOwners($apartment = null)
    {
        if (gettype($apartment) != 'object') {
            $apartment = Apartment::findOrFail($apartment);
        }

        return Owner::selectRaw('owners.id, CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as name')
            ->leftJoin('ownership', 'ownership.owner_id', '=', 'owners.id')
            ->where('ownership.apartment_id', $apartment->id)
            ->whereNull('ownership.deleted_at')
            ->orderBy('owners.first_name')
            ->get();
    }

    public function getContractData($apartment = null, $owner = null, $contract = null)
    {
        $apartment = Apartment::findOrFail($apartment);
        $owner = Owner::findOrFail($owner);
        $rentalContract = RentalContract::find($contract);

        if ($rentalContract) {
            if ($rentalContract->contract_dfrom2) {
                $from = min(Carbon::parse($rentalContract->contract_dfrom1), Carbon::parse($rentalContract->contract_dfrom2))->year;
                $to = max(Carbon::parse($rentalContract->contract_dto1), Carbon::parse($rentalContract->contract_dto2))->year;
            } else {
                $from = Carbon::parse($rentalContract->contract_dfrom1)->year;
                $to = Carbon::parse($rentalContract->contract_dto1)->year;
            }

            $year = $this->getStartYear($rentalContract->contract_dfrom1, $rentalContract->contract_dfrom2);

            $price = null;
            if ($rentalContract->rental_payment_id) {
                $price = RentalPaymentPrices::where('rental_payment_id', $rentalContract->rental_payment_id)->where('rental_payment_prices.room_id', $apartment->room_id)->where('rental_payment_prices.furniture_id', $apartment->furniture_id)->where('rental_payment_prices.view_id', $apartment->view_id)->value('price');
            }

            $proxies = $this->getProxies($apartment, $owner->id, null, $from, $to);
            $years = $this->getMMYears($apartment);

            return response()->json([
                'success' => true,
                'flexiOverdue' => str_contains($rentalContract->name, 'Flexi Overdue') ? true : false,
                'tc' => starts_with($rentalContract->name, 'Thomas Cook') ? true : false,
                'covid' => str_contains($rentalContract->name, 'COVID') ? true : false,
                'proxies' => array_merge([trans(\Locales::getNamespace() . '/forms.selectOption') => ''], $proxies->pluck('id', 'name')->toArray()),
                'from' => $from,
                'years' => $years,
                'to' => $to,
                'year' => $year,
                'price' => $price ?: 0,
                'minDuration' => $rentalContract->min_duration,
                'maxDuration' => $rentalContract->max_duration,
                'maxDate' => Carbon::now()->year($year)->endOfYear()->addYear()->format('d.m.Y'),
                'contract_dfrom1' => $rentalContract->contract_dfrom1 ? Carbon::parse($rentalContract->contract_dfrom1)->toAtomString() : '',
                'contract_dto1' => $rentalContract->contract_dto1 ? Carbon::parse($rentalContract->contract_dto1)->toAtomString() : '',
                'contract_dfrom2' => $rentalContract->contract_dfrom2 ? Carbon::parse($rentalContract->contract_dfrom2)->toAtomString() : '',
                'contract_dto2' => $rentalContract->contract_dto2 ? Carbon::parse($rentalContract->contract_dto2)->toAtomString() : '',
                'personal_dfrom1' => $rentalContract->personal_dfrom1 ? Carbon::parse($rentalContract->personal_dfrom1)->toAtomString() : '',
                'personal_dto1' => $rentalContract->personal_dto1 ? Carbon::parse($rentalContract->personal_dto1)->toAtomString() : '',
                'personal_dfrom2' => $rentalContract->personal_dfrom2 ? Carbon::parse($rentalContract->personal_dfrom2)->toAtomString() : '',
                'personal_dto2' => $rentalContract->personal_dto2 ? Carbon::parse($rentalContract->personal_dto2)->toAtomString() : '',
            ]);
        } else {
            return response()->json([
                'success' => true,
            ]);
        }
    }

    public function getStartYear($contract_dfrom1, $contract_dfrom2)
    {
        if ($contract_dfrom2) {
            $year = (Carbon::parse($contract_dfrom1) > Carbon::parse($contract_dfrom2)) ? Carbon::parse($contract_dfrom2)->year : Carbon::parse($contract_dfrom1)->year;
        } else {
            $year = Carbon::parse($contract_dfrom1)->year;
        }

        return $year;
    }

    public function changeStatus($id, $status)
    {
        $rct = RentalContractTracker::findOrFail($id);

        $rct->is_active = $status;
        $rct->save();

        $href = '';
        $img = '';
        foreach ($this->datatables[$this->route]['columns'] as $column) {
            if ($column['id'] == 'is_active') {
                foreach ($column['status']['rules'] as $key => $value) {
                    if ($key == $status) {
                        $href = \Locales::route($column['status']['route'], [$id, $value['status']]);
                        $img = \HTML::image(\App\Helpers\autover('/img/' . \Locales::getNamespace() . '/' . $value['icon']), $value['title']);
                        break 2;
                    }
                }
            }
        }

        return response()->json(['success' => true, 'href' => $href, 'img' => $img]);
    }
}
