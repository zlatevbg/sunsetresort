@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1 class="text-center">{{ \Locales::getMetaTitle() }}</h1>

    {!! Form::open(['method' => 'post', 'url' => \Locales::route('communal-fee-contracts-tracker/activate'), 'id' => 'activate-contract-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.activateButton'), ['class' => 'btn btn-secondary btn-block']) !!}
    {!! Form::close() !!}
</div>
@endsection
