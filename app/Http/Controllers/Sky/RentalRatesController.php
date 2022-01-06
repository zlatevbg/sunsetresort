<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Models\Sky\RentalRatesPeriod;
use App\Models\Sky\RentalRates;
use App\Models\Sky\Room;
use App\Models\Sky\Project;
use App\Models\Sky\View as Views;
use App\Http\Requests\Sky\RentalRatesRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class RentalRatesController extends Controller {

    protected $route = 'rental-rates';
    protected $datatables;

    public function __construct()
    {
        /*$views = Views::withTranslation()->select('view_translations.slug', 'views.id')->leftJoin('view_translations', 'view_translations.view_id', '=', 'views.id')->where('view_translations.locale', \Locales::getCurrent())->orderBy('view_translations.name')->get()->transform(function ($item, $key) {
            $item->slug = ucfirst($item->slug);
            return $item;
        });
        $views = '<span class="form-control-rental-rates">' . $views->implode('slug', '</span><span class="form-control-rental-rates">') . '</span>';*/

        $this->datatables = [
            $this->route => [
                'title' => trans(\Locales::getNamespace() . '/datatables.titleRentalRates'),
                'url' => \Locales::route($this->route),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'selectors' => ['rental_rates_periods.dto', 'rental_rates_periods.type'],
                'columns' => [
                    [
                        'selector' => 'rental_rates_periods.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center',
                        'with' => 'rates',
                    ],
                    [
                        'selector' => 'rental_rates_periods.dfrom',
                        'id' => 'dfrom',
                        'name' => trans(\Locales::getNamespace() . '/datatables.date'),
                        'search' => true,
                        'data' => [
                            'type' => 'sort',
                            'id' => 'dfrom',
                            'date' => 'YYmmdd',
                            'append' => [
                                [
                                    'id' => 'dto',
                                    'separator' => ' - %s',
                                ],
                                [
                                    'id' => 'type',
                                    'separator' => ' (%s)',
                                    'function' => 'ucfirst',
                                ],
                            ],
                        ],
                    ],/*
                    [
                        'selector' => 'rental_rates_periods.dto',
                        'id' => 'dto',
                        'name' => trans(\Locales::getNamespace() . '/datatables.dto'),
                        'search' => true,
                        'data' => [
                            'type' => 'sort',
                            'id' => 'dto',
                            'date' => 'YYmmdd',
                        ],
                    ],
                    [
                        'id' => 'a1',
                        'name' => trans(\Locales::getNamespace() . '/datatables.a1') . '<br>' . $views,
                        'search' => false,
                        'order' => false,
                        'class' => 'text-center',
                        'process' => 'RentalRatesA1',
                    ],
                    [
                        'id' => 'a2',
                        'name' => trans(\Locales::getNamespace() . '/datatables.a2') . '<br>' . $views,
                        'search' => false,
                        'order' => false,
                        'class' => 'text-center',
                        'process' => 'RentalRatesA2',
                    ],
                    [
                        'id' => 'a3',
                        'name' => trans(\Locales::getNamespace() . '/datatables.a3') . '<br>' . $views,
                        'search' => false,
                        'order' => false,
                        'class' => 'text-center',
                        'process' => 'RentalRatesA3',
                    ],
                    [
                        'id' => 's',
                        'name' => trans(\Locales::getNamespace() . '/datatables.s') . '<br>' . $views,
                        'search' => false,
                        'order' => false,
                        'class' => 'text-center',
                        'process' => 'RentalRatesS',
                    ],*/
                ],
                'orderByColumn' => 1,
                'order' => 'asc',
                'buttons' => [
                    /*[
                        'save' => true,
                        'class' => 'btn-success js-save',
                        'icon' => 'save',
                        'name' => trans(\Locales::getNamespace() . '/forms.storeButton'),
                    ],*/
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

    public function index(DataTable $datatable, RentalRatesPeriod $period, Request $request)
    {
        $table = $request->input('table') ?: $this->route;

        $datatable->setup($period/*->where('dto', '>=', Carbon::now()->setTime(0, 0, 0))*/, $this->route, $this->datatables[$this->route]);

        $datatables = $datatable->getTables();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($datatables);
        } else {
            return view(\Locales::getNamespace() . '.' . $this->route . '.index', compact('datatables', 'table'));
        }
    }

    public function create(Request $request)
    {
        $table = $request->input('table') ?: $this->route;

        $views = Views::withTranslation()->select('view_translations.name', 'view_translations.slug', 'views.id')->leftJoin('view_translations', 'view_translations.view_id', '=', 'views.id')->where('view_translations.locale', \Locales::getCurrent())->orderBy('view_translations.name')->get();
        $rooms = Room::withTranslation()->select('room_translations.name', 'room_translations.slug', 'rooms.id')->leftJoin('room_translations', 'room_translations.room_id', '=', 'rooms.id')->where('room_translations.locale', \Locales::getCurrent())->orderBy('room_translations.name')->get();
        $projects = Project::withTranslation()->select('project_translations.name', 'project_translations.slug', 'projects.id')->leftJoin('project_translations', 'project_translations.project_id', '=', 'projects.id')->where('project_translations.locale', \Locales::getCurrent())->orderBy('project_translations.name')->get();

        $types[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $types = $types + trans(\Locales::getNamespace() . '/multiselect.rentalPeriodsTypes');

        $dates = RentalRatesPeriod::selectRaw('DATE_FORMAT(dfrom, "%Y%m%d") AS "from", DATE_FORMAT(dto, "%Y%m%d") AS "to"')->whereDate('dto', '>=', Carbon::now()->startOfYear())->orderBy('dfrom')->get()->toArray();
        /*array_walk($dates, function (&$date) {
            $date['dfrom'] = Carbon::parse($date['dfrom'])->format('Ymd');
            $date['dto'] = Carbon::parse($date['dto'])->format('Ymd');
        });*/

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('table', 'views', 'rooms', 'projects', 'types', 'dates'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function store(DataTable $datatable, RentalRatesPeriod $period, RentalRatesRequest $request)
    {
        $rates = [];
        /*$error = false;
        $dates = CarbonPeriod::create(Carbon::parse($request->input('dfrom')), Carbon::parse($request->input('dto')));
        foreach ($dates as $date) {
            $newPeriod = RentalRatesPeriod::create([
                'dfrom' => $date,
                'dto' => $date,
            ]);

            if ($newPeriod->id) {
                foreach ($request->input('rates') as $room => $rooms) {
                    foreach ($rooms as $view => $rate) {
                        array_push($rates, [
                            'period_id' => $newPeriod->id,
                            'room' => $room,
                            'view' => $view,
                            'rate' => $rate ?: 0,
                        ]);
                    }
                }
            } else {
                $error = true;
            }
        }*/

        $newPeriod = RentalRatesPeriod::create($request->all());

        if ($newPeriod->id) {
        // if (!$error) {
            foreach ($request->input('rates') as $project => $projects) {
                foreach ($projects as $room => $rooms) {
                    foreach ($rooms as $view => $rate) {
                        array_push($rates, [
                            'period_id' => $newPeriod->id,
                            'project' => $project,
                            'room' => $room,
                            'view' => $view,
                            'rate' => $rate ?: 0,
                        ]);
                    }
                }
            }

            RentalRates::insert($rates);

            $successMessage = trans(\Locales::getNamespace() . '/forms.storedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityRentalRates', 1)]);

            $datatable->setup($period/*->where('dto', '>=', Carbon::now()->setTime(0, 0, 0))*/, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.createError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityRentalRates', 1)]);
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

    public function destroy(DataTable $datatable, RentalRatesPeriod $period, Request $request)
    {
        $count = count($request->input('id'));

        if ($count > 0 && $period->destroy($request->input('id'))) {
            $datatable->setup($period/*->where('dto', '>=', Carbon::now()->setTime(0, 0, 0))*/, $request->input('table'), $this->datatables[$request->input('table')], true);
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
        $period = RentalRatesPeriod::with('rates')->findOrFail($id);

        $rates = [];
        foreach ($period->rates as $rate) {
            $rates[$rate->project][$rate->room][$rate->view] = $rate->rate;
        }

        $table = $request->input('table') ?: $this->route;

        $views = Views::withTranslation()->select('view_translations.name', 'view_translations.slug', 'views.id')->leftJoin('view_translations', 'view_translations.view_id', '=', 'views.id')->where('view_translations.locale', \Locales::getCurrent())->orderBy('view_translations.name')->get();
        $rooms = Room::withTranslation()->select('room_translations.name', 'room_translations.slug', 'rooms.id')->leftJoin('room_translations', 'room_translations.room_id', '=', 'rooms.id')->where('room_translations.locale', \Locales::getCurrent())->orderBy('room_translations.name')->get();
        $projects = Project::withTranslation()->select('project_translations.name', 'project_translations.slug', 'projects.id')->leftJoin('project_translations', 'project_translations.project_id', '=', 'projects.id')->where('project_translations.locale', \Locales::getCurrent())->orderBy('project_translations.name')->get();

        $types[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $types = $types + trans(\Locales::getNamespace() . '/multiselect.rentalPeriodsTypes');

        /*
        $dates = RentalRatesPeriod::select('dfrom', 'dto')->where('id', '!=', $period->id)->whereDate('dto', '>=', Carbon::now()->startOfYear())->orderBy('dfrom')->get()->toArray();
        array_walk($dates, function (&$date) {
            $date['dfrom'] = Carbon::parse($date['dfrom'])->format('Ymd');
            $date['dto'] = Carbon::parse($date['dto'])->format('Ymd');
        });
        */

        $dates = RentalRatesPeriod::selectRaw('DATE_FORMAT(dfrom, "%Y%m%d") AS "from", DATE_FORMAT(dto, "%Y%m%d") AS "to"')->whereDate('dto', '>=', Carbon::now()->startOfYear())->orderBy('dfrom')->get()->toArray();
        /*array_walk($dates, function (&$date) {
            $date['dfrom'] = Carbon::parse($date['dfrom'])->format('Ymd');
            $date['dto'] = Carbon::parse($date['dto'])->format('Ymd');
        });*/

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('table', 'views', 'rooms', 'projects', 'types', 'dates', 'period', 'rates'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, RentalRatesRequest $request)
    {
        $rates = [];
        /*
        $error = false;
        $dates = CarbonPeriod::create(Carbon::parse($request->input('dfrom')), Carbon::parse($request->input('dto')));
        foreach ($dates as $date) {
            $newPeriod = RentalRatesPeriod::firstOrNew(['dfrom' => $date]);
            $newPeriod->fill([
                'dfrom' => $date,
                'dto' => $date,
            ])->save();

            if ($newPeriod->id) {
                RentalRates::where('period_id', $newPeriod->id)->forceDelete();
                foreach ($request->input('rates') as $room => $rooms) {
                    foreach ($rooms as $view => $rate) {
                        array_push($rates, [
                            'period_id' => $newPeriod->id,
                            'room' => $room,
                            'view' => $view,
                            'rate' => $rate ?: 0,
                        ]);
                    }
                }
            } else {
                $error = true;
            }
        }*/

        //if (!$error) {
        $period = RentalRatesPeriod::findOrFail($request->input('id'))->first();

        if ($period->update($request->all())) {
            RentalRates::where('period_id', $period->id)->forceDelete();

            foreach ($request->input('rates') as $project => $projects) {
                foreach ($projects as $room => $rooms) {
                    foreach ($rooms as $view => $rate) {
                        array_push($rates, [
                            'period_id' => $period->id,
                            'project' => $project,
                            'room' => $room,
                            'view' => $view,
                            'rate' => $rate ?: 0,
                        ]);
                    }
                }
            }

            RentalRates::insert($rates);

            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityRentalRates', 1)]);
            $datatable->setup($period/*->where('dto', '>=', Carbon::now()->setTime(0, 0, 0))*/, $request->input('table'), $this->datatables[$request->input('table')], true);
            // $datatable->setup((new RentalRatesPeriod())->where('dto', '>=', Carbon::now()->setTime(0, 0, 0)), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityRentalRates', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function save(DataTable $datatable, Request $request)
    {
        $rates = [];
        RentalRates::whereIn('period_id', array_keys($request->input('dates')))->forceDelete();
        foreach ($request->input('dates') as $id => $date) {
            foreach ($date as $room => $rooms) {
                foreach ($rooms as $view => $rate) {
                    array_push($rates, [
                        'period_id' => $id,
                        'room' => $room,
                        'view' => $view,
                        'rate' => $rate ?: 0,
                    ]);
                }
            }
        }

        RentalRates::insert($rates);

        $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityRentalRates', 1)]);

        $datatable->setup((new RentalRatesPeriod())/*->where('dto', '>=', Carbon::now()->setTime(0, 0, 0))*/, $request->input('table'), $this->datatables[$request->input('table')], true);
        $datatables = $datatable->getTables();

        return response()->json($datatables + [
            'success' => $successMessage,
        ]);
    }

}
