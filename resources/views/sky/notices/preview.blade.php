@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ $notice->name }}</h1>
    <div class="text">{!! $notice->content !!}</div>
</div>
@endsection
