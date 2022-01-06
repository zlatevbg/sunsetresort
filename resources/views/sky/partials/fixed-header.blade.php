<div id="fixed-header">
    <a class="mobile-logo" href="{{ \Locales::route() }}">
        {{ trans(\Locales::getNamespace() . '/messages.company') }}
    </a>
    <ul>
        <li class="submenu">
            <a class="dropdown-toggle">Actions <span class="caret"></span></a>
            <ul class="dropdown-menu dropdown-menu-small dropdown-menu-right">
                <li>
                    <a class="js-create" href="{{ \Locales::route('pay-mm-fees') }}">@lang(\Locales::getNamespace() . '/routes.pay-mm-fees.name')</a>
                    <a class="js-create" href="{{ \Locales::route('pay-communal-fees') }}">@lang(\Locales::getNamespace() . '/routes.pay-communal-fees.name')</a>
                    <a class="js-create" href="{{ \Locales::route('pay-pool-usage') }}">@lang(\Locales::getNamespace() . '/routes.pay-pool-usage.name')</a>
                    <a class="js-create" href="{{ \Locales::route('pay-rental') }}">@lang(\Locales::getNamespace() . '/routes.pay-rental.name')</a>
                    <a class="js-create" href="{{ \Locales::route('cancel-rental') }}">@lang(\Locales::getNamespace() . '/routes.cancel-rental.name')</a>
                </li>
            </ul>
        </li>

        @if (count(\Locales::getLocales()) > 1)
            <li class="submenu">
                <a class="dropdown-toggle">
                    {{ trans(\Locales::getNamespace() . '/messages.changeLanguage') }}
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                @foreach (\Locales::getLanguages() as $key => $language)
                    <li{!! $language['active'] ? ' class="active"' : '' !!}>
                        <a href="{{ $language['link'] }}">
                            {{ $language['native'] }}@if ($language['name'])<span class="sub-text">/ {{ $language['name'] }}</span>@endif
                        </a>
                    </li>
                @endforeach
                </ul>
            </li>
        @endif

        <li class="submenu">
            <a class="dropdown-toggle">{{ Auth::user()->name }} <span class="caret"></span></a>
            <ul class="dropdown-menu dropdown-menu-small dropdown-menu-right">
            @foreach (\Locales::getNavigation('header') as $nav)
                @if ($nav['divider-before'])<li class="divider"></li>@endif
                <li{!! $nav['active'] ? ' class="' . $nav['active'] . '"' : '' !!}>
                    <a href="{{ $nav['link'] }}">
                        @if ($nav['icon'])<span class="glyphicon glyphicon-{{ $nav['icon'] }}"></span>@endif{{ $nav['name'] }}
                    </a>
                </li>
                @if ($nav['divider-after'])<li class="divider"></li>@endif
            @endforeach
            </ul>
        </li>
    </ul>
</div>
