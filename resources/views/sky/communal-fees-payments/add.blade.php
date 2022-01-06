@inject('carbon', '\Carbon\Carbon')

@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($payment))
    {!! Form::model($payment, ['method' => 'put', 'url' => \Locales::route('communal-fees-payments/update'), 'id' => 'edit-payment-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('communal-fees-payments/save'), 'id' => 'add-payment-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    {!! Form::hidden('apartment', $apartment, ['id' => 'input-apartment']) !!}
    {!! Form::hidden('year', $year, ['id' => 'input-year']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    <div class="form-group">
        {!! Form::label('input-payment_method_id', trans(\Locales::getNamespace() . '/forms.paymentMethodLabel')) !!}
        {!! Form::multiselect('payment_method_id', $multiselect['paymentMethods'], ['id' => 'input-payment_method_id', 'class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-amount', trans(\Locales::getNamespace() . '/forms.amountLabel')) !!}
        {!! Form::text('amount', null, ['id' => 'input-amount', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.amountPlaceholder')]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-paid_at', trans(\Locales::getNamespace() . '/forms.paidAtLabel')) !!}
        {!! Form::text('paid_at', null, ['id' => 'input-paid_at', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.paidAtPlaceholder')]) !!}
    </div>

    <div class="clearfix">
        <div class="form-group-left">

            <div class="form-group">
                {!! Form::label('input-rental_company_id', trans(\Locales::getNamespace() . '/forms.paidByCompanyLabel')) !!}
                {!! Form::multiselect('rental_company_id', $multiselect['companies'], ['id' => 'input-rental_company_id', 'class' => 'form-control']) !!}
            </div>

        </div>
        <div class="form-group-right">

            <div class="form-group">
                {!! Form::label('input-owner_id', trans(\Locales::getNamespace() . '/forms.paidByOwnerLabel')) !!}
                {!! Form::multiselect('owner_id', $multiselect['owners'], ['id' => 'input-owner_id', 'class' => 'form-control']) !!}
            </div>

        </div>
    </div>

    <div class="form-group">
        {!! Form::label('input-comments', trans(\Locales::getNamespace() . '/forms.commentsLabel')) !!}
        {!! Form::textarea('comments', null, ['id' => 'input-comments', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.commentsPlaceholder')]) !!}
    </div>

    @if (isset($payment))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}

    <script>
    @section('script')
        unikat.multiselect = {
            'input-payment_method_id': {
                multiple: false,
            },
            'input-rental_company_id': {
                multiple: false,
                open: function() {
                    unikat.multiselect.default_rental_company_id = $($(this).multiselect('getChecked')).val();
                },
                close: function() {
                    if (unikat.multiselect.default_rental_company_id != $($(this).multiselect('getChecked')).val()) {
                        var owners = $('#input-owner_id');
                        owners.val('');
                        owners.multiselect('refresh');
                    }
                },
            },
            'input-owner_id': {
                multiple: false,
                open: function() {
                    unikat.multiselect.default_owner_id = $($(this).multiselect('getChecked')).val();
                },
                close: function() {
                    if (unikat.multiselect.default_owner_id != $($(this).multiselect('getChecked')).val()) {
                        var companies = $('#input-rental_company_id');
                        companies.val('');
                        companies.multiselect('refresh');
                    }
                },
            },
        };

        $.datepicker.regional.{{ \Locales::getCurrent() }} = unikat.variables.datepicker.{{ \Locales::getCurrent() }};
        $.datepicker.setDefaults($.datepicker.regional.{{ \Locales::getCurrent() }});

        $('#input-paid_at').datepicker({
            changeYear: true,
            changeMonth: true,
        });
    @show
    </script>
</div>
@endsection
