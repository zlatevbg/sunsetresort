@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($mm))
    {!! Form::model($mm, ['method' => 'put', 'url' => \Locales::route('buildings-mm/update'), 'id' => 'edit-building-mm-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('buildings-mm/store'), 'id' => 'create-building-mm-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    {!! Form::hidden('project', $project, ['id' => 'input-project']) !!}
    {!! Form::hidden('building', $building, ['id' => 'input-building']) !!}
    {!! Form::hidden('year', $year, ['id' => 'input-year']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    <div class="form-group">
        {!! Form::label('input-management_company_id', trans(\Locales::getNamespace() . '/forms.managementCompanyLabel')) !!}
        {!! Form::select('management_company_id', $companies, null, ['id' => 'input-management_company_id', 'class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-mm_tax', trans(\Locales::getNamespace() . '/forms.mmTaxLabel')) !!}
        {!! Form::text('mm_tax', null, ['id' => 'input-mm_tax', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.mmTaxPlaceholder')]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-deadline_at', trans(\Locales::getNamespace() . '/forms.deadlineAtLabel')) !!}
        {!! Form::text('deadline_at', null, ['id' => 'input-deadline_at', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.deadlineAtPlaceholder')]) !!}
    </div>

    @if (isset($mm))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}

    <script>
    @section('script')
        $.datepicker.regional.{{ \Locales::getCurrent() }} = unikat.variables.datepicker.{{ \Locales::getCurrent() }};
        $.datepicker.setDefaults($.datepicker.regional.{{ \Locales::getCurrent() }});

        $('#input-deadline_at').datepicker({
            changeYear: true,
            changeMonth: true,
        });
    @show
    </script>
</div>
@endsection
