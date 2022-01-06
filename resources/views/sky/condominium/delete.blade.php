@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1 class="text-center">{{ \Locales::getMetaTitle() }}</h1>

    {!! Form::open(['method' => 'delete', 'url' => \Locales::route('condominium/destroy'), 'id' => 'delete-building-mm-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}
    {!! Form::hidden('project', $project, ['id' => 'input-project']) !!}
    {!! Form::hidden('building', $building, ['id' => 'input-building']) !!}
    {!! Form::hidden('year', $year, ['id' => 'input-year']) !!}
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.destroyButton'), ['class' => 'btn btn-danger btn-block']) !!}
    {!! Form::close() !!}
</div>
@endsection
