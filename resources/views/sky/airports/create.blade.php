@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($airport))
    {!! Form::model($airport, ['method' => 'put', 'url' => \Locales::route('airports/update'), 'id' => 'edit-airport-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('airports/store'), 'id' => 'create-airport-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    <div class="form-group">
        {!! Form::label('input-name', trans(\Locales::getNamespace() . '/forms.nameLabel')) !!}
        {!! Form::text('name', null, ['id' => 'input-name', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.namePlaceholder')]) !!}
    </div>

    @foreach (\Locales::getPublicTranslations() as $locale)
    <div class="form-group">
        {!! Form::label('input-name-' . $locale->locale, trans(\Locales::getNamespace() . '/forms.nameLabel')) !!}
        <small>({{ $locale->native }})</small>
        {!! Form::text('name_translations[' . $locale->locale . ']', (isset($airport) && $airport->hasTranslation($locale->locale)) ? $airport->translate($locale->locale)->name : null, ['id' => 'input-name-' . $locale->locale, 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.namePlaceholder') . ' - ' . $locale->name]) !!}
    </div>
    @endforeach

    @if (isset($airport))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}
</div>
@endsection
