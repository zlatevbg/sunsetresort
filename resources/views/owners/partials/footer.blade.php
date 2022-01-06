<div id="footer">
    <ul>@each(\Locales::getNamespace() . '/partials.menu-recursive', \Locales::getMenu('footer-category', true), 'item')</ul>
    <p>{{ trans(\Locales::getNamespace() . '/messages.footerText') }}</p>
</div>
