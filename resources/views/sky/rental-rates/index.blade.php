@extends(\Locales::getNamespace() . '.master')

@section('content')
    @include(\Locales::getNamespace() . '/shared.errors')

    @if (isset($datatables) && count($datatables) > 0)
        {!! Form::open(['url' => \Locales::route('rental-rates/save'), 'id' => 'save-rental-rates-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
            {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}
            @include(\Locales::getNamespace() . '/partials.datatables')
        {!! Form::close() !!}
    @endif
@endsection

@section('jsFiles')
    jsFiles.push('{{ \App\Helpers\autover('/js/' . \Locales::getNamespace() . '/vendor/jquery-ui.js') }}');

    @parent
@endsection

@if (isset($datatables) && count($datatables) > 0)
@section('script')
    unikat.callback = function() {
        this.datatables({!! json_encode($datatables) !!});
    };
@endsection
@endif
