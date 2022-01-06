@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($building))
    {!! Form::model($building, ['method' => 'put', 'url' => \Locales::route('buildings/update'), 'id' => 'edit-building-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('buildings/store'), 'id' => 'create-building-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    {!! Form::hidden('project', $project, ['id' => 'input-project']) !!}
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
        {!! Form::text('name_translations[' . $locale->locale . ']', (isset($building) && $building->hasTranslation($locale->locale)) ? $building->translate($locale->locale)->name : null, ['id' => 'input-name-' . $locale->locale, 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.namePlaceholder') . ' - ' . $locale->name]) !!}
    </div>
    @endforeach

    <div class="form-group">
        {!! Form::label('input-description', trans(\Locales::getNamespace() . '/forms.descriptionLabel')) !!}
        {!! Form::textarea('description', null, ['id' => 'input-description', 'class' => 'form-control small-comments', 'placeholder' => trans(\Locales::getNamespace() . '/forms.descriptionPlaceholder')]) !!}
    </div>

    @foreach (\Locales::getPublicTranslations() as $locale)
    <div class="form-group">
        {!! Form::label('input-description-' . $locale->locale, trans(\Locales::getNamespace() . '/forms.descriptionLabel')) !!}
        <small>({{ $locale->native }})</small>
        {!! Form::textarea('description_translations[' . $locale->locale . ']', (isset($building) && $building->hasTranslation($locale->locale)) ? $building->translate($locale->locale)->description : null, ['id' => 'input-description-' . $locale->locale, 'class' => 'form-control small-comments', 'placeholder' => trans(\Locales::getNamespace() . '/forms.descriptionPlaceholder') . ' - ' . $locale->name]) !!}
    </div>
    @endforeach

    @if (isset($building))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}
</div>
@endsection
