@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    {!! Form::open(['url' => \Locales::route('apartment-owners/save'), 'id' => 'add-owner-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}
    {!! Form::hidden('apartment', $apartment, ['id' => 'input-apartment']) !!}

    <div class="form-group">
        {!! Form::label('input-owners', trans(\Locales::getNamespace() . '/forms.ownersLabel')) !!}
        {!! Form::multiselect('owners[]', $multiselect['owners'], ['id' => 'input-owners', 'class' => 'form-control', 'multiple' => 'multiple']) !!}
    </div>

    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}

    {!! Form::close() !!}

    <script>
    @section('script')
        unikat.multiselect = {
            'input-owners': {},
        };
    @show
    </script>
</div>
@endsection
