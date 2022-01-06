<ul class="sidebar-tabs">
    <li{!! (!isset($jsCookies['sidebar']) || (isset($jsCookies['sidebar']) && $jsCookies['sidebar'] == 0)) ? ' class="sidebar-tab-active"' : '' !!}><a href="{{ \Request::url() }}"><span class="glyphicon glyphicon-menu-hamburger"></span>{{ trans(\Locales::getNamespace() . '/messages.menu') }}</a></li>
    <li{!! (isset($jsCookies['sidebar']) && $jsCookies['sidebar'] == 1) ? ' class="sidebar-tab-active"' : '' !!}><a href="{{ \Request::url() }}#sidebar-1"><span class="glyphicon glyphicon-stats"></span>{{ trans(\Locales::getNamespace() . '/messages.reports') }}</a></li>
    <li{!! (isset($jsCookies['sidebar']) && $jsCookies['sidebar'] == 2) ? ' class="sidebar-tab-active"' : '' !!}><a href="{{ \Request::url() }}#sidebar-2"><span class="glyphicon glyphicon-cog"></span>{{ trans(\Locales::getNamespace() . '/messages.settings') }}</a></li>
</ul>
<ul id="sidebar-0" class="slidedown-menu menu-static sidebar{!! (!isset($jsCookies['sidebar']) || (isset($jsCookies['sidebar']) && $jsCookies['sidebar'] == 0)) ? ' sidebar-active' : '' !!}">
    @each(\Locales::getNamespace() . '/partials.sidebar-recursive', \Locales::getNavigation('sidebar-menu'), 'item')
</ul>
<ul id="sidebar-1" class="slidedown-menu menu-static sidebar{!! (isset($jsCookies['sidebar']) && $jsCookies['sidebar'] == 1) ? ' sidebar-active' : '' !!}">
    @each(\Locales::getNamespace() . '/partials.sidebar-recursive', \Locales::getNavigation('sidebar-reports'), 'item')
</ul>
<ul id="sidebar-2" class="slidedown-menu menu-static sidebar{!! (isset($jsCookies['sidebar']) && $jsCookies['sidebar'] == 2) ? ' sidebar-active' : '' !!}">
    @each(\Locales::getNamespace() . '/partials.sidebar-recursive', \Locales::getNavigation('sidebar-settings'), 'item')
</ul>
