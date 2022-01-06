@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($room))
    {!! Form::model($room, ['method' => 'put', 'url' => \Locales::route('rooms/update'), 'id' => 'edit-room-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('rooms/store'), 'id' => 'create-room-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
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
                {!! Form::text('name_translations[' . $locale->locale . ']', (isset($room) && $room->hasTranslation($locale->locale)) ? $room->translate($locale->locale)->name : null, ['id' => 'input-name-' . $locale->locale, 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.namePlaceholder') . ' - ' . $locale->name]) !!}
            </div>
            @endforeach

        </div>
        <div class="form-group-right">

            <div class="form-group">
                {!! Form::label('input-description', trans(\Locales::getNamespace() . '/forms.descriptionLabel')) !!}
                {!! Form::text('description', null, ['id' => 'input-description', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.descriptionPlaceholder')]) !!}
            </div>

            @foreach (\Locales::getPublicTranslations() as $locale)
            <div class="form-group">
                {!! Form::label('input-description-' . $locale->locale, trans(\Locales::getNamespace() . '/forms.descriptionLabel')) !!}
                <small>({{ $locale->native }})</small>
                {!! Form::text('description_translations[' . $locale->locale . ']', (isset($room) && $room->hasTranslation($locale->locale)) ? $room->translate($locale->locale)->description : null, ['id' => 'input-description-' . $locale->locale, 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.descriptionPlaceholder') . ' - ' . $locale->name]) !!}
            </div>
            @endforeach

        </div>
    </div>

    <div class="form-group">
        {!! Form::label('input-capacity', trans(\Locales::getNamespace() . '/forms.capacityLabel')) !!}
        {!! Form::number('capacity', null, ['id' => 'input-capacity', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.capacityPlaceholder')]) !!}
    </div>

    @if (isset($room))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}
</div>
@endsection
