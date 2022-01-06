@extends(\Locales::getNamespace() . '.master')

@section('content')
    @include(\Locales::getNamespace() . '/shared.errors')

    <div class="report-wrapper">
        <h1 class="h2 text-center">{{ trans(\Locales::getNamespace() . '/datatables.titleReportAgents') }}</h1>

        {!! Form::open(['url' => \Locales::route('reports/agents/generate'), 'id' => 'report-form', 'data-ajax-queue' => 'sync', 'data-alert-position' => 'insertAfter', 'class' => 'ajax-lock form-inline', 'role' => 'form']) !!}

        <div class="row-wrapper">
            <div class="form-group ajax-lock">
                {!! Form::label('input-projects', trans(\Locales::getNamespace() . '/forms.projectLabel')) !!}
                {!! Form::multiselect('projects[]', $multiselect['projects'], ['id' => 'input-projects', 'class' => 'form-control', 'multiple' => 'multiple', 'data-ajax-queue' => 'sync', 'data-href' => \Locales::route('reports/agents/get-buildings')]) !!}
            </div>
            <div class="form-group ajax-lock">
                {!! Form::label('input-buildings', trans(\Locales::getNamespace() . '/forms.buildingLabel')) !!}
                {!! Form::multiselect('buildings[]', $multiselect['buildings'], ['id' => 'input-buildings', 'class' => 'form-control', 'multiple' => 'multiple', 'data-ajax-queue' => 'sync', 'data-href' => \Locales::route('reports/agents/get-apartments')]) !!}
            </div>
            <div class="form-group">
                {!! Form::label('input-apartments', trans(\Locales::getNamespace() . '/forms.apartmentsLabel')) !!}
                {!! Form::multiselect('apartments[]', $multiselect['apartments'], ['id' => 'input-apartments', 'class' => 'form-control', 'multiple' => 'multiple']) !!}
            </div>
        </div>
        <div class="row-wrapper">
            <div class="form-group">
                {!! Form::label('input-agents', trans(\Locales::getNamespace() . '/forms.agentsLabel')) !!}
                {!! Form::multiselect('agents[]', $multiselect['agents'], ['id' => 'input-agents', 'class' => 'form-control', 'multiple' => 'multiple']) !!}
            </div>
            <div class="form-group">
                {!! Form::label('input-group', trans(\Locales::getNamespace() . '/forms.groupByLabel')) !!}
                {!! Form::multiselect('group', $multiselect['group'], ['id' => 'input-group', 'class' => 'form-control']) !!}
            </div>
        </div>
        <div class="row-wrapper">
            <div class="btn-group">{!! Form::button('<span class="glyphicon glyphicon-cog glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/forms.generateReportButton'), ['type' => 'submit', 'class' => 'btn btn-primary']) !!}</div>
            <div class="btn-group">
                <a id="button-reset-report" href="{{ \Locales::route('reports/agents') }}" class="btn btn-danger disabled">
                    <span class="glyphicon glyphicon-remove glyphicon-left"></span>{{ trans(\Locales::getNamespace() . '/forms.resetButton') }}
                </a>
            </div>
            <div class="btn-group">{!! Form::button('<span class="glyphicon glyphicon-save glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/forms.downloadReportButton'), ['id' => 'button-download-report', 'class' => 'btn btn-warning disabled']) !!}</div>
        </div>
        {!! Form::close() !!}
    </div>

    @if (isset($datatables) && count($datatables) > 0)
        @include(\Locales::getNamespace() . '/partials.datatables')
    @endif
@endsection

@section('jsFiles')
    jsFiles.push('{{ \App\Helpers\autover('/js/' . \Locales::getNamespace() . '/vendor/jquery-ui.js') }}');
    jsFiles.push('{{ \App\Helpers\autover('/js/' . \Locales::getNamespace() . '/vendor/jquery.multiselect.js') }}');
    jsFiles.push('{{ \App\Helpers\autover('/js/' . \Locales::getNamespace() . '/vendor/jquery.qtip.js') }}');

    @parent
@endsection

@if (isset($datatables) && count($datatables) > 0)
@section('script')
    var selectedProjects = null;
    var selectedBuildings = null;

    unikat.callback = function() {
        this.datatables({!! json_encode($datatables) !!});
    };

    unikat.multiselect = {
        'input-projects': {
            close: function() {
                selectedProjects = $(this).val();

                unikat.ajaxify({
                    that: $(this).closest('.ajax-lock'),
                    method: 'get',
                    queue: $(this).data('ajaxQueue'),
                    action: $(this).data('href'),
                    data: { projects: selectedProjects },
                    skipErrors: true,
                    functionParams: ['buildings', 'apartments'],
                    function: function(params) {
                        reloadBuildings(params);
                        reloadApartments(params);
                    },
                });
            },
        },
        'input-buildings': {
            close: function() {
                selectedBuildings = $(this).val();

                unikat.ajaxify({
                    that: $(this).closest('.ajax-lock'),
                    method: 'get',
                    queue: $(this).data('ajaxQueue'),
                    action: $(this).data('href'),
                    data: { projects: selectedProjects, buildings: selectedBuildings },
                    skipErrors: true,
                    functionParams: ['apartments'],
                    function: function(params) {
                        reloadApartments(params);
                    },
                });
            },
        },
        'input-apartments': {},
        'input-agents': {},
        'input-group': {
            multiple: false,
        },
    };

    function reloadBuildings(params) {
        var buildings = $('#input-buildings');
        selectedBuildings = buildings.val();
        buildings.empty();

        $.each(params.buildings, function(key, value) {
            buildings.append($('<option></option>').attr('value', value).text(key));
        });

        buildings.val(selectedBuildings);

        buildings.multiselect('refresh');
    }

    function reloadApartments(params) {
        var apartments = $('#input-apartments');
        var selectedApartments = apartments.val();
        apartments.empty();

        $.each(params.apartments, function(key, value) {
            apartments.append($('<option></option>').attr('value', value).text(key));
        });

        apartments.val(selectedApartments);

        apartments.multiselect('refresh');
    }

    $('#button-download-report').click(function(e) {
        e.preventDefault();

        var that = $(this).closest('.ajax-lock');

        unikat.ajaxify({
            that: that,
            method: 'post',
            skipErrors: true,
            queue: that.data('ajaxQueue'),
            action: that.attr('action'),
            data: that.serialize() + '&generate=excel',
            functionParams: ['uuid'],
            function: function(params) {
                window.location.href = '{{ \Locales::route('reports/agents/download') }}?uuid=' + params.uuid;
            },
        });
    });
@endsection
@endif
