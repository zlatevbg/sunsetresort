@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1 class="text-center">{{ \Locales::getMetaTitle() }}</h1>

    {!! Form::open(['method' => 'post', 'url' => \Locales::route('pool-usage-contracts-tracker/send-to-all'), 'id' => 'send-to-all-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.sendToAllButton'), ['class' => 'btn btn-success btn-block']) !!}
    {!! Form::close() !!}
</div>
@endsection
