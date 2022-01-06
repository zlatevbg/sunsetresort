@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="text">{!! $page->content !!}</div>
@endsection
