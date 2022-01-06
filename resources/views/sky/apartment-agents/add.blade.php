@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($agent))
    {!! Form::model($agent, ['method' => 'put', 'url' => \Locales::route('apartment-agents/update'), 'id' => 'edit-agent-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('apartment-agents/save'), 'id' => 'add-agent-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    {!! Form::hidden('apartment', $apartment, ['id' => 'input-apartment']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    <div class="form-group">
        {!! Form::label('input-agents', trans(\Locales::getNamespace() . '/forms.agentsLabel')) !!}
        {!! Form::multiselect('agents[]', $multiselect['agents'], ['id' => 'input-agents', 'class' => 'form-control'] + (isset($agent) ? [] : ['multiple' => 'multiple'])) !!}
    </div>

    <div class="clearfix">
        <div class="form-group-left">
            <div class="form-group">
                {!! Form::label('input-dfrom', trans(\Locales::getNamespace() . '/forms.dfromLabel')) !!}
                {!! Form::text('dfrom', null, ['id' => 'input-dfrom', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.dfromPlaceholder'), 'autocomplete' => 'off']) !!}
            </div>
        </div>
        <div class="form-group-right">
            <div class="form-group">
                {!! Form::label('input-dto', trans(\Locales::getNamespace() . '/forms.dtoLabel')) !!}
                {!! Form::text('dto', null, ['id' => 'input-dto', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.dtoPlaceholder'), 'autocomplete' => 'off'] + ((isset($agent) && ($agent->dfrom || $agent->dto)) ? [] : ['disabled'])) !!}
            </div>
        </div>
    </div>

    @if (isset($agent))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}

    <script>
    @section('script')
        unikat.multiselect = {
            'input-agents': {
                {{ isset($agent) ? 'multiple: false,' : null }}
            },
        };

        $.datepicker.regional.{{ \Locales::getCurrent() }} = unikat.variables.datepicker.{{ \Locales::getCurrent() }};
        $.datepicker.setDefaults($.datepicker.regional.{{ \Locales::getCurrent() }});

        $('#input-dfrom').datepicker({
            changeYear: true,
            changeMonth: true,
            onSelect: function(date) {
                var d = new Date(Date.parse($("#input-dfrom").datepicker("getDate")));
                $('#input-dto').datepicker('option', 'minDate', d);
                $('#input-dto').removeAttr('disabled');
            },
        });

        $('#input-dto').datepicker({
            changeYear: true,
            changeMonth: true,
        });
    @show
    </script>
</div>
@endsection
