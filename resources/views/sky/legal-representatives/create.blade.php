@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($legalRepresentative))
    {!! Form::model($legalRepresentative, ['method' => 'put', 'url' => \Locales::route('legal-representatives/update'), 'id' => 'edit-legal-representative-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('legal-representatives/store'), 'id' => 'create-legal-representative-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    <div class="form-group">
        {!! Form::label('input-name', trans(\Locales::getNamespace() . '/forms.nameLabel')) !!}
        {!! Form::text('name', null, ['id' => 'input-name', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.namePlaceholder')]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-phone', trans(\Locales::getNamespace() . '/forms.phoneLabel')) !!}
        {!! Form::tel('phone', null, ['id' => 'input-phone', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.phonePlaceholder')]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-email', trans(\Locales::getNamespace() . '/forms.emailLabel')) !!}
        {!! Form::email('email', null, ['id' => 'input-email', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.emailPlaceholder')]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-comments', trans(\Locales::getNamespace() . '/forms.commentsLabel')) !!}
        {!! Form::textarea('comments', null, ['id' => 'input-comments', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.commentsPlaceholder')]) !!}
    </div>

    @if (isset($legalRepresentative))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}
</div>
@endsection
