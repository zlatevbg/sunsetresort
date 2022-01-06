@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($tax))
    {!! Form::model($tax, ['method' => 'put', 'url' => \Locales::route('council-tax/update'), 'id' => 'edit-council-tax-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('council-tax/save'), 'id' => 'add-council-tax-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    {!! Form::hidden('owner', $owner, ['id' => 'input-owner']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    <div class="form-group">
        {!! Form::label('input-apartment_id', trans(\Locales::getNamespace() . '/forms.apartmentLabel')) !!}
        {!! Form::select('apartment_id', $apartments, null, ['id' => 'input-apartment_id', 'class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-tax', trans(\Locales::getNamespace() . '/forms.taxLabel')) !!}
        {!! Form::text('tax', null, ['id' => 'input-tax', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.taxPlaceholder')]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-checked_at', trans(\Locales::getNamespace() . '/forms.checkedAtLabel')) !!}
        {!! Form::text('checked_at', null, ['id' => 'input-checked_at', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.checkedAtPlaceholder')]) !!}
    </div>

    @if (isset($tax))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}

    <script>
    @section('script')
        $.datepicker.regional.{{ \Locales::getCurrent() }} = unikat.variables.datepicker.{{ \Locales::getCurrent() }};
        $.datepicker.setDefaults($.datepicker.regional.{{ \Locales::getCurrent() }});

        $('#input-checked_at').datepicker({
            changeYear: true,
            changeMonth: true,
            maxDate: 0,
        });
    @show
    </script>
</div>
@endsection
