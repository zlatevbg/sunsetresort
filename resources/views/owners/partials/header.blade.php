<div id="header">
    <div class="logo"><a href="{{ \Locales::route() }}"></a></div>
    <ul class="profile">
        <li class="submenu profile-notice tooltip{{ $tooltip }}" title="{{ $tooltip ? trans_choice(trans(\Locales::getNamespace() . '/messages.unreadNoticesTitle', ['number' => $notices->count()]), $notices->count()) : trans(\Locales::getNamespace() . '/messages.noticesTitle') }}">
            <a href="{{ \Locales::route('notices') }}">
                <span class="glyphicon glyphicon-bell"></span>
                @if ($notices->count())
                    <span class="notices-counter">{{ $notices->count() }}</span>
                @endif
            </a>
            @if ($notices->count())
            <div class="tooltip-data">
                <a class="btn btn-link" href="{{ \Locales::route('notices') }}">{{ trans(\Locales::getNamespace() . '/messages.readNoticesLink') }}</a>
                <a class="btn btn-link js-dismiss" href="{{ \Locales::route('notices/dismiss') }}" data-ajax-queue="sync">{{ trans(\Locales::getNamespace() . '/messages.dismissNoticesLink') }}</a>
            </div>
            @endif
        </li><li id="mobile-profile" class="submenu">
            <a class="dropdown-toggle">
                <span class="glyphicon glyphicon-user glyphicon-left"></span>
                <span class="name">{{ Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user()->email == 'dummy@sunsetresort.bg' ? 'Dummy Name' : (Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user()->local_name ? Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user()->local_name : Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user()->full_name) }}</span>
                <span class="caret"></span>
            </a>
            <ul id="profile-menu" class="dropdown-menu">
                @foreach(\Locales::getMenu('profile-category', true) as $item)
                    @include(\Locales::getNamespace() . '/partials.menu-recursive', ['item' => $item])
                @endforeach
            </ul>
        </li><li id="mobile-nav">
            <a class="dropdown-toggle">
                <span class="menu">{{ trans(\Locales::getNamespace() . '/messages.mobileNav') }}</span>
                <span class="glyphicon glyphicon-menu-hamburger"></span>
                <span class="glyphicon glyphicon-remove"></span>
            </a>
        </li>
    </ul>
</div>
