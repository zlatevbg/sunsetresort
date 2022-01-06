@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($keyholder))
        {!! Form::model($keyholder, ['method' => 'put', 'url' => \Locales::route('apartment-keyholders/update'), 'id' => 'edit-keyholder-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
        {!! Form::open(['url' => \Locales::route('apartment-keyholders/save'), 'id' => 'add-keyholder-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
        {!! Form::hidden('apartment', $apartment, ['id' => 'input-apartment']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    <div class="form-group">
        {!! Form::label('input-keyholders', trans(\Locales::getNamespace() . '/forms.keyholdersLabel')) !!}
        {!! Form::multiselect('keyholders[]', $multiselect['keyholders'], ['id' => 'input-keyholders', 'class' => 'form-control'] + (isset($keyholder) ? [] : ['multiple' => 'multiple'])) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-comments', trans(\Locales::getNamespace() . '/forms.commentsLabel')) !!}
        {!! Form::textarea('comments', null, ['id' => 'input-comments', 'class' => 'form-control small-comments', 'placeholder' => trans(\Locales::getNamespace() . '/forms.commentsPlaceholder')]) !!}
    </div>

    @if (isset($keyholder))
        {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
        {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}

    <script>
    @section('script')
        unikat.multiselect = {
            'input-keyholders': {
                {{ isset($keyholder) ? 'multiple: false,' : null }}
            },
        };
    @show
    </script>
</div>
@endsection
