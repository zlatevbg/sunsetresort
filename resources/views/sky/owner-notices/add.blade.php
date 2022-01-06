@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    {!! Form::open(['url' => \Locales::route('owner-notices/save'), 'id' => 'add-notice-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}
    {!! Form::hidden('owner', $owner_id, ['id' => 'input-owner']) !!}

    <div class="form-group">
        {!! Form::label('input-notices', trans(\Locales::getNamespace() . '/forms.noticesLabel')) !!}
        {!! Form::multiselect('notices[]', $multiselect['notices'], ['id' => 'input-notices', 'class' => 'form-control', 'multiple' => 'multiple']) !!}
    </div>

    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}

    {!! Form::close() !!}

    <script>
    @section('script')
        unikat.multiselect = {
            'input-notices': {},
        };
    @show
    </script>
</div>
@endsection
