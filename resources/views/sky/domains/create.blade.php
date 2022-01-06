@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($domain))
    {!! Form::model($domain, ['method' => 'put', 'url' => \Locales::route('domains/update'), 'id' => 'edit-domain-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('domains/store'), 'id' => 'create-domain-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    <div class="form-group">
        {!! Form::label('input-name', trans(\Locales::getNamespace() . '/forms.nameLabel')) !!}
        {!! Form::text('name', null, ['id' => 'input-name', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.namePlaceholder')]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-domain', trans(\Locales::getNamespace() . '/forms.domainLabel')) !!}
        {!! Form::text('domain', null, ['id' => 'input-domain', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.domainPlaceholder')]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-route', trans(\Locales::getNamespace() . '/forms.defaultRouteLabel')) !!}
        {!! Form::text('route', null, ['id' => 'input-route', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.defaultRoutePlaceholder')]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-locales', trans(\Locales::getNamespace() . '/forms.localesLabel')) !!}
        {!! Form::multiselect('locales[]', $multiselect['locales'], ['id' => 'input-locales', 'class' => 'form-control', 'multiple' => 'multiple']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-default_locale_id', trans(\Locales::getNamespace() . '/forms.defaultLocaleLabel')) !!}
        @if (count($multiselect['default_locale_id']['options']))
            {!! Form::multiselect('default_locale_id', $multiselect['default_locale_id'], ['id' => 'input-default_locale_id', 'class' => 'form-control']) !!}
        @else
            {!! Form::multiselect('default_locale_id', $multiselect['default_locale_id'], ['id' => 'input-default_locale_id', 'class' => 'form-control', 'disabled' => 'disabled']) !!}
        @endif
    </div>

    <div class="form-group">
        {!! Form::checkboxInline('hide_default_locale', 1, null, ['id' => 'input-hide_default_locale'], trans(\Locales::getNamespace() . '/forms.hideDefaultLocaleOption'), ['class' => 'checkbox-inline']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-description', trans(\Locales::getNamespace() . '/forms.descriptionLabel')) !!}
        {!! Form::text('description', null, ['id' => 'input-description', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.descriptionPlaceholder')]) !!}
    </div>

    @if (isset($domain))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}

    <script>
    @section('script')
        unikat.multiselect = {
            'input-locales': {
                close: function() {
                    var select = $('#input-default_locale_id');
                    var value = select.val();
                    var values = $(this).val();

                    var options = $('option', $(this)).map(function() {
                        if ($.inArray(this.value, values) !== -1) {
                            $(this).removeAttr('aria-selected').removeAttr('checked');
                            return this;
                        }

                        return null;
                    });

                    select.html(options.clone()).val(value).multiselect('refresh');

                    if (values) {
                        select.multiselect('enable');
                    } else {
                        select.multiselect('disable');
                    }
                },
            },
            'input-default_locale_id': {
                multiple: false,
            },
        };
    @show
    </script>
</div>
@endsection
