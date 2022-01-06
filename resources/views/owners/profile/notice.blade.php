@extends(\Locales::getNamespace() . '.master')

@section('content')
@if (isset($ajax))<div class="magnific-popup">@endif
    <article class="notice-wrapper">
        <h1>{{ $notice->name }}</h1>
        <div class="text">{!! $notice->content !!}</div>
    </article>
@if (isset($ajax))</div>@endif
@endsection
