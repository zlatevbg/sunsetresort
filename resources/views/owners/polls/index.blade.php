@extends(\Locales::getNamespace() . '.master')

@section('content')
    <h1 class="text-center">{{ $metaTitle or \Locales::getMenu(\Slug::getRouteSlug())['title'] }}</h1>

    @if (isset($poll))
        <div class="page-wrapper" style="margin-top: 2rem;">
            @if (isset($results))
                <section class="google-analytics-wrapper mb-5">
                    <article class="google-analytics-chart chart-left">
                        <h1>{{ trans(\Locales::getNamespace() . '/messages.totalVotes') }}</h1>
                        <div id="chart-total-votes"></div>
                    </article>
                    <article class="google-analytics-chart chart-right">
                        <h1>{{ trans(\Locales::getNamespace() . '/messages.votesByBuilding') }}</h1>
                        <div id="chart-votes-by-building"></div>
                    </article>
                    <article class="google-analytics-chart chart-left">
                        <h1>{{ trans(\Locales::getNamespace() . '/messages.votesQ1') }}</h1>
                        <div id="chart-q1-votes"></div>
                    </article>
                    <article class="google-analytics-chart chart-right">
                        <h1>{{ trans(\Locales::getNamespace() . '/messages.votesQ2') }}</h1>
                        <div id="chart-q2-votes"></div>
                    </article>
                </section>

                @if (isset($datatables) && count($datatables) > 0)
                    @include(\Locales::getNamespace() . '/partials.datatables')
                @endif
            @else
                {!! Form::open(['url' => \Locales::route('vote', [$poll->id]), 'id' => 'vote-form', 'class' => 'ajax-lock', 'data-ajax-queue' => 'sync', 'role' => 'form']) !!}
                    {!! $poll->content !!}
                    <p class="text-center" style="margin-top: 2rem;">{!! Form::submit(trans(\Locales::getNamespace() . '/forms.sendVoteButton'), ['class' => 'btn btn-lg btn-primary']) !!}</p>
                {!! Form::close() !!}
            @endif
        </div>
    @else
        @if (isset($datatables) && count($datatables) > 0)
            @include(\Locales::getNamespace() . '/partials.datatables')
        @endif
    @endif
@endsection

@if (isset($datatables) && count($datatables) > 0)
@section('script')
    unikat.callback = function() {
        this.datatables({!! json_encode($datatables) !!});
    };
@endsection
@endif

@if (isset($poll) && isset($results))
    @section('scripts')
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script>
            var pieChartOptions = {
                'legend': {
                    'position': 'none',
                },
                'is3D': true,
                'sliceVisibilityThreshold': 0,
                'pieSliceText': 'value',
                'pieResidueSliceLabel': '{{ trans(\Locales::getNamespace() . '/messages.other') }}',
                'pieSliceTextStyle': {
                    'fontSize': 24,
                },
                'chartArea': {
                    'top': 10,
                    'width': '100%',
                    'height': '300',
                },
            };

            var pieBottomChartOptions = {
                'legend': {
                    'position': 'bottom',
                    'alignment': 'center',
                    'textStyle': {
                        'fontSize': 12,
                    },
                },
                'is3D': true,
                'sliceVisibilityThreshold': 0,
                'pieResidueSliceLabel': '{{ trans(\Locales::getNamespace() . '/messages.other') }}',
                'pieSliceTextStyle': {
                    'fontSize': 24,
                },
                'chartArea': {
                    'top': 10,
                    'width': '100%',
                    'height': '300',
                },
            };

            var pieRightChartOptions = {
                'legend': {
                    'position': 'right',
                    'alignment': 'center',
                    'textStyle': {
                        'fontSize': 12,
                    },
                },
                'is3D': true,
                'sliceVisibilityThreshold': 0,
                'pieSliceText': 'value',
                'pieResidueSliceLabel': '{{ trans(\Locales::getNamespace() . '/messages.other') }}',
                'pieSliceTextStyle': {
                    'fontSize': 24,
                },
                'chartArea': {
                    'top': 10,
                    'width': '100%',
                    'height': '300',
                },
            };

            function drawTotalVotes() {
                var data = new google.visualization.DataTable();
                data.addColumn('string', '{{ trans(\Locales::getNamespace() . '/messages.votes') }}');
                data.addColumn('number', '{{ trans(\Locales::getNamespace() . '/messages.total') }}');
                data.addRows([['{{ trans(\Locales::getNamespace() . '/messages.votes') }}', {{ $totalVotes }}]]);
                data.addRows([['{{ trans(\Locales::getNamespace() . '/messages.novotes') }}', {{ $totalApartments - $totalVotes }}]]);

                var chart = new google.visualization.PieChart(document.getElementById('chart-total-votes'));
                chart.draw(data, pieBottomChartOptions);
            }

            function drawVotesByBuilding() {
                var data = new google.visualization.DataTable();
                data.addColumn('string', '{{ trans(\Locales::getNamespace() . '/messages.votes') }}');
                data.addColumn('number', '{{ trans(\Locales::getNamespace() . '/messages.total') }}');
                data.addColumn({type: 'string', role: 'tooltip'});

                data.addRows({!! json_encode($buildings) !!});

                var formatter = new google.visualization.NumberFormat({suffix: '%'});
                formatter.format(data, 1);

                var chart = new google.visualization.PieChart(document.getElementById('chart-votes-by-building'));
                chart.draw(data, pieRightChartOptions);
            }

            function drawQ1Votes() {
                var data = new google.visualization.DataTable();
                data.addColumn('string', '{{ trans(\Locales::getNamespace() . '/messages.votes') }}');
                data.addColumn('number', '{{ trans(\Locales::getNamespace() . '/messages.total') }}');
                data.addColumn({type: 'string', role: 'tooltip'});
                data.addRows([['{{ trans(\Locales::getNamespace() . '/messages.votesQ1') }}', {{ number_format(($q1Votes / $totalApartments) * 100, 2, '.', '') }}, '{{ trans(\Locales::getNamespace() . '/messages.votesQ1') }}\n {{ $q1Votes }} ({{ round(($q1Votes / $totalApartments) * 100, 2) }}%)']]);

                var formatter = new google.visualization.NumberFormat({suffix: '%'});
                formatter.format(data, 1);

                var chart = new google.visualization.PieChart(document.getElementById('chart-q1-votes'));
                chart.draw(data, pieChartOptions);
            }

            function drawQ2Votes() {
                var data = new google.visualization.DataTable();
                data.addColumn('string', '{{ trans(\Locales::getNamespace() . '/messages.votes') }}');
                data.addColumn('number', '{{ trans(\Locales::getNamespace() . '/messages.total') }}');
                data.addColumn({type: 'string', role: 'tooltip'});
                data.addRows([['{{ trans(\Locales::getNamespace() . '/messages.votesQ2') }}', {{ number_format(($q2Votes / $totalApartments) * 100, 2, '.', '') }}, '{{ trans(\Locales::getNamespace() . '/messages.votesQ2') }}\n {{ $q2Votes }} ({{ round(($q2Votes / $totalApartments) * 100, 2) }}%)']]);

                var formatter = new google.visualization.NumberFormat({suffix: '%'});
                formatter.format(data, 1);

                var chart = new google.visualization.PieChart(document.getElementById('chart-q2-votes'));
                chart.draw(data, pieChartOptions);
            }

            google.charts.load('current', {
                'packages': [
                    'corechart',
                ],
            });
            google.charts.setOnLoadCallback(drawTotalVotes);
            google.charts.setOnLoadCallback(drawVotesByBuilding);
            google.charts.setOnLoadCallback(drawQ1Votes);
            google.charts.setOnLoadCallback(drawQ2Votes);
        </script>
    @endsection
@endif
