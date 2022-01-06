@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    {!! Form::open(['url' => \Locales::route('cancel-rental-contracts'), 'id' => 'cancel-rental-contracts-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}

    <div class="form-group">
        {!! Form::label('input-year', trans(\Locales::getNamespace() . '/forms.yearLabel')) !!}
        {!! Form::select('year', $years, null, ['id' => 'input-year', 'class' => 'form-control']) !!}
    </div>

    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.cancelContractsButton'), ['class' => 'btn btn-primary btn-block']) !!}

    {!! Form::close() !!}
</div>
@endsection
