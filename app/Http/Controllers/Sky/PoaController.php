<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Models\Sky\Poa;
use App\Models\Sky\Year;
use App\Models\Sky\Apartment;
use App\Models\Sky\NewsletterTemplates;
use App\Models\Sky\Owner;
use App\Models\Sky\Proxy;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Http\Requests\Sky\PoaRequest;
use App\Services\Newsletter as NewsletterService;
use Mail;
use File;
use Carbon\Carbon;
use Dompdf\Dompdf;

class PoaController extends Controller {

    protected $route = 'poa';
    protected $datatables;
    protected $multiselect;

    public function __construct()
    {
        $this->datatables = [
            $this->route => [
                'title' => trans(\Locales::getNamespace() . '/datatables.titlePoa'),
                'url' => \Locales::route($this->route),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'columns' => [
                    [
                        'selector' => $this->route . '.id',
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
                            'foreignColumn' => $this->route . '.apartment_id',
                        ],
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
                        'join' => [
                            'table' => 'owners',
                            'localColumn' => 'owners.id',
                            'constrain' => '=',
                            'foreignColumn' => $this->route . '.owner_id',
                        ],
                    ],
                    [
                        'selector' => 'proxy_translations.name as proxy',
                        'id' => 'proxy',
                        'name' => trans(\Locales::getNamespace() . '/datatables.proxy'),
                        'search' => true,
                        'class' => 'vertical-center',
                        'join' => [
                            'table' => 'proxy_translations',
                            'localColumn' => 'proxy_translations.proxy_id',
                            'constrain' => '=',
                            'foreignColumn' => $this->route . '.proxy_id',
                            'whereColumn' => 'proxy_translations.locale',
                            'whereConstrain' => '=',
                            'whereValue' => \Locales::getCurrent(),
                        ],
                    ],
                    [
                        'selector' => $this->route . '.from',
                        'id' => 'from',
                        'name' => trans(\Locales::getNamespace() . '/datatables.from'),
                        'class' => 'text-center vertical-center',
                        'search' => true,
                    ],
                    [
                        'selector' => $this->route . '.to',
                        'id' => 'to',
                        'name' => trans(\Locales::getNamespace() . '/datatables.to'),
                        'class' => 'text-center vertical-center',
                        'search' => true,
                    ],
                    [
                        'selector' => 'poa.sent_at',
                        'id' => 'sent_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.dateSent'),
                        'class' => 'text-center vertical-center sent-at',
                        'search' => true,
                        'date' => [
                            'format' => '%d.%m.%Y',
                        ],
                    ],
                    [
                        'selector' => $this->route . '.is_active',
                        'id' => 'is_active',
                        'name' => trans(\Locales::getNamespace() . '/datatables.status'),
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
                'orderByColumn' => 1,
                'orderByRaw' => 'proxy',
                'order' => 'asc',
                'buttons' => [
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
            'owners' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'proxies' => [
                'id' => 'id',
                'name' => 'name',
            ],
        ];
    }

    public function index(DataTable $datatable, Poa $poa, Request $request)
    {
        $datatable->setup($poa, $this->route, $this->datatables[$this->route]);
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

        $this->multiselect['apartments']['options'] = array_merge([['id' => '', 'number' => trans(\Locales::getNamespace() . '/forms.selectOption')]], Apartment::distinct()->select('apartments.id', 'apartments.number')->join('ownership', 'ownership.apartment_id', '=', 'apartments.id')->whereNull('ownership.deleted_at')/*->whereNotExists(function ($query) {
            $query->from('poa')->whereRaw('poa.apartment_id = apartments.id')->whereNull('poa.deleted_at');
        })*/->orderBy('number')->get()->toArray());
        $this->multiselect['apartments']['selected'] = '';

        $this->multiselect['owners']['options'] = [];
        $this->multiselect['owners']['selected'] = '';

        $this->multiselect['proxies']['options'] = [];
        $this->multiselect['proxies']['selected'] = '';

        $multiselect = $this->multiselect;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('table', 'multiselect'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function store(DataTable $datatable, PoaRequest $request)
    {
        $apartment = Apartment::findOrFail($request->input('apartment_id'));

        $poa = Poa::create([
            'from' => $request->input('from'),
            'to' => $request->input('to'),
            'apartment_id' => $apartment->id,
            'owner_id' => $request->input('owner_id'), // $apartment->ownership->first()->owner_id,
            'proxy_id' => $request->input('proxy_id'),
        ]);

        if ($poa->id) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.storedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityPoa', 1)]);

            $datatable->setup($poa, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.createError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityPoa', 1)]);
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

    public function destroy(DataTable $datatable, Poa $poa, Request $request)
    {
        $count = count($request->input('id'));

        if ($count > 0 && $poa->destroy($request->input('id'))) {
            $datatable->setup($poa, $request->input('table'), $this->datatables[$request->input('table')], true);
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
        $poa = Poa::findOrFail($id);

        $table = $this->route;
        if ($request->input('table')) { // magnific popup request
            $table = $request->input('table');
        }

        $this->multiselect['apartments']['options'] = [
            [
                'id' => $poa->apartment_id,
                'number' => $poa->apartment->number,
            ],
        ];
        $this->multiselect['apartments']['selected'] = $poa->apartment_id;

        $this->multiselect['owners']['options'] = $this->getOwners($poa->apartment_id, $poa->id, false)->toArray();
        $this->multiselect['owners']['selected'] = $poa->owner_id;

        $this->multiselect['proxies']['options'] = $this->getProxies($request, $poa->owner_id, $poa->apartment_id, $poa->id, $poa->proxy_id, false)->toArray();
        $this->multiselect['proxies']['selected'] = $poa->proxy_id;

        $multiselect = $this->multiselect;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('poa', 'table', 'multiselect'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, PoaRequest $request)
    {
        $poa = Poa::findOrFail($request->input('id'))->first();

        if ($poa->update($request->all())) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityPoa', 1)]);

            $datatable->setup($poa, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityPoa', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function getOwners($apartment = null, $poa = null, $json = true)
    {
        if ($apartment) {
            if (gettype($apartment) != 'object') {
                $apartment = Apartment::findOrFail($apartment);
            }

            $owners = Owner::selectRaw('owners.id, CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as name')
                ->leftJoin('ownership', 'ownership.owner_id', '=', 'owners.id')
                ->where('ownership.apartment_id', $apartment->id)
                ->whereNull('ownership.deleted_at')
                ->orderBy('owners.first_name')
                ->get();

            if ($json) {
                return response()->json([
                    'success' => true,
                    'owners' => $poa ? array_merge([trans(\Locales::getNamespace() . '/forms.selectOption') => ''], $owners->pluck('id', 'name')->toArray()) : $owners->pluck('id', 'name')->toArray(),
                ]);
            } else {
                return $owners;
            }
        } else {
            if ($json) {
                return response()->json([
                    'success' => true,
                ]);
            } else {
                return [];
            }
        }
    }

    public function getProxies(Request $request, $owner = null, $apartment = null, $poa = null, $proxy = null, $json = true)
    {
        if ($apartment && $owner) {
            $apartment = Apartment::findOrFail($apartment);

            Poa::where('to', '<', Carbon::now()->year)->delete();

            if ($request->input('from') && $request->input('to')) {
                $from = $request->input('from');
                $to = $request->input('to');
            } else {
                if ($poa) {
                    $poa = Poa::findOrFail($poa);
                }

                if ($poa && $poa->rentalContract) {
                    if ($poa->rentalContract->contract_dfrom2) {
                        $min = min(Carbon::parse($poa->rentalContract->contract_dfrom1), Carbon::parse($poa->rentalContract->contract_dfrom2));
                        $max = max(Carbon::parse($poa->rentalContract->contract_dto1), Carbon::parse($poa->rentalContract->contract_dto2));
                    } else {
                        $min = Carbon::parse($poa->rentalContract->contract_dfrom1);
                        $max = Carbon::parse($poa->rentalContract->contract_dto1);
                    }

                    $from = $min;
                    $to = $max;
                    $duration = $poa->rentalContract->duration;
                } else {
                    if ($apartment->contracts->count()) {
                        foreach ($apartment->contracts as $contract) {
                            $contractYear = $contract->contractYears()->withTrashed()->orderBy('year')->first();
                            if ($contractYear->contract_dfrom2) {
                                $min = min(Carbon::parse($contractYear->contract_dfrom1), Carbon::parse($contractYear->contract_dfrom2));
                                $max = max(Carbon::parse($contractYear->contract_dto1), Carbon::parse($contractYear->contract_dto2));
                            } else {
                                $min = Carbon::parse($contractYear->contract_dfrom1);
                                $max = Carbon::parse($contractYear->contract_dto1);
                            }

                            if (!isset($from) || $min < $from) {
                                $from = $min;
                            }

                            if (!isset($to) || $max > $to) {
                                $to = $max;
                                $duration = $contract->duration;
                            }
                        }
                    } else {
                        $from = Carbon::now();
                        $to = Carbon::now();
                        $duration = 1;
                    }
                }

                $from = $from->year;
                $to = $to->addYear($duration - 1)->year;
            }

            $exclude1 = Poa::select('poa.proxy_id') // exclude proxies with more than 2 POAs already
                ->leftJoin('apartments', 'apartments.id', '=', 'poa.apartment_id')
                ->where('apartments.building_id', $apartment->building_id)
                ->where('poa.owner_id', '!=', $owner) // Exclude the selected owner as the owner can give multiple poas to the same proxy for different apartments they own
                ->where('poa.is_active', 1)
                ->when($proxy, function ($query) use ($proxy) { // exclude the currently edited item
                    return $query->where('poa.proxy_id', '!=', $proxy);
                })
                ->when($from && $to, function ($query) use ($from, $to) {
                    return $query->where('poa.to', '>=', $from)->where('poa.from', '<=', $to);
                })
                ->groupBy('poa.proxy_id')
                ->havingRaw('COUNT(DISTINCT poa.owner_id) > 2')
                ->get()
                ->pluck('proxy_id');

            $exclude2 = Poa::select('poa.proxy_id') // exclude proxy for the same apartment, owner and years
                ->leftJoin('apartments', 'apartments.id', '=', 'poa.apartment_id')
                ->where('apartments.building_id', $apartment->building_id)
                ->where('poa.apartment_id', '=', $apartment->id)
                ->where('poa.owner_id', '=', $owner)
                ->where('poa.is_active', 1)
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
                ->where('poa.is_active', 1)
                ->when($proxy, function ($query) use ($proxy) { // exclude the currently edited item
                    return $query->where('poa.proxy_id', '!=', $proxy);
                })
                ->when($from && $to, function ($query) use ($from, $to) {
                    return $query->where('poa.to', '>=', $from)->where('poa.from', '<=', $to);
                })
                ->value('proxy_id');

            $exclude = $exclude1->filter()->merge($exclude2)->merge($exclude3);

            $proxies = Proxy::withTranslation()
                ->distinct()
                ->select('proxy_translations.name', 'proxies.id')
                ->leftJoin('proxy_translations', 'proxy_translations.proxy_id', '=', 'proxies.id')
                ->leftJoin('poa', function ($join) use ($from, $to, $owner) { // if there is already a poa from the same owner - prefer the same proxy
                    $join->on('poa.proxy_id', '=', 'proxies.id')
                        ->whereNull('poa.deleted_at')
                        ->where('poa.owner_id', '=', $owner)
                        ->where('poa.is_active', '=', 1);

                        if ($from && $to) {
                            $join->where('poa.to', '>=', $from)->where('poa.from', '<=', $to);
                        }
                })
                ->where('proxy_translations.locale', \Locales::getCurrent())
                ->whereNotIn('proxies.id', $exclude)
                ->orderBy('poa.proxy_id', 'desc')
                ->orderBy('proxy_translations.name')
                ->get();

            if ($json) {
                return response()->json([
                    'success' => true,
                    'from' => $from,
                    'to' => $to,
                    'proxies' => $poa ? array_merge([trans(\Locales::getNamespace() . '/forms.selectOption') => ''], $proxies->pluck('id', 'name')->toArray()) : $proxies->pluck('id', 'name')->toArray(),
                ]);
            } else {
                return $proxies;
            }
        } else {
            if ($json) {
                return response()->json([
                    'success' => true,
                ]);
            } else {
                return [];
            }
        }
    }

    public function changeStatus($id, $status)
    {
        $poa = Poa::findOrFail($id);

        $poa->is_active = $status;
        $poa->save();

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

    public function print(Request $request, $id)
    {
        $poa = Poa::findOrFail($id);
        $year = Year::where('year', $poa->from)->firstOrFail();
        $company = $year->rentalCompanies->first();
        $proxy = $poa->proxy;
        $apartment = $poa->apartment;
        $building = $apartment->building;
        $owner = $poa->owner;
        $locale = $owner->locale->locale;
        $mm = $apartment->buildingMM()->where('year_id', $year->id)->first();

        \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);

        $templateProcessor = new \App\Extensions\PhpOffice\PhpWord\CustomTemplateProcessor(storage_path('app/templates/poa-' . $locale . '.docx'));

        $templateProcessor->setValue('DATE', Carbon::now('Europe/Sofia')->format('d.m.Y'));
        $templateProcessor->setValue('OWNER-NAME', $owner->full_name);
        $templateProcessor->setValue('OWNER-ADDRESS', $owner->full_address);
        $templateProcessor->setValue('APARTMENT', $apartment->number);
        $templateProcessor->setValue('BUILDINGNAME-BG', $building->translate('bg')->name);
        $templateProcessor->setValue('BUILDINGNAME', $building->translate($locale)->name);
        $templateProcessor->setValue('BUILDINGDESCRIPTION-BG', $building->translate('bg')->description);
        $templateProcessor->setValue('BUILDINGDESCRIPTION', $building->translate($locale)->description);
        $templateProcessor->setValue('PROXYNAME-BG', $proxy->translate('bg')->name);
        $templateProcessor->setValue('PROXYNAME', $proxy->translate($locale)->name);

        if ($proxy->is_company) {
            $templateProcessor->setValue('PROXYDETAILS-BG', \Lang::get('contracts.proxy-company', ['bulstat' => $proxy->bulstat, 'address' => $company->translate('bg')->address], 'bg'));
            $templateProcessor->setValue('PROXYDETAILS', \Lang::get('contracts.proxy-company', ['bulstat' => $proxy->bulstat, 'address' => $company->translate($locale)->address], $locale));
        } else {
            $templateProcessor->setValue('PROXYDETAILS-BG', \Lang::get('contracts.proxy-person', ['egn' => $proxy->egn, 'passport' => $proxy->id_card, 'issuedat' => $proxy->issued_at, 'issuedby' => $proxy->translate('bg')->issued_by, 'address' => $company->translate('bg')->address], 'bg'));
            $templateProcessor->setValue('PROXYDETAILS', \Lang::get('contracts.proxy-person', ['egn' => $proxy->egn, 'passport' => $proxy->id_card, 'issuedat' => $proxy->issued_at, 'issuedby' => $proxy->translate($locale)->issued_by, 'address' => $company->translate($locale)->address], $locale));
        }

        $nf = new \NumberFormatter('bg', \NumberFormatter::SPELLOUT);
        $templateProcessor->setValue('MMFEE-BG', number_format($mm->mm_tax) . ' (' . $nf->format($mm->mm_tax) . ')');
        $nf = new \NumberFormatter($locale, \NumberFormatter::SPELLOUT);
        $templateProcessor->setValue('MMFEE', number_format($mm->mm_tax) . ' (' . $nf->format($mm->mm_tax) . ')');

        $templateProcessor->setValue('EXPIRY', Carbon::createFromDate($poa->to)->endOfYear()->format('d.m.Y'));

        return response()->stream(function () use ($templateProcessor) {
            $templateProcessor->saveAs("php://output");
        }, 200, [
            'Content-Disposition' => 'attachment; filename="poa.docx"',
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Transfer-Encoding' => 'binary',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => 0,
        ]);
    }

    public function test(Request $request, NewsletterService $newsletterService, $id)
    {
        set_time_limit(300); // 5 mins.

        $poa = Poa::findOrFail($id);
        $owner = $poa->owner;
        $locale = $owner->locale->locale;

        $data = $this->getData($poa, $owner, $locale);

        $template = NewsletterTemplates::where('template', 'poa')->where('locale_id', $owner->locale_id)->firstOrFail();

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
            $message->attachData($data, 'poa.pdf');
        });

        if (count(Mail::failures()) > 0) {
            $msg = '';
            foreach (Mail::failures() as $email) {
                $msg .= '<br />' . $email;
            }
            return response()->json([
                'errors' => [trans(\Locales::getNamespace() . '/forms.poaSentError') . $msg],
            ]);
        } else {
            return response()->json([
                'success' => [trans(\Locales::getNamespace() . '/forms.poaSentSuccessfully')],
            ]);
        }
    }

    public function send(Request $request, NewsletterService $newsletterService, $id)
    {
        set_time_limit(300); // 5 mins.

        $poa = Poa::findOrFail($id);
        $owner = $poa->owner;
        $locale = $owner->locale->locale;

        $data = $this->getData($poa, $owner, $locale);

        $template = NewsletterTemplates::where('template', 'poa')->where('locale_id', $owner->locale_id)->firstOrFail();

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
            $message->to((\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $owner->email), "=?UTF-8?B?" . base64_encode($owner->full_name) . "?=");
            if ($owner->email_cc) {
                $message->to((\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $owner->email_cc), "=?UTF-8?B?" . base64_encode($owner->full_name) . "?=");
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
            $message->attachData($data, 'poa.pdf');
        });

        if (count(Mail::failures()) > 0) {
            $msg = '';
            foreach (Mail::failures() as $email) {
                $msg .= '<br />' . $email;
            }
            return response()->json([
                'errors' => [trans(\Locales::getNamespace() . '/forms.poaSentError') . $msg],
            ]);
        } else {
            $poa->sent_at = Carbon::now();
            $poa->save();

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
                $message->attachData($data, 'poa.pdf');
            });

            \Session::flash('success', [trans(\Locales::getNamespace() . '/forms.poaSentSuccessfully')]);

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

        $poas = Poa::findOrFail($request->input('id'));
        $errors = [];

        foreach ($poas as $poa) {
            $owner = $poa->owner;
            $locale = $owner->locale->locale;

            $data = $this->getData($poa, $owner, $locale);

            $template = NewsletterTemplates::where('template', 'poa')->where('locale_id', $owner->locale_id)->firstOrFail();

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
                $message->to((\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $owner->email), "=?UTF-8?B?" . base64_encode($owner->full_name) . "?=");
                if ($owner->email_cc) {
                    $message->to((\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $owner->email_cc), "=?UTF-8?B?" . base64_encode($owner->full_name) . "?=");
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
                $message->attachData($data, 'poa.pdf');
            });

            if (count(Mail::failures()) > 0) {
                $msg = '';
                foreach (Mail::failures() as $email) {
                    $msg .= '<br />' . $email;
                }

                array_push($errors, trans(\Locales::getNamespace() . '/forms.poaSentError') . $msg);
            } else {
                $poa->sent_at = Carbon::now();
                $poa->save();

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
                    $message->attachData($data, 'poa.pdf');
                });
            }
        }

        if ($errors) {
            $errors = new \Illuminate\Support\MessageBag($errors);
            \Session::flash('errors', $errors);
        }

        \Session::flash('success', [trans(\Locales::getNamespace() . '/forms.poaSentSuccessfully')]);

        return response()->json([
            'refresh' => true,
        ]);
    }

    public function getData($poa, $owner, $locale)
    {
        $year = Year::where('year', $poa->from)->firstOrFail();
        $company = $year->rentalCompanies->first();
        $proxy = $poa->proxy;
        $apartment = $poa->apartment;
        $building = $apartment->building;
        $mm = $apartment->buildingMM()->where('year_id', $year->id)->first();

        if (!defined('DOMPDF_ENABLE_AUTOLOAD')) {
            define("DOMPDF_ENABLE_AUTOLOAD", false);
        }
        $dompdf = new Dompdf();
        $dompdf->set_paper('A4');
        $dompdf->set_option('isFontSubsettingEnabled', true);

        $html = file_get_contents(storage_path('app/templates/poa-' . $locale . '.html'));

        $html = preg_replace('/\$\{DATE\}/', Carbon::now('Europe/Sofia')->format('d.m.Y'), $html);
        $html = preg_replace('/\$\{OWNER-NAME\}/', $owner->full_name, $html);
        $html = preg_replace('/\$\{OWNER-ADDRESS\}/', $owner->full_address, $html);
        $html = preg_replace('/\$\{APARTMENT\}/', $apartment->number, $html);
        $html = preg_replace('/\$\{BUILDINGNAME-BG\}/', $building->translate('bg')->name, $html);
        $html = preg_replace('/\$\{BUILDINGNAME\}/', $building->translate($locale)->name, $html);
        $html = preg_replace('/\$\{BUILDINGDESCRIPTION-BG\}/', $building->translate('bg')->description, $html);
        $html = preg_replace('/\$\{BUILDINGDESCRIPTION\}/', $building->translate($locale)->description, $html);
        $html = preg_replace('/\$\{PROXYNAME-BG\}/', $proxy->translate('bg')->name, $html);
        $html = preg_replace('/\$\{PROXYNAME\}/', $proxy->translate($locale)->name, $html);

        if ($proxy->is_company) {
            $html = preg_replace('/\$\{PROXYDETAILS-BG\}/', \Lang::get('contracts.proxy-company', ['bulstat' => $proxy->bulstat, 'address' => $company->translate('bg')->address], 'bg'), $html);
            $html = preg_replace('/\$\{PROXYDETAILS\}/', \Lang::get('contracts.proxy-company', ['bulstat' => $proxy->bulstat, 'address' => $company->translate($locale)->address], $locale), $html);
        } else {
            $html = preg_replace('/\$\{PROXYDETAILS-BG\}/', \Lang::get('contracts.proxy-person', ['egn' => $proxy->egn, 'passport' => $proxy->id_card, 'issuedat' => $proxy->issued_at, 'issuedby' => $proxy->translate('bg')->issued_by, 'address' => $company->translate('bg')->address], 'bg'), $html);
            $html = preg_replace('/\$\{PROXYDETAILS\}/', \Lang::get('contracts.proxy-person', ['egn' => $proxy->egn, 'passport' => $proxy->id_card, 'issuedat' => $proxy->issued_at, 'issuedby' => $proxy->translate($locale)->issued_by, 'address' => $company->translate($locale)->address], $locale), $html);
        }

        $nf = new \NumberFormatter('bg', \NumberFormatter::SPELLOUT);
        $html = preg_replace('/\$\{MMFEE-BG\}/', number_format($mm->mm_tax) . ' (' . $nf->format($mm->mm_tax) . ')', $html);
        $nf = new \NumberFormatter($locale, \NumberFormatter::SPELLOUT);
        $html = preg_replace('/\$\{MMFEE\}/', number_format($mm->mm_tax) . ' (' . $nf->format($mm->mm_tax) . ')', $html);

        $html = preg_replace('/\$\{EXPIRY\}/', Carbon::createFromDate($poa->to)->endOfYear()->format('d.m.Y'), $html);

        $dompdf->load_html($html);
        $dompdf->render();
        return $dompdf->output();
    }
}
