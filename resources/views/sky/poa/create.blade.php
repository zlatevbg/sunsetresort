@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($poa))
    {!! Form::model($poa, ['method' => 'put', 'url' => \Locales::route('poa/update'), 'id' => 'edit-poa-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('poa/store'), 'id' => 'create-poa-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    <div class="form-group ajax-lock">
        {!! Form::label('input-apartment_id', trans(\Locales::getNamespace() . '/forms.apartmentLabel')) !!}
        {!! Form::multiselect('apartment_id', $multiselect['apartments'], ['id' => 'input-apartment_id', 'class' => 'form-control', 'data-ajax-queue' => 'sync', 'data-href' => \Locales::route('poa/get-owners')]) !!}
    </div>
    <div class="form-group ajax-lock">
        {!! Form::label('input-owner_id', trans(\Locales::getNamespace() . '/forms.ownerLabel')) !!}
        {!! Form::multiselect('owner_id', $multiselect['owners'], ['id' => 'input-owner_id', 'class' => 'form-control', 'data-ajax-queue' => 'sync', 'data-href' => \Locales::route('poa/get-proxies')] + (isset($poa) ? [] : ['disabled'])) !!}
    </div>
    <div class="form-group">
        {!! Form::label('input-proxy_id', trans(\Locales::getNamespace() . '/forms.proxyLabel')) !!}
        {!! Form::multiselect('proxy_id', $multiselect['proxies'], ['id' => 'input-proxy_id', 'class' => 'form-control'] + (isset($poa) ? [] : ['disabled'])) !!}
    </div>
    <div class="form-group">
        {!! Form::label('input-from', trans(\Locales::getNamespace() . '/forms.fromLabel')) !!}
        {!! Form::text('from', null, ['id' => 'input-from', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.fromPlaceholder')] + (isset($poa) ? [] : ['disabled'])) !!}
    </div>
    <div class="form-group">
        {!! Form::label('input-to', trans(\Locales::getNamespace() . '/forms.toLabel')) !!}
        {!! Form::text('to', null, ['id' => 'input-to', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.toPlaceholder')] + (isset($poa) ? [] : ['disabled'])) !!}
    </div>

    @if (isset($poa))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}

    <script>
    @section('script')
        var apartment_id = $('#input-apartment_id');
        var owner_id = $('#input-owner_id');
        var proxy_id = $('#input-proxy_id');
        var from = $('#input-from');
        var to = $('#input-to');

        var selectedApartment = null;
        var selectedOwner = null;

        unikat.multiselect = {
            'input-apartment_id': {
                multiple: false,
                open: function() {
                    unikat.multiselect.default_apartment_id = $($(this).multiselect('getChecked')).val();
                },
                close: function() {
                    if (unikat.multiselect.default_apartment_id != $($(this).multiselect('getChecked')).val()) {
                        loadOwners(this);
                    }
                },
            },
            'input-owner_id': {
                multiple: false,
                open: function() {
                    unikat.multiselect.default_owner_id = $($(this).multiselect('getChecked')).val();
                },
                close: function() {
                    if (unikat.multiselect.default_owner_id != $($(this).multiselect('getChecked')).val()) {
                        loadProxies(this);
                    }
                },
            },
            'input-proxy_id': {
                multiple: false,
            },
        };

        from.on('keyup input', $.debounce(400, function(e) {
          var fromValue = $(this).val();
          var toValue = to.val();
          if (fromValue.length == 4) {
            loadProxies(owner_id, fromValue, toValue);
          }
        }));

        to.on('keyup input', $.debounce(400, function(e) {
          var fromValue = from.val();
          var toValue = $(this).val();
          if (toValue.length == 4) {
            loadProxies(owner_id, fromValue, toValue);
          }
        }));

        function loadOwners(that) {
            selectedApartment = $(that).val();
            unikat.ajaxify({
                that: $(that).closest('.ajax-lock'),
                method: 'get',
                queue: $(that).data('ajaxQueue'),
                action: $(that).data('href') + '/' + selectedApartment,
                skipErrors: true,
                functionParams: ['owners'],
                function: function(params) {
                    owner_id.empty();

                    $.each(params.owners, function(key, value) {
                        owner_id.append($('<option></option>').attr('value', value).text(key));
                    });

                    owner_id.val(null);

                    if (parseInt(selectedApartment)) {
                        owner_id.multiselect('enable');
                        proxy_id.multiselect('disable');
                        from.prop('disabled', true).val('');
                        to.prop('disabled', true).val('');
                    } else {
                        owner_id.multiselect('disable');
                        proxy_id.multiselect('disable');
                        from.prop('disabled', true).val('');
                        to.prop('disabled', true).val('');
                    }

                    owner_id.multiselect('refresh');
                    proxy_id.multiselect('refresh');
                },
            });
        }

        function loadProxies(that, fromValue, toValue) {
            selectedOwner = $(that).val();
            unikat.ajaxify({
                that: $(that).closest('.ajax-lock'),
                method: 'get',
                queue: $(that).data('ajaxQueue'),
                @if (isset($poa))
                    action: $(that).data('href') + '/' + selectedOwner + '/' + selectedApartment + '/' + {{ $poa->id }} + (fromValue ? '?from=' + fromValue + '&to=' + toValue : ''),
                @else
                    action: $(that).data('href') + '/' + selectedOwner + '/' + selectedApartment + (fromValue ? '?from=' + fromValue + '&to=' + toValue : ''),
                @endif
                skipErrors: true,
                functionParams: ['proxies', 'from', 'to'],
                function: function(params) {
                    var selectedProxy = proxy_id.val();
                    proxy_id.empty();

                    $.each(params.proxies, function(key, value) {
                        proxy_id.append($('<option></option>').attr('value', value).text(key));
                    });

                    proxy_id.val(selectedProxy);

                    if (parseInt(selectedOwner)) {
                        proxy_id.multiselect('enable');
                        from.prop('disabled', false).val(params.from);
                        to.prop('disabled', false).val(params.to);
                    } else {
                        proxy_id.multiselect('disable');
                        from.prop('disabled', true).val('');
                        to.prop('disabled', true).val('');
                    }

                    proxy_id.multiselect('refresh');
                },
            });
        }
    @show
    </script>
</div>
@endsection
