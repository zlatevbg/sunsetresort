<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Models\Sky\Owner;
use App\Models\Sky\Country;
use App\Models\Sky\Notice;
use App\Models\Sky\NewsletterTemplates;
use App\Models\Sky\Signature;
use App\Services\DataTable;
use App\Services\Newsletter as NewsletterService;
use Illuminate\Http\Request;
use App\Http\Requests\Sky\OwnerRequest;
use Mailgun\Mailgun;

class OwnerController extends Controller
{

    protected $route = 'owners';
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
                'title' => trans(\Locales::getNamespace() . '/datatables.titleOwners'),
                'url' => \Locales::route($this->route),
                'class' => 'table-checkbox table-striped table-bordered table-hover table-dropdown',
                'selectors' => [$this->route . '.outstanding_bills', $this->route . '.letting_offer', $this->route . '.srioc', $this->route . '.comments', $this->route . '.temp_password'],
                'columns' => [
                    [
                        'selector' => $this->route . '.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center',
                    ],
                    [
                        'id' => 'dropdown',
                        'name' => '',
                        'order' => false,
                        'class' => 'text-center datatables-dropdown',
                        'width' => '1.25em',
                        'dropdown' => [
                            'route' => $this->route,
                            'routeParameter' => 'id',
                            'title' => trans(\Locales::getNamespace() . '/messages.menu'),
                            'menu' => trans(\Locales::getNamespace() . '/multiselect.ownerProperties'),
                        ],
                        'impersonate' => [
                            'slug' => 'impersonate',
                            'name' => trans(\Locales::getNamespace() . '/messages.impersonateOwner'),
                        ]
                    ],
                    [
                        'selector' => $this->route . '.first_name',
                        'id' => 'first_name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.name'),
                        'search' => true,
                        'append' => [
                            'selector' => [$this->route . '.last_name'],
                            'text' => 'last_name',
                        ],
                        'info' => 'comments',
                    ],
                    [
                        'selector' => 'country_translations.name',
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.country'),
                        'join' => [
                            'table' => 'country_translations',
                            'localColumn' => 'country_translations.country_id',
                            'constrain' => '=',
                            'foreignColumn' => $this->route . '.country_id',
                            'whereColumn' => 'country_translations.locale',
                            'whereConstrain' => '=',
                            'whereValue' => \Locales::getCurrent(),
                        ],
                    ],
                    [
                        'selector' => $this->route . '.email',
                        'id' => 'email',
                        'name' => trans(\Locales::getNamespace() . '/datatables.email'),
                        'search' => true,
                    ],
                    [
                        'selectRaw' => 'CONCAT(COALESCE(owners.phone, ""), " / ", COALESCE(owners.mobile, "")) as phones',
                        'id' => 'phones',
                        'name' => trans(\Locales::getNamespace() . '/datatables.phone'),
                        'search' => true,
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
                'orderByColumn' => 2,
                'order' => 'asc',
                'buttons' => [
                    [
                        'url' => \Locales::route($this->route . '/send-profile'),
                        'class' => 'btn-success hidden js-send-profile',
                        'icon' => 'send',
                        'name' => trans(\Locales::getNamespace() . '/forms.sendProfileButton'),
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
            ],
        ];
    }

    public function index(DataTable $datatable, Owner $owner, Request $request, $id = null)
    {
        $breadcrumbs = [];

        if ($id) {
            $owner = Owner::findOrFail($id);
            $breadcrumbs[] = ['id' => $owner->id, 'slug' => $owner->id, 'name' => $owner->full_name];

            $this->datatables['properties']['data'] = [
                [
                    'name' => '<a href="' . \Locales::route($this->route, true) . '/apartments"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/multiselect.ownerProperties.apartments') . '</a>',
                ],
                [
                    'name' => '<a href="' . \Locales::route($this->route, true) . '/former-apartments"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/multiselect.ownerProperties.former-apartments') . '</a>',
                ],
                [
                    'name' => '<a href="' . \Locales::route($this->route, true) . '/bank-accounts"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/multiselect.ownerProperties.bank-accounts') . '</a>',
                ],
                [
                    'name' => '<a href="' . \Locales::route($this->route, true) . '/council-tax"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/multiselect.ownerProperties.council-tax') . '</a>',
                ],
                [
                    'name' => '<a href="' . \Locales::route($this->route, true) . '/notices"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/multiselect.ownerProperties.notices') . '</a>',
                ],
                [
                    'name' => '<a href="' . \Locales::route($this->route, true) . '/files"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/multiselect.ownerProperties.files') . '</a>',
                ],
                [
                    'name' => '<a target="_blank" href="' . \Locales::route($this->route) . '/impersonate/' . $owner->id . '"><span class="glyphicon glyphicon-search glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/messages.impersonateOwner') . '</a>',
                ],
            ];
            $datatable->setup(null, 'properties', $this->datatables['properties']);
        } else {
            $datatable->setup($owner, $this->route, $this->datatables[$this->route]);
        }

        $datatables = $datatable->getTables();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($datatables);
        } else {
            return view(\Locales::getNamespace() . '.' . $this->route . '.index', compact('datatables', 'breadcrumbs'));
        }
    }

    public function create(Request $request)
    {
        $table = $this->route;
        if ($request->input('table')) { // magnific popup request
            $table = $request->input('table');
        }

        $sex[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $sex = $sex + trans(\Locales::getNamespace() . '/multiselect.sex');

        $wt = trans(\Locales::getNamespace() . '/multiselect.applyWt');
        $ob = trans(\Locales::getNamespace() . '/multiselect.outstandingBills');
        $srioc = trans(\Locales::getNamespace() . '/multiselect.srioc');
        $subscribed = trans(\Locales::getNamespace() . '/multiselect.newsletterSubscription');

        $locales[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $locales = $locales + \Locales::getPublicDomain()->locales->keyBy('locale')->lists('name', 'id')->toArray();

        $countries[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $countries = $countries + Country::withTranslation()->select('country_translations.name', 'countries.id')->leftJoin('country_translations', 'country_translations.country_id', '=', 'countries.id')->where('country_translations.locale', \Locales::getCurrent())->orderBy('country_translations.name')->get()->lists('name', 'id')->toArray();

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('table', 'sex', 'wt', 'ob', 'srioc', 'subscribed', 'locales', 'countries'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function store(DataTable $datatable, Owner $owner, OwnerRequest $request)
    {
        $data = $request->all();
        $data['temp_password'] = $data['password'];
        $data['password'] = bcrypt($data['password']);

        $newOwner = Owner::create($data);

        if ($newOwner->id) {
            $notices = Notice::where('auto_assign', 1)->where('locale_id', $newOwner->locale_id)->get()->lists('id')->toArray();
            if ($notices) {
                $newOwner->notices()->attach($notices);
            }

            $successMessage = trans(\Locales::getNamespace() . '/forms.storedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityOwners', 1)]);

            $datatable->setup($owner, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'reset' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.createError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityOwners', 1)]);
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

    public function destroy(DataTable $datatable, Owner $owner, Request $request)
    {
        $count = count($request->input('id'));

        if ($count > 0 && $owner->destroy($request->input('id'))) {
            $datatable->setup($owner, $request->input('table'), $this->datatables[$request->input('table')], true);
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
        $owner = Owner::findOrFail($id);

        $table = $this->route;
        if ($request->input('table')) { // magnific popup request
            $table = $request->input('table');
        }

        $sex = trans(\Locales::getNamespace() . '/multiselect.sex');

        $wt = trans(\Locales::getNamespace() . '/multiselect.applyWt');
        $ob = trans(\Locales::getNamespace() . '/multiselect.outstandingBills');
        $srioc = trans(\Locales::getNamespace() . '/multiselect.srioc');

        $subscribed = trans(\Locales::getNamespace() . '/multiselect.newsletterSubscription');

        $locales = \Locales::getPublicDomain()->locales->keyBy('locale')->lists('name', 'id')->toArray();
        $countries = Country::withTranslation()->select('country_translations.name', 'countries.id')->leftJoin('country_translations', 'country_translations.country_id', '=', 'countries.id')->where('country_translations.locale', \Locales::getCurrent())->orderBy('country_translations.name')->get()->lists('name', 'id')->toArray();

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('owner', 'table', 'sex', 'wt', 'ob', 'srioc', 'subscribed', 'locales', 'countries'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, OwnerRequest $request)
    {
        $owner = Owner::findOrFail($request->input('id'))->first();

        $data = $request->all();
        if ($data['password']) {
            if ($owner->temp_password) {
                $data['temp_password'] = $data['password'];
            }
            $data['password'] = bcrypt($data['password']);
        } else {
            $data['password'] = $owner->password;
        }

        if ($owner->update($data)) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityOwners', 1)]);

            $datatable->setup($owner, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityOwners', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function changeStatus($id, $status)
    {
        $owner = Owner::findOrFail($id);

        $owner->is_active = $status;
        $owner->save();

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

    public function impersonate($id)
    {
        $owner = Owner::findOrFail($id);
        if (\Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->loginUsingId($id)) {
            return redirect('https://' . env('APP_OWNERS_SUBDOMAIN') . '.' . env('APP_DOMAIN') . '/' . ($owner->locale->locale != \Locales::getDefault() ? $owner->locale->locale . '/' : '') . \Locales::getPublicDomain()->route);
        } else {
            return redirect()->back()->withErrors([trans(\Locales::getNamespace() . '/messages.impersonateError')]);
        }
    }

    public function sendProfile(NewsletterService $newsletterService, $id)
    {
        $owner = Owner::whereNotNull('temp_password')->whereNotNull('email')->findOrFail($id);

        $language = \Locales::getPublicLocales()->filter(function($value, $key) use ($owner) {
            return $value->id == $owner->locale_id;
        })->first()->locale;

        $template = NewsletterTemplates::where('template', 'new-profile')->where('locale_id', $owner->locale_id)->firstOrFail();

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

        $signature = $template->signature->translate($language)->content;

        $links = \Lang::get(\Locales::getPublicNamespace() . '/newsletters.links', [], $language);
        $copyright = \Lang::get(\Locales::getPublicNamespace() . '/newsletters.copyright', [], $language);
        $disclaimer = \Lang::get(\Locales::getPublicNamespace() . '/newsletters.disclaimer', [], $language);

        $html = \View::make(\Locales::getNamespace() . '.newsletters.templates.profile', compact('template', 'body', 'signature', 'links', 'copyright', 'disclaimer'))->render();
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

        $mg = new Mailgun(env('MAILGUN_SECRET'), new \Http\Adapter\Guzzle6\Client()); // , 'bin.mailgun.net'
        // $mg->setApiVersion('7fb5efa5'); // bin.mailgun.net/7fb5efa5
        // $mg->setSslEnabled(false);

        $result = $mg->sendMessage(env('MAILGUN_DOMAIN'),
            [
                'from' => \Config::get('mail.from.name') . ' <' . \Config::get('mail.from.address') . '>',
                'h:Sender' => \Config::get('mail.from.name') . ' <' . \Config::get('mail.from.address') . '>',
                'to' => $owner->full_name . ' <' . (\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $owner->email) . '>',
                'bcc' => \Auth::user()->full_name . ' <' . (\Config::get('mail.to.address') ? \Config::get('mail.to.address') : \Auth::user()->email) . '>',
                'subject' => $template->subject,
                'html' => $html,
                'text' => $text,
                // 'o:testmode' => true,
                'o:tag' => 'owners-new-profile',
                'v:templateId' => $template->id,
                'v:ownerId' => $owner->id,
            ],
            [
                'attachment' => $attachments,
                'inline' => $images,
            ]
        );

        if ($result->http_response_code == 200) {
            $owner->temp_password = null;
            $owner->save();

            \Session::flash('success', [trans(\Locales::getNamespace() . '/forms.profileSentSuccessfully')]);

            return response()->json([
                'refresh' => true,
            ]);
        } else {
            return response()->json([
                'error' => trans(\Locales::getNamespace() . '/forms.profileSentError'),
            ]);
        }
    }
}
