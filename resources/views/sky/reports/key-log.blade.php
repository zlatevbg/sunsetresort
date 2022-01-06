@extends(\Locales::getNamespace() . '.master')

@section('content')
    @include(\Locales::getNamespace() . '/shared.errors')

    <div class="report-wrapper">
        <h1 class="h2 text-center">{{ trans(\Locales::getNamespace() . '/datatables.titleReportKeyLog') }}</h1>

        {!! Form::open(['url' => \Locales::route('reports/key-log/generate'), 'id' => 'report-form', 'data-ajax-queue' => 'sync', 'data-alert-position' => 'insertAfter', 'class' => 'ajax-lock form-inline', 'role' => 'form']) !!}

        <div class="row-wrapper">
            <div class="form-group ajax-lock">
                {!! Form::label('input-projects', trans(\Locales::getNamespace() . '/forms.projectLabel')) !!}
                {!! Form::multiselect('projects[]', $multiselect['projects'], ['id' => 'input-projects', 'class' => 'form-control', 'multiple' => 'multiple', 'data-ajax-queue' => 'sync', 'data-href' => \Locales::route('reports/key-log/get-buildings')]) !!}
            </div>
            <div class="form-group ajax-lock">
                {!! Form::label('input-buildings', trans(\Locales::getNamespace() . '/forms.buildingLabel')) !!}
                {!! Form::multiselect('buildings[]', $multiselect['buildings'], ['id' => 'input-buildings', 'class' => 'form-control', 'multiple' => 'multiple', 'data-ajax-queue' => 'sync', 'data-href' => \Locales::route('reports/key-log/get-apartments')]) !!}
            </div>
            <div class="form-group">
                {!! Form::label('input-apartments', trans(\Locales::getNamespace() . '/forms.apartmentsLabel')) !!}
                {!! Form::multiselect('apartments[]', $multiselect['apartments'], ['id' => 'input-apartments', 'class' => 'form-control', 'multiple' => 'multiple']) !!}
            </div>
        </div>
        <div class="row-wrapper">
            <div class="form-group">
                {!! Form::label('input-rooms', trans(\Locales::getNamespace() . '/forms.roomTypeLabel')) !!}
                {!! Form::multiselect('rooms[]', $multiselect['rooms'], ['id' => 'input-rooms', 'class' => 'form-control', 'multiple' => 'multiple']) !!}
            </div>
            <div class="form-group">
                {!! Form::label('input-furniture', trans(\Locales::getNamespace() . '/forms.furnitureLabel')) !!}
                {!! Form::multiselect('furniture[]', $multiselect['furniture'], ['id' => 'input-furniture', 'class' => 'form-control', 'multiple' => 'multiple']) !!}
            </div>
            <div class="form-group">
                {!! Form::label('input-views', trans(\Locales::getNamespace() . '/forms.viewLabel')) !!}
                {!! Form::multiselect('views[]', $multiselect['views'], ['id' => 'input-views', 'class' => 'form-control', 'multiple' => 'multiple']) !!}
            </div>
        </div>
        <div class="row-wrapper">
            <div class="form-group">
                {!! Form::label('input-dfrom', trans(\Locales::getNamespace() . '/forms.dfromLabel')) !!}
                {!! Form::text('dfrom', null, ['id' => 'input-dfrom', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.dfromPlaceholder')]) !!}
            </div>
            <div class="form-group">
                {!! Form::label('input-dto', trans(\Locales::getNamespace() . '/forms.dtoLabel')) !!}
                {!! Form::text('dto', null, ['id' => 'input-dto', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.dtoPlaceholder')]) !!}
            </div>
            <div class="form-group">
                {!! Form::label('input-languages', trans(\Locales::getNamespace() . '/forms.languageLabel')) !!}
                {!! Form::multiselect('languages[]', $multiselect['languages'], ['id' => 'input-languages', 'class' => 'form-control', 'multiple' => 'multiple']) !!}
            </div>
        </div>
        <div class="row-wrapper">
            <div class="form-group">
                {!! Form::label('input-mode', trans(\Locales::getNamespace() . '/forms.modeLabel')) !!}
                {!! Form::multiselect('mode', $multiselect['mode'], ['id' => 'input-mode', 'class' => 'form-control']) !!}
            </div>
            <div class="form-group">
                {!! Form::label('input-group', trans(\Locales::getNamespace() . '/forms.groupByLabel')) !!}
                {!! Form::multiselect('group', $multiselect['group'], ['id' => 'input-group', 'class' => 'form-control']) !!}
            </div>
        </div>
        <div class="row-wrapper">
            <div class="btn-group">{!! Form::button('<span class="glyphicon glyphicon-cog glyphicon-left"></span>' . trans(\Locales::getNamespace() . '/forms.generateReportButton'), ['type' => 'submit', 'class' => 'btn btn-primary']) !!}</div>
            <div class="btn-group">
                <a id="button-reset-report" href="{{ \Locales::route('reports/key-log') }}" class="btn btn-danger disabled">
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
        'input-rooms': {},
        'input-furniture': {},
        'input-views': {},
        'input-languages': {},
        'input-group': {
            multiple: false,
        },
        'input-mode': {
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

        if (parseInt(selectedProjects)) {
            buildings.multiselect('enable');
        } else {
            buildings.multiselect('disable');
            selectedBuildings = null;
        }

        buildings.multiselect('refresh');
        selectedBuildings = buildings.val();
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

    $.datepicker.regional.{{ \Locales::getCurrent() }} = unikat.variables.datepicker.{{ \Locales::getCurrent() }};
    $.datepicker.setDefaults($.datepicker.regional.{{ \Locales::getCurrent() }});

    $('#input-dfrom').datepicker({
        changeYear: true,
        changeMonth: true,
        onSelect: function(date) {
            var d = new Date(Date.parse($("#input-dfrom").datepicker("getDate")));
            $("#input-dto").datepicker('option', 'minDate', d);
        },
    });

    $('#input-dto').datepicker({
        changeYear: true,
        changeMonth: true,
    });

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
                window.location.href = '{{ \Locales::route('reports/key-log/download') }}?uuid=' + params.uuid;
            },
        });
    });
@endsection
@endif
