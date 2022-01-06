<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Models\Sky\PoolUsageContractTracker;
use App\Models\Sky\Apartment;
use App\Models\Sky\Year;
use App\Models\Sky\Owner;
use App\Models\Sky\Signature;
use App\Models\Sky\NewsletterTemplates;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Http\Requests\Sky\PoolUsageContractTrackerRequest;
use App\Services\Newsletter as NewsletterService;
use Mail;
use File;
use Carbon\Carbon;
use Dompdf\Dompdf;

class PoolUsageContractTrackerController extends Controller
{
    protected $route = 'pool-usage-contracts-tracker';
    protected $datatables;
    protected $multiselect;

    public function __construct()
    {
        $this->datatables = [
            $this->route => [
                'title' => trans(\Locales::getNamespace() . '/datatables.titlePoolUsageContractsTracker'),
                'url' => \Locales::route($this->route),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'selectors' => ['pool_usage_contracts_tracker.comments'],
                'joins' => [
                    [
                        'table' => 'owners',
                        'localColumn' => 'owners.id',
                        'constrain' => '=',
                        'foreignColumn' => 'pool_usage_contracts_tracker.owner_id',
                        'whereNull' => 'owners.deleted_at',
                    ],
                ],
                'columns' => [
                    [
                        'selector' => 'pool_usage_contracts_tracker.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center vertical-center',
                    ],
                    [
                        'selector' => 'years.year',
                        'id' => 'year',
                        'name' => trans(\Locales::getNamespace() . '/datatables.year'),
                        'search' => true,
                        'class' => 'vertical-center',
                        'join' => [
                            'table' => 'years',
                            'localColumn' => 'years.id',
                            'constrain' => '=',
                            'foreignColumn' => 'pool_usage_contracts_tracker.year_id',
                        ],
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
                            'foreignColumn' => 'pool_usage_contracts_tracker.apartment_id',
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
                        'selector' => 'pool_usage_contracts_tracker.sent_at',
                        'id' => 'sent_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.dateSent'),
                        'class' => 'text-center vertical-center sent-at',
                        'search' => true,
                        'date' => [
                            'format' => '%d.%m.%Y',
                        ],
                    ],
                    [
                        'selector' => 'pool_usage_contracts_tracker.is_active',
                        'id' => 'is_active',
                        'name' => trans(\Locales::getNamespace() . '/datatables.contractSignedStatus'),
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
                'orderByColumn' => 'pool_usage_contracts_tracker.sent_at',
                'orderByRaw' => 'NOT ISNULL(pool_usage_contracts_tracker.sent_at)',
                'order' => 'desc',
                'buttons' => [
                    /*[
                        'url' => \Locales::route($this->route . '/confirm-activate'),
                        'class' => 'btn-secondary js-activate hidden',
                        'icon' => 'ok',
                        'name' => trans(\Locales::getNamespace() . '/forms.activateButton'),
                    ],*/
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
            'years' => [
                'id' => 'id',
                'name' => 'year',
            ],
        ];
    }

    public function index(DataTable $datatable, PoolUsageContractTracker $poolUsageContract, Request $request)
    {
        $datatable->setup($poolUsageContract, $this->route, $this->datatables[$this->route]);
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

        $this->multiselect['owners']['options'] = [];
        $this->multiselect['owners']['selected'] = '';

        $year = Year::select('id')->where('year', date('Y'))->value('id');
        $this->multiselect['years']['options'] = Year::select('id', 'year')->where('year', '>=', date('Y'))->orderBy('year', 'desc')->get()->toArray();
        $this->multiselect['years']['selected'] = $year;

        $multiselect = $this->multiselect;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('table', 'multiselect'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function store(DataTable $datatable, PoolUsageContractTracker $poolUsageContract, PoolUsageContractTrackerRequest $request)
    {
        $newPoolUsageContract = PoolUsageContractTracker::create($request->all());

        if ($newPoolUsageContract->id) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.storedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityPoolUsageContracts', 1)]);

            $datatable->setup($poolUsageContract, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.createError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityPoolUsageContracts', 1)]);
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

    public function destroy(DataTable $datatable, PoolUsageContractTracker $poolUsageContract, Request $request)
    {
        $count = count($request->input('id'));

        if ($count > 0 && $poolUsageContract->destroy($request->input('id'))) {
            $datatable->setup($poolUsageContract, $request->input('table'), $this->datatables[$request->input('table')], true);
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
        $poolUsageContract = PoolUsageContractTracker::findOrFail($id);

        $table = $this->route;
        if ($request->input('table')) { // magnific popup request
            $table = $request->input('table');
        }

        $this->multiselect['apartments']['options'] = Apartment::distinct()->select('apartments.id', 'apartments.number')->join('ownership', 'ownership.apartment_id', '=', 'apartments.id')->whereNull('ownership.deleted_at')->orderBy('number')->get()->toArray();
        $this->multiselect['apartments']['selected'] = $poolUsageContract->apartment_id;

        $this->multiselect['owners']['options'] = $this->getOwners($poolUsageContract->apartment_id, false);
        $this->multiselect['owners']['selected'] = $poolUsageContract->owner_id;

        $this->multiselect['years']['options'] = Year::select('id', 'year')->orderBy('year', 'desc')->get()->toArray();
        $this->multiselect['years']['selected'] = $poolUsageContract->year_id;

        $multiselect = $this->multiselect;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('table', 'multiselect', 'poolUsageContract'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, PoolUsageContractTracker $poolUsageContracts, PoolUsageContractTrackerRequest $request)
    {
        $poolUsageContract = PoolUsageContractTracker::findOrFail($request->input('id'))->first();

        if ($poolUsageContract->update($request->all())) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityPoolUsageContracts', 1)]);

            $datatable->setup($poolUsageContracts, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityPoolUsageContracts', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function print(Request $request, $id)
    {
        $contract = PoolUsageContractTracker::findOrFail($id);

        $apartment = $contract->apartment;
        $building = $apartment->building;
        $owners = $apartment->owners;
        $owner = $contract->owner; // $owners->first()->owner;
        $locale = $owner->locale->locale;

        $year = Year::where('year', date('Y'))->firstOrFail();

        $poolTax = 0;
        $poolBracelets = 0;
        $fees = $year->fees->where('room_id', $apartment->room_id)->first();
        if ($fees) {
            $poolTax = number_format($fees->pool_tax, 2);
            $poolBracelets = $fees->pool_bracelets;
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

        $templateProcessor = new \App\Extensions\PhpOffice\PhpWord\CustomTemplateProcessor(storage_path('app/templates/pool-usage-contract-' . $locale . '.docx'));

        $templateProcessor->setValue('DATE', Carbon::now('Europe/Sofia')->format('d.m.Y'));
        $templateProcessor->setValue('NAMES', implode('; ', $names));
        $templateProcessor->setValue('ADDRESSES', implode('; ', array_unique($addresses)));
        $templateProcessor->setValue('APARTMENT', $apartment->number);
        $templateProcessor->setValue('BUILDINGNAME-BG', $building->translate('bg')->name);
        $templateProcessor->setValue('BUILDINGNAME', $building->translate($locale)->name);
        $templateProcessor->setValue('EMAILS', implode('; ', array_unique(array_filter($emails))));
        $templateProcessor->setValue('POOL_TAX', $poolTax);
        $templateProcessor->setValue('POOL_BRACELETS', $poolBracelets);

        return response()->stream(function () use ($templateProcessor) {
            $templateProcessor->saveAs("php://output");
        }, 200, [
            'Content-Disposition' => 'attachment; filename="pool-usage-contract.docx"',
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Transfer-Encoding' => 'binary',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => 0,
        ]);
    }

    public function test(Request $request, NewsletterService $newsletterService, $id)
    {
        set_time_limit(300); // 5 mins.

        $contract = PoolUsageContractTracker::findOrFail($id);
        $apartment = $contract->apartment;
        $owners = $apartment->ownership;
        $owner = $contract->owner; // $owners->first()->owner;
        $locale = $owner->locale->locale;

        $data = $this->getData($contract, $apartment, $owners, $locale);

        $template = NewsletterTemplates::where('template', 'pool-usage-contract')->where('locale_id', $owner->locale_id)->firstOrFail();

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
            $message->attachData($data, 'pool-usage-contract.pdf');
        });

        if (count(Mail::failures()) > 0) {
            $msg = '';
            foreach (Mail::failures() as $email) {
                $msg .= '<br />' . $email;
            }
            return response()->json([
                'errors' => [trans(\Locales::getNamespace() . '/forms.poolUsageContractSentError') . $msg],
            ]);
        } else {
            return response()->json([
                'success' => [trans(\Locales::getNamespace() . '/forms.poolUsageContractSentSuccessfully')],
            ]);
        }
    }

    public function send(Request $request, NewsletterService $newsletterService, $id)
    {
        set_time_limit(300); // 5 mins.

        $contract = PoolUsageContractTracker::findOrFail($id);
        $apartment = $contract->apartment;
        $owners = $apartment->ownership;
        $owner = $contract->owner; /*$owners->filter(function ($value, $key) {
            return $value->owner->email;
        })->first()->owner;*/
        $locale = $owner->locale->locale;

        $data = $this->getData($contract, $apartment, $owners, $locale);

        $template = NewsletterTemplates::where('template', 'pool-usage-contract')->where('locale_id', $owner->locale_id)->firstOrFail();

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
            $message->attachData($data, 'pool-usage-contract.pdf');
        });

        if (count(Mail::failures()) > 0) {
            $msg = '';
            foreach (Mail::failures() as $email) {
                $msg .= '<br />' . $email;
            }
            return response()->json([
                'errors' => [trans(\Locales::getNamespace() . '/forms.poolUsageContractSentError') . $msg],
            ]);
        } else {
            $contract->sent_at = Carbon::now();
            $contract->save();

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
                $message->attachData($data, 'pool-usage-contract.pdf');
            });

            \Session::flash('success', [trans(\Locales::getNamespace() . '/forms.poolUsageContractSentSuccessfully')]);

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

        $contracts = PoolUsageContractTracker::findOrFail($request->input('id'));
        $errors = [];

        foreach ($contracts as $contract) {
            $apartment = $contract->apartment;
            $owners = $apartment->ownership;
            $owner = $contract->owner; // $owners->first()->owner;
            $locale = $owner->locale->locale;

            $data = $this->getData($contract, $apartment, $owners, $locale);

            $template = NewsletterTemplates::where('template', 'pool-usage-contract')->where('locale_id', $owner->locale_id)->firstOrFail();

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
                $message->attachData($data, 'pool-usage-contract.pdf');
            });

            if (count(Mail::failures()) > 0) {
                $msg = '';
                foreach (Mail::failures() as $email) {
                    $msg .= '<br />' . $email;
                }

                array_push($errors, trans(\Locales::getNamespace() . '/forms.poolUsageContractSentError') . $msg);
            } else {
                $contract->sent_at = Carbon::now();
                $contract->save();

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
                    $message->attachData($data, 'pool-usage-contract.pdf');
                });
            }
        }

        if ($errors) {
            $errors = new \Illuminate\Support\MessageBag($errors);
            \Session::flash('errors', $errors);
        }

        \Session::flash('success', [trans(\Locales::getNamespace() . '/forms.poolUsageContractSentSuccessfully')]);

        return response()->json([
            'refresh' => true,
        ]);
    }

    /*public function confirmActivate(Request $request)
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

        $contracts = PoolUsageContractTracker::findOrFail($request->input('id'));

        foreach ($contracts as $contract) {
            $newContract = Contract::create([
                'signed_at' => $now,
                'comments' => $contract->comments,
                'apartment_id' => $contract->apartment_id,
            ]);

            if ($newContract->id) {
                $contract->delete();
            }
        }

        \Session::flash('success', [trans(\Locales::getNamespace() . '/forms.poolUsageContractActivatedSuccessfully')]);

        return response()->json([
            'refresh' => true,
        ]);
    }*/

    public function getData($contract, $apartment, $owners, $locale)
    {
        $building = $apartment->building;
        $owner = $contract->owner;

        $year = Year::where('year', date('Y'))->firstOrFail();

        $poolTax = 0;
        $poolBracelets = 0;
        $fees = $year->fees->where('room_id', $apartment->room_id)->first();
        if ($fees) {
            $poolTax = number_format($fees->pool_tax, 2);
            $poolBracelets = $fees->pool_bracelets;
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

        $html = file_get_contents(storage_path('app/templates/pool-usage-contract-' . $locale . '.html'));

        $html = preg_replace('/\$\{DATE\}/', Carbon::now('Europe/Sofia')->format('d.m.Y'), $html);
        $html = preg_replace('/\$\{NAMES\}/', implode('; ', $names), $html);
        $html = preg_replace('/\$\{ADDRESSES\}/', implode('; ', array_unique($addresses)), $html);
        $html = preg_replace('/\$\{APARTMENT\}/', $apartment->number, $html);
        $html = preg_replace('/\$\{BUILDINGNAME-BG\}/', $building->translate('bg')->name, $html);
        $html = preg_replace('/\$\{BUILDINGNAME\}/', $building->translate($locale)->name, $html);
        $html = preg_replace('/\$\{EMAILS\}/', implode('; ', array_unique(array_filter($emails))), $html);
        $html = preg_replace('/\$\{POOL_TAX\}/', $poolTax, $html);
        $html = preg_replace('/\$\{POOL_BRACELETS\}/', $poolBracelets, $html);

        $dompdf->load_html($html);
        $dompdf->render();
        return $dompdf->output();
    }

    public function getOwners($apartment = null, $json = true)
    {
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
                'owners' => array_merge([trans(\Locales::getNamespace() . '/forms.selectOption') => ''], $owners->pluck('id', 'name')->toArray()),
            ]);
        } else {
            return $owners->toArray();
        }
    }

    public function changeStatus($id, $status)
    {
        $ct = PoolUsageContractTracker::findOrFail($id);

        $ct->is_active = $status;
        $ct->save();

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
