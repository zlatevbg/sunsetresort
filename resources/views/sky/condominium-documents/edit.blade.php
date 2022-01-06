@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    {!! Form::model($file, ['method' => 'put', 'url' => \Locales::route('condominium-documents/update'), 'id' => 'edit-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    <div class="form-group">
        {!! Form::label('input-type', trans(\Locales::getNamespace() . '/forms.typeLabel')) !!}
        {!! Form::select('type', $types, null, ['id' => 'input-type', 'class' => 'form-control']) !!}
    </div>

    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}

    {!! Form::close() !!}
</div>
@endsection
