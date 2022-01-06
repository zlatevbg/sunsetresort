@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($proxy))
    {!! Form::model($proxy, ['method' => 'put', 'url' => \Locales::route('proxies/update'), 'id' => 'edit-proxy-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('proxies/store'), 'id' => 'create-proxy-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    <div class="form-group">
        {!! Form::checkboxInline('is_company', 1, null, ['id' => 'input-is_company'], trans(\Locales::getNamespace() . '/forms.isCompanyOption'), ['class' => 'checkbox-inline']) !!}
    </div>

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
                {!! Form::text('name_translations[' . $locale->locale . ']', (isset($proxy) && $proxy->hasTranslation($locale->locale)) ? $proxy->translate($locale->locale)->name : null, ['id' => 'input-name-' . $locale->locale, 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.namePlaceholder') . ' - ' . $locale->name]) !!}
            </div>
            @endforeach

            <div class="form-group js-input-bulstat-wrapper{{ (isset($proxy) && $proxy->is_company ? '' : ' hidden') }}">
                {!! Form::label('input-bulstat', trans(\Locales::getNamespace() . '/forms.bulstatLabel')) !!}
                {!! Form::text('bulstat', null, ['id' => 'input-bulstat', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.bulstatPlaceholder')] + (isset($proxy) && $proxy->is_company ? [] : ['disabled'])) !!}
            </div>

            <div class="form-group js-input-egn-wrapper{{ (isset($proxy) && $proxy->is_company ? ' hidden' : '') }}">
                {!! Form::label('input-egn', trans(\Locales::getNamespace() . '/forms.egnLabel')) !!}
                {!! Form::text('egn', null, ['id' => 'input-egn', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.egnPlaceholder')] + (isset($proxy) && $proxy->is_company ? ['disabled'] : [])) !!}
            </div>

            <div class="form-group js-input-id_card-wrapper{{ (isset($proxy) && $proxy->is_company ? ' hidden' : '') }}">
                {!! Form::label('input-id_card', trans(\Locales::getNamespace() . '/forms.idCardLabel')) !!}
                {!! Form::text('id_card', null, ['id' => 'input-id_card', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.idCardPlaceholder')] + (isset($proxy) && $proxy->is_company ? ['disabled'] : [])) !!}
            </div>

            <div class="form-group js-input-issued_at-wrapper{{ (isset($proxy) && $proxy->is_company ? ' hidden' : '') }}">
                {!! Form::label('input-issued_at', trans(\Locales::getNamespace() . '/forms.idCardLabel') . ' ' . trans(\Locales::getNamespace() . '/forms.issuedAtLabel')) !!}
                {!! Form::text('issued_at', null, ['id' => 'input-issued_at', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.issuedAtPlaceholder')] + (isset($proxy) && $proxy->is_company ? ['disabled'] : [])) !!}
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
                {!! Form::text('address_translations[' . $locale->locale . ']', (isset($proxy) && $proxy->hasTranslation($locale->locale)) ? $proxy->translate($locale->locale)->address : null, ['id' => 'input-address-' . $locale->locale, 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.addressPlaceholder') . ' - ' . $locale->name]) !!}
            </div>
            @endforeach

            <div class="form-group js-input-issued_by-wrapper{{ (isset($proxy) && $proxy->is_company ? ' hidden' : '') }}">
                {!! Form::label('input-issued_by', trans(\Locales::getNamespace() . '/forms.idCardLabel') . ' ' . trans(\Locales::getNamespace() . '/forms.issuedByLabel')) !!}
                {!! Form::text('issued_by', null, ['id' => 'input-issued_by', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.issuedByPlaceholder')] + (isset($proxy) && $proxy->is_company ? ['disabled'] : [])) !!}
            </div>

            @foreach (\Locales::getPublicTranslations() as $locale)
            <div class="form-group js-input-issued_by-wrapper{{ (isset($proxy) && $proxy->is_company ? ' hidden' : '') }}">
                {!! Form::label('input-issued_by-' . $locale->locale, trans(\Locales::getNamespace() . '/forms.idCardLabel') . ' ' . trans(\Locales::getNamespace() . '/forms.issuedByLabel')) !!}
                <small>({{ $locale->native }})</small>
                {!! Form::text('issued_by_translations[' . $locale->locale . ']', (isset($proxy) && $proxy->hasTranslation($locale->locale)) ? $proxy->translate($locale->locale)->issued_by : null, ['id' => 'input-issued_by-' . $locale->locale, 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.issuedByPlaceholder') . ' - ' . $locale->name] + (isset($proxy) && $proxy->is_company ? ['disabled'] : [])) !!}
            </div>
            @endforeach
        </div>
    </div>

    @if (isset($proxy))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}

    <script>
    @section('script')
        $.datepicker.regional.{{ \Locales::getCurrent() }} = unikat.variables.datepicker.{{ \Locales::getCurrent() }};
        $.datepicker.setDefaults($.datepicker.regional.{{ \Locales::getCurrent() }});

        $('#input-issued_at').datepicker({
            changeYear: true,
            changeMonth: true,
            yearRange: "-100:+0",
        });

        $(document).on('change', '#input-is_company', function() {
            if ($(this).is(':checked')) {
                $('.js-input-egn-wrapper, .js-input-id_card-wrapper, .js-input-issued_at-wrapper, .js-input-issued_by-wrapper').addClass('hidden');
                $('.js-input-bulstat-wrapper').removeClass('hidden');
                $('#input-egn, #input-id_card, #input-issued_at, #input-issued_by').prop('disabled', true).val('');
                $('#input-bulstat').prop('disabled', false);
            } else {
                $('.js-input-egn-wrapper, .js-input-id_card-wrapper, .js-input-issued_at-wrapper, .js-input-issued_by-wrapper').removeClass('hidden');
                $('.js-input-bulstat-wrapper').addClass('hidden');
                $('#input-egn, #input-id_card, #input-issued_at, #input-issued_by').prop('disabled', false);
                $('#input-bulstat').prop('disabled', true).val('');
            }
        });
    @show
    </script>
</div>
@endsection
