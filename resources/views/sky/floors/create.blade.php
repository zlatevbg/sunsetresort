@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($floor))
    {!! Form::model($floor, ['method' => 'put', 'url' => \Locales::route('floors/update'), 'id' => 'edit-floor-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('floors/store'), 'id' => 'create-floor-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    {!! Form::hidden('project', $project, ['id' => 'input-project']) !!}
    {!! Form::hidden('building', $building, ['id' => 'input-building']) !!}
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
        {!! Form::text('name_translations[' . $locale->locale . ']', (isset($floor) && $floor->hasTranslation($locale->locale)) ? $floor->translate($locale->locale)->name : null, ['id' => 'input-name-' . $locale->locale, 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.namePlaceholder') . ' - ' . $locale->name]) !!}
    </div>
    @endforeach

    @if (isset($floor))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}
</div>
@endsection
