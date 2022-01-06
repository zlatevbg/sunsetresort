<div id="nav-wrapper">
    <nav id="fixed-nav">
        <p>{{ trans(\Locales::getNamespace() . '/messages.company') }}</p>
        <ul id="menu">@each(\Locales::getNamespace() . '/partials.menu-recursive', $nav, 'item')</ul>
    </nav>
</div>
