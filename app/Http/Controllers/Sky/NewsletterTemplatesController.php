<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Models\Sky\NewsletterTemplates;
use App\Models\Sky\Country;
use App\Models\Sky\Locale;
use App\Models\Sky\Owner;
use App\Models\Sky\Apartment;
use App\Models\Sky\Recipient;
use App\Models\Sky\Signature;
use App\Models\Sky\Project;
use App\Models\Sky\Building;
use App\Models\Sky\Floor;
use App\Models\Sky\Room;
use App\Models\Sky\Furniture;
use App\Models\Sky\View;
use App\Services\DataTable;
use App\Services\Newsletter as NewsletterService;
use Illuminate\Http\Request;
use App\Http\Requests\Sky\NewsletterTemplatesRequest;
use Storage;

class NewsletterTemplatesController extends Controller {

    protected $route = 'newsletter-templates';
    protected $uploadDirectory = 'newsletter-templates';
    protected $datatables;
    protected $multiselect;

    public function __construct()
    {
        $this->datatables = [
            $this->route => [
                'title' => trans(\Locales::getNamespace() . '/datatables.titleNewsletterTemplates'),
                'url' => \Locales::route($this->route),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'selectors' => ['newsletter_templates.teaser'],
                'columns' => [
                    [
                        'selector' => 'newsletter_templates.id',
                        'id' => 'checkbox',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center vertical-center',
                        'width' => '1.25em',
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
                            'title' => trans(\Locales::getNamespace() . '/datatables.previewTemplate'),
                        ],
                    ],
                    [
                        'selector' => 'newsletter_templates.template',
                        'id' => 'template',
                        'name' => trans(\Locales::getNamespace() . '/datatables.template'),
                        'class' => 'vertical-center',
                        'search' => true,
                        'replace' => [
                            'array' => trans(\Locales::getNamespace() . '/multiselect.newsletterTemplates'),
                        ],
                        'link' => [
                            'icon' => 'folder-open',
                            'route' => $this->route,
                            'routeParameters' => ['id'],
                        ],
                    ],
                    [
                        'selector' => 'newsletter_templates.subject',
                        'id' => 'subject',
                        'name' => trans(\Locales::getNamespace() . '/datatables.subject'),
                        'search' => true,
                        'class' => 'vertical-center',
                        'append' => 'teaser',

                    ],
                    [
                        'selector' => 'locales.name as locale',
                        'id' => 'locale',
                        'name' => trans(\Locales::getNamespace() . '/datatables.locale'),
                        'class' => 'vertical-center',
                        'join' => [
                            'table' => 'locales',
                            'localColumn' => 'locales.id',
                            'constrain' => '=',
                            'foreignColumn' => 'newsletter_templates.locale_id',
                        ],
                    ],
                ],
                'orderByColumn' => 'template',
                'orderByColumnExtra' => ['subject' => 'asc'],
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

    public function index(DataTable $datatable, NewsletterTemplates $template, Request $request, $id = null)
    {
        $breadcrumbs = [];

        if ($id) {
            $template = $template->findOrFail($id);
            $breadcrumbs[] = ['id' => 'templates', 'slug' => $template->id, 'name' => $template->subject];

            $uploadDirectory = $this->uploadDirectory;
            if (!Storage::disk('local-public')->exists($uploadDirectory)) {
                Storage::disk('local-public')->makeDirectory($uploadDirectory);
            }

            $uploadDirectory .= DIRECTORY_SEPARATOR . $template->id;
            if (!Storage::disk('local-public')->exists($uploadDirectory)) {
                Storage::disk('local-public')->makeDirectory($uploadDirectory);
            }

            $datatable->setup(null, 'properties', $this->datatables['properties']);
        } else {
            $datatable->setup($template, $this->route, $this->datatables[$this->route]);
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

        $templates = trans(\Locales::getNamespace() . '/multiselect.newsletterTemplates');

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

    public function store(DataTable $datatable, NewsletterTemplates $template, NewsletterTemplatesRequest $request)
    {
        $newTemplate = NewsletterTemplates::create($request->all());

        if ($newTemplate->id) {
            $uploadDirectory = $this->uploadDirectory . DIRECTORY_SEPARATOR . $newTemplate->id;
            if (!Storage::disk('local-public')->exists($uploadDirectory)) {
                Storage::disk('local-public')->makeDirectory($uploadDirectory);
            }

            $successMessage = trans(\Locales::getNamespace() . '/forms.storedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityTemplates', 1)]);

            $datatable->setup($template, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.createError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityTemplates', 1)]);
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

    public function destroy(DataTable $datatable, NewsletterTemplates $template, Request $request)
    {
        $count = count($request->input('id'));

        $rows = NewsletterTemplates::select('id')->whereIn('id', $request->input('id'))->get();

        if ($count > 0 && $template->destroy($request->input('id'))) {
            foreach ($rows as $row) {
                Storage::disk('local-public')->deleteDirectory($this->uploadDirectory . DIRECTORY_SEPARATOR . $row->id);
            }

            $datatable->setup($template, $request->input('table'), $this->datatables[$request->input('table')], true);
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
        $template = NewsletterTemplates::findOrFail($id);

        $table = $this->route;
        if ($request->input('table')) { // magnific popup request
            $table = $request->input('table');
        }

        $templates = trans(\Locales::getNamespace() . '/multiselect.newsletterTemplates');

        $this->multiselect['countries']['options'] = Country::withTranslation()->select('country_translations.name', 'countries.id')->leftJoin('country_translations', 'country_translations.country_id', '=', 'countries.id')->where('country_translations.locale', \Locales::getCurrent())->orderBy('country_translations.name')->get()->toArray();
        $this->multiselect['countries']['selected'] = explode(',', $template->countries);

        $recipients = [];
        foreach (trans(\Locales::getNamespace() . '/forms.newsletterRecipients') as $key => $value) {
            array_push($recipients, ['id' => $key, 'name' => $value]);
        }
        $this->multiselect['recipients']['options'] = $recipients;
        $this->multiselect['recipients']['selected'] = explode(',', $template->recipients);

        $locales = [];
        foreach (\Locales::getPublicDomain()->locales->toArray() as $key => $value) {
            $locales[$key]['id'] = $value['id'];
            $locales[$key]['name'] = $value['name'];
        }

        $this->multiselect['locales']['options'] = $locales;
        $this->multiselect['locales']['selected'] = $template->locale_id;

        $this->multiselect['projects']['options'] = Project::withTranslation()->select('project_translations.name', 'projects.id')->leftJoin('project_translations', 'project_translations.project_id', '=', 'projects.id')->where('project_translations.locale', \Locales::getCurrent())->orderBy('project_translations.name')->get()->toArray();
        $this->multiselect['projects']['selected'] = explode(',', $template->projects);

        $this->multiselect['buildings']['options'] = Building::withTranslation()->select('building_translations.name', 'buildings.id')->leftJoin('building_translations', 'building_translations.building_id', '=', 'buildings.id')->where('building_translations.locale', \Locales::getCurrent())->whereIn('buildings.project_id', explode(',', $template->projects))->orderBy('building_translations.name')->get()->toArray();
        $this->multiselect['buildings']['selected'] = explode(',', $template->buildings);

        $this->multiselect['floors']['options'] = Floor::withTranslation()->select('floor_translations.name', 'floors.id')->leftJoin('floor_translations', 'floor_translations.floor_id', '=', 'floors.id')->where('floor_translations.locale', \Locales::getCurrent())->whereIn('floors.building_id', explode(',', $template->buildings))->orderBy('floor_translations.name')->get()->toArray();
        $this->multiselect['floors']['selected'] = explode(',', $template->floors);

        $this->multiselect['rooms']['options'] = Room::withTranslation()->selectRaw('CONCAT(room_translations.name, " (", room_translations.description, ")") as room, rooms.id')->leftJoin('room_translations', 'room_translations.room_id', '=', 'rooms.id')->where('room_translations.locale', \Locales::getCurrent())->orderBy('room_translations.name')->get()->toArray();
        $this->multiselect['rooms']['selected'] = explode(',', $template->rooms);

        $this->multiselect['furniture']['options'] = Furniture::withTranslation()->select('furniture_translations.name', 'furniture.id')->leftJoin('furniture_translations', 'furniture_translations.furniture_id', '=', 'furniture.id')->where('furniture_translations.locale', \Locales::getCurrent())->orderBy('furniture_translations.name')->get()->toArray();
        $this->multiselect['furniture']['selected'] = explode(',', $template->furniture);

        $this->multiselect['views']['options'] = View::withTranslation()->select('view_translations.name', 'views.id')->leftJoin('view_translations', 'view_translations.view_id', '=', 'views.id')->where('view_translations.locale', \Locales::getCurrent())->orderBy('view_translations.name')->get()->toArray();
        $this->multiselect['views']['selected'] = explode(',', $template->views);

        $this->multiselect['apartments']['options'] = Apartment::distinct()->select('apartments.id', 'apartments.number')->join('ownership', 'ownership.apartment_id', '=', 'apartments.id')->whereNull('ownership.deleted_at')->orderBy('number')->get()->toArray();
        $this->multiselect['apartments']['selected'] = explode(',', $template->apartments);

        $this->multiselect['owners']['options'] = Owner::distinct()->selectRaw('owners.id, CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as name')->join('ownership', 'ownership.owner_id', '=', 'owners.id')->whereNull('ownership.deleted_at')->orderBy('name')->get()->toArray();
        $this->multiselect['owners']['selected'] = explode(',', $template->owners);

        $multiselect = $this->multiselect;

        $signatures = Signature::select('description', 'id')->orderBy('description')->get()->pluck('description', 'id')->toArray();

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('template', 'table', 'multiselect', 'templates', 'signatures'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, NewsletterTemplates $templates, NewsletterTemplatesRequest $request)
    {
        $template = NewsletterTemplates::findOrFail($request->input('id'))->first();

        if ($template->update($request->all())) {
            $uploadDirectory = $this->uploadDirectory . DIRECTORY_SEPARATOR . $template->id;
            if (!Storage::disk('local-public')->exists($uploadDirectory)) {
                Storage::disk('local-public')->makeDirectory($uploadDirectory);
            }

            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityTemplates', 1)]);

            $datatable->setup($templates, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityTemplates', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function preview(Request $request, NewsletterService $newsletterService, $id)
    {
        $template = NewsletterTemplates::findOrFail($id);

        $template->body = \HTML::image(asset('img/' . env('APP_OWNERS_SUBDOMAIN') . '/newsletter-logo.png')) . $template->body;

        $patterns = array_map(function($value) { return '/' . $value . '/'; }, $newsletterService->patterns());
        $template->body = preg_replace($patterns, '<span style="background-color: #ff0;">$0</span>', $template->body);

        $uploadDirectory = asset('upload/' . $this->uploadDirectory . '/' . $template->id . '/' . \Config::get('upload.imagesDirectory') . '/');
        foreach ($template->images as $image) {
            $template->body = preg_replace('/{IMAGE}/', \HTML::image($uploadDirectory . '/' . $image->uuid . '/' . $image->file), $template->body, 1);
        }

        $template->body = preg_replace('/{TOKEN}/', '<span style="background-color: #ff0;">$0</span>', $template->body);
        $template->body = preg_replace('/{MERGE_APARTMENT}/', '<span style="background-color: #ff0;">$0</span>', $template->body);
        $template->body = preg_replace('/{MERGE_OWNER}/', '<span style="background-color: #ff0;">$0</span>', $template->body);
        $template->body = preg_replace('/{MERGE}/', '<span style="background-color: #ff0;">$0</span>', $template->body);
        $template->body = preg_replace('/\[\[MERGE_ID\]\]/', '<span style="background-color: #ff0;">$0</span>', $template->body);
        $template->body = preg_replace('/\[\[MERGE_YEAR\]\]/', '<span style="background-color: #ff0;">$0</span>', $template->body);
        $template->body = preg_replace('/\[\[MERGE_BLOCK\]\]/', '<span style="background-color: #ff0;">$0</span>', $template->body);
        $template->body = preg_replace('/\[\[MERGE_MM_AMOUNT\]\]/', '<span style="background-color: #ff0;">$0</span>', $template->body);
        $template->body = preg_replace('/\[\[MERGE_MM_FEE\]\]/', '<span style="background-color: #ff0;">$0</span>', $template->body);
        $template->body = preg_replace('/\[\[MERGE_INTEREST\]\]/', '<span style="background-color: #ff0;">$0</span>', $template->body);
        $template->body = preg_replace('/\[\[MERGE_DATE\]\]/', '<span style="background-color: #ff0;">$0</span>', $template->body);
        $template->body = preg_replace('/\[\[MERGE_TOTAL\]\]/', '<span style="background-color: #ff0;">$0</span>', $template->body);

        $language = \Locales::getPublicLocales()->filter(function($value, $key) use ($template) {
            return $value->id == $template->locale_id;
        })->first()->locale;
        $signature = $template->signature->translate($language)->content;

        $directory = asset('upload/signatures/' . $template->signature->id) . '/';
        foreach ($template->signature->images as $image) {
            if (strpos($signature, '{SIGNATURE}') !== false) {
                $signature = preg_replace('/{SIGNATURE}/', '<img class="signature" src="' . $directory . $image->uuid . '/' . $image->file . '" />', $signature, 1);
            }
        }

        $template->body .= $signature;

        $filters = [];
        if ($template->projects) {
            array_push($filters, [
                'title' => trans(\Locales::getNamespace() . '/multiselect.newsletterFilters.projects'),
                'values' => implode(', ', Project::withTranslation()->select('project_translations.name')->leftJoin('project_translations', 'project_translations.project_id', '=', 'projects.id')->where('project_translations.locale', \Locales::getCurrent())->orderBy('project_translations.name')->whereIn('projects.id', explode(',', $template->projects))->pluck('name')->toArray()),
            ]);
        }

        if ($template->buildings) {
            array_push($filters, [
                'title' => trans(\Locales::getNamespace() . '/multiselect.newsletterFilters.buildings'),
                'values' => implode(', ', Building::withTranslation()->select('building_translations.name')->leftJoin('building_translations', 'building_translations.building_id', '=', 'buildings.id')->where('building_translations.locale', \Locales::getCurrent())->orderBy('building_translations.name')->whereIn('buildings.id', explode(',', $template->buildings))->pluck('name')->toArray()),
            ]);
        }

        if ($template->floors) {
            array_push($filters, [
                'title' => trans(\Locales::getNamespace() . '/multiselect.newsletterFilters.floors'),
                'values' => implode(', ', Floor::withTranslation()->select('floor_translations.name')->leftJoin('floor_translations', 'floor_translations.floor_id', '=', 'floors.id')->where('floor_translations.locale', \Locales::getCurrent())->orderBy('floor_translations.name')->whereIn('floors.id', explode(',', $template->floors))->pluck('name')->toArray()),
            ]);
        }

        if ($template->rooms) {
            array_push($filters, [
                'title' => trans(\Locales::getNamespace() . '/multiselect.newsletterFilters.rooms'),
                'values' => implode(', ', Room::withTranslation()->select('room_translations.name')->leftJoin('room_translations', 'room_translations.room_id', '=', 'rooms.id')->where('room_translations.locale', \Locales::getCurrent())->orderBy('room_translations.name')->whereIn('rooms.id', explode(',', $template->rooms))->pluck('name')->toArray()),
            ]);
        }

        if ($template->furniture) {
            array_push($filters, [
                'title' => trans(\Locales::getNamespace() . '/multiselect.newsletterFilters.furniture'),
                'values' => implode(', ', Furniture::withTranslation()->select('furniture_translations.name')->leftJoin('furniture_translations', 'furniture_translations.furniture_id', '=', 'furniture.id')->where('furniture_translations.locale', \Locales::getCurrent())->orderBy('furniture_translations.name')->whereIn('furniture.id', explode(',', $template->furniture))->pluck('name')->toArray()),
            ]);
        }

        if ($template->views) {
            array_push($filters, [
                'title' => trans(\Locales::getNamespace() . '/multiselect.newsletterFilters.views'),
                'values' => implode(', ', View::withTranslation()->select('view_translations.name')->leftJoin('view_translations', 'view_translations.view_id', '=', 'views.id')->where('view_translations.locale', \Locales::getCurrent())->orderBy('view_translations.name')->whereIn('views.id', explode(',', $template->views))->pluck('name')->toArray()),
            ]);
        }

        if ($template->apartments) {
            array_push($filters, [
                'title' => trans(\Locales::getNamespace() . '/multiselect.newsletterFilters.apartments'),
                'values' => implode(', ', Apartment::select('number')->orderBy('number')->whereIn('id', explode(',', $template->apartments))->pluck('number')->toArray()),
            ]);
        }

        if ($template->owners) {
            array_push($filters, [
                'title' => trans(\Locales::getNamespace() . '/multiselect.newsletterFilters.owners'),
                'values' => implode(', ', Owner::selectRaw('CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as name')->orderBy('name')->whereIn('id', explode(',', $template->owners))->pluck('name')->toArray()),
            ]);
        }

        if ($template->countries) {
            array_push($filters, [
                'title' => trans(\Locales::getNamespace() . '/multiselect.newsletterFilters.countries'),
                'values' => implode(', ', Country::withTranslation()->select('country_translations.name')->leftJoin('country_translations', 'country_translations.country_id', '=', 'countries.id')->where('country_translations.locale', \Locales::getCurrent())->orderBy('country_translations.name')->whereIn('countries.id', explode(',', $template->countries))->pluck('name')->toArray()),
            ]);
        }

        if ($template->recipients) {
            array_push($filters, [
                'title' => trans(\Locales::getNamespace() . '/multiselect.newsletterFilters.recipients'),
                'values' => implode(', ', array_map(function ($key) { return trans(\Locales::getNamespace() . '/forms.newsletterRecipients.' . $key); }, explode(',', $template->recipients))),
            ]);
        }

        if ($template->locale_id) {
            array_push($filters, [
                'title' => trans(\Locales::getNamespace() . '/multiselect.newsletterFilters.language'),
                'values' => $template->locale->name,
            ]);
        }

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.preview', compact('template', 'filters', 'language'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

}
