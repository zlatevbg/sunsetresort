<div id="breadcrumbs">
    <ol>
        @foreach (\Locales::getBreadcrumbsFromSlugs(isset($breadcrumbs) ? $breadcrumbs : []) as $breadcrumb)
            <li{!! $breadcrumb['last'] ? ' class="active"' : '' !!}>@if ($breadcrumb['link'])<a href="{{ $breadcrumb['link'] }}">@endif{{ $breadcrumb['name'] }}@if ($breadcrumb['link'])</a>@endif</li>
            @if (!$breadcrumb['last'])
                <li class="separator"><span class="glyphicon glyphicon-chevron-right"></span></li>
            @endif
        @endforeach
    </ol>
</div>
