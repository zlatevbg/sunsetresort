@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($account))
    {!! Form::model($account, ['method' => 'put', 'url' => \Locales::route('bank-accounts/update'), 'id' => 'edit-bank-account-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('bank-accounts/save'), 'id' => 'add-bank-account-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    {!! Form::hidden('owner', $owner, ['id' => 'input-owner']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    <div class="form-group">
        {!! Form::label('input-apartments', trans(\Locales::getNamespace() . '/forms.apartmentsLabel')) !!}
        {!! Form::multiselect('apartments[]', $multiselect['apartments'], ['id' => 'input-apartments', 'class' => 'form-control', 'multiple']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-rental', trans(\Locales::getNamespace() . '/forms.rentalAmountLabel')) !!}
        {!! Form::select('rental', $amount, null, ['id' => 'input-rental', 'class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-bank_iban', trans(\Locales::getNamespace() . '/forms.bankIbanLabel')) !!}
        {!! Form::text('bank_iban', null, ['id' => 'input-bank_iban', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.bankIbanPlaceholder')]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-bank_bic', trans(\Locales::getNamespace() . '/forms.bankBicLabel')) !!}
        {!! Form::text('bank_bic', null, ['id' => 'input-bank_bic', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.bankBicPlaceholder')]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-bank_beneficiary', trans(\Locales::getNamespace() . '/forms.bankBeneficiaryLabel')) !!}
        {!! Form::text('bank_beneficiary', null, ['id' => 'input-bank_beneficiary', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.bankBeneficiaryPlaceholder')]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-bank_name', trans(\Locales::getNamespace() . '/forms.bankNameLabel')) !!}
        {!! Form::text('bank_name', null, ['id' => 'input-bank_name', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.bankNamePlaceholder')]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-comments', trans(\Locales::getNamespace() . '/forms.commentsLabel')) !!}
        {!! Form::textarea('comments', null, ['id' => 'input-comments', 'class' => 'form-control apartments-comments', 'placeholder' => trans(\Locales::getNamespace() . '/forms.commentsPlaceholder')]) !!}
    </div>

    @if (isset($account))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}

    <script>
    @section('script')
        unikat.multiselect = {
            'input-apartments': {},
        };
    @show
    </script>
</div>
@endsection
