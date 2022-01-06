@extends(\Locales::getNamespace() . '.master')

@section('content')
    <article>
        <h1 class="text-center">{{ $page->title }}</h1>
        <div class="text">{!! $page->content !!}</div>
    </article>

    @if (isset($datatables) && count($datatables) > 0)
        @include(\Locales::getNamespace() . '/partials.datatables')
    @endif
@endsection

@if (isset($datatables) && count($datatables) > 0)
@section('script')
    unikat.callback = function() {
        this.datatables({!! json_encode($datatables) !!});
    };
@endsection
@endif
