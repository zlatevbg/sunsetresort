@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($log))
    {!! Form::model($log, ['method' => 'put', 'url' => \Locales::route('key-log/update'), 'id' => 'edit-key-log-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('key-log/store'), 'id' => 'create-key-log-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    <div class="form-group">
        {!! Form::label('input-occupied_at', trans(\Locales::getNamespace() . '/forms.dateLabel')) !!}
        {!! Form::text('occupied_at', null, ['id' => 'input-occupied_at', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.datePlaceholder')]) !!}
    </div>
    <div class="form-group ajax-lock">
        {!! Form::label('input-apartment_id', trans(\Locales::getNamespace() . '/forms.apartmentLabel')) !!}
        {!! Form::multiselect('apartment_id', $multiselect['apartments'], ['id' => 'input-apartment_id', 'class' => 'form-control', 'data-ajax-queue' => 'sync', 'data-href' => \Locales::route('key-log/get-owners')]) !!}
    </div>
    <div class="form-group">
        {!! Form::label('input-people', trans(\Locales::getNamespace() . '/forms.peopleLabel')) !!}
        {!! Form::text('people', null, ['id' => 'input-people', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.peoplePlaceholder')]) !!}
    </div>

    @if (isset($log))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}

    <script>
    @section('script')
        unikat.multiselect = {
            'input-apartment_id': {
                multiple: false,
            },
        };

        $.datepicker.regional.{{ \Locales::getCurrent() }} = unikat.variables.datepicker.{{ \Locales::getCurrent() }};
        $.datepicker.setDefaults($.datepicker.regional.{{ \Locales::getCurrent() }});

        $('#input-occupied_at').datepicker({
            changeYear: false,
            changeMonth: false,
        });
    @show
    </script>
</div>
@endsection
