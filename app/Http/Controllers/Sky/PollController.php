<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Models\Sky\Poll;
use App\Models\Sky\Apartment;
use App\Http\Requests\Sky\PollRequest;

class PollController extends Controller {

    protected $route = 'polls';
    protected $datatables;
    protected $totalApartments;

    public function __construct()
    {
        $this->totalApartments = Apartment::count();

        $this->datatables = [
            $this->route => [
                'translation' => true,
                'title' => trans(\Locales::getNamespace() . '/datatables.titlePolls'),
                'url' => \Locales::route($this->route),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'columns' => [
                    [
                        'selector' => $this->route . '.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'vertical-center text-center',
                    ],
                    [
                        'selector' => 'poll_translations.name',
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.name'),
                        'search' => true,
                        'order' => false,
                        'class' => 'vertical-center',
                        'join' => [
                            'table' => 'poll_translations',
                            'localColumn' => 'poll_translations.poll_id',
                            'constrain' => '=',
                            'foreignColumn' => $this->route . '.id',
                            'whereColumn' => 'poll_translations.locale',
                            'whereConstrain' => '=',
                            'whereValue' => \Locales::getCurrent(),
                        ],
                    ],
                    [
                        'selector' => $this->route . '.dfrom',
                        'id' => 'dfrom',
                        'name' => trans(\Locales::getNamespace() . '/datatables.dateFrom'),
                        'search' => true,
                        'class' => 'vertical-center text-center',
                        'data' => [
                            'type' => 'sort',
                            'id' => 'dfrom',
                            'date' => 'YYmmdd',
                        ],
                    ],
                    [
                        'selector' => $this->route . '.dto',
                        'id' => 'dto',
                        'name' => trans(\Locales::getNamespace() . '/datatables.dateTo'),
                        'search' => true,
                        'class' => 'vertical-center text-center',
                        'data' => [
                            'type' => 'sort',
                            'id' => 'dto',
                            'date' => 'YYmmdd',
                        ],
                    ],
                    [
                        'selector' => '',
                        'id' => 'votes',
                        'name' => trans(\Locales::getNamespace() . '/datatables.votes'),
                        'class' => 'vertical-center text-right',
                        'order' => false,
                        'aggregate' => 'votesCount',
                        'append' => [
                            'simpleText' => ' / ' . $this->totalApartments,
                        ],
                    ],
                    [
                        'selector' => $this->route . '.id',
                        'id' => 'actions',
                        'name' => trans(\Locales::getNamespace() . '/datatables.actions'),
                        'class' => 'vertical-center text-center',
                        'buttons' => [
                            '<a href="' . \Locales::route($this->route) . '/[id]" class="btn btn-info glyphicon-left"><span class="glyphicon glyphicon-edit glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/forms.voteButton') . '</a>',
                            '<a href="' . \Locales::route($this->route) . '/[id]" class="btn btn-default"><span class="glyphicon glyphicon-stats glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/forms.resultsButton') . '</a>',
                        ],
                    ],
                ],
                'orderByColumn' => 'id',
                'order' => 'desc',
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
            'alpha' => [
                'type' => 'poll',
                'dom' => "<'dataTableFull'l>tr<'clearfix'<'dataTableI'i><'dataTableP'p>>",
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'subtitle' => trans(\Locales::getNamespace() . '/datatables.titleBuildingAlpha'),
                'columns' => [
                    [
                        'selector' => 'apartments.number',
                        'id' => 'number',
                        'name' => trans(\Locales::getNamespace() . '/datatables.apartment'),
                        'class' => 'vertical-center',
                        'search' => true,
                        'order' => false,
                    ],
                    [
                        'selector' => 'apartment_poll.q1',
                        'id' => 'q1',
                        'name' => trans(\Locales::getNamespace() . '/datatables.q1'),
                        'order' => false,
                        'class' => 'vertical-center text-center',
                        'trans' => 'questions',
                    ],
                    [
                        'selector' => 'apartment_poll.q2',
                        'id' => 'q2',
                        'name' => trans(\Locales::getNamespace() . '/datatables.q2'),
                        'order' => false,
                        'class' => 'vertical-center text-center',
                        'trans' => 'questions',
                    ],
                ],
                'orderByRaw' => 'CONCAT(SUBSTR(apartments.number, 1, LOCATE("-", apartments.number) - 1), LPAD(SUBSTR(apartments.number, LOCATE("-", apartments.number) + 1), 3, "0"))',
                'orderByColumn' => 'number',
                'order' => 'asc',
                'footer' => [
                    'class' => 'bg-info',
                    'columns' => [
                        [ // apartment
                            'data' => '<span></span>',
                            'count' => true,
                        ],
                        [ // q1
                            'data' => '<span></span>',
                            'filter' => [
                                [
                                    'comparison' => '=',
                                    'value' => '✓',
                                ],
                            ],
                        ],
                        [ // q2
                            'data' => '<span></span>',
                            'filter' => [
                                [
                                    'comparison' => '=',
                                    'value' => '✓',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'beta' => [
                'type' => 'poll',
                'dom' => "<'dataTableFull'l>tr<'clearfix'<'dataTableI'i><'dataTableP'p>>",
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'subtitle' => trans(\Locales::getNamespace() . '/datatables.titleBuildingBeta'),
                'columns' => [
                    [
                        'selector' => 'apartments.number',
                        'id' => 'number',
                        'name' => trans(\Locales::getNamespace() . '/datatables.apartment'),
                        'class' => 'vertical-center',
                        'search' => true,
                        'order' => false,
                    ],
                    [
                        'selector' => 'apartment_poll.q1',
                        'id' => 'q1',
                        'name' => trans(\Locales::getNamespace() . '/datatables.q1'),
                        'order' => false,
                        'class' => 'vertical-center text-center',
                        'trans' => 'questions',
                    ],
                    [
                        'selector' => 'apartment_poll.q2',
                        'id' => 'q2',
                        'name' => trans(\Locales::getNamespace() . '/datatables.q2'),
                        'order' => false,
                        'class' => 'vertical-center text-center',
                        'trans' => 'questions',
                    ],
                ],
                'orderByRaw' => 'CONCAT(SUBSTR(apartments.number, 1, LOCATE("-", apartments.number) - 1), LPAD(SUBSTR(apartments.number, LOCATE("-", apartments.number) + 1), 3, "0"))',
                'orderByColumn' => 'number',
                'order' => 'asc',
                'footer' => [
                    'class' => 'bg-info',
                    'columns' => [
                        [ // apartment
                            'data' => '<span></span>',
                            'count' => true,
                        ],
                        [ // q1
                            'data' => '<span></span>',
                            'filter' => [
                                [
                                    'comparison' => '=',
                                    'value' => '✓',
                                ],
                            ],
                        ],
                        [ // q2
                            'data' => '<span></span>',
                            'filter' => [
                                [
                                    'comparison' => '=',
                                    'value' => '✓',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'sigma' => [
                'dom' => "<'dataTableFull'l>tr<'clearfix'<'dataTableI'i><'dataTableP'p>>",
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'subtitle' => trans(\Locales::getNamespace() . '/datatables.titleBuildingSigma'),
                'columns' => [
                    [
                        'selector' => 'apartments.number',
                        'id' => 'number',
                        'name' => trans(\Locales::getNamespace() . '/datatables.apartment'),
                        'class' => 'vertical-center',
                        'search' => true,
                        'order' => false,
                    ],
                    [
                        'selector' => 'apartment_poll.q1',
                        'id' => 'q1',
                        'name' => trans(\Locales::getNamespace() . '/datatables.q1'),
                        'order' => false,
                        'class' => 'vertical-center text-center',
                        'trans' => 'questions',
                    ],
                    [
                        'selector' => 'apartment_poll.q2',
                        'id' => 'q2',
                        'name' => trans(\Locales::getNamespace() . '/datatables.q2'),
                        'order' => false,
                        'class' => 'vertical-center text-center',
                        'trans' => 'questions',
                    ],
                ],
                'orderByRaw' => 'CONCAT(SUBSTR(apartments.number, 1, LOCATE("-", apartments.number) - 1), LPAD(SUBSTR(apartments.number, LOCATE("-", apartments.number) + 1), 3, "0"))',
                'orderByColumn' => 'number',
                'order' => 'asc',
                'footer' => [
                    'class' => 'bg-info',
                    'columns' => [
                        [ // apartment
                            'data' => '<span></span>',
                            'count' => true,
                        ],
                        [ // q1
                            'data' => '<span></span>',
                            'filter' => [
                                [
                                    'comparison' => '=',
                                    'value' => '✓',
                                ],
                            ],
                        ],
                        [ // q2
                            'data' => '<span></span>',
                            'filter' => [
                                [
                                    'comparison' => '=',
                                    'value' => '✓',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'delta' => [
                'type' => 'poll',
                'dom' => "<'dataTableFull'l>tr<'clearfix'<'dataTableI'i><'dataTableP'p>>",
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'subtitle' => trans(\Locales::getNamespace() . '/datatables.titleBuildingDelta'),
                'columns' => [
                    [
                        'selector' => 'apartments.number',
                        'id' => 'number',
                        'name' => trans(\Locales::getNamespace() . '/datatables.apartment'),
                        'class' => 'vertical-center',
                        'search' => true,
                        'order' => false,
                    ],
                    [
                        'selector' => 'apartment_poll.q1',
                        'id' => 'q1',
                        'name' => trans(\Locales::getNamespace() . '/datatables.q1'),
                        'order' => false,
                        'class' => 'vertical-center text-center',
                        'trans' => 'questions',
                    ],
                    [
                        'selector' => 'apartment_poll.q2',
                        'id' => 'q2',
                        'name' => trans(\Locales::getNamespace() . '/datatables.q2'),
                        'order' => false,
                        'class' => 'vertical-center text-center',
                        'trans' => 'questions',
                    ],
                ],
                'orderByRaw' => 'CONCAT(SUBSTR(apartments.number, 1, LOCATE("-", apartments.number) - 1), LPAD(SUBSTR(apartments.number, LOCATE("-", apartments.number) + 1), 3, "0"))',
                'orderByColumn' => 'number',
                'order' => 'asc',
                'footer' => [
                    'class' => 'bg-info',
                    'columns' => [
                        [ // apartment
                            'data' => '<span></span>',
                            'count' => true,
                        ],
                        [ // q1
                            'data' => '<span></span>',
                            'filter' => [
                                [
                                    'comparison' => '=',
                                    'value' => '✓',
                                ],
                            ],
                        ],
                        [ // q2
                            'data' => '<span></span>',
                            'filter' => [
                                [
                                    'comparison' => '=',
                                    'value' => '✓',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'eta' => [
                'type' => 'poll',
                'dom' => "<'dataTableFull'l>tr<'clearfix'<'dataTableI'i><'dataTableP'p>>",
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'subtitle' => trans(\Locales::getNamespace() . '/datatables.titleBuildingEta'),
                'columns' => [
                    [
                        'selector' => 'apartments.number',
                        'id' => 'number',
                        'name' => trans(\Locales::getNamespace() . '/datatables.apartment'),
                        'class' => 'vertical-center',
                        'search' => true,
                        'order' => false,
                    ],
                    [
                        'selector' => 'apartment_poll.q1',
                        'id' => 'q1',
                        'name' => trans(\Locales::getNamespace() . '/datatables.q1'),
                        'order' => false,
                        'class' => 'vertical-center text-center',
                        'trans' => 'questions',
                    ],
                    [
                        'selector' => 'apartment_poll.q2',
                        'id' => 'q2',
                        'name' => trans(\Locales::getNamespace() . '/datatables.q2'),
                        'order' => false,
                        'class' => 'vertical-center text-center',
                        'trans' => 'questions',
                    ],
                ],
                'orderByRaw' => 'CONCAT(SUBSTR(apartments.number, 1, LOCATE("-", apartments.number) - 1), LPAD(SUBSTR(apartments.number, LOCATE("-", apartments.number) + 1), 3, "0"))',
                'orderByColumn' => 'number',
                'order' => 'asc',
                'footer' => [
                    'class' => 'bg-info',
                    'columns' => [
                        [ // apartment
                            'data' => '<span></span>',
                            'count' => true,
                        ],
                        [ // q1
                            'data' => '<span></span>',
                            'filter' => [
                                [
                                    'comparison' => '=',
                                    'value' => '✓',
                                ],
                            ],
                        ],
                        [ // q2
                            'data' => '<span></span>',
                            'filter' => [
                                [
                                    'comparison' => '=',
                                    'value' => '✓',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'feta' => [
                'type' => 'poll',
                'dom' => "<'dataTableFull'l>tr<'clearfix'<'dataTableI'i><'dataTableP'p>>",
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'subtitle' => trans(\Locales::getNamespace() . '/datatables.titleBuildingFeta'),
                'columns' => [
                    [
                        'selector' => 'apartments.number',
                        'id' => 'number',
                        'name' => trans(\Locales::getNamespace() . '/datatables.apartment'),
                        'class' => 'vertical-center',
                        'search' => true,
                        'order' => false,
                    ],
                    [
                        'selector' => 'apartment_poll.q1',
                        'id' => 'q1',
                        'name' => trans(\Locales::getNamespace() . '/datatables.q1'),
                        'order' => false,
                        'class' => 'vertical-center text-center',
                        'trans' => 'questions',
                    ],
                    [
                        'selector' => 'apartment_poll.q2',
                        'id' => 'q2',
                        'name' => trans(\Locales::getNamespace() . '/datatables.q2'),
                        'order' => false,
                        'class' => 'vertical-center text-center',
                        'trans' => 'questions',
                    ],
                ],
                'orderByRaw' => 'CONCAT(SUBSTR(apartments.number, 1, LOCATE("-", apartments.number) - 1), LPAD(SUBSTR(apartments.number, LOCATE("-", apartments.number) + 1), 3, "0"))',
                'orderByColumn' => 'number',
                'order' => 'asc',
                'footer' => [
                    'class' => 'bg-info',
                    'columns' => [
                        [ // apartment
                            'data' => '<span></span>',
                            'count' => true,
                        ],
                        [ // q1
                            'data' => '<span></span>',
                            'filter' => [
                                [
                                    'comparison' => '=',
                                    'value' => '✓',
                                ],
                            ],
                        ],
                        [ // q2
                            'data' => '<span></span>',
                            'filter' => [
                                [
                                    'comparison' => '=',
                                    'value' => '✓',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'geta' => [
                'type' => 'poll',
                'dom' => "<'dataTableFull'l>tr<'clearfix'<'dataTableI'i><'dataTableP'p>>",
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'subtitle' => trans(\Locales::getNamespace() . '/datatables.titleBuildingGeta'),
                'columns' => [
                    [
                        'selector' => 'apartments.number',
                        'id' => 'number',
                        'name' => trans(\Locales::getNamespace() . '/datatables.apartment'),
                        'class' => 'vertical-center',
                        'search' => true,
                        'order' => false,
                    ],
                    [
                        'selector' => 'apartment_poll.q1',
                        'id' => 'q1',
                        'name' => trans(\Locales::getNamespace() . '/datatables.q1'),
                        'order' => false,
                        'class' => 'vertical-center text-center',
                        'trans' => 'questions',
                    ],
                    [
                        'selector' => 'apartment_poll.q2',
                        'id' => 'q2',
                        'name' => trans(\Locales::getNamespace() . '/datatables.q2'),
                        'order' => false,
                        'class' => 'vertical-center text-center',
                        'trans' => 'questions',
                    ],
                ],
                'orderByRaw' => 'CONCAT(SUBSTR(apartments.number, 1, LOCATE("-", apartments.number) - 1), LPAD(SUBSTR(apartments.number, LOCATE("-", apartments.number) + 1), 3, "0"))',
                'orderByColumn' => 'number',
                'order' => 'asc',
                'footer' => [
                    'class' => 'bg-info',
                    'columns' => [
                        [ // apartment
                            'data' => '<span></span>',
                            'count' => true,
                        ],
                        [ // q1
                            'data' => '<span></span>',
                            'filter' => [
                                [
                                    'comparison' => '=',
                                    'value' => '✓',
                                ],
                            ],
                        ],
                        [ // q2
                            'data' => '<span></span>',
                            'filter' => [
                                [
                                    'comparison' => '=',
                                    'value' => '✓',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'heta' => [
                'type' => 'poll',
                'dom' => "<'dataTableFull'l>tr<'clearfix'<'dataTableI'i><'dataTableP'p>>",
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'subtitle' => trans(\Locales::getNamespace() . '/datatables.titleBuildingHeta'),
                'columns' => [
                    [
                        'selector' => 'apartments.number',
                        'id' => 'number',
                        'name' => trans(\Locales::getNamespace() . '/datatables.apartment'),
                        'class' => 'vertical-center',
                        'search' => true,
                        'order' => false,
                    ],
                    [
                        'selector' => 'apartment_poll.q1',
                        'id' => 'q1',
                        'name' => trans(\Locales::getNamespace() . '/datatables.q1'),
                        'order' => false,
                        'class' => 'vertical-center text-center',
                        'trans' => 'questions',
                    ],
                    [
                        'selector' => 'apartment_poll.q2',
                        'id' => 'q2',
                        'name' => trans(\Locales::getNamespace() . '/datatables.q2'),
                        'order' => false,
                        'class' => 'vertical-center text-center',
                        'trans' => 'questions',
                    ],
                ],
                'orderByRaw' => 'CONCAT(SUBSTR(apartments.number, 1, LOCATE("-", apartments.number) - 1), LPAD(SUBSTR(apartments.number, LOCATE("-", apartments.number) + 1), 3, "0"))',
                'orderByColumn' => 'number',
                'order' => 'asc',
                'footer' => [
                    'class' => 'bg-info',
                    'columns' => [
                        [ // apartment
                            'data' => '<span></span>',
                            'count' => true,
                        ],
                        [ // q1
                            'data' => '<span></span>',
                            'filter' => [
                                [
                                    'comparison' => '=',
                                    'value' => '✓',
                                ],
                            ],
                        ],
                        [ // q2
                            'data' => '<span></span>',
                            'filter' => [
                                [
                                    'comparison' => '=',
                                    'value' => '✓',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function index(DataTable $datatable, Poll $poll, Request $request, $id = null)
    {
        $buildings = [];
        $totalVotes = null;
        $q1Votes = null;
        $q2Votes = null;
        $totalApartments = $this->totalApartments;

        if ($id) {
            $poll = $poll->findOrFail($id);

            $votesByBuilding = Apartment::selectRaw('building_translations.name AS building, COUNT(apartments.id) AS total, COUNT(apartment_poll.apartment_id) AS votes, COUNT(apartment_poll.q1) AS q1, COUNT(apartment_poll.q2) AS q2')->leftJoin('apartment_poll', function ($join) use ($poll) {
                $join->on('apartment_poll.apartment_id', '=', 'apartments.id')->where('apartment_poll.poll_id', '=', $poll->id);
            })->leftJoin('building_translations', 'building_translations.building_id', '=', 'apartments.building_id')->where('building_translations.locale', '=', \Locales::getCurrent())->groupBy('apartments.building_id')->orderBy('apartments.building_id')->get()->toArray();
            $totalVotes = array_sum(array_column($votesByBuilding, 'votes'));
            $q1Votes = array_sum(array_column($votesByBuilding, 'q1'));
            $q2Votes = array_sum(array_column($votesByBuilding, 'q2'));

            foreach ($votesByBuilding as $building) {
                array_push($buildings, [$building['building'], round(($building['votes'] / $building['total']) * 100, 2), $building['building'] . "\n" . trans(\Locales::getNamespace() . '/messages.votes') . ': '. $building['votes'] . ' (' . round(($building['votes'] / $building['total']) * 100, 2) . '%)']);
            }

            $datatable->setup(Apartment::rightJoin('apartment_poll', 'apartment_poll.apartment_id', '=', 'apartments.id')->whereNull('apartment_poll.deleted_at')->where('apartments.building_id', 1), 'alpha', $this->datatables['alpha']);
            $datatable->setup(Apartment::rightJoin('apartment_poll', 'apartment_poll.apartment_id', '=', 'apartments.id')->whereNull('apartment_poll.deleted_at')->where('apartments.building_id', 2), 'beta', $this->datatables['beta']);
            $datatable->setup(Apartment::rightJoin('apartment_poll', 'apartment_poll.apartment_id', '=', 'apartments.id')->whereNull('apartment_poll.deleted_at')->where('apartments.building_id', 3), 'sigma', $this->datatables['sigma']);
            $datatable->setup(Apartment::rightJoin('apartment_poll', 'apartment_poll.apartment_id', '=', 'apartments.id')->whereNull('apartment_poll.deleted_at')->where('apartments.building_id', 4), 'delta', $this->datatables['delta']);
            $datatable->setup(Apartment::rightJoin('apartment_poll', 'apartment_poll.apartment_id', '=', 'apartments.id')->whereNull('apartment_poll.deleted_at')->where('apartments.building_id', 5), 'eta', $this->datatables['eta']);
            $datatable->setup(Apartment::rightJoin('apartment_poll', 'apartment_poll.apartment_id', '=', 'apartments.id')->whereNull('apartment_poll.deleted_at')->where('apartments.building_id', 6), 'feta', $this->datatables['feta']);
            $datatable->setup(Apartment::rightJoin('apartment_poll', 'apartment_poll.apartment_id', '=', 'apartments.id')->whereNull('apartment_poll.deleted_at')->where('apartments.building_id', 7), 'geta', $this->datatables['geta']);
            $datatable->setup(Apartment::rightJoin('apartment_poll', 'apartment_poll.apartment_id', '=', 'apartments.id')->whereNull('apartment_poll.deleted_at')->where('apartments.building_id', 8), 'heta', $this->datatables['heta']);
        } else {
            $datatable->setup($poll, $this->route, $this->datatables[$this->route]);
            $poll = null;
        }

        $datatables = $datatable->getTables();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($datatables);
        } else {
            return view(\Locales::getNamespace() . '.' . $this->route . '.index', compact('datatables', 'poll', 'buildings', 'totalApartments', 'totalVotes', 'q1Votes', 'q2Votes'));
        }
    }

    public function create(Request $request)
    {
        $table = $request->input('table') ?: $this->route;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('table'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function store(DataTable $datatable, Poll $poll, PollRequest $request)
    {
        $data = \Locales::prepareTranslations($request);

        $newPoll = Poll::create($data);

        if ($newPoll->id) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.storedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityPolls', 1)]);

            $datatable->setup($poll, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'reset' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.createError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityPolls', 1)]);
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

    public function destroy(DataTable $datatable, Poll $poll, Request $request)
    {
        $count = count($request->input('id'));

        if ($count > 0 && $poll->destroy($request->input('id'))) {
            $datatable->setup($poll, $request->input('table'), $this->datatables[$request->input('table')], true);
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
        $poll = Poll::findOrFail($id);

        $table = $request->input('table') ?: $this->route;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('poll', 'table'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, PollRequest $request)
    {
        $poll = Poll::findOrFail($request->input('id'))->first();

        $data = \Locales::prepareTranslations($request);

        if ($poll->update($data)) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityPolls', 1)]);

            $datatable->setup($poll, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityPolls', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

}
