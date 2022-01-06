@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($company))
    {!! Form::model($company, ['method' => 'put', 'url' => \Locales::route('rental-companies/update'), 'id' => 'edit-company-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('rental-companies/store'), 'id' => 'create-company-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    <div class="clearfix">
        <div class="form-group-left">
            <div class="form-group">
                {!! Form::label('input-name', trans(\Locales::getNamespace() . '/forms.nameLabel')) !!}
                {!! Form::text('name', null, ['id' => 'input-name', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.namePlaceholder')]) !!}
            </div>

            @foreach (\Locales::getPublicTranslations() as $locale)
            <div class="form-group">
                {!! Form::label('input-name-' . $locale->locale, trans(\Locales::getNamespace() . '/forms.nameLabel')) !!}
                <small>({{ $locale->native }})</small>
                {!! Form::text('name_translations[' . $locale->locale . ']', (isset($company) && $company->hasTranslation($locale->locale)) ? $company->translate($locale->locale)->name : null, ['id' => 'input-name-' . $locale->locale, 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.namePlaceholder') . ' - ' . $locale->name]) !!}
            </div>
            @endforeach

            <div class="form-group">
                {!! Form::label('input-bulstat', trans(\Locales::getNamespace() . '/forms.bulstatLabel')) !!}
                {!! Form::text('bulstat', null, ['id' => 'input-bulstat', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.bulstatPlaceholder')]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-egn', trans(\Locales::getNamespace() . '/forms.egnLabel')) !!}
                {!! Form::text('egn', null, ['id' => 'input-egn', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.egnPlaceholder')]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-id_card', trans(\Locales::getNamespace() . '/forms.idCardLabel')) !!}
                {!! Form::text('id_card', null, ['id' => 'input-id_card', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.idCardPlaceholder')]) !!}
            </div>
        </div>

        <div class="form-group-right">
            <div class="form-group">
                {!! Form::label('input-address', trans(\Locales::getNamespace() . '/forms.addressLabel')) !!}
                {!! Form::text('address', null, ['id' => 'input-address', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.addressPlaceholder')]) !!}
            </div>

            @foreach (\Locales::getPublicTranslations() as $locale)
            <div class="form-group">
                {!! Form::label('input-address-' . $locale->locale, trans(\Locales::getNamespace() . '/forms.addressLabel')) !!}
                <small>({{ $locale->native }})</small>
                {!! Form::text('address_translations[' . $locale->locale . ']', (isset($company) && $company->hasTranslation($locale->locale)) ? $company->translate($locale->locale)->address : null, ['id' => 'input-address-' . $locale->locale, 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.addressPlaceholder') . ' - ' . $locale->name]) !!}
            </div>
            @endforeach

            <div class="form-group">
                {!! Form::label('input-manager', trans(\Locales::getNamespace() . '/forms.managerLabel')) !!}
                {!! Form::text('manager', null, ['id' => 'input-manager', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.managerPlaceholder')]) !!}
            </div>

            @foreach (\Locales::getPublicTranslations() as $locale)
            <div class="form-group">
                {!! Form::label('input-manager-' . $locale->locale, trans(\Locales::getNamespace() . '/forms.managerLabel')) !!}
                <small>({{ $locale->native }})</small>
                {!! Form::text('manager_translations[' . $locale->locale . ']', (isset($company) && $company->hasTranslation($locale->locale)) ? $company->translate($locale->locale)->manager : null, ['id' => 'input-manager-' . $locale->locale, 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.managerPlaceholder') . ' - ' . $locale->name]) !!}
            </div>
            @endforeach
        </div>
    </div>

    @if (isset($company))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}
</div>
@endsection
