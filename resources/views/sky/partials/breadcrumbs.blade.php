<div id="breadcrumbs">
    <ol>
        <li class="nav-toggle{!! isset($jsCookies['navState']) ? ' collapsed' : '' !!}"><a href="#"><span class="glyphicon glyphicon-menu-hamburger"></span><span class="glyphicon glyphicon-remove"></span></a></li>
        @foreach (\Locales::createBreadcrumbsFromSlugs(isset($breadcrumbs) ? $breadcrumbs : [], true) as $breadcrumb)
            <li{!! $breadcrumb['last'] ? ' class="active"' : '' !!}><a href="{{ $breadcrumb['link'] }}">{{ $breadcrumb['name'] }}</a></li>
            @if (!$breadcrumb['last'])
                <li class="separator"><span class="glyphicon glyphicon-chevron-right"></span></li>
            @endif
        @endforeach
    </ol>
</div>
