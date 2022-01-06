@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($condominium))
    {!! Form::model($condominium, ['method' => 'put', 'url' => \Locales::route('condominium/update'), 'id' => 'edit-condominium-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('condominium/store'), 'id' => 'create-condominium-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    {!! Form::hidden('project', $project, ['id' => 'input-project']) !!}
    {!! Form::hidden('building', $building, ['id' => 'input-building']) !!}
    {!! Form::hidden('year', $year, ['id' => 'input-year']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    <div class="form-group">
        {!! Form::label('input-assembly_at', trans(\Locales::getNamespace() . '/forms.assemblyAtLabel')) !!}
        {!! Form::text('assembly_at', null, ['id' => 'input-assembly_at', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.assemblyAtPlaceholder')]) !!}
    </div>

    @if (isset($condominium))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}

    <script>
    @section('script')
        $.datepicker.regional.{{ \Locales::getCurrent() }} = unikat.variables.datepicker.{{ \Locales::getCurrent() }};
        $.datepicker.setDefaults($.datepicker.regional.{{ \Locales::getCurrent() }});

        $('#input-assembly_at').datepicker({
            changeYear: true,
            changeMonth: true,
        });
    @show
    </script>
</div>
@endsection
