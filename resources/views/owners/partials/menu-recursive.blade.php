@if (isset($item['children']))
<li class="submenu {!! ($item['slug'] == head(explode('/', \Slug::getSlug())) or $item['slug'] == \Slug::getSlug()) ? 'active' : '' !!}">
    <a class="dropdown-toggle" href="{{ $item['url'] }}">
        @if ($item['icon'])<span class="glyphicon glyphicon-{{ $item['icon'] }} glyphicon-left glyphicon-large"></span>@endif{{ $item['name'] }}<span class="caret"></span>
    </a>
    <ul data-level="1" class="dropdown-menu">
        @each(\Locales::getNamespace() . '/partials.menu-recursive', $item['children'], 'item')
    </ul>
</li>
@else
<li{!! ($item['slug'] == last(explode('/', \Slug::getRouteSlug())) or $item['slug'] == \Slug::getRouteSlug() or $item['slug'] == last(\Slug::getRouteParameters())) ? ' class="active"' : '' !!}>
    <a @if (isset($item['is_popup']) && $item['is_popup'])class="js-popup"@endif href="{{ $item['url'] }}">
        @if (isset($item['icon']) && $item['icon'])<span class="glyphicon glyphicon-{{ $item['icon'] }} glyphicon-left glyphicon-large"></span>@endif{!! $item['name'] !!}
    </a>
</li>
@endif
