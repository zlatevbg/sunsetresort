<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Models\Sky\Navigation;
use App\Http\Requests\Sky\NavigationRequest;

class NavigationController extends Controller {

    protected $route = 'navigation';
    protected $datatables;

    public function __construct()
    {
        $this->datatables = [
            'languages' => [
                'dom' => 'tr',
                'title' => trans(\Locales::getNamespace() . '/datatables.titleLanguages'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'columns' => [
                    [
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.name'),
                    ],
                ],
                'orderByColumn' => 'name',
                'order' => 'asc',
            ],
            $this->route => [
                'title' => trans(\Locales::getNamespace() . '/datatables.titlePages'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'columns' => [
                    [
                        'selector' => $this->route . '.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center',
                    ],
                    [
                        'selector' => $this->route . '.name',
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.name'),
                        'search' => true,
                        'join' => [
                            'table' => 'locales',
                            'localColumn' => 'locales.id',
                            'constrain' => '=',
                            'foreignColumn' => $this->route . '.locale_id',
                        ],
                        'link' => [
                            'selector' => ['locales.locale', $this->route . '.is_category'],
                            'rules' => [
                                1 => [
                                    'column' => 'is_category',
                                    'value' => 1,
                                    'icon' => 'folder-open',
                                ],
                            ],
                            'route' => $this->route,
                            'routeParameters' => ['locale', 'slug'],
                        ],
                    ],
                    [
                        'selector' => $this->route . '.slug',
                        'id' => 'slug',
                        'name' => trans(\Locales::getNamespace() . '/datatables.slug'),
                        'search' => true,
                    ],
                    [
                        'selector' => $this->route . '.order',
                        'id' => 'order',
                        'name' => trans(\Locales::getNamespace() . '/datatables.order'),
                        'class' => 'text-center',
                    ],
                    [
                        'selector' => $this->route . '.is_active',
                        'id' => 'is_active',
                        'name' => trans(\Locales::getNamespace() . '/datatables.status'),
                        'class' => 'text-center',
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
                'orderByColumn' => 'order',
                'order' => 'asc',
                'buttons' => [
                    [
                        'url' => \Locales::route($this->route . '/create'),
                        'class' => 'btn-primary js-create',
                        'icon' => 'plus',
                        'name' => trans(\Locales::getNamespace() . '/forms.createMenuButton'),
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

    public function index(DataTable $datatable, Request $request, $locale = null, $slugs = null)
    {
        $breadcrumbs = [];

        if ($locale) {
            $locale = \Locales::getPublicDomain()->locales()->where('locale', $locale)->firstOrFail();
            $breadcrumbs[] = ['id' => 'locales', 'slug' => $locale->locale, 'name' => $locale->name];

            if ($slugs) {
                $slugsArray = explode('/', $slugs);
                $pages = Navigation::select('id', 'parent', 'slug', 'is_category', 'name')->where('locale_id', $locale->id)->get()->toArray();
                $pages = \App\Helpers\arrayToTree($pages);
                if ($row = \Slug::arrayMatchSlugsRecursive($slugsArray, $pages)) { // match slugs against the pages array
                    $breadcrumbs = array_merge($breadcrumbs, \Slug::createBreadcrumbsFromParameters($slugsArray, $pages));

                    $this->datatables[$this->route]['columns'][1]['link']['prepend'] = $slugs;
                    $datatable->setup(Navigation::where('parent', $row['id'])->where('locale_id', $locale->id), $this->route, $this->datatables[$this->route]);
                    $datatable->setOption('parent', $row['id']);
                    $datatable->setOption('slugs', $slugs);
                } else {
                    abort(404);
                }
            } else {
                $datatable->setup(Navigation::whereNull('parent')->where('locale_id', $locale->id), $this->route, $this->datatables[$this->route]);
                $datatable->setOption('parent', null);
            }

            $datatable->setOption('locale', $locale->id);
        } else {
            $datatable->setup(null, 'languages', $this->datatables['languages']);
            $languages = [];
            foreach (\Locales::getPublicDomain()->locales->keyBy('locale')->lists('name', 'locale')->toArray() as $locale => $name) {
                $languages[] = ['name' => '<a href="' . \Locales::route($this->route) . '/' . $locale . '"><span class="glyphicon glyphicon-folder-open glyphicon-left"></span>' . $name . '</a>'];
            }
            $datatable->setOption('data', $languages);
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
        $table = $request->input('table');

        $locale = $request->input('locale') ?: null;
        $parent = $request->input('parent') ?: null;
        $slugs = $request->input('slugs') ?: null;

        $types[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $types = array_merge($types, trans(\Locales::getNamespace() . '/multiselect.navigationPageTypes'));

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('table', 'types', 'locale', 'parent', 'slugs'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function store(DataTable $datatable, Navigation $page, NavigationRequest $request)
    {
        $parent = $request->input('parent') ?: null;
        $page = $page->where('parent', $parent)->where('locale_id', $request->input('locale'));

        $order = $request->input('order');
        $maxOrder = $page->max('order') + 1;

        if (!$order || $order > $maxOrder) {
            $order = $maxOrder;
        } else { // re-order all higher order rows
            $clone = clone $page;
            $clone->where('order', '>=', $order)->increment('order');
        }

        $request->merge([
            'locale_id' => $request->input('locale'),
            'parent' => $parent,
            'order' => $order,
        ]);

        $newNav = Navigation::create($request->all());

        if ($newNav->id) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.storedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityMenus', 1)]);

            $this->datatables[$this->route]['columns'][1]['link']['prepend'] = $request->input('slugs');
            $datatable->setup($page, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, [$request->input('locale'), $request->input('slugs')]));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'reset' => true,
                'resetEditor' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.createError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityMenus', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function delete(Request $request)
    {
        $table = $request->input('table');

        $slugs = $request->input('slugs') ?: null;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.delete', compact('table', 'slugs'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function destroy(DataTable $datatable, Navigation $page, Request $request)
    {
        $count = count($request->input('id'));

        $row = Navigation::select('locale_id', 'parent')->whereIn('id', $request->input('id'))->first();

        if ($count > 0 && $page->destroy($request->input('id'))) {
            \DB::statement('SET @pos := 0');
            \DB::update('update ' . $page->getTable() . ' SET `order` = (SELECT @pos := @pos + 1) WHERE parent = ? AND locale_id = ? ORDER BY `order`', [$row->parent, $row->locale_id]);

            $this->datatables[$this->route]['columns'][1]['link']['prepend'] = $request->input('slugs');
            $datatable->setup($page->where('parent', $row->parent)->where('locale_id', $row->locale_id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, [$row->locale_id, $request->input('slugs')]));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => trans(\Locales::getNamespace() . '/forms.destroyedSuccessfully'),
                'closePopup' => true,
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
        $page = Navigation::findOrFail($id);

        $table = $request->input('table');

        $slugs = $request->input('slugs') ?: null;

        $types[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $types = array_merge($types, trans(\Locales::getNamespace() . '/multiselect.navigationPageTypes'));

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('page', 'table', 'types', 'slugs'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, Navigation $nav, NavigationRequest $request)
    {
        $page = Navigation::findOrFail($request->input('id'))->first();

        $order = $request->input('order');
        if (!$order || $order < 0) {
            $order = $page->order;
        } elseif ($order) {
            $nav = $nav->where('parent', $page->parent)->where('locale_id', $page->locale_id);
            $maxOrder = $nav->max('order');

            if ($order > $maxOrder) {
                $order = $maxOrder;
            } elseif ($order < $page->order) {
                $nav->where('order', '>=', $order)->where('order', '<', $page->order)->increment('order');
            } elseif ($order > $page->order) {
                $nav->where('order', '<=', $order)->where('order', '>', $page->order)->decrement('order');
            }
        }

        $request->merge([
            'order' => $order,
        ]);

        if ($page->update($request->all())) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityMenus', 1)]);

            $this->datatables[$this->route]['columns'][1]['link']['prepend'] = $request->input('slugs');
            $datatable->setup(Navigation::where('parent', $page->parent)->where('locale_id', $page->locale_id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route, [$page->locale_id, $request->input('slugs')]));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityMenus', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function changeStatus($id, $status)
    {
        $nav = Navigation::findOrFail($id);

        $nav->is_active = $status;
        $nav->save();

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
