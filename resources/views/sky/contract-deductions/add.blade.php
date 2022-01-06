@inject('carbon', '\Carbon\Carbon')

@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($deduction))
    {!! Form::model($deduction, ['method' => 'put', 'url' => \Locales::route('contract-deductions/update'), 'id' => 'edit-deduction-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('contract-deductions/save'), 'id' => 'add-deduction-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    {!! Form::hidden('year', $year, ['id' => 'input-year']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    <div class="form-group">
        {!! Form::label('input-deduction_id', trans(\Locales::getNamespace() . '/forms.deductionLabel')) !!}
        {!! Form::multiselect('deduction_id', $multiselect['deductions'], ['id' => 'input-deduction_id', 'class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-amount', trans(\Locales::getNamespace() . '/forms.amountLabel')) !!}
        {!! Form::text('amount', null, ['id' => 'input-amount', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.amountPlaceholder')]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-signed_at', trans(\Locales::getNamespace() . '/forms.signedAtLabel')) !!}
        {!! Form::text('signed_at', null, ['id' => 'input-signed_at', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.signedAtPlaceholder')]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-comments', trans(\Locales::getNamespace() . '/forms.commentsLabel')) !!}
        {!! Form::textarea('comments', null, ['id' => 'input-comments', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.commentsPlaceholder')]) !!}
    </div>

    @if (isset($deduction))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}

    <script>
    @section('script')
        unikat.multiselect = {
            'input-deduction_id': {
                multiple: false,
            }
        };

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
