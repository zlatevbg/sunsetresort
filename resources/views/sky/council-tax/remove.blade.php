@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1 class="text-center">{{ \Locales::getMetaTitle() }}</h1>

    {!! Form::open(['method' => 'delete', 'url' => \Locales::route('council-tax/destroy'), 'id' => 'delete-council-tax-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}
    {!! Form::hidden('owner', $owner, ['id' => 'input-owner']) !!}
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.removePermanentlyButton'), ['class' => 'btn btn-danger btn-block']) !!}
    {!! Form::close() !!}
</div>
@endsection
