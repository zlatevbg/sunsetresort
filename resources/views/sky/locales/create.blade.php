@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($locale))
    {!! Form::model($locale, ['method' => 'put', 'url' => \Locales::route('locales/update'), 'id' => 'edit-locale-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('locales/store'), 'id' => 'create-locale-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    <div class="form-group">
        {!! Form::label('input-name', trans(\Locales::getNamespace() . '/forms.nameLabel')) !!}
        {!! Form::text('name', null, ['id' => 'input-name', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.namePlaceholder')]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-native', trans(\Locales::getNamespace() . '/forms.nativeNameLabel')) !!}
        {!! Form::text('native', null, ['id' => 'input-native', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.nativeNamePlaceholder')]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-locale', trans(\Locales::getNamespace() . '/forms.localeLabel')) !!}
        {!! Form::text('locale', null, ['id' => 'input-locale', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.localePlaceholder')]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-script', trans(\Locales::getNamespace() . '/forms.scriptLabel')) !!}
        {!! Form::select('script', $scripts, $script, ['id' => 'input-script', 'class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-description', trans(\Locales::getNamespace() . '/forms.descriptionLabel')) !!}
        {!! Form::text('description', null, ['id' => 'input-description', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.descriptionPlaceholder')]) !!}
    </div>

    @if (isset($locale))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}
</div>
@endsection
