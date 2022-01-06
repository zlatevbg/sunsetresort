@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1 class="text-center">{{ \Locales::getMetaTitle() }}</h1>

    {!! Form::open(['method' => 'delete', 'url' => \Locales::route('communal-fees-payments/destroy'), 'id' => 'delete-payment-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}
    {!! Form::hidden('apartment', $apartment, ['id' => 'input-apartment']) !!}
    {!! Form::hidden('year', $year, ['id' => 'input-year']) !!}
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.removeButton'), ['class' => 'btn btn-danger btn-block']) !!}
    {!! Form::close() !!}
</div>
@endsection
