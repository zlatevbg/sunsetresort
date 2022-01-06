<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Models\Sky\Newsletter;
use App\Models\Sky\NewsletterAttachments;
use App\Models\Sky\NewsletterImages;
use App\Models\Sky\NewsletterArchive;
use App\Models\Sky\NewsletterArchiveMerge;
use App\Models\Sky\NewsletterMerge;
use App\Models\Sky\NewsletterTemplates;
use App\Models\Sky\Country;
use App\Models\Sky\Locale;
use App\Models\Sky\Owner;
use App\Models\Sky\Apartment;
use App\Models\Sky\CouncilTax;
use App\Models\Sky\Recipient;
use App\Models\Sky\Signature;
use App\Models\Sky\Project;
use App\Models\Sky\Building;
use App\Models\Sky\Floor;
use App\Models\Sky\Room;
use App\Models\Sky\Furniture;
use App\Models\Sky\View;
use App\Models\Sky\Year;
use App\Models\Sky\RentalContract;
use App\Models\Sky\RentalRatesPeriod;
use App\Models\Sky\KeyLog;
use App\Services\DataTable;
use App\Services\Newsletter as NewsletterService;
use Illuminate\Http\Request;
use App\Http\Requests\Sky\NewsletterRequest;
use Illuminate\Support\Str;
use Storage;
use File;
use Carbon\Carbon;
use Mailgun\Mailgun;
use Mail;

class NewsletterController extends Controller {

    protected $route = 'newsletters';
    protected $uploadDirectory = 'newsletters';
    protected $datatables;
    protected $multiselect;

    public function __construct()
    {
        $this->datatables = [
            $this->route => [
                'title' => trans(\Locales::getNamespace() . '/datatables.titleNewsletters'),
                'url' => \Locales::route($this->route),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'selectors' => [$this->route . '.teaser'],
                'columns' => [
                    [
                        'selector' => $this->route . '.id',
                        'id' => 'checkbox',
                        'order' => false,
                        'class' => 'text-center vertical-center',
                        'width' => '1.25em',
                        'replace' => [
                            'id' => 'id',
                            'rules' => [
                                0 => [
                                    'column' => 'sent_at',
                                    'value' => null,
                                    'checkbox' => true,
                                ],
                            ],
                        ],
                    ],
                    [
                        'id' => 'preview',
                        'name' => trans(\Locales::getNamespace() . '/datatables.preview'),
                        'order' => false,
                        'class' => 'text-center vertical-center',
                        'width' => '2.50em',
                        'preview' => [
                            'icon' => 'search',
                            'route' => $this->route . '/preview',
                            'routeParameter' => 'id',
                            'title' => trans(\Locales::getNamespace() . '/datatables.previewNewsletter'),
                        ],
                    ],
                    [
                        'selector' => $this->route . '.subject',
                        'id' => 'subject',
                        'name' => trans(\Locales::getNamespace() . '/datatables.subject'),
                        'search' => true,
                        'order' => false,
                        'class' => 'vertical-center',
                        'link' => [
                            'icon' => 'folder-open',
                            'route' => $this->route,
                            'routeParameters' => ['id'],
                            'append' => 'teaser',
                        ],
                    ],
                    [
                        'selector' => 'locales.name as locale',
                        'id' => 'locale',
                        'name' => trans(\Locales::getNamespace() . '/datatables.locale'),
                        'order' => false,
                        'class' => 'vertical-center',
                        'join' => [
                            'table' => 'locales',
                            'localColumn' => 'locales.id',
                            'constrain' => '=',
                            'foreignColumn' => $this->route . '.locale_id',
                        ],
                    ],
                    [
                        'selector' => $this->route . '.sent_at',
                        'id' => 'sent_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.dateSent'),
                        'class' => 'text-center vertical-center sent-at',
                        'search' => true,
                        'date' => [
                            'format' => '%d.%m.%Y',
                        ],
                    ],
                ],
                'orderByColumn' => 'sent_at',
                'orderByRaw' => 'NOT ISNULL(sent_at)',
                'order' => 'desc',
                'buttons' => [
                    [
                        'url' => \Locales::route($this->route . '/send'),
                        'class' => 'btn-success js-send hidden',
                        'icon' => 'send',
                        'id' => 'button-newsletter-send',
                        'name' => trans(\Locales::getNamespace() . '/forms.sendButton'),
                    ],
                    [
                        'url' => \Locales::route($this->route . '/test'),
                        'class' => 'btn-info js-test hidden',
                        'icon' => 'repeat',
                        'id' => 'button-newsletter-test',
                        'name' => trans(\Locales::getNamespace() . '/forms.testButton'),
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
                        'id' => 'button-newsletter-edit',
                        'name' => trans(\Locales::getNamespace() . '/forms.editButton'),
                    ],
                    [
                        'url' => \Locales::route($this->route . '/delete'),
                        'class' => 'btn-danger disabled js-destroy',
                        'icon' => 'trash',
                        'id' => 'button-newsletter-delete',
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
                'data' => [
                    [
                        'name' => '<a href="' . \Locales::route($this->route, true) . '/attachments"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/multiselect.newsletterProperties.attachments') . '</a>',
                    ],
                    [
                        'name' => '<a href="' . \Locales::route($this->route, true) . '/attachments-apartment"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/multiselect.newsletterProperties.attachments-apartment') . '</a>',
                    ],
                    [
                        'name' => '<a href="' . \Locales::route($this->route, true) . '/attachments-owner"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/multiselect.newsletterProperties.attachments-owner') . '</a>',
                    ],
                    [
                        'name' => '<a href="' . \Locales::route($this->route, true) . '/images"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/multiselect.newsletterProperties.images') . '</a>',
                    ],
                ],
            ],
        ];

        $this->multiselect = [
            'countries' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'recipients' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'locales' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'years' => [
                'id' => 'id',
                'name' => 'year',
            ],
            'projects' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'buildings' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'floors' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'apartments' => [
                'id' => 'id',
                'name' => 'number',
            ],
            'owners' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'rooms' => [
                'id' => 'id',
                'name' => 'room',
            ],
            'furniture' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'views' => [
                'id' => 'id',
                'name' => 'name',
            ],
        ];
    }

    public function index(DataTable $datatable, Newsletter $newsletter, Request $request, $id = null)
    {
        $breadcrumbs = [];

        if ($id) {
            $newsletter = $newsletter->findOrFail($id);
            $breadcrumbs[] = ['id' => 'newsletters', 'slug' => $newsletter->id, 'name' => $newsletter->subject];

            $uploadDirectory = $this->uploadDirectory;
            if (!Storage::disk('local-public')->exists($uploadDirectory)) {
                Storage::disk('local-public')->makeDirectory($uploadDirectory);
            }

            $uploadDirectory .= DIRECTORY_SEPARATOR . $newsletter->id;
            if (!Storage::disk('local-public')->exists($uploadDirectory)) {
                Storage::disk('local-public')->makeDirectory($uploadDirectory);
            }

            $datatable->setup(null, 'properties', $this->datatables['properties']);
        } else {
            $datatable->setup($newsletter, $this->route, $this->datatables[$this->route]);
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

        $templates = ['' => trans(\Locales::getNamespace() . '/forms.selectOption')] + collect(trans(\Locales::getNamespace() . '/multiselect.newsletterTemplates'))->filter(function ($value, $key) {
            return starts_with($key, 'newsletter');
        })->toArray();

        $this->multiselect['countries']['options'] = Country::withTranslation()->select('country_translations.name', 'countries.id')->leftJoin('country_translations', 'country_translations.country_id', '=', 'countries.id')->where('country_translations.locale', \Locales::getCurrent())->orderBy('country_translations.name')->get()->toArray();
        $this->multiselect['countries']['selected'] = '';

        $recipients = [];
        foreach (trans(\Locales::getNamespace() . '/forms.newsletterRecipients') as $key => $value) {
            array_push($recipients, ['id' => $key, 'name' => $value]);
        }
        $this->multiselect['recipients']['options'] = $recipients;
        $this->multiselect['recipients']['selected'] = 'subscribed';

        $locales[''] = ['id' => 0, 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        foreach (\Locales::getPublicDomain()->locales->toArray() as $key => $value) {
            $locales[$key]['id'] = $value['id'];
            $locales[$key]['name'] = $value['name'];
        }

        $this->multiselect['locales']['options'] = $locales;
        $this->multiselect['locales']['selected'] = '';

        $year = Year::select('id')->where('year', date('Y'))->value('id');
        $this->multiselect['years']['options'] = Year::select('id', 'year')->orderBy('year', 'desc')->get()->toArray();
        $this->multiselect['years']['selected'] = $year;

        $this->multiselect['projects']['options'] = Project::withTranslation()->select('project_translations.name', 'projects.id')->leftJoin('project_translations', 'project_translations.project_id', '=', 'projects.id')->where('project_translations.locale', \Locales::getCurrent())->orderBy('project_translations.name')->get()->toArray();
        $this->multiselect['projects']['selected'] = '';

        $this->multiselect['buildings']['options'] = [];
        $this->multiselect['buildings']['selected'] = '';

        $this->multiselect['floors']['options'] = [];
        $this->multiselect['floors']['selected'] = '';

        $this->multiselect['rooms']['options'] = Room::withTranslation()->selectRaw('CONCAT(room_translations.name, " (", room_translations.description, ")") as room, rooms.id')->leftJoin('room_translations', 'room_translations.room_id', '=', 'rooms.id')->where('room_translations.locale', \Locales::getCurrent())->orderBy('room_translations.name')->get()->toArray();
        $this->multiselect['rooms']['selected'] = '';

        $this->multiselect['furniture']['options'] = Furniture::withTranslation()->select('furniture_translations.name', 'furniture.id')->leftJoin('furniture_translations', 'furniture_translations.furniture_id', '=', 'furniture.id')->where('furniture_translations.locale', \Locales::getCurrent())->orderBy('furniture_translations.name')->get()->toArray();
        $this->multiselect['furniture']['selected'] = '';

        $this->multiselect['views']['options'] = View::withTranslation()->select('view_translations.name', 'views.id')->leftJoin('view_translations', 'view_translations.view_id', '=', 'views.id')->where('view_translations.locale', \Locales::getCurrent())->orderBy('view_translations.name')->get()->toArray();
        $this->multiselect['views']['selected'] = '';

        $this->multiselect['apartments']['options'] = Apartment::distinct()->select('apartments.id', 'apartments.number')->join('ownership', 'ownership.apartment_id', '=', 'apartments.id')->whereNull('ownership.deleted_at')->orderBy('number')->get()->toArray();
        $this->multiselect['apartments']['selected'] = '';

        $this->multiselect['owners']['options'] = Owner::distinct()->selectRaw('owners.id, CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as name')->join('ownership', 'ownership.owner_id', '=', 'owners.id')->whereNull('ownership.deleted_at')->orderBy('name')->get()->toArray();
        $this->multiselect['owners']['selected'] = '';

        $multiselect = $this->multiselect;

        $signatures[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $signatures = $signatures + Signature::select('description', 'id')->orderBy('description')->get()->pluck('description', 'id')->toArray();

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('table', 'multiselect', 'templates', 'signatures'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function store(DataTable $datatable, Newsletter $newsletter, NewsletterRequest $request)
    {
        $newNewsletter = Newsletter::create($request->all());

        if ($newNewsletter->id) {
            if ($request->input('merge_by')) {
                $merge = [];
                $i = 1;
                foreach ($request->input('merge') as $value) {
                    array_push($merge, new NewsletterMerge([
                        'merge' => $value,
                        'order' => $i,
                        'newsletter_id' => $newNewsletter->id,
                    ]));
                    $i++;
                }
                $newNewsletter->merge()->saveMany($merge);
            }

            $uploadDirectory = $this->uploadDirectory . DIRECTORY_SEPARATOR . $newNewsletter->id;
            if (!Storage::disk('local-public')->exists($uploadDirectory)) {
                Storage::disk('local-public')->makeDirectory($uploadDirectory);
            }

            if ($request->input('template')) {
                $template = NewsletterTemplates::where('template', $request->input('template'))->first();
                if ($template) {
                    $path = Storage::disk('local-public')->getDriver()->getAdapter()->getPathPrefix();
                    $attachments = $template->attachments;
                    if ($attachments->count()) {
                        $data = [];
                        foreach ($attachments as $attachment) {
                            array_push($data, [
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                                'file' => $attachment->file,
                                'uuid' => $attachment->uuid,
                                'extension' => $attachment->extension,
                                'size' => $attachment->size,
                                'order' => $attachment->order,
                                'newsletter_id' => $newNewsletter->id,
                            ]);
                        }

                        NewsletterAttachments::insert($data);
                        File::copyDirectory($path . 'newsletter-templates' . DIRECTORY_SEPARATOR . $template->id . DIRECTORY_SEPARATOR . 'attachments', $path . $uploadDirectory . DIRECTORY_SEPARATOR . 'attachments');
                    }

                    $images = $template->images;
                    if ($images->count()) {
                        $data = [];
                        foreach ($images as $image) {
                            array_push($data, [
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                                'file' => $image->file,
                                'uuid' => $image->uuid,
                                'extension' => $image->extension,
                                'size' => $image->size,
                                'order' => $image->order,
                                'newsletter_id' => $newNewsletter->id,
                            ]);
                        }

                        NewsletterImages::insert($data);
                        File::copyDirectory($path . 'newsletter-templates' . DIRECTORY_SEPARATOR . $template->id . DIRECTORY_SEPARATOR . 'images', $path . $uploadDirectory . DIRECTORY_SEPARATOR . 'images');
                    }
                }
            }

            $successMessage = trans(\Locales::getNamespace() . '/forms.storedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityNewsletters', 1)]);

            $datatable->setup($newsletter, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.createError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityNewsletters', 1)]);
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

    public function destroy(DataTable $datatable, Newsletter $newsletter, Request $request)
    {
        $count = count($request->input('id'));

        $rows = Newsletter::select('id')->whereIn('id', $request->input('id'))->get();

        if ($count > 0 && $newsletter->destroy($request->input('id'))) {
            foreach ($rows as $row) {
                Storage::disk('local-public')->deleteDirectory($this->uploadDirectory . DIRECTORY_SEPARATOR . $row->id);
            }

            $datatable->setup($newsletter, $request->input('table'), $this->datatables[$request->input('table')], true);
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
        $newsletter = Newsletter::findOrFail($id);

        $table = $this->route;
        if ($request->input('table')) { // magnific popup request
            $table = $request->input('table');
        }

        $this->multiselect['countries']['options'] = Country::withTranslation()->select('country_translations.name', 'countries.id')->leftJoin('country_translations', 'country_translations.country_id', '=', 'countries.id')->where('country_translations.locale', \Locales::getCurrent())->orderBy('country_translations.name')->get()->toArray();
        $this->multiselect['countries']['selected'] = explode(',', $newsletter->countries);

        $recipients = [];
        foreach (trans(\Locales::getNamespace() . '/forms.newsletterRecipients') as $key => $value) {
            array_push($recipients, ['id' => $key, 'name' => $value]);
        }
        $this->multiselect['recipients']['options'] = $recipients;
        $this->multiselect['recipients']['selected'] = explode(',', $newsletter->recipients);

        $locales = [];
        foreach (\Locales::getPublicDomain()->locales->toArray() as $key => $value) {
            $locales[$key]['id'] = $value['id'];
            $locales[$key]['name'] = $value['name'];
        }

        $this->multiselect['locales']['options'] = $locales;
        $this->multiselect['locales']['selected'] = $newsletter->locale_id;

        $this->multiselect['years']['options'] = Year::select('id', 'year')->orderBy('year', 'desc')->get()->toArray();
        $this->multiselect['years']['selected'] = $newsletter->year_id;

        $this->multiselect['projects']['options'] = Project::withTranslation()->select('project_translations.name', 'projects.id')->leftJoin('project_translations', 'project_translations.project_id', '=', 'projects.id')->where('project_translations.locale', \Locales::getCurrent())->orderBy('project_translations.name')->get()->toArray();
        $this->multiselect['projects']['selected'] = explode(',', $newsletter->projects);

        $this->multiselect['buildings']['options'] = Building::withTranslation()->select('building_translations.name', 'buildings.id')->leftJoin('building_translations', 'building_translations.building_id', '=', 'buildings.id')->where('building_translations.locale', \Locales::getCurrent())->whereIn('buildings.project_id', explode(',', $newsletter->projects))->orderBy('building_translations.name')->get()->toArray();
        $this->multiselect['buildings']['selected'] = explode(',', $newsletter->buildings);

        $this->multiselect['floors']['options'] = Floor::withTranslation()->select('floor_translations.name', 'floors.id')->leftJoin('floor_translations', 'floor_translations.floor_id', '=', 'floors.id')->where('floor_translations.locale', \Locales::getCurrent())->whereIn('floors.building_id', explode(',', $newsletter->buildings))->orderBy('floor_translations.name')->get()->toArray();
        $this->multiselect['floors']['selected'] = explode(',', $newsletter->floors);

        $this->multiselect['rooms']['options'] = Room::withTranslation()->selectRaw('CONCAT(room_translations.name, " (", room_translations.description, ")") as room, rooms.id')->leftJoin('room_translations', 'room_translations.room_id', '=', 'rooms.id')->where('room_translations.locale', \Locales::getCurrent())->orderBy('room_translations.name')->get()->toArray();
        $this->multiselect['rooms']['selected'] = explode(',', $newsletter->rooms);

        $this->multiselect['furniture']['options'] = Furniture::withTranslation()->select('furniture_translations.name', 'furniture.id')->leftJoin('furniture_translations', 'furniture_translations.furniture_id', '=', 'furniture.id')->where('furniture_translations.locale', \Locales::getCurrent())->orderBy('furniture_translations.name')->get()->toArray();
        $this->multiselect['furniture']['selected'] = explode(',', $newsletter->furniture);

        $this->multiselect['views']['options'] = View::withTranslation()->select('view_translations.name', 'views.id')->leftJoin('view_translations', 'view_translations.view_id', '=', 'views.id')->where('view_translations.locale', \Locales::getCurrent())->orderBy('view_translations.name')->get()->toArray();
        $this->multiselect['views']['selected'] = explode(',', $newsletter->views);

        $this->multiselect['apartments']['options'] = Apartment::distinct()->select('apartments.id', 'apartments.number')->join('ownership', 'ownership.apartment_id', '=', 'apartments.id')->whereNull('ownership.deleted_at')->orderBy('number')->get()->toArray();
        $this->multiselect['apartments']['selected'] = explode(',', $newsletter->apartments);

        $this->multiselect['owners']['options'] = Owner::distinct()->selectRaw('owners.id, CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as name')->join('ownership', 'ownership.owner_id', '=', 'owners.id')->whereNull('ownership.deleted_at')->orderBy('name')->get()->toArray();
        $this->multiselect['owners']['selected'] = explode(',', $newsletter->owners);

        $multiselect = $this->multiselect;

        $signatures = Signature::select('description', 'id')->orderBy('description')->get()->pluck('description', 'id')->toArray();

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('newsletter', 'table', 'multiselect', 'signatures'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, Newsletter $newsletters, NewsletterRequest $request)
    {
        $newsletter = Newsletter::findOrFail($request->input('id'))->first();

        if ($newsletter->update($request->all())) {
            $newsletter->merge()->delete();
            if ($request->input('merge_by')) {
                $merge = [];
                $i = 1;
                foreach ($request->input('merge', []) as $value) {
                    array_push($merge, new NewsletterMerge([
                        'merge' => $value,
                        'order' => $i,
                        'newsletter_id' => $newsletter->id,
                    ]));
                    $i++;
                }
                $newsletter->merge()->saveMany($merge);
            }

            $uploadDirectory = $this->uploadDirectory . DIRECTORY_SEPARATOR . $newsletter->id;
            if (!Storage::disk('local-public')->exists($uploadDirectory)) {
                Storage::disk('local-public')->makeDirectory($uploadDirectory);
            }

            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityNewsletters', 1)]);

            $datatable->setup($newsletters, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityNewsletters', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function preview(Request $request, NewsletterService $newsletterService, $id)
    {
        $newsletter = Newsletter::findOrFail($id);

        $newsletter->body = \HTML::image(asset('img/' . env('APP_OWNERS_SUBDOMAIN') . '/newsletter-logo.png')) . $newsletter->body;

        $patterns = array_map(function($value) { return '/' . $value . '/'; }, $newsletterService->patterns());
        $newsletter->body = preg_replace($patterns, '<span style="background-color: #ff0;">$0</span>', $newsletter->body);

        $uploadDirectory = asset('upload/' . $this->uploadDirectory . '/' . $newsletter->id . '/' . \Config::get('upload.imagesDirectory') . '/');
        foreach ($newsletter->images as $image) {
            $newsletter->body = preg_replace('/{IMAGE}/', \HTML::image($uploadDirectory . '/' . $image->uuid . '/' . $image->file), $newsletter->body, 1);
        }

        $newsletter->body = preg_replace('/{TOKEN}/', '<span style="background-color: #ff0;">$0</span>', $newsletter->body);
        $newsletter->body = preg_replace('/{MERGE_APARTMENT}/', '<span style="background-color: #ff0;">$0</span>', $newsletter->body);
        $newsletter->body = preg_replace('/{MERGE_OWNER}/', '<span style="background-color: #ff0;">$0</span>', $newsletter->body);
        $newsletter->body = preg_replace('/{MERGE}/', '<span style="background-color: #ff0;">$0</span>', $newsletter->body);
        $newsletter->body = preg_replace('/\[\[MERGE_ID\]\]/', '<span style="background-color: #ff0;">$0</span>', $newsletter->body);
        $newsletter->body = preg_replace('/\[\[MERGE_YEAR\]\]/', '<span style="background-color: #ff0;">$0</span>', $newsletter->body);
        $newsletter->body = preg_replace('/\[\[MERGE_DUE_YEAR\]\]/', '<span style="background-color: #ff0;">$0</span>', $newsletter->body);
        $newsletter->body = preg_replace('/\[\[MERGE_BLOCK\]\]/', '<span style="background-color: #ff0;">$0</span>', $newsletter->body);
        $newsletter->body = preg_replace('/\[\[MERGE_MM_AMOUNT\]\]/', '<span style="background-color: #ff0;">$0</span>', $newsletter->body);
        $newsletter->body = preg_replace('/\[\[MERGE_MM_FEE\]\]/', '<span style="background-color: #ff0;">$0</span>', $newsletter->body);
        $newsletter->body = preg_replace('/\[\[MERGE_INTEREST\]\]/', '<span style="background-color: #ff0;">$0</span>', $newsletter->body);
        $newsletter->body = preg_replace('/\[\[MERGE_DATE\]\]/', '<span style="background-color: #ff0;">$0</span>', $newsletter->body);
        $newsletter->body = preg_replace('/\[\[MERGE_TOTAL\]\]/', '<span style="background-color: #ff0;">$0</span>', $newsletter->body);
        $newsletter->body = preg_replace('/\[\[MERGE_BANK\]\]/', '<span style="background-color: #ff0;">$0</span>', $newsletter->body);
        $newsletter->body = preg_replace('/\[\[MERGE_BENEFICIARY\]\]/', '<span style="background-color: #ff0;">$0</span>', $newsletter->body);
        $newsletter->body = preg_replace('/\[\[MERGE_IBAN\]\]/', '<span style="background-color: #ff0;">$0</span>', $newsletter->body);
        $newsletter->body = preg_replace('/\[\[MERGE_BIC\]\]/', '<span style="background-color: #ff0;">$0</span>', $newsletter->body);
        $newsletter->body = preg_replace('/\[\[MERGE_RENT_AMOUNT\]\]/', '<span style="background-color: #ff0;">$0</span>', $newsletter->body);
        $newsletter->body = preg_replace('/\[\[MERGE_INCOME_AMOUNT\]\]/', '<span style="background-color: #ff0;">$0</span>', $newsletter->body);
        $newsletter->body = preg_replace('/\[\[MERGE_WT\]\]/', '<span style="background-color: #ff0;">$0</span>', $newsletter->body);
        $newsletter->body = preg_replace('/\[\[MERGE_DEDUCTIONS\]\]/', '<span style="background-color: #ff0;">$0</span>', $newsletter->body);
        $newsletter->body = preg_replace('/\[\[MERGE_TOTAL_EXPENDITURE\]\]/', '<span style="background-color: #ff0;">$0</span>', $newsletter->body);
        $newsletter->body = preg_replace('/\[\[MERGE_NET_RENT\]\]/', '<span style="background-color: #ff0;">$0</span>', $newsletter->body);
        $newsletter->body = preg_replace('/\[\[MERGE_WT_AMOUNT\]\]/', '<span style="background-color: #ff0;">$0</span>', $newsletter->body);
        $newsletter->body = preg_replace('/\[\[MERGE_AMOUNT\]\]/', '<span style="background-color: #ff0;">$0</span>', $newsletter->body);
        $newsletter->body = preg_replace('/\[\[MERGE_MM_YEAR\]\]/', '<span style="background-color: #ff0;">$0</span>', $newsletter->body);

        $language = \Locales::getPublicLocales()->filter(function($value, $key) use ($newsletter) {
            return $value->id == $newsletter->locale_id;
        })->first()->locale;
        $signature = $newsletter->signature->translate($language)->content;

        $directory = asset('upload/signatures/' . $newsletter->signature->id) . '/';
        foreach ($newsletter->signature->images as $image) {
            if (strpos($signature, '{SIGNATURE}') !== false) {
                $signature = preg_replace('/{SIGNATURE}/', '<img class="signature" src="' . $directory . $image->uuid . '/' . $image->file . '" />', $signature, 1);
            }
        }

        $newsletter->body .= $signature;

        $filters = [];
        if ($newsletter->projects) {
            array_push($filters, [
                'title' => trans(\Locales::getNamespace() . '/multiselect.newsletterFilters.projects'),
                'values' => implode(', ', Project::withTranslation()->select('project_translations.name')->leftJoin('project_translations', 'project_translations.project_id', '=', 'projects.id')->where('project_translations.locale', \Locales::getCurrent())->orderBy('project_translations.name')->whereIn('projects.id', explode(',', $newsletter->projects))->pluck('name')->toArray()),
            ]);
        }

        if ($newsletter->buildings) {
            array_push($filters, [
                'title' => trans(\Locales::getNamespace() . '/multiselect.newsletterFilters.buildings'),
                'values' => implode(', ', Building::withTranslation()->select('building_translations.name')->leftJoin('building_translations', 'building_translations.building_id', '=', 'buildings.id')->where('building_translations.locale', \Locales::getCurrent())->orderBy('building_translations.name')->whereIn('buildings.id', explode(',', $newsletter->buildings))->pluck('name')->toArray()),
            ]);
        }

        if ($newsletter->floors) {
            array_push($filters, [
                'title' => trans(\Locales::getNamespace() . '/multiselect.newsletterFilters.floors'),
                'values' => implode(', ', Floor::withTranslation()->select('floor_translations.name')->leftJoin('floor_translations', 'floor_translations.floor_id', '=', 'floors.id')->where('floor_translations.locale', \Locales::getCurrent())->orderBy('floor_translations.name')->whereIn('floors.id', explode(',', $newsletter->floors))->pluck('name')->toArray()),
            ]);
        }

        if ($newsletter->rooms) {
            array_push($filters, [
                'title' => trans(\Locales::getNamespace() . '/multiselect.newsletterFilters.rooms'),
                'values' => implode(', ', Room::withTranslation()->select('room_translations.name')->leftJoin('room_translations', 'room_translations.room_id', '=', 'rooms.id')->where('room_translations.locale', \Locales::getCurrent())->orderBy('room_translations.name')->whereIn('rooms.id', explode(',', $newsletter->rooms))->pluck('name')->toArray()),
            ]);
        }

        if ($newsletter->furniture) {
            array_push($filters, [
                'title' => trans(\Locales::getNamespace() . '/multiselect.newsletterFilters.furniture'),
                'values' => implode(', ', Furniture::withTranslation()->select('furniture_translations.name')->leftJoin('furniture_translations', 'furniture_translations.furniture_id', '=', 'furniture.id')->where('furniture_translations.locale', \Locales::getCurrent())->orderBy('furniture_translations.name')->whereIn('furniture.id', explode(',', $newsletter->furniture))->pluck('name')->toArray()),
            ]);
        }

        if ($newsletter->views) {
            array_push($filters, [
                'title' => trans(\Locales::getNamespace() . '/multiselect.newsletterFilters.views'),
                'values' => implode(', ', View::withTranslation()->select('view_translations.name')->leftJoin('view_translations', 'view_translations.view_id', '=', 'views.id')->where('view_translations.locale', \Locales::getCurrent())->orderBy('view_translations.name')->whereIn('views.id', explode(',', $newsletter->views))->pluck('name')->toArray()),
            ]);
        }

        if ($newsletter->apartments) {
            array_push($filters, [
                'title' => trans(\Locales::getNamespace() . '/multiselect.newsletterFilters.apartments'),
                'values' => implode(', ', Apartment::select('number')->orderBy('number')->whereIn('id', explode(',', $newsletter->apartments))->pluck('number')->toArray()),
            ]);
        }

        if ($newsletter->owners) {
            array_push($filters, [
                'title' => trans(\Locales::getNamespace() . '/multiselect.newsletterFilters.owners'),
                'values' => implode(', ', Owner::selectRaw('CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as name')->orderBy('name')->whereIn('id', explode(',', $newsletter->owners))->pluck('name')->toArray()),
            ]);
        }

        if ($newsletter->countries) {
            array_push($filters, [
                'title' => trans(\Locales::getNamespace() . '/multiselect.newsletterFilters.countries'),
                'values' => implode(', ', Country::withTranslation()->select('country_translations.name')->leftJoin('country_translations', 'country_translations.country_id', '=', 'countries.id')->where('country_translations.locale', \Locales::getCurrent())->orderBy('country_translations.name')->whereIn('countries.id', explode(',', $newsletter->countries))->pluck('name')->toArray()),
            ]);
        }

        if ($newsletter->recipients) {
            array_push($filters, [
                'title' => trans(\Locales::getNamespace() . '/multiselect.newsletterFilters.recipients'),
                'values' => implode(', ', array_map(function ($key) { return trans(\Locales::getNamespace() . '/forms.newsletterRecipients.' . $key); }, explode(',', $newsletter->recipients))),
            ]);
        }

        if ($newsletter->locale_id) {
            array_push($filters, [
                'title' => trans(\Locales::getNamespace() . '/multiselect.newsletterFilters.language'),
                'values' => $newsletter->locale->name,
            ]);
        }

        if ($newsletter->year_id) {
            array_push($filters, [
                'title' => trans(\Locales::getNamespace() . '/multiselect.newsletterFilters.year'),
                'values' => $newsletter->year->year,
            ]);
        }

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.preview', compact('newsletter', 'filters', 'language'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function test(Request $request, NewsletterService $newsletterService, $id)
    {
        set_time_limit(300); // 5 mins.

        $newsletter = Newsletter::where('id', $id)->whereNull('sent_at')->firstOrFail();

        $language = \Locales::getPublicLocales()->filter(function($value, $key) use ($newsletter) {
            return $value->id == $newsletter->locale_id;
        })->first()->locale;

        $body = $newsletterService->replaceHtml($newsletter->body);

        if (in_array($newsletter->template, ['newsletter-mm-fees', 'newsletter-mm-fees-first-reminder', 'newsletter-mm-fees-second-reminder', 'newsletter-mm-fees-final-reminder'])) {
            $apartments = Apartment::select('id', 'mm_tax_formula', 'apartment_area', 'common_area', 'balcony_area', 'extra_balcony_area', 'total_area', 'building_id', 'room_id')
            ->with(['buildingMM' => function ($query) use ($newsletter) {
                // $query->where('building_mm.year_id', $newsletter->year->id); // I need all BuildingMM years, not just the selected one!!!
            }, 'mmFeesPayments', 'contracts' => function ($query) use ($newsletter) {
                if ($newsletter->year->year < date('Y')) {
                    $query->withTrashed();
                }
            }, 'contracts.contractYears' => function ($query) use ($newsletter) {
                $query->where('contract_years.year', $newsletter->year->year);

                if ($newsletter->year->year < date('Y')) {
                    $query->withTrashed();
                }
            }, 'contracts.rentalContract' => function ($query) {
                $query->withTrashed();
            }, 'rooms'])
            ->groupBy('id')
            ->get();

            $apartmentsMM = DataTable::calculateMmFees($apartments, 'due-by-owner', $newsletter->year->year)->keyBy('id');
            // dd($apartmentsMM);

            if (!$apartmentsMM->count()) {
                return response()->json([
                    'errors' => [trans(\Locales::getNamespace() . '/forms.noApartmentsError')],
                ]);
            }

            $owners = Owner::selectRaw('apartments.id as apartment, apartments.number, owners.id, owners.email, owners.first_name, owners.last_name, CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as full_name, building_translations.name AS building')
            ->join('ownership', 'ownership.owner_id', '=', 'owners.id')
            ->join('apartments', 'apartments.id', '=', 'ownership.apartment_id')
            ->leftJoin('building_translations', function ($join) use ($language) {
                $join->on('building_translations.building_id', '=', 'apartments.building_id')->where('building_translations.locale', '=', $language);
            })
            ->whereNotExists(function($query) use ($newsletter) {
                $query->from('newsletter_archive')->whereRaw('newsletter_archive.owner_id = owners.id AND newsletter_archive.apartment_id = apartments.id')->where('newsletter_archive.newsletter_id', $newsletter->id);
            })->whereNull('ownership.deleted_at')->where('owners.is_active', 1)->whereNotNull('owners.email')->whereIn('apartments.id', $apartmentsMM->pluck('id'));
        } elseif ($newsletter->template == 'newsletter-mm-payment-confirmation') {
            $apartments = Apartment::select('id', 'mm_tax_formula', 'apartment_area', 'common_area', 'balcony_area', 'extra_balcony_area', 'total_area', 'building_id', 'room_id')
            ->with(['buildingMM' => function ($query) use ($newsletter) {
                // $query->where('building_mm.year_id', $newsletter->year->id); // I need all BuildingMM years, not just the selected one!!!
            }, 'mmFeesPayments', 'contracts' => function ($query) use ($newsletter) {
                if ($newsletter->year->year < date('Y')) {
                    $query->withTrashed();
                }
            }, 'contracts.contractYears' => function ($query) use ($newsletter) {
                $query->where('contract_years.year', $newsletter->year->year);

                if ($newsletter->year->year < date('Y')) {
                    $query->withTrashed();
                }
            }, 'contracts.rentalContract' => function ($query) {
                $query->withTrashed();
            }, 'rooms'])
            ->groupBy('id')
            ->get();

            $apartmentsMM = DataTable::calculateMmFees($apartments, 'paid-by-rental', $newsletter->year->year)->keyBy('id');
            // dd($apartmentsMM);
            if (!$apartmentsMM->count()) {
                return response()->json([
                    'errors' => [trans(\Locales::getNamespace() . '/forms.noApartmentsError')],
                ]);
            }

            $owners = Owner::selectRaw('apartments.id as apartment, apartments.number, owners.id, owners.email, owners.first_name, owners.last_name, CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as full_name, building_translations.name AS building')
            ->join('ownership', 'ownership.owner_id', '=', 'owners.id')
            ->join('apartments', 'apartments.id', '=', 'ownership.apartment_id')
            ->leftJoin('building_translations', function ($join) use ($language) {
                $join->on('building_translations.building_id', '=', 'apartments.building_id')->where('building_translations.locale', '=', $language);
            })
            ->whereNotExists(function ($query) use ($newsletter) {
                $query->from('newsletter_archive')->whereRaw('newsletter_archive.owner_id = owners.id AND newsletter_archive.apartment_id = apartments.id')->where('newsletter_archive.newsletter_id', $newsletter->id);
            })->whereNull('ownership.deleted_at')->where('owners.is_active', 1)->whereNotNull('owners.email')->whereIn('apartments.id', $apartmentsMM->pluck('id'));
        } elseif ($newsletter->template == 'newsletter-bank-account-details') {
            $apartments = Apartment::with(['mmFeesPayments', 'buildingMM' => function ($query) use ($newsletter) {
                // $query->where('building_mm.year_id', $newsletter->year->id); // I need all BuildingMM years, not just the selected one!!!
            }, 'contracts' => function ($query) use ($newsletter) {
                if ($newsletter->year->year < date('Y')) {
                    $query->withTrashed();
                }
            }, 'contracts.contractYears' => function ($query) use ($newsletter) {
                $query->where('contract_years.year', $newsletter->year->year);

                if ($newsletter->year->year < date('Y')) {
                    $query->withTrashed();
                }
            }, 'contracts.rentalContract' => function ($query) {
                $query->withTrashed()->withTranslation();
            }, 'contracts.contractYears.payments', 'contracts.contractYears.deductions', 'owners' => function ($query) use ($newsletter) {
                if ($newsletter->year->year < date('Y')) {
                    $query->withTrashed();
                }
            }, 'rooms'])->selectRaw('apartments.id, apartments.mm_tax_formula, apartments.apartment_area, apartments.common_area, apartments.balcony_area, apartments.extra_balcony_area, apartments.total_area, apartments.building_id, apartments.room_id, project_translations.slug AS projectSlug, room_translations.slug AS roomSlug, view_translations.slug AS viewSlug')->leftJoin('project_translations', function($join) use ($language) {
                $join->on('project_translations.project_id', '=', 'apartments.project_id')->where('project_translations.locale', '=', $language);
            })->leftJoin('room_translations', function($join) use ($language) {
                $join->on('room_translations.room_id', '=', 'apartments.room_id')->where('room_translations.locale', '=', $language);
            })->leftJoin('view_translations', function($join) use ($language) {
                $join->on('view_translations.view_id', '=', 'apartments.view_id')->where('view_translations.locale', '=', $language);
            })->get();

            $apartmentsMM = DataTable::calculateRentalOptions($apartments, $newsletter->year->year, 'rental', 'due')->keyBy('id');
            // dd($apartmentsMM);

            $owners = Owner::selectRaw('apartments.id as apartment, apartments.number, owners.id, owners.email, owners.first_name, owners.last_name, CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as full_name, building_translations.name AS building, bank_accounts.bank_iban, bank_accounts.bank_bic, bank_accounts.bank_name, bank_accounts.bank_beneficiary')
            ->join('ownership', 'ownership.owner_id', '=', 'owners.id')
            ->leftJoin('bank_accounts', 'bank_accounts.id', '=', 'ownership.bank_account_id')
            ->join('apartments', 'apartments.id', '=', 'ownership.apartment_id')
            ->leftJoin('building_translations', function ($join) use ($language) {
                $join->on('building_translations.building_id', '=', 'apartments.building_id')->where('building_translations.locale', '=', $language);
            })
            ->whereNotExists(function ($query) use ($newsletter) {
                $query->from('newsletter_archive')->whereRaw('newsletter_archive.owner_id = owners.id AND newsletter_archive.apartment_id = apartments.id')->where('newsletter_archive.newsletter_id', $newsletter->id);
            })->whereNull('ownership.deleted_at')->where('owners.is_active', 1)->whereNotNull('owners.email')->whereIn('apartments.id', $apartmentsMM->pluck('id'))->where(function ($query) {
                $query->whereNull('bank_accounts.rental')->orWhere('bank_accounts.rental', '>', 0);
            })->groupBy('apartments.id');
        } elseif ($newsletter->template == 'newsletter-occupancy') {
            // $apartmentsMM = collect();
            // $rentalRatesIds = RentalContract::select('rental_contracts.id')->leftJoin('contracts', 'rental_contracts.id', '=', 'contracts.rental_contract_id')->leftJoin('contract_years', 'contracts.id', '=', 'contract_years.contract_id')->where('rental_contracts.rental_payment_id', 9)->where('contract_years.year', $newsletter->year->year)->distinct()->get()->pluck('id')->toArray();
            // if ($rentalRatesIds) {
                $apartments = Apartment::with(['mmFeesPayments', 'buildingMM' => function ($query) use ($newsletter) {
                    // $query->where('building_mm.year_id', $newsletter->year->id); // I need all BuildingMM years, not just the selected one!!!
                }, 'contracts' => function ($query) use ($newsletter) {
                    if ($newsletter->year->year < date('Y')) {
                        $query->withTrashed();
                    }
                }, 'contracts.contractYears' => function ($query) use ($newsletter) {
                    $query->where('contract_years.year', $newsletter->year->year);

                    if ($newsletter->year->year < date('Y')) {
                        $query->withTrashed();
                    }
                }, 'contracts.rentalContract' => function ($query) {
                    $query->withTrashed()->withTranslation();
                }, 'contracts.contractYears.payments', 'contracts.contractYears.deductions', 'owners' => function ($query) use ($newsletter) {
                    if ($newsletter->year->year < date('Y')) {
                        $query->withTrashed();
                    }
                }, 'rooms'])->selectRaw('apartments.id, apartments.mm_tax_formula, apartments.apartment_area, apartments.common_area, apartments.balcony_area, apartments.extra_balcony_area, apartments.total_area, apartments.building_id, apartments.room_id, project_translations.slug AS projectSlug, room_translations.slug AS roomSlug, view_translations.slug AS viewSlug')->leftJoin('project_translations', function($join) use ($language) {
                    $join->on('project_translations.project_id', '=', 'apartments.project_id')->where('project_translations.locale', '=', $language);
                })->leftJoin('room_translations', function($join) use ($language) {
                    $join->on('room_translations.room_id', '=', 'apartments.room_id')->where('room_translations.locale', '=', $language);
                })->leftJoin('view_translations', function($join) use ($language) {
                    $join->on('view_translations.view_id', '=', 'apartments.view_id')->where('view_translations.locale', '=', $language);
                })->get();

                $apartmentsMM = DataTable::calculateRentalOptions($apartments, $newsletter->year->year, 'rental'/*$rentalRatesIds*/, 'due')->keyBy('id');
                // dd($apartmentsMM);
            // }

            $owners = Owner::selectRaw('apartments.id as apartment, apartments.number, owners.id, owners.email, owners.first_name, owners.last_name, CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as full_name')
            ->join('ownership', 'ownership.owner_id', '=', 'owners.id')
            ->join('apartments', 'apartments.id', '=', 'ownership.apartment_id')
            ->whereNotExists(function ($query) use ($newsletter) {
                $query->from('newsletter_archive')->whereRaw('newsletter_archive.owner_id = owners.id AND newsletter_archive.apartment_id = apartments.id')->where('newsletter_archive.newsletter_id', $newsletter->id);
            })->whereNull('ownership.deleted_at')->where('owners.is_active', 1)->whereNotNull('owners.email')->whereIn('apartments.id', $apartmentsMM->pluck('id'));
        } elseif (in_array($newsletter->template, ['newsletter-rental-payment-confirmation', 'newsletter-rental-payment-income-tax-only'])) {
            // $apartmentsMM = collect();
            // $rentalRatesIds = RentalContract::select('rental_contracts.id')->leftJoin('contracts', 'rental_contracts.id', '=', 'contracts.rental_contract_id')->leftJoin('contract_years', 'contracts.id', '=', 'contract_years.contract_id')->where('rental_contracts.rental_payment_id', 9)->where('contract_years.year', $newsletter->year->year)->distinct()->get()->pluck('id')->toArray();
            // if ($rentalRatesIds) {
                $apartments = Apartment::with(['mmFeesPayments', 'buildingMM' => function ($query) use ($newsletter) {
                    // $query->where('building_mm.year_id', $newsletter->year->id); // I need all BuildingMM years, not just the selected one!!!
                }, 'contracts' => function ($query) use ($newsletter) {
                    if ($newsletter->year->year < date('Y')) {
                        $query->withTrashed();
                    }
                }, 'contracts.contractYears' => function ($query) use ($newsletter) {
                    $query->where('contract_years.year', $newsletter->year->year);

                    if ($newsletter->year->year < date('Y')) {
                        $query->withTrashed();
                    }
                }, 'contracts.rentalContract' => function ($query) {
                    $query->withTrashed()->withTranslation();
                }, 'contracts.contractYears.payments', 'contracts.contractYears.deductions', 'owners' => function ($query) use ($newsletter) {
                    if ($newsletter->year->year < date('Y')) {
                        $query->withTrashed();
                    }
                }, 'rooms'])->selectRaw('apartments.id, apartments.number, apartments.mm_tax_formula, apartments.apartment_area, apartments.common_area, apartments.balcony_area, apartments.extra_balcony_area, apartments.total_area, apartments.building_id, apartments.room_id, project_translations.slug AS projectSlug, room_translations.slug AS roomSlug, view_translations.slug AS viewSlug')->leftJoin('project_translations', function($join) use ($language) {
                    $join->on('project_translations.project_id', '=', 'apartments.project_id')->where('project_translations.locale', '=', $language);
                })->leftJoin('room_translations', function($join) use ($language) {
                    $join->on('room_translations.room_id', '=', 'apartments.room_id')->where('room_translations.locale', '=', $language);
                })->leftJoin('view_translations', function($join) use ($language) {
                    $join->on('view_translations.view_id', '=', 'apartments.view_id')->where('view_translations.locale', '=', $language);
                })->get();

                $apartmentsMM = DataTable::calculateRentalOptions($apartments, $newsletter->year->year, 'rental'/*$rentalRatesIds*/, 'paid', null, false)->keyBy('id');
                if ($newsletter->template == 'newsletter-rental-payment-confirmation') {
                    $apartmentsMM = $apartmentsMM->filter(function ($item) { return $item->payments > 0; });
                } elseif ($newsletter->template == 'newsletter-rental-payment-income-tax-only') {
                    $apartmentsMM = $apartmentsMM->filter(function ($item) { return $item->payments <= 0; });
                }
                // dd($apartmentsMM);
            // }

            $owners = Owner::selectRaw('apartments.id as apartment, apartments.number, owners.id, owners.email, owners.first_name, owners.last_name, CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as full_name')
            ->join('ownership', 'ownership.owner_id', '=', 'owners.id')
            ->join('apartments', 'apartments.id', '=', 'ownership.apartment_id')
            ->whereNotExists(function ($query) use ($newsletter) {
                $query->from('newsletter_archive')->whereRaw('newsletter_archive.owner_id = owners.id AND newsletter_archive.apartment_id = apartments.id')->where('newsletter_archive.newsletter_id', $newsletter->id);
            })->whereNull('ownership.deleted_at')->where('owners.is_active', 1)->whereNotNull('owners.email')->whereIn('apartments.id', $apartmentsMM->pluck('id'));
        } elseif ($newsletter->template == 'newsletter-council-tax-letter') {
            $owners = Owner::selectRaw('apartments.id as apartment, apartments.number, owners.id, owners.bulstat, owners.tax_pin, owners.email, owners.first_name, owners.last_name, CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as full_name, council_tax.tax, council_tax.checked_at')
            ->join('ownership', 'ownership.owner_id', '=', 'owners.id')
            ->join('apartments', 'apartments.id', '=', 'ownership.apartment_id')
            ->join('council_tax', function ($join) {
                $join->on('owners.id', '=', 'council_tax.owner_id')->on('apartments.id', '=', 'council_tax.apartment_id');
            })
            ->whereNotExists(function ($query) use ($newsletter) {
                $query->from('newsletter_archive')->whereRaw('newsletter_archive.owner_id = owners.id AND newsletter_archive.apartment_id = apartments.id')->where('newsletter_archive.newsletter_id', $newsletter->id);
            })->whereNull('ownership.deleted_at')->where('owners.is_active', 1)->whereNotNull('owners.email');
        } else {
            $select = 'GROUP_CONCAT(apartments.number SEPARATOR ",") AS apartments, owners.id, owners.email, owners.first_name, owners.last_name, CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as full_name';
            if (strpos($body, '{OWNER_PASSWORD}') !== false) {
                $select .= ', temp_password';
            }

            $owners = Owner::distinct()->selectRaw($select)->join('ownership', 'ownership.owner_id', '=', 'owners.id')->join('apartments', 'apartments.id', '=', 'ownership.apartment_id')->whereNotExists(function ($query) use ($newsletter) {
                $query->from('newsletter_archive')->whereRaw('newsletter_archive.owner_id = owners.id AND newsletter_archive.apartment_id = apartments.id')->where('newsletter_archive.newsletter_id', $newsletter->id);
            })->whereNull('ownership.deleted_at')->where('owners.is_active', 1)->whereNotNull('owners.email');
        }

        if ($newsletter->projects) {
            $owners = $owners->whereIn('apartments.project_id', explode(',', $newsletter->projects));
        }

        if ($newsletter->buildings) {
            $owners = $owners->whereIn('apartments.building_id', explode(',', $newsletter->buildings));
        }

        if ($newsletter->floors) {
            $owners = $owners->whereIn('apartments.floor_id', explode(',', $newsletter->floors));
        }

        if ($newsletter->rooms) {
            $owners = $owners->whereIn('apartments.room_id', explode(',', $newsletter->rooms));
        }

        if ($newsletter->furniture) {
            $owners = $owners->whereIn('apartments.furniture_id', explode(',', $newsletter->furniture));
        }

        if ($newsletter->views) {
            $owners = $owners->whereIn('apartments.view_id', explode(',', $newsletter->views));
        }

        if ($newsletter->apartments) {
            $owners = $owners->whereIn('apartments.id', explode(',', $newsletter->apartments));
        }

        if ($newsletter->owners) {
            $owners = $owners->whereIn('owners.id', explode(',', $newsletter->owners));
        }

        if ($newsletter->countries) {
            $owners = $owners->whereIn('owners.country_id', explode(',', $newsletter->countries));
        }

        if ($newsletter->locale_id) {
            $owners = $owners->where('owners.locale_id', $newsletter->locale_id);
        }

        $merge = [];
        if ($newsletter->merge_by) {
            $merge = $newsletter->merge;

            $apartments = explode('|', $merge->first()->merge);
            $owners = $owners->whereIn('apartments.number', $apartments)->orderByRaw('FIELD(apartments.number,\'' . implode("','", $apartments) . '\')');

            if ($newsletter->merge_by == 2) { // owners
                $owners = $owners->whereIn('owners.id', explode('|', $merge->get(1)->merge));
            }
        }

        if (in_array($newsletter->template, ['newsletter-council-tax-letter', 'newsletter-bank-account-details', 'newsletter-mm-payment-confirmation', 'newsletter-mm-fees', 'newsletter-mm-fees-first-reminder', 'newsletter-mm-fees-second-reminder', 'newsletter-mm-fees-final-reminder', 'newsletter-occupancy', 'newsletter-rental-payment-confirmation', 'newsletter-rental-payment-income-tax-only'])) {
            $owner = $owners->first();
        } else {
            $owner = $owners->orderBy('apartments.number')->groupBy('owners.id')->first();
        }

        if ($owners->count()) {
            $directory = public_path('upload') . DIRECTORY_SEPARATOR . $this->uploadDirectory . DIRECTORY_SEPARATOR . $newsletter->id . DIRECTORY_SEPARATOR;

            $attachments = [];
            foreach ($newsletter->attachments as $attachment) {
                array_push($attachments, $directory . \Config::get('upload.attachmentsDirectory') . DIRECTORY_SEPARATOR . $attachment->uuid . DIRECTORY_SEPARATOR . $attachment->file);
            }

            if ($newsletter->merge_by) {
                if ($newsletter->attachmentsApartment->count()) {
                    $apartments = explode(',', Str::lower($owner->apartments));
                    $attachmentsApartment = $newsletter->attachmentsApartment->filter(function ($value, $key) use ($apartments) {
                        return in_array(File::name($value->file), $apartments);
                    });

                    foreach ($attachmentsApartment as $attachment) {
                        array_push($attachments, $directory . \Config::get('upload.attachmentsDirectory') . '-apartment' . DIRECTORY_SEPARATOR . $attachment->uuid . DIRECTORY_SEPARATOR . $attachment->file);
                    }
                }

                if ($newsletter->attachmentsOwner->count()) {
                    $apartments = array_map(function($number) use ($owner) {
                        return $number . '-' . $owner->id;
                    }, explode(',', Str::lower($owner->apartments)));

                    $attachmentsOwner = $newsletter->attachmentsOwner->filter(function ($value, $key) use ($apartments) {
                        return in_array(File::name($value->file), $apartments);
                    });

                    foreach ($attachmentsOwner as $attachment) {
                        array_push($attachments, $directory . \Config::get('upload.attachmentsDirectory') . '-owner' . DIRECTORY_SEPARATOR . $attachment->uuid . DIRECTORY_SEPARATOR . $attachment->file);
                    }
                }

                $body = preg_replace('/{MERGE_APARTMENT}/', '<span style="background-color: #ff0;">' . explode('|', $merge->first()->merge)[0] . '</span>', $body);
                if ($newsletter->merge_by == 2) { // owners
                    $body = preg_replace('/{MERGE_OWNER}/', '<span style="background-color: #ff0;">' . $owner->full_name . '</span>', $body);
                }

                foreach ($merge->slice($newsletter->merge_by) as $key => $value) {
                    $value = explode('|', $value->merge)[0];
                    $body = preg_replace('/{MERGE}/', '<span style="background-color: #ff0;">' . $value . '</span>', $body, 1);
                }
            }

            foreach ($newsletterService->patterns() as $key => $pattern) {
                if (strpos($body, $pattern) !== false) {
                    $body = preg_replace('/' . $pattern . '/', '<span style="background-color: #ff0;">%recipient.' . $newsletterService->columns()[$key] . '%</span>', $body);
                }
            }

            if (in_array($newsletter->template, ['newsletter-mm-fees', 'newsletter-mm-fees-first-reminder', 'newsletter-mm-fees-second-reminder', 'newsletter-mm-fees-final-reminder', 'newsletter-mm-payment-confirmation'])) {
                $cleanAmount = ceil((float) str_replace(',', '', $apartmentsMM[$owner->apartment]->amount) * 1.95583);
                $amount = number_format($cleanAmount, 2);
                $body = preg_replace('/{MERGE_APARTMENT}/', '<span style="background-color: #ff0;">' . $owner->number . '</span>', $body);
                $body = preg_replace('/\[\[MERGE_BLOCK\]\]/', '<span style="background-color: #ff0;">' . $owner->building . '</span>', $body);
                $body = preg_replace('/\[\[MERGE_YEAR\]\]/', '<span style="background-color: #ff0;">' . $apartmentsMM[$owner->apartment]->mm_for_year_name . '</span>', $body);
                $body = preg_replace('/\[\[MERGE_DUE_YEAR\]\]/', '<span style="background-color: #ff0;">' . substr($apartmentsMM[$owner->apartment]->buildingMM->where('year_id', $newsletter->year->id)->first()->deadline_at, -4) . '</span>', $body);
                $body = preg_replace('/\[\[MERGE_MM_AMOUNT\]\]/', '<span style="background-color: #ff0;">' . $amount . '</span>', $body);
                $body = preg_replace('/\[\[MERGE_MM_FEE\]\]/', '<span style="background-color: #ff0;">' . number_format($apartmentsMM[$owner->apartment]->buildingMM->where('year_id', $newsletter->year->id)->first()->mm_tax, 2) . '</span>', $body);

                if ($newsletter->template == 'newsletter-mm-fees-final-reminder') {
                    $body = preg_replace('/\[\[MERGE_INTEREST\]\]/', '<span style="background-color: #ff0;">' . number_format($cleanAmount * 1.4 - $cleanAmount, 2) . '</span>', $body);
                    $body = preg_replace('/\[\[MERGE_TOTAL\]\]/', '<span style="background-color: #ff0;">' . number_format($cleanAmount * 1.4, 2) . '</span>', $body);
                }
            } elseif (in_array($newsletter->template, ['newsletter-rental-payment-confirmation', 'newsletter-rental-payment-income-tax-only'])) {
                $body = preg_replace('/{MERGE_APARTMENT}/', '<span style="background-color: #ff0;">' . $owner->number . '</span>', $body);
                $body = preg_replace('/\[\[MERGE_BLOCK\]\]/', '<span style="background-color: #ff0;">' . $owner->building . '</span>', $body);
                $body = preg_replace('/\[\[MERGE_YEAR\]\]/', '<span style="background-color: #ff0;">' . $newsletter->year->year . '</span>', $body);
                $body = preg_replace('/\[\[MERGE_AMOUNT\]\]/', '<span style="background-color: #ff0;">' . ($newsletter->template == 'newsletter-rental-payment-confirmation' ? $apartmentsMM[$owner->apartment]->paymentsValue : number_format($apartmentsMM[$owner->apartment]->rentAmount - $apartmentsMM[$owner->apartment]->tax, 2)) . '</span>', $body);
                $body = preg_replace('/\[\[MERGE_WT_AMOUNT\]\]/', '<span style="background-color: #ff0;">' . $apartmentsMM[$owner->apartment]->taxValue . '</span>', $body);
            } elseif ($newsletter->template == 'newsletter-bank-account-details') {
                $body = preg_replace('/{MERGE_APARTMENT}/', '<span style="background-color: #ff0;">' . $owner->number . '</span>', $body);
                $body = preg_replace('/\[\[MERGE_BANK\]\]/', '<span style="background-color: #ff0;">' . $owner->bank_name . '</span>', $body);
                $body = preg_replace('/\[\[MERGE_BENEFICIARY\]\]/', '<span style="background-color: #ff0;">' . $owner->bank_beneficiary . '</span>', $body);
                $body = preg_replace('/\[\[MERGE_IBAN\]\]/', '<span style="background-color: #ff0;">' . $owner->bank_iban . '</span>', $body);
                $body = preg_replace('/\[\[MERGE_BIC\]\]/', '<span style="background-color: #ff0;">' . $owner->bank_bic . '</span>', $body);
            } elseif ($newsletter->template == 'newsletter-occupancy') {
                $apartment = $apartmentsMM[$owner->apartment];

                $body = preg_replace('/{MERGE_APARTMENT}/', '<span style="background-color: #ff0;">' . $owner->number . '</span>', $body);
                $body = preg_replace('/\[\[MERGE_YEAR\]\]/', '<span style="background-color: #ff0;">' . $newsletter->year->year . '</span>', $body);

                $rentAmount = 0;
                $rows = '';
                $rentalRates = RentalRatesPeriod::with('rates')->whereYear('dfrom', '=', $newsletter->year->year)->get();
                foreach ($rentalRates as $period) {
                    $nights = KeyLog::whereBetween('occupied_at', [Carbon::parse($period->dfrom), Carbon::parse($period->dto)])->where('apartment_id', $owner->apartment)->count();

                    if ($period->type == 'personal-usage') {
                        $nights = $nights - 53; // personal usage period
                        $body = preg_replace('/\[\[MERGE_PERSONAL_USAGE_PERIOD\]\]/', '<span style="background-color: #ff0;">' . $period->dfrom . ' - ' . $period->dto . '</span>', $body);
                    }

                    if ($nights < 0) {
                        $nights = 0;
                    }

                    $rates = $period->rates->where('project', $apartment->projectSlug)->where('room', $apartment->roomSlug)->where('view', $apartment->viewSlug)->first();
                    if ($rates) {
                        $rate = $rates->rate;
                        if ($nights > 0) {
                            $rentAmount += $nights * $rate;
                        }
                    } else {
                        $rate = 0;
                    }

                    $rows .= '<tr>
                        <td class="text-left" style="border-color:rgb(221, 221, 221); vertical-align:middle">' . $period->dfrom . ' - ' . $period->dto . ($period->type == 'personal-usage' ? ' *' : '') . '</td>
                        <td class="text-right" style="border-color:rgb(221, 221, 221); vertical-align:middle">&euro; ' . $rate . '</td>
                        <td class="text-right" style="border-color:rgb(221, 221, 221); vertical-align:middle">' . $nights . '</td>
                        <td class="text-right" style="border-color:rgb(221, 221, 221); vertical-align:middle;white-space:nowrap;">&euro; ' . number_format($nights * $rate, 2) . '</td>
                    </tr>';
                }

                $rows .= '<tr>
                    <td colspan="3" class="text-left" style="border-color:rgb(221, 221, 221); vertical-align:middle;font-weight:bold;">' . \Lang::get(\Locales::getPublicNamespace() . '/messages.total', [], $language) . '</td>
                    <td class="text-right" style="border-color:rgb(221, 221, 221); vertical-align:middle;white-space:nowrap;font-weight:bold;">&euro; ' . number_format($rentAmount, 2) . '</td>
                </tr>';

                $body = preg_replace('/<tbody>(.*?)<\/tbody>/s', '<tbody>' . $rows . '</tbody>', $body, 1);
                $body = preg_replace('/\[\[MERGE_MM_FEE\]\]/', '<span style="background-color: #ff0;">' . number_format($apartment->mmFee, 2) . '</span>', $body);
                $body = preg_replace('/\[\[MERGE_RENT_AMOUNT\]\]/', '<span style="background-color: #ff0;">' . number_format($rentAmount, 2) . '</span>', $body);
                $body = preg_replace('/\[\[MERGE_INCOME_AMOUNT\]\]/', '<span style="background-color: #ff0;">' . number_format($apartment->mmFee + $rentAmount, 2) . '</span>', $body);
                $body = preg_replace('/\[\[MERGE_WT\]\]/', '<span style="background-color: #ff0;">' . number_format($apartment->tax, 2) . '</span>', $body);
                $body = preg_replace('/\[\[MERGE_MM_YEAR\]\]/', '<span style="background-color: #ff0;">' . $apartment->mm_for_year . '</span>', $body);
                $body = preg_replace('/\[\[MERGE_DEDUCTIONS\]\]/', '<span style="background-color: #ff0;">' . number_format($apartment->deductions, 2) . '</span>', $body);
                $body = preg_replace('/\[\[MERGE_TOTAL_EXPENDITURE\]\]/', '<span style="background-color: #ff0;">' . number_format($apartment->tax + $apartment->mmFee + $apartment->deductions, 2) . '</span>', $body);
                $body = preg_replace('/\[\[MERGE_NET_RENT\]\]/', '<span style="background-color: #ff0;">' . number_format($apartment->netRent, 2) . '</span>', $body);
            } elseif ($newsletter->template == 'newsletter-council-tax-letter') {
                $total = 0;
                $rows = '';
                $taxes = CouncilTax::selectRaw('owners.bulstat, owners.tax_pin, CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as full_name, council_tax.tax')->leftJoin('owners', 'owners.id', '=', 'council_tax.owner_id')->join('ownership', 'ownership.owner_id', '=', 'owners.id')->where('owners.is_active', 1)->whereNull('ownership.deleted_at')->where('council_tax.apartment_id', $owner->apartment)->where('ownership.apartment_id', $owner->apartment)->get();
                foreach ($taxes as $tax) {
                    $total += $tax->tax;

                    $rows .= '<tr>
                        <td class="text-left" style="border-color:rgb(221, 221, 221); vertical-align:middle">' . $tax->full_name . '</td>
                        <td class="text-center" style="border-color:rgb(221, 221, 221); vertical-align:middle">' . $tax->bulstat . '</td>
                        <td class="text-center" style="border-color:rgb(221, 221, 221); vertical-align:middle">' . $tax->tax_pin . '</td>
                        <td class="text-right" style="border-color:rgb(221, 221, 221); vertical-align:middle;white-space:nowrap;">' . number_format($tax->tax, 2) . ' лв.</td>
                    </tr>';
                }

                $body = preg_replace('/{MERGE_APARTMENT}/', '<span style="background-color: #ff0;">' . $owner->number . '</span>', $body);
                $body = preg_replace('/\[\[MERGE_DATE\]\]/', '<span style="background-color: #ff0;">' . Carbon::parse($owner->checked_at)->format('d.m.Y') . '</span>', $body);
                $body = preg_replace('/\[\[MERGE_TOTAL\]\]/', '<span style="background-color: #ff0;">' . number_format($total, 2) . ' лв.</span>', $body);
                $body = preg_replace('/<tbody>(.*?)<\/tbody>/s', '<tbody>' . $rows . '</tbody>', $body);
            }

            $signature = $newsletter->signature->translate($language)->content;

            $onlineView = \Lang::get(\Locales::getPublicNamespace() . '/newsletters.onlineView', ['url' => 'https://' . env('APP_OWNERS_SUBDOMAIN') . '.' . env('APP_DOMAIN') . '/' . \Locales::getLanguage($language) . 'newsletters/' . $newsletter->id], $language);
            $links = \Lang::get(\Locales::getPublicNamespace() . '/newsletters.links', [], $language);
            $copyright = \Lang::get(\Locales::getPublicNamespace() . '/newsletters.copyright', [], $language);
            $disclaimer = \Lang::get(\Locales::getPublicNamespace() . '/newsletters.disclaimer', [], $language);

            $html = \View::make(\Locales::getNamespace() . '.' . $this->route . '.templates.default', compact('newsletter', 'body', 'signature', 'onlineView', 'links', 'copyright', 'disclaimer'))->render();
            $text = preg_replace('/{IMAGE}/', '', $body);
            $text = $newsletterService->replaceText($text);

            $images = [storage_path('app/images/newsletter-logo.png')];
            foreach ($newsletter->images as $image) {
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

            $directory = public_path('upload') . DIRECTORY_SEPARATOR . 'signatures' . DIRECTORY_SEPARATOR . $newsletter->signature->id . DIRECTORY_SEPARATOR;
            foreach ($newsletter->signature->images as $image) {
                $path = $directory . $image->uuid . DIRECTORY_SEPARATOR . $image->file;
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
                    'from' => $newsletter->signature->translate($language)->name . ' <' . $newsletter->signature->email . '>',
                    'h:Sender' => $newsletter->signature->translate($language)->name . ' <' . $newsletter->signature->email . '>',
                    'to' => $owner->full_name . ' <' . \Auth::user()->email . '>',
                    'subject' => $newsletter->subject,
                    'html' => $html,
                    'text' => $text,
                    'o:tag' => 'owners-newsletter-test',
                    'v:newsletterId' => $newsletter->id,
                    'recipient-variables' => json_encode([\Auth::user()->email => $owner]),
                ],
                [
                    'attachment' => $attachments,
                    'inline' => $images,
                ]
            );

            if ($result->http_response_code == 200) {
                $msg = [
                    'success' => [trans(\Locales::getNamespace() . '/forms.testSentSuccessfully')],
                ];
            } else {
                $msg = [
                    'errors' => [trans(\Locales::getNamespace() . '/forms.testSentError')],
                ];
            }
        } else {
            $msg = [
                'errors' => [trans(\Locales::getNamespace() . '/forms.noOwnersError')],
            ];
        }

        return response()->json($msg);
    }

    public function send(Request $request, NewsletterService $newsletterService, $id)
    {
        set_time_limit(0);

        $newsletter = Newsletter::where('id', $id)->whereNull('sent_at')->firstOrFail();

        $language = \Locales::getPublicLocales()->filter(function($value, $key) use ($newsletter) {
            return $value->id == $newsletter->locale_id;
        })->first()->locale;

        $body = $newsletterService->replaceHtml($newsletter->body);

        $recipients = [];
        if ($newsletter->recipients) {
            $recipients = explode(',', $newsletter->recipients);
        }

        $apartmentsMM = collect();
        if (in_array('mm', $recipients) || in_array($newsletter->template, ['newsletter-mm-fees', 'newsletter-mm-fees-first-reminder', 'newsletter-mm-fees-second-reminder', 'newsletter-mm-fees-final-reminder'])) {
            $apartments = Apartment::select('id', 'mm_tax_formula', 'apartment_area', 'common_area', 'balcony_area', 'extra_balcony_area', 'total_area', 'building_id', 'room_id')
            ->with(['buildingMM' => function ($query) use ($newsletter) {
                // $query->where('building_mm.year_id', $newsletter->year->id); // I need all BuildingMM years, not just the selected one!!!
            }, 'mmFeesPayments', 'contracts' => function ($query) use ($newsletter) {
                if ($newsletter->year->year < date('Y')) {
                    $query->withTrashed();
                }
            }, 'contracts.contractYears' => function ($query) use ($newsletter) {
                $query->where('contract_years.year', $newsletter->year->year);

                if ($newsletter->year->year < date('Y')) {
                    $query->withTrashed();
                }
            }, 'contracts.rentalContract' => function ($query) {
                $query->withTrashed();
            }, 'rooms'])
            ->groupBy('id')
            ->get();

            $apartmentsMM = DataTable::calculateMmFees($apartments, 'due-by-owner', $newsletter->year->year)->keyBy('id');
            // dd($apartmentsMM);

            if (!$apartmentsMM->count()) {
                return response()->json([
                    'errors' => [trans(\Locales::getNamespace() . '/forms.noApartmentsError')],
                ]);
            }
        } elseif (in_array($newsletter->template, ['newsletter-rental-payment-confirmation', 'newsletter-rental-payment-income-tax-only'])) {
            // $apartmentsMM = collect();
            // $rentalRatesIds = RentalContract::select('rental_contracts.id')->leftJoin('contracts', 'rental_contracts.id', '=', 'contracts.rental_contract_id')->leftJoin('contract_years', 'contracts.id', '=', 'contract_years.contract_id')->where('rental_contracts.rental_payment_id', 9)->where('contract_years.year', $newsletter->year->year)->distinct()->get()->pluck('id')->toArray();
            // if ($rentalRatesIds) {
                $apartments = Apartment::with(['mmFeesPayments', 'buildingMM' => function ($query) use ($newsletter) {
                    // $query->where('building_mm.year_id', $newsletter->year->id); // I need all BuildingMM years, not just the selected one!!!
                }, 'contracts' => function ($query) use ($newsletter) {
                    if ($newsletter->year->year < date('Y')) {
                        $query->withTrashed();
                    }
                }, 'contracts.contractYears' => function ($query) use ($newsletter) {
                    $query->where('contract_years.year', $newsletter->year->year);

                    if ($newsletter->year->year < date('Y')) {
                        $query->withTrashed();
                    }
                }, 'contracts.rentalContract' => function ($query) {
                    $query->withTrashed()->withTranslation();
                }, 'contracts.contractYears.payments', 'contracts.contractYears.deductions', 'owners' => function ($query) use ($newsletter) {
                    if ($newsletter->year->year < date('Y')) {
                        $query->withTrashed();
                    }
                }, 'rooms'])->selectRaw('apartments.id, apartments.number, apartments.mm_tax_formula, apartments.apartment_area, apartments.common_area, apartments.balcony_area, apartments.extra_balcony_area, apartments.total_area, apartments.building_id, apartments.room_id, project_translations.slug AS projectSlug, room_translations.slug AS roomSlug, view_translations.slug AS viewSlug')->leftJoin('project_translations', function($join) use ($language) {
                    $join->on('project_translations.project_id', '=', 'apartments.project_id')->where('project_translations.locale', '=', $language);
                })->leftJoin('room_translations', function($join) use ($language) {
                    $join->on('room_translations.room_id', '=', 'apartments.room_id')->where('room_translations.locale', '=', $language);
                })->leftJoin('view_translations', function($join) use ($language) {
                    $join->on('view_translations.view_id', '=', 'apartments.view_id')->where('view_translations.locale', '=', $language);
                })->get();

                $apartmentsMM = DataTable::calculateRentalOptions($apartments, $newsletter->year->year, 'rental'/*$rentalRatesIds*/, 'paid', null, false)->keyBy('id');
                // dd($apartmentsMM->pluck('number'));
                if ($newsletter->template == 'newsletter-rental-payment-confirmation') {
                    $apartmentsMM = $apartmentsMM->filter(function ($item) { return $item->payments > 0; });
                } elseif ($newsletter->template == 'newsletter-rental-payment-income-tax-only') {
                    $apartmentsMM = $apartmentsMM->filter(function ($item) { return $item->payments <= 0; });
                }
                // dd($apartmentsMM->pluck('number'));

                if (!$apartmentsMM->count()) {
                    return response()->json([
                        'errors' => [trans(\Locales::getNamespace() . '/forms.noApartmentsError')],
                    ]);
                }
            // }
        } elseif ($newsletter->template == 'newsletter-bank-account-details') {
            $apartments = Apartment::with(['mmFeesPayments', 'buildingMM' => function ($query) use ($newsletter) {
                // $query->where('building_mm.year_id', $newsletter->year->id); // I need all BuildingMM years, not just the selected one!!!
            }, 'contracts', 'contracts.contractYears' => function ($query) use ($newsletter) {
                $query->where('contract_years.year', $newsletter->year->year);
            }, 'contracts.rentalContract' => function ($query) {
                $query->withTrashed()->withTranslation();
            }, 'contracts.contractYears.payments', 'contracts.contractYears.deductions', 'owners', 'rooms'])->selectRaw('apartments.id, apartments.mm_tax_formula, apartments.apartment_area, apartments.common_area, apartments.balcony_area, apartments.extra_balcony_area, apartments.total_area, apartments.building_id, apartments.room_id, project_translations.slug AS projectSlug, room_translations.slug AS roomSlug, view_translations.slug AS viewSlug')->leftJoin('project_translations', function($join) use ($language) {
                $join->on('project_translations.project_id', '=', 'apartments.project_id')->where('project_translations.locale', '=', $language);
            })->leftJoin('room_translations', function($join) use ($language) {
                $join->on('room_translations.room_id', '=', 'apartments.room_id')->where('room_translations.locale', '=', $language);
            })->leftJoin('view_translations', function($join) use ($language) {
                $join->on('view_translations.view_id', '=', 'apartments.view_id')->where('view_translations.locale', '=', $language);
            })->get();

            $apartmentsMM = DataTable::calculateRentalOptions($apartments, $newsletter->year->year, 'rental', 'due')->keyBy('id');
            // dd($apartmentsMM);

            if (!$apartmentsMM->count()) {
                return response()->json([
                    'errors' => [trans(\Locales::getNamespace() . '/forms.noApartmentsError')],
                ]);
            }
        } elseif ($newsletter->template == 'newsletter-mm-payment-confirmation') {
            $apartments = Apartment::select('id', 'mm_tax_formula', 'apartment_area', 'common_area', 'balcony_area', 'extra_balcony_area', 'total_area', 'building_id', 'room_id')
            ->with(['buildingMM' => function ($query) use ($newsletter) {
                // $query->where('building_mm.year_id', $newsletter->year->id); // I need all BuildingMM years, not just the selected one!!!
            }, 'mmFeesPayments', 'contracts' => function ($query) use ($newsletter) {
                if ($newsletter->year->year < date('Y')) {
                    $query->withTrashed();
                }
            }, 'contracts.contractYears' => function ($query) use ($newsletter) {
                $query->where('contract_years.year', $newsletter->year->year);

                if ($newsletter->year->year < date('Y')) {
                    $query->withTrashed();
                }
            }, 'contracts.rentalContract' => function ($query) {
                $query->withTrashed();
            }, 'rooms'])
            ->groupBy('id')
            ->get();

            $apartmentsMM = DataTable::calculateMmFees($apartments, 'paid-by-rental', $newsletter->year->year)->keyBy('id');
            // dd($apartmentsMM);

            if (!$apartmentsMM->count()) {
                return response()->json([
                    'errors' => [trans(\Locales::getNamespace() . '/forms.noApartmentsError')],
                ]);
            }
        } elseif ($newsletter->template == 'newsletter-occupancy') {
            $rentalRatesIds = RentalContract::select('rental_contracts.id')->leftJoin('contracts', 'rental_contracts.id', '=', 'contracts.rental_contract_id')->leftJoin('contract_years', 'contracts.id', '=', 'contract_years.contract_id')->where('rental_contracts.rental_payment_id', 9)->where('contract_years.year', $newsletter->year->year)->distinct()->get()->pluck('id')->toArray();
            if ($rentalRatesIds) {
                $apartments = Apartment::with(['mmFeesPayments', 'buildingMM' => function ($query) use ($newsletter) {
                    // $query->where('building_mm.year_id', $newsletter->year->id); // I need all BuildingMM years, not just the selected one!!!
                }, 'contracts', 'contracts.contractYears' => function ($query) use ($newsletter) {
                    $query->where('contract_years.year', $newsletter->year->year);
                }, 'contracts.rentalContract' => function ($query) {
                    $query->withTrashed()->withTranslation();
                }, 'contracts.contractYears.payments', 'contracts.contractYears.deductions', 'owners', 'rooms'])->selectRaw('apartments.id, apartments.mm_tax_formula, apartments.apartment_area, apartments.common_area, apartments.balcony_area, apartments.extra_balcony_area, apartments.total_area, apartments.building_id, apartments.room_id, project_translations.slug AS projectSlug, room_translations.slug AS roomSlug, view_translations.slug AS viewSlug')->leftJoin('project_translations', function($join) use ($language) {
                    $join->on('project_translations.project_id', '=', 'apartments.project_id')->where('project_translations.locale', '=', $language);
                })->leftJoin('room_translations', function($join) use ($language) {
                    $join->on('room_translations.room_id', '=', 'apartments.room_id')->where('room_translations.locale', '=', $language);
                })->leftJoin('view_translations', function($join) use ($language) {
                    $join->on('view_translations.view_id', '=', 'apartments.view_id')->where('view_translations.locale', '=', $language);
                })->get();

                $apartmentsMM = DataTable::calculateRentalOptions($apartments, $newsletter->year->year, $rentalRatesIds, 'due')->keyBy('id');
                // dd($apartmentsMM);

                if (!$apartmentsMM->count()) {
                    return response()->json([
                        'errors' => [trans(\Locales::getNamespace() . '/forms.noApartmentsError')],
                    ]);
                }
            }
        }

        if (in_array($newsletter->template, ['newsletter-mm-fees', 'newsletter-mm-fees-first-reminder', 'newsletter-mm-fees-second-reminder', 'newsletter-mm-fees-final-reminder', 'newsletter-mm-payment-confirmation', 'newsletter-rental-payment-confirmation', 'newsletter-rental-payment-income-tax-only'])) {
            $owners = Owner::selectRaw('apartments.id as apartment, apartments.number, owners.id, owners.email, owners.email_cc, owners.first_name, owners.last_name, CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as full_name, building_translations.name AS building')
            ->join('ownership', 'ownership.owner_id', '=', 'owners.id')
            ->join('apartments', 'apartments.id', '=', 'ownership.apartment_id')
            ->leftJoin('building_translations', function ($join) use ($language) {
                $join->on('building_translations.building_id', '=', 'apartments.building_id')->where('building_translations.locale', '=', $language);
            })
            ->whereNotExists(function($query) use ($newsletter) {
                $query->from('newsletter_archive')->whereRaw('newsletter_archive.owner_id = owners.id AND newsletter_archive.apartment_id = apartments.id')->where('newsletter_archive.newsletter_id', $newsletter->id);
            })->whereNull('ownership.deleted_at')->where('owners.is_active', 1)->whereNotNull('owners.email')->whereIn('apartments.id', $apartmentsMM->pluck('id'));
        } elseif ($newsletter->template == 'newsletter-bank-account-details') {
            $owners = Owner::selectRaw('apartments.id as apartment, apartments.number, owners.id, owners.email, owners.email_cc, owners.first_name, owners.last_name, CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as full_name, building_translations.name AS building, bank_accounts.bank_iban, bank_accounts.bank_bic, bank_accounts.bank_name, bank_accounts.bank_beneficiary')
            ->join('ownership', 'ownership.owner_id', '=', 'owners.id')
            ->leftJoin('bank_accounts', 'bank_accounts.id', '=', 'ownership.bank_account_id')
            ->join('apartments', 'apartments.id', '=', 'ownership.apartment_id')
            ->leftJoin('building_translations', function ($join) use ($language) {
                $join->on('building_translations.building_id', '=', 'apartments.building_id')->where('building_translations.locale', '=', $language);
            })
            ->whereNotExists(function ($query) use ($newsletter) {
                $query->from('newsletter_archive')->whereRaw('newsletter_archive.owner_id = owners.id AND newsletter_archive.apartment_id = apartments.id')->where('newsletter_archive.newsletter_id', $newsletter->id);
            })->whereNull('ownership.deleted_at')->where('owners.is_active', 1)->whereNotNull('owners.email')->whereIn('apartments.id', $apartmentsMM->pluck('id'));
        } elseif ($newsletter->template == 'newsletter-occupancy') {
            $owners = Owner::selectRaw('apartments.id as apartment, apartments.number, owners.id, owners.email, owners.first_name, owners.last_name, CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as full_name')
            ->join('ownership', 'ownership.owner_id', '=', 'owners.id')
            ->join('apartments', 'apartments.id', '=', 'ownership.apartment_id')
            ->whereNotExists(function ($query) use ($newsletter) {
                $query->from('newsletter_archive')->whereRaw('newsletter_archive.owner_id = owners.id AND newsletter_archive.apartment_id = apartments.id')->where('newsletter_archive.newsletter_id', $newsletter->id);
            })->whereNull('ownership.deleted_at')->where('owners.is_active', 1)->whereNotNull('owners.email')->whereIn('apartments.id', $apartmentsMM->pluck('id'));
        } elseif ($newsletter->template == 'newsletter-council-tax-letter') {
            $owners = Owner::selectRaw('apartments.id as apartment, apartments.number, owners.id, owners.bulstat, owners.tax_pin, owners.email, owners.email_cc, owners.first_name, owners.last_name, CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as full_name, council_tax.tax, council_tax.checked_at')
            ->join('ownership', 'ownership.owner_id', '=', 'owners.id')
            ->join('apartments', 'apartments.id', '=', 'ownership.apartment_id')
            ->join('council_tax', function($join) {
                $join->on('owners.id', '=', 'council_tax.owner_id')->on('apartments.id', '=', 'council_tax.apartment_id');
            })
            ->whereNotExists(function($query) use ($newsletter) {
                $query->from('newsletter_archive')->whereRaw('newsletter_archive.owner_id = owners.id AND newsletter_archive.apartment_id = apartments.id')->where('newsletter_archive.newsletter_id', $newsletter->id);
            })->whereNull('ownership.deleted_at')->where('owners.is_active', 1)->whereNotNull('owners.email');
        } else {
            $select = 'owners.id, owners.email, owners.email_cc, owners.first_name, owners.last_name, CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as full_name';
            if (strpos($body, '{OWNER_PASSWORD}') !== false) {
                $select .= ', temp_password';
            }

            $owners = Owner::join('ownership', 'ownership.owner_id', '=', 'owners.id')->join('apartments', 'apartments.id', '=', 'ownership.apartment_id')->whereNotExists(function($query) use ($newsletter) {
                $query->from('newsletter_archive')->whereRaw('newsletter_archive.owner_id = owners.id AND newsletter_archive.apartment_id = apartments.id')->where('newsletter_archive.newsletter_id', $newsletter->id);
            })->whereNull('ownership.deleted_at')->whereNull('apartments.deleted_at')->where('owners.is_active', 1)->whereNotNull('owners.email');

            $merge = [];
            if ($newsletter->merge_by) {
                $merge = $newsletter->merge;

                $select .= ', apartments.id as apartment_id, apartments.number';

                $owners = $owners->selectRaw($select)->whereIn('apartments.number', explode('|', $merge->first()->merge));

                if ($newsletter->merge_by == 2) { // owners
                    $owners = $owners->whereIn('owners.id', explode('|', $merge->get(1)->merge));
                }
            } else {
                $owners = $owners->distinct()->selectRaw($select);
            }
        }

        if (!in_array('all', $recipients)) {
            $owners = $owners->where(function ($query) use ($recipients, $apartmentsMM) {
                if (in_array('subscribed', $recipients)) {
                    $query->orWhere('is_subscribed', 1);
                }

                if (in_array('unsubscribed', $recipients)) {
                    $query->orWhere('is_subscribed', 0);
                }

                if (in_array('srioc', $recipients)) {
                    $query->orWhere('srioc', 1);
                } else {
                    $query->orWhere('srioc', 0);
                }

                if (in_array('letting', $recipients)) {
                    $query->orWhere('letting_offer', 0);
                }

                if (in_array('bills', $recipients)) {
                    $query->orWhere('outstanding_bills', 1);
                }

                if (in_array('mm', $recipients)) {
                    $query->orWhereIn('apartments.id', $apartmentsMM->pluck('id'));
                }
            });
        }

        if ($newsletter->projects) {
            $owners = $owners->whereIn('apartments.project_id', explode(',', $newsletter->projects));
        }

        if ($newsletter->buildings) {
            $owners = $owners->whereIn('apartments.building_id', explode(',', $newsletter->buildings));
        }

        if ($newsletter->floors) {
            $owners = $owners->whereIn('apartments.floor_id', explode(',', $newsletter->floors));
        }

        if ($newsletter->rooms) {
            $owners = $owners->whereIn('apartments.room_id', explode(',', $newsletter->rooms));
        }

        if ($newsletter->furniture) {
            $owners = $owners->whereIn('apartments.furniture_id', explode(',', $newsletter->furniture));
        }

        if ($newsletter->views) {
            $owners = $owners->whereIn('apartments.view_id', explode(',', $newsletter->views));
        }

        if ($newsletter->apartments) {
            $owners = $owners->whereIn('apartments.id', explode(',', $newsletter->apartments));
        }

        if ($newsletter->owners) {
            $owners = $owners->whereIn('owners.id', explode(',', $newsletter->owners));
        }

        if ($newsletter->countries) {
            $owners = $owners->whereIn('owners.country_id', explode(',', $newsletter->countries));
        }

        if ($newsletter->locale_id) {
            $owners = $owners->where('owners.locale_id', $newsletter->locale_id);
        }

        $owners = $owners->get();

        if ($owners->count()) {
            $directory = public_path('upload') . DIRECTORY_SEPARATOR . $this->uploadDirectory . DIRECTORY_SEPARATOR . $newsletter->id . DIRECTORY_SEPARATOR;

            $attachments = [];
            foreach ($newsletter->attachments as $attachment) {
                array_push($attachments, $directory . \Config::get('upload.attachmentsDirectory') . DIRECTORY_SEPARATOR . $attachment->uuid . DIRECTORY_SEPARATOR . $attachment->file);
            }

            foreach ($newsletterService->patterns() as $key => $pattern) {
                if (strpos($body, $pattern) !== false) {
                    $body = preg_replace('/' . $pattern . '/', '%recipient.' . $newsletterService->columns()[$key] . '%', $body);
                }
            }

            $signature = $newsletter->signature->translate($language)->content;

            $onlineView = \Lang::get(\Locales::getPublicNamespace() . '/newsletters.onlineView', ['url' => 'https://' . env('APP_OWNERS_SUBDOMAIN') . '.' . env('APP_DOMAIN') . '/' . \Locales::getLanguage($language) . 'newsletters/' . $newsletter->id], $language);
            $links = \Lang::get(\Locales::getPublicNamespace() . '/newsletters.links', [], $language);
            $copyright = \Lang::get(\Locales::getPublicNamespace() . '/newsletters.copyright', [], $language);
            $disclaimer = \Lang::get(\Locales::getPublicNamespace() . '/newsletters.disclaimer', [], $language);

            $html = \View::make(\Locales::getNamespace() . '.' . $this->route . '.templates.default', compact('newsletter', 'body', 'signature', 'onlineView', 'links', 'copyright', 'disclaimer'))->render();
            $text = preg_replace('/{IMAGE}/', '', $body);

            $images = [storage_path('app/images/newsletter-logo.png')];
            foreach ($newsletter->images as $image) {
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

            $directorySignatures = public_path('upload') . DIRECTORY_SEPARATOR . 'signatures' . DIRECTORY_SEPARATOR . $newsletter->signature->id . DIRECTORY_SEPARATOR;
            foreach ($newsletter->signature->images as $image) {
                $path = $directorySignatures . $image->uuid . DIRECTORY_SEPARATOR . $image->file;
                if (strpos($html, '{SIGNATURE}') !== false) {
                    $html = preg_replace('/{SIGNATURE}/', '<img src="cid:' . $image->file . '" class="responsiveImage" style="height:auto !important;max-width:' . \Image::make($path)->width() . 'px !important;" />', $html, 1);
                    array_push($images, $path);
                }
            }

            $error = false;

            $mg = new Mailgun(env('MAILGUN_SECRET'), new \Http\Adapter\Guzzle6\Client()); // , 'bin.mailgun.net'
            // $mg->setApiVersion('7fb5efa5'); // bin.mailgun.net/7fb5efa5
            // $mg->setSslEnabled(false);

            if (in_array($newsletter->template, ['newsletter-mm-fees', 'newsletter-mm-fees-first-reminder', 'newsletter-mm-fees-second-reminder', 'newsletter-mm-fees-final-reminder', 'newsletter-mm-payment-confirmation', 'newsletter-bank-account-details', 'newsletter-rental-payment-confirmation', 'newsletter-rental-payment-income-tax-only'])) {
                foreach ($owners as $owner) {
                    $mergeHtml = $html;
                    $mergeText = $text;
                    $merge = [];

                    if (in_array($newsletter->template, ['newsletter-mm-fees', 'newsletter-mm-fees-first-reminder', 'newsletter-mm-fees-second-reminder', 'newsletter-mm-fees-final-reminder', 'newsletter-mm-payment-confirmation'])) {
                        $cleanAmount = ceil((float) str_replace(',', '', $apartmentsMM[$owner->apartment]->amount) * 1.95583);
                        $amount = number_format($cleanAmount, 2);

                        $mergeHtml = preg_replace('/{MERGE_APARTMENT}/', $owner->number, $mergeHtml);
                        $mergeText = preg_replace('/{MERGE_APARTMENT}/', $owner->number, $mergeText);
                        $merge[] = [
                            'key' => 'MERGE_BLOCK',
                            'value' => $owner->building,
                        ];
                        $mergeHtml = preg_replace('/\[\[MERGE_BLOCK\]\]/', $owner->building, $mergeHtml);
                        $mergeText = preg_replace('/\[\[MERGE_BLOCK\]\]/', $owner->building, $mergeText);
                        $merge[] = [
                            'key' => 'MERGE_YEAR',
                            'value' => $apartmentsMM[$owner->apartment]->mm_for_year_name,
                        ];
                        $mergeHtml = preg_replace('/\[\[MERGE_YEAR\]\]/', $apartmentsMM[$owner->apartment]->mm_for_year_name, $mergeHtml);
                        $mergeText = preg_replace('/\[\[MERGE_YEAR\]\]/', $apartmentsMM[$owner->apartment]->mm_for_year_name, $mergeText);
                        $merge[] = [
                            'key' => 'MERGE_DUE_YEAR',
                            'value' => substr($apartmentsMM[$owner->apartment]->buildingMM->where('year_id', $newsletter->year->id)->first()->deadline_at, -4),
                        ];
                        $mergeHtml = preg_replace('/\[\[MERGE_DUE_YEAR\]\]/', substr($apartmentsMM[$owner->apartment]->buildingMM->where('year_id', $newsletter->year->id)->first()->deadline_at, -4), $mergeHtml);
                        $mergeText = preg_replace('/\[\[MERGE_DUE_YEAR\]\]/', substr($apartmentsMM[$owner->apartment]->buildingMM->where('year_id', $newsletter->year->id)->first()->deadline_at, -4), $mergeText);
                        $merge[] = [
                            'key' => 'MERGE_MM_AMOUNT',
                            'value' => $amount,
                        ];
                        $mergeHtml = preg_replace('/\[\[MERGE_MM_AMOUNT\]\]/', $amount, $mergeHtml);
                        $mergeText = preg_replace('/\[\[MERGE_MM_AMOUNT\]\]/', $amount, $mergeText);
                        $merge[] = [
                            'key' => 'MERGE_MM_FEE',
                            'value' => number_format($apartmentsMM[$owner->apartment]->buildingMM->where('year_id', $newsletter->year->id)->first()->mm_tax, 2),
                        ];
                        $mergeHtml = preg_replace('/\[\[MERGE_MM_FEE\]\]/', number_format($apartmentsMM[$owner->apartment]->buildingMM->where('year_id', $newsletter->year->id)->first()->mm_tax, 2), $mergeHtml);
                        $mergeText = preg_replace('/\[\[MERGE_MM_FEE\]\]/', number_format($apartmentsMM[$owner->apartment]->buildingMM->where('year_id', $newsletter->year->id)->first()->mm_tax, 2), $mergeText);

                        if ($newsletter->template == 'newsletter-mm-fees-final-reminder') {
                            $merge[] = [
                                'key' => 'MERGE_INTEREST',
                                'value' => number_format($cleanAmount * 1.4 - $cleanAmount, 2),
                            ];
                            $mergeHtml = preg_replace('/\[\[MERGE_INTEREST\]\]/', number_format($cleanAmount * 1.4 - $cleanAmount, 2), $mergeHtml);
                            $mergeText = preg_replace('/\[\[MERGE_INTEREST\]\]/', number_format($cleanAmount * 1.4 - $cleanAmount, 2), $mergeText);

                            $merge[] = [
                                'key' => 'MERGE_TOTAL',
                                'value' => number_format($cleanAmount * 1.4, 2),
                            ];
                            $mergeHtml = preg_replace('/\[\[MERGE_TOTAL\]\]/', number_format($cleanAmount * 1.4, 2), $mergeHtml);
                            $mergeText = preg_replace('/\[\[MERGE_TOTAL\]\]/', number_format($cleanAmount * 1.4, 2), $mergeText);
                        }
                    } elseif (in_array($newsletter->template, ['newsletter-rental-payment-confirmation', 'newsletter-rental-payment-income-tax-only'])) {
                        $mergeHtml = preg_replace('/{MERGE_APARTMENT}/', $owner->number, $mergeHtml);
                        $mergeText = preg_replace('/{MERGE_APARTMENT}/', $owner->number, $mergeText);
                        $merge[] = [
                            'key' => 'MERGE_BLOCK',
                            'value' => $owner->building,
                        ];
                        $mergeHtml = preg_replace('/\[\[MERGE_BLOCK\]\]/', $owner->building, $mergeHtml);
                        $mergeText = preg_replace('/\[\[MERGE_BLOCK\]\]/', $owner->building, $mergeText);
                        $merge[] = [
                            'key' => 'MERGE_YEAR',
                            'value' => $newsletter->year->year,
                        ];
                        $mergeHtml = preg_replace('/\[\[MERGE_YEAR\]\]/', $newsletter->year->year, $mergeHtml);
                        $mergeText = preg_replace('/\[\[MERGE_YEAR\]\]/', $newsletter->year->year, $mergeText);
                        $merge[] = [
                            'key' => 'MERGE_AMOUNT',
                            'value' => ($newsletter->template == 'newsletter-rental-payment-confirmation' ? $apartmentsMM[$owner->apartment]->paymentsValue : number_format($apartmentsMM[$owner->apartment]->rentAmount - $apartmentsMM[$owner->apartment]->tax, 2)),
                        ];
                        $mergeHtml = preg_replace('/\[\[MERGE_AMOUNT\]\]/', ($newsletter->template == 'newsletter-rental-payment-confirmation' ? $apartmentsMM[$owner->apartment]->paymentsValue : number_format($apartmentsMM[$owner->apartment]->rentAmount - $apartmentsMM[$owner->apartment]->tax, 2)), $mergeHtml);
                        $mergeText = preg_replace('/\[\[MERGE_AMOUNT\]\]/', ($newsletter->template == 'newsletter-rental-payment-confirmation' ? $apartmentsMM[$owner->apartment]->paymentsValue : number_format($apartmentsMM[$owner->apartment]->rentAmount - $apartmentsMM[$owner->apartment]->tax, 2)), $mergeText);
                        $merge[] = [
                            'key' => 'MERGE_WT_AMOUNT',
                            'value' => $apartmentsMM[$owner->apartment]->taxValue,
                        ];
                        $mergeHtml = preg_replace('/\[\[MERGE_WT_AMOUNT\]\]/', $apartmentsMM[$owner->apartment]->taxValue, $mergeHtml);
                        $mergeText = preg_replace('/\[\[MERGE_WT_AMOUNT\]\]/', $apartmentsMM[$owner->apartment]->taxValue, $mergeText);
                    } elseif ($newsletter->template == 'newsletter-bank-account-details') {
                        $mergeHtml = preg_replace('/{MERGE_APARTMENT}/', $owner->number, $mergeHtml);
                        $mergeText = preg_replace('/{MERGE_APARTMENT}/', $owner->number, $mergeText);
                        $merge[] = [
                            'key' => 'MERGE_BANK',
                            'value' => $owner->bank_name,
                        ];
                        $mergeHtml = preg_replace('/\[\[MERGE_BANK\]\]/', $owner->bank_name, $mergeHtml);
                        $mergeText = preg_replace('/\[\[MERGE_BANK\]\]/', $owner->bank_name, $mergeText);
                        $merge[] = [
                            'key' => 'MERGE_BENEFICIARY',
                            'value' => $owner->bank_beneficiary,
                        ];
                        $mergeHtml = preg_replace('/\[\[MERGE_BENEFICIARY\]\]/', $owner->bank_beneficiary, $mergeHtml);
                        $mergeText = preg_replace('/\[\[MERGE_BENEFICIARY\]\]/', $owner->bank_beneficiary, $mergeText);
                        $merge[] = [
                            'key' => 'MERGE_IBAN',
                            'value' => $owner->bank_iban,
                        ];
                        $mergeHtml = preg_replace('/\[\[MERGE_IBAN\]\]/', $owner->bank_iban, $mergeHtml);
                        $mergeText = preg_replace('/\[\[MERGE_IBAN\]\]/', $owner->bank_iban, $mergeText);
                        $merge[] = [
                            'key' => 'MERGE_BIC',
                            'value' => $owner->bank_bic,
                        ];
                        $mergeHtml = preg_replace('/\[\[MERGE_BIC\]\]/', $owner->bank_bic, $mergeHtml);
                        $mergeText = preg_replace('/\[\[MERGE_BIC\]\]/', $owner->bank_bic, $mergeText);
                    }

                    $recipients = [];
                    array_push($recipients, $owner->full_name . ' <' . (\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $owner->email) . '>');
                    if ($owner->email_cc) {
                        array_push($recipients, $owner->full_name . ' <' . (\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $owner->email_cc) . '>');
                    }

                    $result = $mg->sendMessage(env('MAILGUN_DOMAIN'),
                        [
                            'from' => $newsletter->signature->translate($language)->name . ' <' . $newsletter->signature->email . '>',
                            'h:Sender' => $newsletter->signature->translate($language)->name . ' <' . $newsletter->signature->email . '>',
                            'to' => implode(',', $recipients),
                            'subject' => $newsletter->subject,
                            'html' => $mergeHtml,
                            'text' => $newsletterService->replaceText($mergeText),
                            // 'o:testmode' => true,
                            'o:tag' => $newsletter->template,
                            // 'o:deliverytime' => 'Wed, 16 Jan 2019 11:00:00 +0200',
                            'v:newsletterId' => $newsletter->id,
                            'v:ownerId' => '%recipient.id%',
                            'recipient-variables' => json_encode([$owner->email => $owner]),
                        ],
                        [
                            'attachment' => $attachments,
                            'inline' => $images,
                        ]
                    );

                    // $apartment_id = ;

                    if ($result->http_response_code == 200) {
                        $archive = NewsletterArchive::create([
                            'newsletter_id' => $newsletter->id,
                            'apartment_id' => $owner->apartment,
                            'owner_id' => $owner->id,
                        ]);

                        if ($archive->id && $merge) {
                            $archive->merge()->createMany($merge);
                        }
                    } else {
                        $error = true;
                    }
                }
            } elseif ($newsletter->template == 'newsletter-council-tax-letter') {
                foreach ($owners as $owner) {
                    $mergeHtml = $html;
                    $mergeText = $text;

                    $total = 0;
                    $rows = '';
                    $taxes = CouncilTax::selectRaw('owners.bulstat, owners.tax_pin, CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as full_name, council_tax.tax')->leftJoin('owners', 'owners.id', '=', 'council_tax.owner_id')->leftJoin('ownership', 'ownership.owner_id', '=', 'owners.id')->where('owners.is_active', 1)->whereNull('ownership.deleted_at')->where('council_tax.apartment_id', $owner->apartment)->where('ownership.apartment_id', $owner->apartment)->get();
                    foreach ($taxes as $tax) {
                        $total += $tax->tax;

                        $rows .= '<tr>
                            <td class="text-left" style="border-color:rgb(221, 221, 221); vertical-align:middle">' . $tax->full_name . '</td>
                            <td class="text-center" style="border-color:rgb(221, 221, 221); vertical-align:middle">' . $tax->bulstat . '</td>
                            <td class="text-center" style="border-color:rgb(221, 221, 221); vertical-align:middle">' . $tax->tax_pin . '</td>
                            <td class="text-right" style="border-color:rgb(221, 221, 221); vertical-align:middle; white-space: nowrap;">' . number_format($tax->tax, 2) . ' лв.</td>
                        </tr>';
                    }

                    $mergeHtml = preg_replace('/{MERGE_APARTMENT}/', $owner->number, $mergeHtml);
                    $mergeText = preg_replace('/{MERGE_APARTMENT}/', $owner->number, $mergeText);
                    $mergeHtml = preg_replace('/\[\[MERGE_DATE\]\]/', Carbon::parse($owner->checked_at)->format('d.m.Y'), $mergeHtml);
                    $mergeText = preg_replace('/\[\[MERGE_DATE\]\]/', Carbon::parse($owner->checked_at)->format('d.m.Y'), $mergeText);
                    $mergeHtml = preg_replace('/\[\[MERGE_TOTAL\]\]/', number_format($total, 2) . ' лв.', $mergeHtml);
                    $mergeText = preg_replace('/\[\[MERGE_TOTAL\]\]/', number_format($total, 2) . ' лв.', $mergeText);
                    $mergeHtml = preg_replace('/<tbody>(.*?)<\/tbody>/s', '<tbody>' . $rows . '</tbody>', $mergeHtml);
                    $mergeText = preg_replace('/<tbody>(.*?)<\/tbody>/s', '<tbody>' . $rows . '</tbody>', $mergeText);

                    $recipients = [];
                    array_push($recipients, $owner->full_name . ' <' . (\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $owner->email) . '>');
                    if ($owner->email_cc) {
                        array_push($recipients, $owner->full_name . ' <' . (\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $owner->email_cc) . '>');
                    }

                    $result = $mg->sendMessage(env('MAILGUN_DOMAIN'),
                        [
                            'from' => $newsletter->signature->translate($language)->name . ' <' . $newsletter->signature->email . '>',
                            'h:Sender' => $newsletter->signature->translate($language)->name . ' <' . $newsletter->signature->email . '>',
                            'to' => implode(',', $recipients),
                            'subject' => $newsletter->subject,
                            'html' => $mergeHtml,
                            'text' => $newsletterService->replaceText($mergeText),
                            // 'o:testmode' => true,
                            'o:tag' => $newsletter->template,
                            // 'o:deliverytime' => 'Wed, 29 Mar 2017 14:00:00 +0200',
                            'v:newsletterId' => $newsletter->id,
                            'v:ownerId' => '%recipient.id%',
                            'recipient-variables' => json_encode([$owner->email => $owner]),
                        ],
                        [
                            'attachment' => $attachments,
                            'inline' => $images,
                        ]
                    );

                    // $apartment_id = ;

                    if ($result->http_response_code == 200) {
                        NewsletterArchive::create([
                            'newsletter_id' => $newsletter->id,
                            'apartment_id' => $owner->apartment,
                            'owner_id' => $owner->id,
                        ]);
                    } else {
                        $error = true;
                    }
                }
            } elseif ($newsletter->template == 'newsletter-occupancy') {
                $rentalRates = RentalRatesPeriod::with('rates')->whereYear('dfrom', '=', $newsletter->year->year)->get();

                $keylogs = [];
                foreach ($rentalRates as $period) {
                    $keylogs[$period->id] = KeyLog::selectRaw('apartment_id, COUNT(*) AS nights')->whereBetween('occupied_at', [Carbon::parse($period->dfrom), Carbon::parse($period->dto)])->groupBy('apartment_id')->get();
                }

                foreach ($owners as $owner) {
                    $mergeHtml = $html;
                    $mergeText = $text;
                    $merge = [];

                    $apartment = $apartmentsMM[$owner->apartment];

                    $mergeHtml = preg_replace('/{MERGE_APARTMENT}/', $owner->number, $mergeHtml);
                    $mergeText = preg_replace('/{MERGE_APARTMENT}/', $owner->number, $mergeText);
                    $mergeHtml = preg_replace('/\[\[MERGE_YEAR\]\]/', $newsletter->year->year, $mergeHtml);
                    $mergeText = preg_replace('/\[\[MERGE_YEAR\]\]/', $newsletter->year->year, $mergeText);

                    $rentAmount = 0;
                    $rows = '';
                    foreach ($rentalRates as $period) {
                        $nights = 0;
                        $rate = 0;

                        $keylog = $keylogs[$period->id]->where('apartment_id', $owner->apartment)->first();
                        if ($keylog) {
                            $nights = $keylog->nights;
                        }

                        if ($period->type == 'personal-usage') {
                            $nights = $nights - 53; // personal usage period
                            $mergeHtml = preg_replace('/\[\[MERGE_PERSONAL_USAGE_PERIOD\]\]/', $period->dfrom . ' - ' . $period->dto, $mergeHtml);
                            $mergeText = preg_replace('/\[\[MERGE_PERSONAL_USAGE_PERIOD\]\]/', $period->dfrom . ' - ' . $period->dto, $mergeText);
                        }

                        if ($nights < 0) {
                            $nights = 0;
                        }

                        $rates = $period->rates->where('project', $apartment->projectSlug)->where('room', $apartment->roomSlug)->where('view', $apartment->viewSlug)->first();
                        if ($rates) {
                            $rate = $rates->rate;
                            if ($nights > 0) {
                                $rentAmount += $nights * $rate;
                            }
                        } else {
                            $rate = 0;
                        }

                        $rows .= '<tr>
                            <td class="text-left" style="border-color:rgb(221, 221, 221); vertical-align:middle">' . $period->dfrom . ' - ' . $period->dto . ($period->type == 'personal-usage' ? ' *' : '') . '</td>
                            <td class="text-right" style="border-color:rgb(221, 221, 221); vertical-align:middle">&euro; ' . $rate . '</td>
                            <td class="text-right" style="border-color:rgb(221, 221, 221); vertical-align:middle">' . $nights . '</td>
                            <td class="text-right" style="border-color:rgb(221, 221, 221); vertical-align:middle;white-space:nowrap;">&euro; ' . number_format($nights * $rate, 2) . '</td>
                        </tr>';
                    }

                    $rows .= '<tr>
                        <td colspan="3" class="text-left" style="border-color:rgb(221, 221, 221); vertical-align:middle;font-weight:bold;">' . \Lang::get(\Locales::getPublicNamespace() . '/messages.total', [], $language) . '</td>
                        <td class="text-right" style="border-color:rgb(221, 221, 221); vertical-align:middle;white-space:nowrap;font-weight:bold;">&euro; ' . number_format($rentAmount, 2) . '</td>
                    </tr>';

                    $mergeHtml = preg_replace('/<tbody>(.*?)<\/tbody>/s', '<tbody>' . $rows . '</tbody>', $mergeHtml, 1);
                    $mergeText = preg_replace('/<tbody>(.*?)<\/tbody>/s', '<tbody>' . $rows . '</tbody>', $mergeText, 1);

                    $merge[] = [
                        'key' => 'MERGE_MM_FEE',
                        'value' => number_format($apartment->mmFee, 2),
                    ];
                    $mergeHtml = preg_replace('/\[\[MERGE_MM_FEE\]\]/', number_format($apartment->mmFee, 2), $mergeHtml);
                    $mergeText = preg_replace('/\[\[MERGE_MM_FEE\]\]/', number_format($apartment->mmFee, 2), $mergeText);

                    $merge[] = [
                        'key' => 'MERGE_RENT_AMOUNT',
                        'value' => number_format($rentAmount, 2),
                    ];
                    $mergeHtml = preg_replace('/\[\[MERGE_RENT_AMOUNT\]\]/', number_format($rentAmount, 2), $mergeHtml);
                    $mergeText = preg_replace('/\[\[MERGE_RENT_AMOUNT\]\]/', number_format($rentAmount, 2), $mergeText);

                    $merge[] = [
                        'key' => 'MERGE_INCOME_AMOUNT',
                        'value' => number_format($apartment->mmFee + $rentAmount, 2),
                    ];
                    $mergeHtml = preg_replace('/\[\[MERGE_INCOME_AMOUNT\]\]/', number_format($apartment->mmFee + $rentAmount, 2), $mergeHtml);
                    $mergeText = preg_replace('/\[\[MERGE_INCOME_AMOUNT\]\]/', number_format($apartment->mmFee + $rentAmount, 2), $mergeText);

                    $merge[] = [
                        'key' => 'MERGE_WT',
                        'value' => number_format($apartment->tax, 2),
                    ];
                    $mergeHtml = preg_replace('/\[\[MERGE_WT\]\]/', number_format($apartment->tax, 2), $mergeHtml);
                    $mergeText = preg_replace('/\[\[MERGE_WT\]\]/', number_format($apartment->tax, 2), $mergeText);

                    $merge[] = [
                        'key' => 'MERGE_MM_YEAR',
                        'value' => number_format($apartment->mm_for_year, 2),
                    ];
                    $mergeHtml = preg_replace('/\[\[MERGE_MM_YEAR\]\]/', $apartment->mm_for_year, $mergeHtml);
                    $mergeText = preg_replace('/\[\[MERGE_MM_YEAR\]\]/', $apartment->mm_for_year, $mergeText);

                    $merge[] = [
                        'key' => 'MERGE_DEDUCTIONS',
                        'value' => number_format($apartment->deductions, 2),
                    ];
                    $mergeHtml = preg_replace('/\[\[MERGE_DEDUCTIONS\]\]/', number_format($apartment->deductions, 2), $mergeHtml);
                    $mergeText = preg_replace('/\[\[MERGE_DEDUCTIONS\]\]/', number_format($apartment->deductions, 2), $mergeText);

                    $merge[] = [
                        'key' => 'MERGE_TOTAL_EXPENDITURE',
                        'value' => number_format($apartment->tax + $apartment->mmFee + $apartment->deductions, 2),
                    ];
                    $mergeHtml = preg_replace('/\[\[MERGE_TOTAL_EXPENDITURE\]\]/', number_format($apartment->tax + $apartment->mmFee + $apartment->deductions, 2), $mergeHtml);
                    $mergeText = preg_replace('/\[\[MERGE_TOTAL_EXPENDITURE\]\]/', number_format($apartment->tax + $apartment->mmFee + $apartment->deductions, 2), $mergeText);

                    $merge[] = [
                        'key' => 'MERGE_NET_RENT',
                        'value' => number_format($apartment->netRent, 2),
                    ];
                    $mergeHtml = preg_replace('/\[\[MERGE_NET_RENT\]\]/', number_format($apartment->netRent, 2), $mergeHtml);
                    $mergeText = preg_replace('/\[\[MERGE_NET_RENT\]\]/', number_format($apartment->netRent, 2), $mergeText);

                    $recipients = [];
                    array_push($recipients, $owner->full_name . ' <' . (\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $owner->email) . '>');
                    if ($owner->email_cc) {
                        array_push($recipients, $owner->full_name . ' <' . (\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $owner->email_cc) . '>');
                    }

                    $result = $mg->sendMessage(env('MAILGUN_DOMAIN'),
                        [
                            'from' => $newsletter->signature->translate($language)->name . ' <' . $newsletter->signature->email . '>',
                            'h:Sender' => $newsletter->signature->translate($language)->name . ' <' . $newsletter->signature->email . '>',
                            'to' => implode(',', $recipients),
                            'subject' => $newsletter->subject,
                            'html' => $mergeHtml,
                            'text' => $newsletterService->replaceText($mergeText),
                            // 'o:testmode' => true,
                            'o:tag' => $newsletter->template,
                            // 'o:deliverytime' => 'Wed, 29 Mar 2017 14:00:00 +0200',
                            'v:newsletterId' => $newsletter->id,
                            'v:ownerId' => '%recipient.id%',
                            'recipient-variables' => json_encode([$owner->email => $owner]),
                        ],
                        [
                            'attachment' => $attachments,
                            'inline' => $images,
                        ]
                    );

                    // $apartment_id = ;

                    if ($result->http_response_code == 200) {
                        $archive = NewsletterArchive::create([
                            'newsletter_id' => $newsletter->id,
                            'apartment_id' => $owner->apartment,
                            'owner_id' => $owner->id,
                        ]);

                        if ($archive->id && $merge) {
                            $archive->merge()->createMany($merge);
                        }
                    } else {
                        $error = true;
                    }
                }
            } elseif ($newsletter->merge_by) {
                $mergeApartments = explode('|', Str::lower($merge->first()->merge));
                if ($newsletter->merge_by == 2) { // owners
                    $mergeOwners = explode('|', Str::lower($merge->get(1)->merge));
                }

                foreach ($owners as $owner) {
                    $index = false;
                    foreach ($mergeApartments as $key => $apartment) {
                        if (Str::lower($owner->number) == $apartment) {
                            if ($newsletter->merge_by == 2) { // owners
                                if ($mergeOwners[$key] == $owner->id) {
                                    $index = $key;
                                    break;
                                }
                            } else {
                                $index = $key;
                                break;
                            }
                        }
                    }

                    if ($index !== false) {
                        $mergeHtml = $html;
                        $mergeText = $text;

                        $mergeHtml = preg_replace('/{MERGE_APARTMENT}/', explode('|', $merge->first()->merge)[$index], $mergeHtml);
                        $mergeText = preg_replace('/{MERGE_APARTMENT}/', explode('|', $merge->first()->merge)[$index], $mergeText);
                        if ($newsletter->merge_by == 2) { // owners
                            $mergeHtml = preg_replace('/{MERGE_OWNER}/', $owner->full_name, $mergeHtml);
                            $mergeText = preg_replace('/{MERGE_OWNER}/', $owner->full_name, $mergeText);
                        }

                        foreach ($merge->slice($newsletter->merge_by) as $key => $value) {
                            $values = explode('|', $value->merge);
                            $value = '';
                            if (isset($values[$index])) {
                                $value = $values[$index];
                            }
                            $mergeHtml = preg_replace('/{MERGE}/', $value, $mergeHtml, 1);
                            $mergeText = preg_replace('/{MERGE}/', $value, $mergeText, 1);
                        }

                        $mergeAtachments = $attachments;
                        if ($newsletter->attachmentsApartment->count()) {
                            $attachment = $newsletter->attachmentsApartment->first(function($key, $value) use ($owner) {
                                return Str::lower(File::name($value->file)) == Str::lower($owner->number);
                            });

                            if ($attachment) {
                                array_push($mergeAtachments, $directory . \Config::get('upload.attachmentsDirectory') . '-apartment' . DIRECTORY_SEPARATOR . $attachment->uuid . DIRECTORY_SEPARATOR . $attachment->file);
                            }
                        }

                        if ($newsletter->merge_by == 2 && $newsletter->attachmentsOwner->count()) {
                            $attachment = $newsletter->attachmentsOwner->first(function($key, $value) use ($owner) {
                                return Str::lower(File::name($value->file)) == Str::lower($owner->number . '-' . $owner->id);
                            });

                            if ($attachment) {
                                array_push($mergeAtachments, $directory . \Config::get('upload.attachmentsDirectory') . '-owner' . DIRECTORY_SEPARATOR . $attachment->uuid . DIRECTORY_SEPARATOR . $attachment->file);
                            }
                        }

                        $recipients = [];
                        array_push($recipients, $owner->full_name . ' <' . (\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $owner->email) . '>');
                        if ($owner->email_cc) {
                            array_push($recipients, $owner->full_name . ' <' . (\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $owner->email_cc) . '>');
                        }

                        $result = $mg->sendMessage(env('MAILGUN_DOMAIN'),
                            [
                                'from' => $newsletter->signature->translate($language)->name . ' <' . $newsletter->signature->email . '>',
                                'h:Sender' => $newsletter->signature->translate($language)->name . ' <' . $newsletter->signature->email . '>',
                                'to' => implode(',', $recipients),
                                'subject' => $newsletter->subject,
                                'html' => $mergeHtml,
                                'text' => $newsletterService->replaceText($mergeText),
                                // 'o:testmode' => true,
                                'o:tag' => 'owners-newsletter',
                                // 'o:deliverytime' => 'Wed, 29 Mar 2017 14:00:00 +0200',
                                'v:newsletterId' => $newsletter->id,
                                'v:ownerId' => '%recipient.id%',
                                'recipient-variables' => json_encode([$owner->email => $owner]),
                            ],
                            [
                                'attachment' => $mergeAtachments,
                                'inline' => $images,
                            ]
                        );

                        // $apartment_id = ;

                        if ($result->http_response_code == 200) {
                            NewsletterArchive::create([
                                'newsletter_id' => $newsletter->id,
                                'apartment_id' => $owner->apartment_id,
                                'owner_id' => $owner->id,
                            ]);
                        } else {
                            $error = true;
                        }
                    }
                }
            } else {
                $chunks = $owners->chunk(1000);
                foreach ($chunks as $chunk) {
                    $recipients = [];
                    $ready = [];
                    foreach ($chunk as $owner) {
                        array_push($recipients, str_replace([',', ';'], [' ', ''], $owner->full_name) . ' <' . (\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $owner->email) . '>');
                        if ($owner->email_cc) {
                            array_push($recipients, str_replace([',', ';'], [' ', ''], $owner->full_name) . ' <' . (\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $owner->email_cc) . '>');
                        }

                        array_push($ready, [
                            'newsletter_id' => $newsletter->id,
                            'apartment_id' => $owner->apartment_id,
                            'owner_id' => $owner->id,
                        ]);
                    }

                    $variables = json_encode($chunk->keyBy('email')->filter(function ($value, $key) {
                        return $key;
                    })->all() + $chunk->keyBy('email_cc')->filter(function ($value, $key) {
                        return $key;
                    })->all());

                    $result = $mg->sendMessage(env('MAILGUN_DOMAIN'),
                        [
                            'from' => $newsletter->signature->translate($language)->name . ' <' . $newsletter->signature->email . '>',
                            'h:Sender' => $newsletter->signature->translate($language)->name . ' <' . $newsletter->signature->email . '>',
                            'to' => implode(',', $recipients),
                            // 'bcc' => 'Nikolay Pavlov <nikolay@sunsetresort.bg>', // also remove 'recipient-variables' line as otherwise the mails are sent separate
                            'subject' => $newsletter->subject,
                            'html' => $html,
                            'text' => $newsletterService->replaceText($text),
                            // 'o:testmode' => true,
                            'o:tag' => 'owners-newsletter',
                            // 'o:deliverytime' => 'Wed, 29 Mar 2017 14:00:00 +0200',
                            'v:newsletterId' => $newsletter->id,
                            'v:ownerId' => '%recipient.id%',
                            'recipient-variables' => $variables, // json_encode($chunk->keyBy('email')),
                        ],
                        [
                            'attachment' => $attachments,
                            'inline' => $images,
                        ]
                    );

                    if ($result->http_response_code == 200) {
                        NewsletterArchive::insert($ready);
                    } else {
                        $error = true;
                    }
                }
            }

            $recipients = Recipient::select('name', 'email')->where('is_active', 1)->get();
            if ($recipients->count()) {
                $admins = [];
                foreach ($recipients as $recipient) {
                    array_push($admins, $recipient->name . ' <' . (\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $recipient->email) . '>');
                }

                $patterns = array_map(function($value) { return '/%recipient.' . $value . '%/'; }, $newsletterService->columns());
                $html = preg_replace($patterns, '<span style="background-color: #ff0;">$0</span>', $html);

                if ($newsletter->merge_by) {
                    $html = preg_replace('/{MERGE_APARTMENT}/', '<span style="background-color: #ff0;">$0</span>', $html);
                    $html = preg_replace('/{MERGE_OWNER}/', '<span style="background-color: #ff0;">$0</span>', $html);
                    $html = preg_replace('/{MERGE}/', '<span style="background-color: #ff0;">$0</span>', $html);
                }

                if (in_array($newsletter->template, ['newsletter-mm-fees', 'newsletter-mm-fees-first-reminder', 'newsletter-mm-fees-second-reminder', 'newsletter-mm-fees-final-reminder', 'newsletter-mm-payment-confirmation'])) {
                    $html = preg_replace('/{MERGE_APARTMENT}/', '<span style="background-color: #ff0;">$0</span>', $html);
                    $html = preg_replace('/\[\[MERGE_BLOCK\]\]/', '<span style="background-color: #ff0;">$0</span>', $html);
                    $html = preg_replace('/\[\[MERGE_YEAR\]\]/', '<span style="background-color: #ff0;">$0</span>', $html);
                    $html = preg_replace('/\[\[MERGE_DUE_YEAR\]\]/', '<span style="background-color: #ff0;">$0</span>', $html);
                    $html = preg_replace('/\[\[MERGE_MM_AMOUNT\]\]/', '<span style="background-color: #ff0;">$0</span>', $html);
                    $html = preg_replace('/\[\[MERGE_MM_FEE\]\]/', '<span style="background-color: #ff0;">$0</span>', $html);

                    if ($newsletter->template == 'newsletter-mm-fees-final-reminder') {
                        $html = preg_replace('/\[\[MERGE_INTEREST\]\]/', '<span style="background-color: #ff0;">$0</span>', $html);
                        $html = preg_replace('/\[\[MERGE_TOTAL\]\]/', '<span style="background-color: #ff0;">$0</span>', $html);
                    }
                } elseif (in_array($newsletter->template, ['newsletter-rental-payment-confirmation', 'newsletter-rental-payment-income-tax-only'])) {
                    $html = preg_replace('/{MERGE_APARTMENT}/', '<span style="background-color: #ff0;">$0</span>', $html);
                    $html = preg_replace('/\[\[MERGE_BLOCK\]\]/', '<span style="background-color: #ff0;">$0</span>', $html);
                    $html = preg_replace('/\[\[MERGE_YEAR\]\]/', '<span style="background-color: #ff0;">$0</span>', $html);
                    $html = preg_replace('/\[\[MERGE_AMOUNT\]\]/', '<span style="background-color: #ff0;">$0</span>', $html);
                    $html = preg_replace('/\[\[MERGE_WT_AMOUNT\]\]/', '<span style="background-color: #ff0;">$0</span>', $html);
                } elseif ($newsletter->template == 'newsletter-bank-account-details') {
                    $html = preg_replace('/{MERGE_APARTMENT}/', '<span style="background-color: #ff0;">$0</span>', $html);
                    $html = preg_replace('/\[\[MERGE_BANK\]\]/', '<span style="background-color: #ff0;">$0</span>', $html);
                    $html = preg_replace('/\[\[MERGE_BENEFICIARY\]\]/', '<span style="background-color: #ff0;">$0</span>', $html);
                    $html = preg_replace('/\[\[MERGE_IBAN\]\]/', '<span style="background-color: #ff0;">$0</span>', $html);
                    $html = preg_replace('/\[\[MERGE_BIC\]\]/', '<span style="background-color: #ff0;">$0</span>', $html);
                } elseif ($newsletter->template == 'newsletter-council-tax-letter') {
                    $rows = '<tr>
                        <td class="text-left" style="border-color:rgb(221, 221, 221); vertical-align:middle"><span style="background-color: #ff0;">{MERGE_OWNER}</span></td>
                        <td class="text-center" style="border-color:rgb(221, 221, 221); vertical-align:middle"><span style="background-color: #ff0;">{MERGE_BULSTAT}</span></td>
                        <td class="text-center" style="border-color:rgb(221, 221, 221); vertical-align:middle"><span style="background-color: #ff0;">{MERGE_PIN}</span></td>
                        <td class="text-right" style="border-color:rgb(221, 221, 221); vertical-align:middle; white-space: nowrap;"><span style="background-color: #ff0;">{MERGE_TAX}</span></td>
                    </tr>';

                    $html = preg_replace('/{MERGE_APARTMENT}/', '<span style="background-color: #ff0;">$0</span>', $html);
                    $html = preg_replace('/\[\[MERGE_DATE\]\]/', '<span style="background-color: #ff0;">$0</span>', $html);
                    $html = preg_replace('/\[\[MERGE_TOTAL\]\]/', '<span style="background-color: #ff0;">$0</span>', $html);
                    $html = preg_replace('/<tbody>(.*?)<\/tbody>/s', '<tbody>' . $rows . '</tbody>', $html);
                } elseif ($newsletter->template == 'newsletter-occupancy') {
                    $html = preg_replace('/{MERGE_APARTMENT}/', '<span style="background-color: #ff0;">$0</span>', $html);
                    $html = preg_replace('/\[\[MERGE_YEAR\]\]/', '<span style="background-color: #ff0;">$0</span>', $html);
                    $html = preg_replace('/\[\[MERGE_PERSONAL_USAGE_PERIOD\]\]/', '<span style="background-color: #ff0;">$0</span>', $html);

                    $rows .= '<tr>
                        <td class="text-left" style="border-color:rgb(221, 221, 221); vertical-align:middle"><span style="background-color: #ff0;">{MERGE_PERIOD}</span></td>
                        <td class="text-right" style="border-color:rgb(221, 221, 221); vertical-align:middle"><span style="background-color: #ff0;">{MERGE_RENT_PER_DAY}</span></td>
                        <td class="text-right" style="border-color:rgb(221, 221, 221); vertical-align:middle"><span style="background-color: #ff0;">{MERGE_PERIOD_NIGHTS}</span></td>
                        <td class="text-right" style="border-color:rgb(221, 221, 221); vertical-align:middle;white-space:nowrap;"><span style="background-color: #ff0;">{MERGE_PERIOD_RENT}</span></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-right" style="border-color:rgb(221, 221, 221); vertical-align:middle;white-space:nowrap;font-weight:bold;"><span style="background-color: #ff0;">{MERGE_TOTAL_RENT}</span></td>
                    </tr>';

                    $html = preg_replace('/<tbody>(.*?)<\/tbody>/s', '<tbody>' . $rows . '</tbody>', $html, 1);
                    $html = preg_replace('/\[\[MERGE_MM_FEE\]\]/', '<span style="background-color: #ff0;">$0</span>', $html);
                    $html = preg_replace('/\[\[MERGE_RENT_AMOUNT\]\]/', '<span style="background-color: #ff0;">$0</span>', $html);
                    $html = preg_replace('/\[\[MERGE_INCOME_AMOUNT\]\]/', '<span style="background-color: #ff0;">$0</span>', $html);
                    $html = preg_replace('/\[\[MERGE_WT\]\]/', '<span style="background-color: #ff0;">$0</span>', $html);
                    $html = preg_replace('/\[\[MERGE_MM_YEAR\]\]/', '<span style="background-color: #ff0;">$0</span>', $html);
                    $html = preg_replace('/\[\[MERGE_DEDUCTIONS\]\]/', '<span style="background-color: #ff0;">$0</span>', $html);
                    $html = preg_replace('/\[\[MERGE_TOTAL_EXPENDITURE\]\]/', '<span style="background-color: #ff0;">$0</span>', $html);
                    $html = preg_replace('/\[\[MERGE_NET_RENT\]\]/', '<span style="background-color: #ff0;">$0</span>', $html);
                }

                $result = $mg->sendMessage(env('MAILGUN_DOMAIN'),
                    [
                        'from' => $newsletter->signature->translate($language)->name . ' <' . $newsletter->signature->email . '>',
                        'h:Sender' => $newsletter->signature->translate($language)->name . ' <' . $newsletter->signature->email . '>',
                        'to' => $newsletter->signature->translate($language)->name . ' <' . (\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $newsletter->signature->email) . '>',
                        'cc' => $admins ? implode(',', $admins) : null,
                        'subject' => $newsletter->subject,
                        'html' => $html,
                        'text' => $newsletterService->replaceText($text),
                        // 'o:testmode' => true,
                        'o:tag' => 'owners-newsletter-sky',
                        // 'o:deliverytime' => 'Wed, 29 Mar 2017 14:00:00 +0200',
                        'v:newsletterId' => $newsletter->id,
                    ],
                    [
                        'attachment' => $attachments,
                        'inline' => $images,
                    ]
                );

                if ($result->http_response_code != 200) {
                    $error = true;
                }
            }

            if ($error) {
                return response()->json([
                    'errors' => [trans(\Locales::getNamespace() . '/forms.newsletterSentError')],
                ]);
            } else {
                $newsletter->sent_at = Carbon::now();
                $newsletter->save();

                $request->session()->flash('success', [trans(\Locales::getNamespace() . '/forms.newsletterSentSuccessfully')]);

                return response()->json([
                    'refresh' => true,
                ]);
            }
        } else {
            return response()->json([
                'errors' => [trans(\Locales::getNamespace() . '/forms.noOwnersError')],
            ]);
        }
    }

    public function getTemplate(Request $request)
    {
        $buildings = null;
        $floors = null;
        $apartments = null;
        $owners = null;

        $template = NewsletterTemplates::where('template', $request->input('template'))->where('locale_id', $request->input('locale'))->first();

        if ($template) {
            $projects = explode(',', $template->projects);

            if ($template->projects) {
                $buildings = $this->getBuildings($request, false, $projects);
            }

            if ($template->buildings) {
                $floors = $this->getFloors($request, false, explode(',', $template->buildings));
            } elseif ($buildings) {
                $floors = $this->getFloors($request, false, array_values($buildings));
            }

            if ($template->floors) {
                $apartments = $this->getApartments($request, false, $projects, ($buildings ? array_values($buildings) : null), explode(',', $template->floors));
            } elseif ($buildings || $floors) {
                $apartments = $this->getApartments($request, false, $projects, ($buildings ? array_values($buildings) : null), ($floors ? array_values($floors) : null));
            }

            if ($template->apartments) {
                $owners = $this->getOwners($request, false, explode(',', $template->apartments));
            } elseif ($template->projects || $buildings || $floors || $apartments) {
                $owners = $this->getOwners($request, false, ($apartments ? array_values($apartments) : null));
            }
        }

        return response()->json([
            'success' => true,
            'template' => $template,
            'buildings' => $buildings,
            'floors' => $floors,
            'apartments' => $apartments,
            'owners' => $owners,
        ]);
    }

    public function getBuildings(Request $request, $json = true, $projects = null)
    {
        $projects = $request->input('projects', $projects);

        $buildings = Building::withTranslation()->select('building_translations.name', 'buildings.id')->leftJoin('building_translations', 'building_translations.building_id', '=', 'buildings.id')->where('building_translations.locale', \Locales::getCurrent())->whereIn('buildings.project_id', $projects)->orderBy('building_translations.name')->get()->pluck('id', 'name')->toArray();

        if ($json) {
            $floors = $this->getFloors($request, false, array_values($buildings));
            $apartments = $this->getApartments($request, false, $request->input('projects'), array_values($buildings), array_values($floors));

            return response()->json([
                'success' => true,
                'buildings' => $buildings,
                'floors' => $floors,
                'apartments' => $apartments,
                'owners' => $this->getOwners($request, false, array_values($apartments)),
            ]);
        } else {
            return $buildings;
        }
    }

    public function getFloors(Request $request, $json = true, $buildings = null)
    {
        $buildings = $request->input('buildings', $buildings);

        $floors = Floor::withTranslation()->select('floor_translations.name', 'floors.id')->leftJoin('floor_translations', 'floor_translations.floor_id', '=', 'floors.id')->where('floor_translations.locale', \Locales::getCurrent())->whereIn('floors.building_id', $buildings)->orderBy('floor_translations.name')->get()->pluck('id', 'name')->toArray();

        if ($json) {
            $apartments = $this->getApartments($request, false, $request->input('projects'), $buildings, array_values($floors));

            return response()->json([
                'success' => true,
                'floors' => $floors,
                'apartments' => $apartments,
                'owners' => $this->getOwners($request, false, array_values($apartments)),
            ]);
        } else {
            return $floors;
        }
    }

    public function getApartments(Request $request, $json = true, $projects = null, $buildings = null, $floors = null, $rooms = null, $furniture = null, $views = null)
    {
        $projects = $request->input('projects', $projects);
        $buildings = $request->input('buildings', $buildings);
        $floors = $request->input('floors', $floors);
        $rooms = $request->input('rooms', $rooms);
        $furniture = $request->input('furniture', $furniture);
        $views = $request->input('views', $views);

        $apartments = Apartment::distinct()->select('apartments.id', 'apartments.number')->join('ownership', 'ownership.apartment_id', '=', 'apartments.id')->whereNull('ownership.deleted_at');

        if ($projects) {
            $apartments = $apartments->whereIn('apartments.project_id', $projects);
        }

        if ($buildings) {
            $apartments = $apartments->whereIn('apartments.building_id', $buildings);
        }

        if ($floors) {
            $apartments = $apartments->whereIn('apartments.floor_id', $floors);
        }

        if ($rooms) {
            $apartments = $apartments->whereIn('apartments.room_id', $rooms);
        }

        if ($furniture) {
            $apartments = $apartments->whereIn('apartments.furniture_id', $furniture);
        }

        if ($views) {
            $apartments = $apartments->whereIn('apartments.view_id', $views);
        }

        $apartments = $apartments->orderBy('apartments.number')->get()->pluck('id', 'number')->toArray();

        if ($json) {
            return response()->json([
                'success' => true,
                'apartments' => $apartments,
                'owners' => $this->getOwners($request, false, array_values($apartments)),
            ]);
        } else {
            return $apartments;
        }
    }

    public function getOwners(Request $request, $json = true, $apartments = null)
    {
        $apartments = $request->input('apartments', $apartments);

        if (!$apartments) {
            $buildings = Building::withTranslation()->select('building_translations.name', 'buildings.id')->leftJoin('building_translations', 'building_translations.building_id', '=', 'buildings.id')->where('building_translations.locale', \Locales::getCurrent())->whereIn('buildings.project_id', $request->input('projects'))->orderBy('building_translations.name')->get()->pluck('id', 'name')->toArray();
            $floors = $this->getFloors($request, false, array_values($buildings));
            $apartments = $this->getApartments($request, false, $request->input('projects'), array_values($buildings), array_values($floors), $request->input('rooms'), $request->input('furniture'), $request->input('views'));
        }

        $owners = [];
        if ($apartments) {
            $owners = Owner::distinct()->selectRaw('owners.id, CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as name')->join('ownership', 'ownership.owner_id', '=', 'owners.id')->whereNull('ownership.deleted_at')->whereIn('ownership.apartment_id', $apartments)->orderBy('name')->get()->pluck('id', 'name')->toArray();
        }

        if ($json) {
            return response()->json([
                'success' => true,
                'owners' => $owners,
            ]);
        } else {
            return $owners;
        }
    }

}
