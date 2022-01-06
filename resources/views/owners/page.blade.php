@extends(\Locales::getNamespace() . '.master')

@section('content')
@if (isset($ajax))<div class="magnific-popup">@endif
    <article class="page-wrapper">
        <h1>{{ $page->title }}</h1>
        <div class="text">{!! $page->content !!}</div>
    </article>
@if (isset($ajax))</div>@endif
@endsection
