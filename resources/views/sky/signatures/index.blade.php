@extends(\Locales::getNamespace() . '.master')

@section('content')
    @include(\Locales::getNamespace() . '/shared.errors')

    @if (isset($datatables) && count($datatables) > 0)
        @include(\Locales::getNamespace() . '/partials.datatables')
    @endif
@endsection

@section('jsFiles')
    jsFiles.push('{{ \App\Helpers\autover('/js/' . \Locales::getNamespace() . '/vendor/ckeditor/ckeditor.js') }}');

    @parent
@endsection

@if (isset($datatables) && count($datatables) > 0)
@section('script')
    unikat.callback = function() {
        this.datatables({!! json_encode($datatables) !!});
    };
@endsection
@endif
