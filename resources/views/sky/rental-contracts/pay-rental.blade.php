@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    {!! Form::open(['url' => \Locales::route('pay-rental'), 'id' => 'pay-rental-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}

    <div class="form-group">
        {!! Form::label('input-year', trans(\Locales::getNamespace() . '/forms.yearLabel')) !!}
        {!! Form::select('year', $years, null, ['id' => 'input-year', 'class' => 'form-control']) !!}
    </div>

    <div class="btn-group-wrapper">
        <div class="btn-group">
            <div id="fine-uploader-upload" data-form="true" data-reupload="false" data-header-wrapper="#pay-rental-form" data-url="{{ \Locales::route('pay-rental/upload') }}" data-table="pay-rental" class="btn btn-primary js-upload" data-multiple="false" data-is-file="true"><span class="glyphicon glyphicon-upload"></span>{{ trans(\Locales::getNamespace() . '/forms.uploadButton') }}</div>
        </div>
    </div>

    {!! Form::close() !!}

    <script>
        @section('script')
            Modernizr.load([{
                load: ['{{ \App\Helpers\autover('/js/' . \Locales::getNamespace() . '/vendor/fine-uploader.js') }}'],
                complete: function() {
                    unikat.run(); // run scripts again
                },
            }]);
        @show
    </script>
</div>
@endsection
