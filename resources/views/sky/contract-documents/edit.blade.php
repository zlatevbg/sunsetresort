@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    {!! Form::model($file, ['method' => 'put', 'url' => \Locales::route('contract-documents/update'), 'id' => 'edit-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    <div class="form-group">
        {!! Form::label('input-type', trans(\Locales::getNamespace() . '/forms.typeLabel')) !!}
        {!! Form::select('type', $types, null, ['id' => 'input-type', 'class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-signed_at', trans(\Locales::getNamespace() . '/forms.signedAtLabel')) !!}
        {!! Form::text('signed_at', null, ['id' => 'input-signed_at', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.signedAtPlaceholder')]) !!}
    </div>

    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}

    {!! Form::close() !!}

    <script>
    @section('script')
        $.datepicker.regional.{{ \Locales::getCurrent() }} = unikat.variables.datepicker.{{ \Locales::getCurrent() }};
        $.datepicker.setDefaults($.datepicker.regional.{{ \Locales::getCurrent() }});

        $('#input-signed_at').datepicker({
            changeYear: true,
            changeMonth: true,
        });
    @show
    </script>
</div>
@endsection
