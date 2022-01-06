<div class="magnific-popup">
    @if (isset($datatables) && count($datatables) > 0)
        @include(\Locales::getNamespace() . '/partials.datatables')

        <script>
            unikat.callback = function() {
                this.datatables({!! json_encode($datatables) !!});
            };
        </script>
    @endif
</div>
