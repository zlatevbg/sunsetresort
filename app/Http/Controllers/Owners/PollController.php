<?php namespace App\Http\Controllers\Owners;

use App\Http\Controllers\Controller;
use App\Models\Owners\Navigation;
use App\Models\Owners\Poll;
use App\Models\Owners\Apartment;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Http\Requests\Owners\VoteRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PollController extends Controller {

    protected $route = 'polls';
    protected $datatables;
    protected $totalApartments;

    public function __construct()
    {
        $this->totalApartments = Apartment::count();

        $this->datatables = [
            $this->route => [
                'dom' => "tr",
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'columns' => [
                    [
                        'selector' => 'poll_translations.name',
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.poll'),
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
                        'name' => trans(\Locales::getNamespace() . '/datatables.dfrom'),
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
                        'name' => trans(\Locales::getNamespace() . '/datatables.dto'),
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
                        'class' => 'vertical-center text-center',
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
                            '<a href="' . \Locales::route($this->route) . '/[id]/results" class="btn btn-default"><span class="glyphicon glyphicon-stats glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/forms.resultsButton') . '</a>',
                        ],
                    ],
                ],
                'orderByColumn' => $this->route . '.id',
                'order' => 'desc',
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

    public function index(Navigation $page, DataTable $datatable, Poll $poll, $id = null, $results = null)
    {
        $owner = Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user();
        $apartments = $owner->ownership()->whereNotExists(function ($query) {
            $query->from('apartment_poll')->whereRaw('apartment_poll.apartment_id = ownership.apartment_id')->whereNull('apartment_poll.deleted_at');
        })->count();

        if ($id) {
            $poll = $poll->findOrFail($id);

            $metaTitle = $poll->name;
            $metaDescription = $poll->name;

            $breadcrumbs = [];
            $breadcrumbs[] = ['id' => 'poll', 'slug' => $poll->id, 'name' => $poll->name];

            $datatables = null;
            $totalVotes = null;
            $q1Votes = null;
            $q2Votes = null;

            if ($results) {
                if ($results != 'results') {
                    abort(404);
                }

                $breadcrumbs[] = ['id' => 'poll-results', 'slug' => 'results', 'name' => 'Results'];

                $totalApartments = $this->totalApartments;
                $votesByBuilding = Apartment::selectRaw('building_translations.name AS building, COUNT(apartments.id) AS total, COUNT(apartment_poll.apartment_id) AS votes, COUNT(apartment_poll.q1) AS q1, COUNT(apartment_poll.q2) AS q2')->leftJoin('apartment_poll', function ($join) use ($poll) {
                    $join->on('apartment_poll.apartment_id', '=', 'apartments.id')->where('apartment_poll.poll_id', '=', $poll->id);
                })->leftJoin('building_translations', 'building_translations.building_id', '=', 'apartments.building_id')->where('building_translations.locale', '=', \Locales::getCurrent())->groupBy('apartments.building_id')->orderBy('apartments.building_id')->get()->toArray();
                $totalVotes = array_sum(array_column($votesByBuilding, 'votes'));
                $q1Votes = array_sum(array_column($votesByBuilding, 'q1'));
                $q2Votes = array_sum(array_column($votesByBuilding, 'q2'));

                $buildings = [];
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
                $datatables = $datatable->getTables();
            } else {
                if (!$apartments || Carbon::parse($poll->dto)->toDateString() < Carbon::now()->toDateString()) {
                    abort(404);
                }

                $poll->content = str_replace('[[Q1]]', '<div class="form-group checkbox-big"><input id="input-q1" name="q1" type="checkbox" value="1"><label for="input-q1" class="checkbox-inline text-uppercase h3 text-primary"><strong>' . trans(\Locales::getNamespace() . '/forms.q1Label') . '</strong></label></div>', $poll->content);
                $poll->content = str_replace('[[Q2]]', '<div class="form-group checkbox-big"><input id="input-q2" name="q2" type="checkbox" value="1"><label for="input-q2" class="checkbox-inline text-uppercase h3 text-primary"><strong>' . trans(\Locales::getNamespace() . '/forms.q2Label') . '</strong></label></div>', $poll->content);
            }

            return view(\Locales::getNamespace() . '/' . $this->route . '.index', compact('poll', 'breadcrumbs', 'metaTitle', 'metaDescription', 'datatables', 'results', 'buildings', 'totalApartments', 'totalVotes', 'q1Votes', 'q2Votes'));
        } else {
            $page = $page->where('slug', \Locales::getDomain()->route)->where('locale_id', \Locales::getId())->firstOrFail();

            /*if ($apartments) {
                $last = count($this->datatables[$this->route]['columns']) - 1;
                array_unshift($this->datatables[$this->route]['columns'][$last]['buttons'], '<a href="' . \Locales::route($this->route) . '/[id]" class="btn btn-info glyphicon-left"><span class="glyphicon glyphicon-edit glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/forms.voteButton') . '</a>');
            }*/

            $datatable->setup($poll, $this->route, $this->datatables[$this->route]);
            $datatables = $datatable->getTables();

            return view(\Locales::getNamespace() . '/' . $this->route . '.index', compact('page', 'datatables'));
        }
    }

    public function vote(VoteRequest $request, $id)
    {
        $poll = Poll::whereDate('dto', '>=', Carbon::now()->toDateString())->findOrFail($id);

        $data = [];
        $owner = Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user();
        $parameters = ['q1' => $request->input('q1'), 'q2' => $request->input('q2'), 'owner_id' => $owner->id];
        $apartments = $owner->ownership()->whereNotExists(function ($query) {
            $query->from('apartment_poll')->whereRaw('apartment_poll.apartment_id = ownership.apartment_id')->whereNull('apartment_poll.deleted_at');
        })->pluck('apartment_id');

        if (!$apartments->count()) {
            return response()->json(['redirect' => \Locales::route($this->route)]);
        }

        foreach ($apartments as $key => $apartment) {
            $data[$apartment] = $parameters;
        }

        $poll->votes()->attach($data);

        $request->session()->flash('success', [trans(\Locales::getNamespace() . '/forms.votedSuccessfully')]);
        return response()->json(['redirect' => \Locales::route($this->route)]);
    }

}
