@inject('carbon', '\Carbon\Carbon')

@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($rentalContract))
    {!! Form::model($rentalContract, ['method' => 'put', 'url' => \Locales::route('rental-contracts-tracker/update'), 'id' => 'edit-rental-contract-tracker-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('rental-contracts-tracker/store'), 'id' => 'create-rental-contract-tracker-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}
    {!! Form::hidden('flexiOverdue', null, ['id' => 'input-flexiOverdue']) !!}
    {!! Form::hidden('min_duration', null, ['id' => 'input-min_duration']) !!}
    {!! Form::hidden('max_duration', null, ['id' => 'input-max_duration']) !!}

    <div class="form-group ajax-lock">
        {!! Form::label('input-apartment_id', trans(\Locales::getNamespace() . '/forms.apartmentLabel')) !!}
        {!! Form::multiselect('apartment_id', $multiselect['apartments'], ['id' => 'input-apartment_id', 'class' => 'form-control', 'data-ajax-queue' => 'sync', 'data-href' => \Locales::route('rental-contracts-tracker/get-contracts')]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-owner_id', trans(\Locales::getNamespace() . '/forms.ownerLabel')) !!}
        {!! Form::multiselect('owner_id', $multiselect['owners'], ['id' => 'input-owner_id', 'class' => 'form-control'] + (isset($rentalContract) ? [] : ['disabled'])) !!}
    </div>

    <div class="form-group ajax-lock">
        {!! Form::label('input-rental_contract_id', trans(\Locales::getNamespace() . '/forms.rentalContractLabel')) !!}
        {!! Form::multiselect('rental_contract_id', $multiselect['contracts'], ['id' => 'input-rental_contract_id', 'class' => 'form-control', 'data-ajax-queue' => 'sync', 'data-href' => \Locales::route('rental-contracts-tracker/get-contract-data')] + (isset($rentalContract) ? [] : ['disabled'])) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-proxy_id', trans(\Locales::getNamespace() . '/forms.proxyLabel')) !!}
        {!! Form::multiselect('proxy_id', $multiselect['proxies'], ['id' => 'input-proxy_id', 'class' => 'form-control'] + ((isset($rentalContract) && !$covid) ? [] : ['disabled'])) !!}
    </div>

    <div class="clearfix">
        <div class="form-group-left">
            <div class="form-group">
                {!! Form::label('input-from', trans(\Locales::getNamespace() . '/forms.proxyFromLabel')) !!}
                {!! Form::text('from', isset($poa) ? $poa->from : null, ['id' => 'input-from', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.proxyFromPlaceholder')] + ((isset($rentalContract) && !$covid) ? [] : ['disabled'])) !!}
            </div>
        </div>
        <div class="form-group-right">
            <div class="form-group">
                {!! Form::label('input-to', trans(\Locales::getNamespace() . '/forms.proxyToLabel')) !!}
                {!! Form::text('to', isset($poa) ? $poa->to : null, ['id' => 'input-to', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.proxyToPlaceholder')] + ((isset($rentalContract) && !$covid) ? [] : ['disabled'])) !!}
            </div>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('input-price', trans(\Locales::getNamespace() . '/forms.priceLabel')) !!}
        {!! Form::text('price', null, ['id' => 'input-price', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.pricePlaceholder')] + (isset($rentalContract) ? [] : ['disabled'])) !!}
    </div>

    <div class="form-group {{ (isset($rentalContract) && $tc) ? '' : 'hidden' }}">
        {!! Form::label('input-price_tc', trans(\Locales::getNamespace() . '/forms.priceTCLabel')) !!}
        {!! Form::text('price_tc', null, ['id' => 'input-price_tc', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.priceTCPlaceholder')]) !!}
    </div>

    <div class="form-group {{ (!isset($rentalContract) || (isset($rentalContract) && $flexiOverdue)) ? 'hidden' : '' }}">
        {!! Form::label('input-mm_for_year', trans(\Locales::getNamespace() . '/forms.mmFeesForYearLabel')) !!}
        {!! Form::select('mm_for_year', $years, null, ['id' => 'input-mm_for_year', 'class' => 'form-control'] + (isset($rentalContract) ? [] : ['disabled'])) !!}
    </div>

    <div class="form-group {{ !isset($rentalContract) ? 'hidden' : ((isset($rentalContract) && $flexiOverdue) ? '' : 'hidden') }}">
        {!! Form::label('input-mm_for_years', trans(\Locales::getNamespace() . '/forms.mmFeesForYearsLabel')) !!}
        {!! Form::multiselect('mm_for_years[]', $multiselect['mm_for_years'], ['id' => 'input-mm_for_years', 'class' => 'form-control', 'multiple' => 'multiple'] + (isset($rentalContract) ? [] : ['disabled'])) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-duration', trans(\Locales::getNamespace() . '/forms.durationLabel')) !!}
        {!! Form::multiselect('duration', $multiselect['duration'], ['id' => 'input-duration', 'class' => 'form-control'] + (isset($rentalContract) ? [] : ['disabled'])) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-is_exception', trans(\Locales::getNamespace() . '/forms.isExceptionLabel')) !!}
        {!! Form::select('is_exception', $exceptions, null, ['id' => 'input-is_exception', 'class' => 'form-control']) !!}
    </div>

    <p class="text-center form-control">{{ trans(\Locales::getNamespace() . '/forms.sectionRentalContractPeriod') }}</p>
    <div class="clearfix">
        <div class="form-group-left">
            <div class="form-group">
                {!! Form::label('input-contract_dfrom1', trans(\Locales::getNamespace() . '/forms.dfrom1Label')) !!}
                {!! Form::text('contract_dfrom1', null, ['id' => 'input-contract_dfrom1', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.dfrom1Placeholder'), 'disabled']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-contract_dfrom2', trans(\Locales::getNamespace() . '/forms.dfrom2Label')) !!}
                {!! Form::text('contract_dfrom2', null, ['id' => 'input-contract_dfrom2', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.dfrom2Placeholder'), 'disabled']) !!}
            </div>
        </div>
        <div class="form-group-right">
            <div class="form-group">
                {!! Form::label('input-contract_dto1', trans(\Locales::getNamespace() . '/forms.dto1Label')) !!}
                {!! Form::text('contract_dto1', null, ['id' => 'input-contract_dto1', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.dto1Placeholder'), 'disabled']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-contract_dto2', trans(\Locales::getNamespace() . '/forms.dto2Label')) !!}
                {!! Form::text('contract_dto2', null, ['id' => 'input-contract_dto2', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.dto2Placeholder'), 'disabled']) !!}
            </div>
        </div>
    </div>

    <div class="form-group">
        <label>&nbsp;</label>
        <p class="text-center form-control">{{ trans(\Locales::getNamespace() . '/forms.sectionPersonalUsagePeriod') }}</p>
    </div>

    <div class="clearfix">
        <div class="form-group-left">
            <div class="form-group">
                {!! Form::label('input-personal_dfrom1', trans(\Locales::getNamespace() . '/forms.dfrom1Label')) !!}
                {!! Form::text('personal_dfrom1', null, ['id' => 'input-personal_dfrom1', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.dfrom1Placeholder'), 'disabled']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-personal_dfrom2', trans(\Locales::getNamespace() . '/forms.dfrom2Label')) !!}
                {!! Form::text('personal_dfrom2', null, ['id' => 'input-personal_dfrom2', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.dfrom2Placeholder'), 'disabled']) !!}
            </div>
        </div>
        <div class="form-group-right">
            <div class="form-group">
                {!! Form::label('input-personal_dto1', trans(\Locales::getNamespace() . '/forms.dto1Label')) !!}
                {!! Form::text('personal_dto1', null, ['id' => 'input-personal_dto1', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.dto1Placeholder'), 'disabled']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-personal_dto2', trans(\Locales::getNamespace() . '/forms.dto2Label')) !!}
                {!! Form::text('personal_dto2', null, ['id' => 'input-personal_dto2', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.dto2Placeholder'), 'disabled']) !!}
            </div>
        </div>
    </div>

    <div class="form-group">
        <label>&nbsp;</label>
        <p class="text-center form-control">{{ trans(\Locales::getNamespace() . '/forms.sectionComments') }}</p>
    </div>

    <div class="form-group">
        {!! Form::label('input-comments', trans(\Locales::getNamespace() . '/forms.commentsLabel')) !!}
        {!! Form::textarea('comments', null, ['id' => 'input-comments', 'class' => 'form-control contract-year-comments', 'placeholder' => trans(\Locales::getNamespace() . '/forms.commentsPlaceholder')]) !!}
    </div>

    @if (isset($rentalContract))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}

    <script>
    @section('script')
        var contract_dfrom1_selected = null;
        var contract_dto1_selected = null;
        var contract_dfrom2_selected = null;
        var contract_dto2_selected = null;
        var personal_dfrom1_selected = null;
        var personal_dto1_selected = null;
        var personal_dfrom2_selected = null;

        var contract_dfrom1 = $('#input-contract_dfrom1');
        var contract_dto1 = $('#input-contract_dto1');
        var contract_dfrom2 = $('#input-contract_dfrom2');
        var contract_dto2 = $('#input-contract_dto2');
        var personal_dfrom1 = $('#input-personal_dfrom1');
        var personal_dto1 = $('#input-personal_dto1');
        var personal_dfrom2 = $('#input-personal_dfrom2');
        var personal_dto2 = $('#input-personal_dto2');

        var minDate = '1.1.2007';
        contract_dfrom1.datepicker('option', 'minDate', minDate);
        contract_dto1.datepicker('option', 'minDate', minDate);
        contract_dfrom2.datepicker('option', 'minDate', minDate);
        contract_dto2.datepicker('option', 'minDate', minDate);
        personal_dfrom1.datepicker('option', 'minDate', minDate);
        personal_dto1.datepicker('option', 'minDate', minDate);
        personal_dfrom2.datepicker('option', 'minDate', minDate);
        personal_dto2.datepicker('option', 'minDate', minDate);

        var maxDate = null;

        var selectedApartment = {!! isset($rentalContract) ? $rentalContract->apartment_id : 'null' !!};
        var selectedOwner = {!! isset($rentalContract) ? $rentalContract->owner_id : 'null' !!};
        var selectedContract = {!! isset($rentalContract) ? $rentalContract->rental_contract_id : 'null' !!};
        var rental_contract_id = $('#input-rental_contract_id');
        var proxy_id = $('#input-proxy_id');
        var owner_id = $('#input-owner_id');
        var from = $('#input-from');
        var to = $('#input-to');
        var price = $('#input-price');
        var price_tc = $('#input-price_tc');
        var mm_for_year = $('#input-mm_for_year');
        var mm_for_years = $('#input-mm_for_years');
        var duration = $('#input-duration');

        unikat.multiselect = {
            'input-apartment_id': {
                multiple: false,
                open: function() {
                    unikat.multiselect.default_apartment_id = $($(this).multiselect('getChecked')).val();
                },
                close: function() {
                    if (unikat.multiselect.default_apartment_id != $($(this).multiselect('getChecked')).val()) {
                        selectedApartment = $(this).val();

                        unikat.ajaxify({
                            that: $(this).closest('.ajax-lock'),
                            method: 'get',
                            queue: $(this).data('ajaxQueue'),
                            action: $(this).data('href') + '/' + selectedApartment,
                            skipErrors: true,
                            functionParams: ['contracts', 'owners'],
                            function: function(params) {
                                var selectedOwner = owner_id.val();
                                owner_id.empty();
                                $.each(params.owners, function(key, value) {
                                    owner_id.append($('<option></option>').attr('value', value).text(key));
                                });
                                owner_id.val(selectedOwner);

                                // var selectedRentalContract = rental_contract_id.val();
                                rental_contract_id.empty();

                                $.each(params.contracts, function(key, value) {
                                    rental_contract_id.append($('<option></option>').attr('value', value).text(key));
                                });

                                // rental_contract_id.val(selectedRentalContract);

                                if (parseInt(selectedApartment)) {
                                    owner_id.multiselect('enable');
                                    // rental_contract_id.multiselect('enable');
                                } else {
                                    owner_id.multiselect('disable');
                                    rental_contract_id.multiselect('disable');
                                }

                                proxy_id.multiselect('disable');
                                mm_for_year.prop('disabled', true).empty().parent().addClass('hidden');
                                mm_for_years.multiselect('disable');
                                mm_for_years.val('').parent().parent().addClass('hidden');
                                duration.multiselect('disable');
                                from.prop('disabled', true).val('');
                                to.prop('disabled', true).val('');
                                price.prop('disabled', true).val('');
                                price_tc.val('').parent().addClass('hidden');
                                contract_dfrom1.prop('disabled', true).val('');
                                contract_dto1.prop('disabled', true).val('');
                                contract_dfrom2.prop('disabled', true).val('');
                                contract_dto2.prop('disabled', true).val('');
                                personal_dfrom1.prop('disabled', true).val('');
                                personal_dto1.prop('disabled', true).val('');
                                personal_dfrom2.prop('disabled', true).val('');
                                personal_dto2.prop('disabled', true).val('');

                                owner_id.multiselect('refresh');
                                rental_contract_id.multiselect('refresh');
                                proxy_id.multiselect('refresh');
                                mm_for_years.multiselect('refresh');
                                duration.multiselect('refresh');
                            },
                        });
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
                        selectedOwner = $(this).val();

                        rental_contract_id.multiselect('enable');
                        rental_contract_id.multiselect('refresh');
                    }
                },
            },
            'input-proxy_id': {
                multiple: false,
            },
            'input-mm_for_years': {},
            'input-rental_contract_id': {
                multiple: false,
                open: function() {
                    unikat.multiselect.default_contract_id = $($(this).multiselect('getChecked')).val();
                },
                close: function() {
                    if (unikat.multiselect.default_contract_id != $($(this).multiselect('getChecked')).val()) {
                        selectedContract = $(this).val();

                        unikat.ajaxify({
                            that: $(this).closest('.ajax-lock'),
                            method: 'get',
                            queue: $(this).data('ajaxQueue'),
                            action: $(this).data('href') + '/' + selectedApartment + '/' + selectedOwner + '/' + selectedContract,
                            skipErrors: true,
                            functionParams: ['year', 'price', 'tc', 'covid', 'minDuration', 'maxDuration', 'maxDate', 'contract_dfrom1', 'contract_dto1', 'contract_dfrom2', 'contract_dto2', 'personal_dfrom1', 'personal_dto1', 'personal_dfrom2', 'personal_dto2', 'from', 'to', 'proxies', 'years', 'flexiOverdue'],
                            function: function(params) {
                                if (!params.covid) {
                                    var selectedProxy = proxy_id.val();
                                    proxy_id.empty();
                                    $.each(params.proxies, function(key, value) {
                                        proxy_id.append($('<option></option>').attr('value', value).text(key));
                                    });
                                    proxy_id.val(selectedProxy);
                                }

                                var selectedYears = mm_for_years.val();
                                mm_for_years.empty();
                                $.each(params.years, function(key, value) {
                                    mm_for_years.append($('<option></option>').attr('value', value).text(key));
                                });
                                mm_for_years.val(selectedYears);

                                var selectedDuration = duration.val() || 1;
                                duration.empty();
                                for (var i = parseInt(params.minDuration); i <= parseInt(params.maxDuration); i++) {
                                    duration.append($('<option></option>').attr('value', i).text(i));
                                }
                                duration.val(selectedDuration);

                                if (parseInt(selectedContract)) {
                                    if (!params.covid) {
                                        proxy_id.multiselect('enable');
                                        from.prop('disabled', false).val(params.from);
                                        to.prop('disabled', false).val(params.to);
                                    }

                                    if (params.flexiOverdue) {
                                        $('#input-flexiOverdue').val(params.flexiOverdue);
                                        mm_for_years.parent().parent().removeClass('hidden');
                                        mm_for_year.val('').prop('disabled', true).parent().addClass('hidden');
                                    } else {
                                        $('#input-flexiOverdue').val('');
                                        mm_for_year.parent().removeClass('hidden');
                                        mm_for_years.val('').parent().parent().addClass('hidden');

                                        mm_for_year.prop('disabled', false);
                                        mm_for_year.empty();
                                        mm_for_year.append($('<option></option>').attr('value', '').text('{{ trans(\Locales::getNamespace() . '/forms.selectOption') }}'));
                                        mm_for_year.append($('<option></option>').attr('value', params.year).text(params.year));
                                        mm_for_year.append($('<option></option>').attr('value', params.year + 1).text(params.year + 1));
                                    }

                                    mm_for_years.multiselect('enable');
                                    duration.multiselect('enable');

                                    $('#input-min_duration').val(parseInt(params.minDuration));
                                    $('#input-max_duration').val(parseInt(params.maxDuration));

                                    price.prop('disabled', false).val(params.price);
                                    if (params.tc) {
                                        price_tc.parent().removeClass('hidden');
                                    } else {
                                        price_tc.val('').parent().addClass('hidden');
                                    }

                                    if (params.covid) {
                                        proxy_id.multiselect('disable');
                                        from.prop('disabled', true).val('');
                                        to.prop('disabled', true).val('');
                                    }
                                } else {
                                    proxy_id.multiselect('disable');
                                    mm_for_year.prop('disabled', true).empty().parent().addClass('hidden');
                                    mm_for_years.multiselect('disable');
                                    mm_for_years.val('').parent().parent().addClass('hidden');
                                    duration.multiselect('disable');
                                    from.prop('disabled', true).val('');
                                    to.prop('disabled', true).val('');
                                    price.prop('disabled', true).val('');
                                    price_tc.val('').parent().addClass('hidden');
                                    contract_dfrom1.prop('disabled', true).val('');
                                    contract_dto1.prop('disabled', true).val('');
                                    contract_dfrom2.prop('disabled', true).val('');
                                    contract_dto2.prop('disabled', true).val('');
                                    personal_dfrom1.prop('disabled', true).val('');
                                    personal_dto1.prop('disabled', true).val('');
                                    personal_dfrom2.prop('disabled', true).val('');
                                    personal_dto2.prop('disabled', true).val('');
                                }

                                proxy_id.multiselect('refresh');
                                mm_for_years.multiselect('refresh');
                                duration.multiselect('refresh');

                                contract_dfrom1_selected = null;
                                contract_dto1_selected = null;
                                contract_dfrom2_selected = null;
                                contract_dto2_selected = null;
                                personal_dfrom1_selected = null;
                                personal_dto1_selected = null;
                                personal_dfrom2_selected = null;

                                maxDate = params.maxDate;
                                contract_dfrom1.datepicker('option', 'maxDate', maxDate);
                                contract_dfrom2.datepicker('option', 'maxDate', maxDate);
                                personal_dfrom1.datepicker('option', 'minDate', new Date(Math.min(params.contract_dfrom1, isNaN(params.contract_dfrom2) ? Infinity : params.contract_dfrom2)));
                                personal_dfrom1.datepicker('option', 'maxDate', new Date(Math.max(params.contract_dto1, isNaN(params.contract_dto2) ? Infinity : params.contract_dto2)));
                                personal_dfrom2.datepicker('option', 'minDate', new Date(Math.min(params.contract_dfrom1, isNaN(params.contract_dfrom2) ? Infinity : params.contract_dfrom2)));
                                personal_dfrom2.datepicker('option', 'maxDate', new Date(Math.max(params.contract_dto1, isNaN(params.contract_dto2) ? Infinity : params.contract_dto2)));

                                if (params.contract_dfrom1) {
                                    contract_dfrom1.prop('disabled', false);
                                    personal_dfrom1.prop('disabled', false);
                                    contract_dfrom1_selected = new Date(params.contract_dfrom1);
                                    contract_dfrom1.datepicker('setDate', contract_dfrom1_selected);
                                }

                                if (params.contract_dto1) {
                                    contract_dto1.prop('disabled', false);
                                    contract_dfrom2.prop('disabled', false);
                                    contract_dto1_selected = new Date(params.contract_dto1);
                                    contract_dto1.datepicker('setDate', contract_dto1_selected);
                                    contract_dto1.datepicker('option', 'minDate', new Date(params.contract_dfrom1));
                                }

                                if (params.contract_dfrom2) {
                                    contract_dfrom2.prop('disabled', false);
                                    contract_dfrom2_selected = new Date(params.contract_dfrom2);
                                    contract_dfrom2.datepicker('setDate', contract_dfrom2_selected);
                                }

                                if (params.contract_dto2) {
                                    contract_dto2.prop('disabled', false);
                                    contract_dto2_selected = new Date(params.contract_dto2);
                                    contract_dto2.datepicker('setDate', contract_dto2_selected);
                                    contract_dto2.datepicker('option', 'minDate', new Date(params.contract_dfrom2));
                                    if (params.contract_dfrom1 > params.contract_dfrom2) {
                                        contract_dto2.datepicker('option', 'maxDate', new Date(params.contract_dfrom1));
                                    }
                                }

                                if (params.personal_dfrom1) {
                                    personal_dfrom1.prop('disabled', false);
                                    personal_dfrom1_selected = new Date(params.personal_dfrom1);
                                    personal_dfrom1.datepicker('setDate', personal_dfrom1_selected);
                                }

                                if (params.personal_dto1) {
                                    personal_dto1.prop('disabled', false);
                                    personal_dfrom2.prop('disabled', false);
                                    personal_dto1_selected = new Date(params.personal_dto1);
                                    personal_dto1.datepicker('setDate', personal_dto1_selected);
                                    personal_dto1.datepicker('option', 'minDate', new Date(params.personal_dfrom1));
                                    personal_dto1.datepicker('option', 'maxDate', new Date(Math.max(params.contract_dto1, isNaN(params.contract_dto2) ? Infinity : params.contract_dto2)));
                                }

                                if (params.personal_dfrom2) {
                                    personal_dfrom2.prop('disabled', false);
                                    personal_dfrom2_selected = new Date(params.personal_dfrom2);
                                    personal_dfrom2.datepicker('setDate', personal_dfrom2_selected);
                                }

                                if (params.personal_dto2) {
                                    personal_dto2.prop('disabled', false);
                                    personal_dto2.datepicker('setDate', new Date(params.personal_dto2));
                                    personal_dto2.datepicker('option', 'minDate', new Date(params.personal_dfrom2));
                                    personal_dto2.datepicker('option', 'maxDate', getMaxDate());
                                }
                            },
                        });
                    }
                },
            },
            'input-duration': {
                multiple: false,
                close: function() {
                    to.val(parseInt(from.val()) + parseInt($(this).val()) - 1);
                },
            },
        };

        $.datepicker.regional.{{ \Locales::getCurrent() }} = unikat.variables.datepicker.{{ \Locales::getCurrent() }};
        $.datepicker.setDefaults($.datepicker.regional.{{ \Locales::getCurrent() }});

        contract_dfrom1.datepicker({
            onSelect: function(date) {
                contract_dfrom1_selected = new Date(Date.parse(contract_dfrom1.datepicker('getDate')));

                personal_dfrom1.datepicker('option', 'minDate', contract_dfrom1_selected);
                personal_dfrom2.datepicker('option', 'minDate', contract_dfrom1_selected);
                contract_dto1.datepicker('option', 'minDate', contract_dfrom1_selected);
                contract_dto1.prop('disabled', false);
            }
        });

        contract_dto1.datepicker({
            onSelect: function(date) {
                contract_dto1_selected = new Date(Date.parse(contract_dto1.datepicker('getDate')));

                personal_dfrom1.datepicker('option', 'maxDate', contract_dto1_selected);
                personal_dfrom2.datepicker('option', 'maxDate', contract_dto1_selected);
                personal_dto1.datepicker('option', 'maxDate', contract_dto1_selected);
                personal_dto2.datepicker('option', 'maxDate', contract_dto1_selected);
                personal_dfrom1.prop('disabled', false);
                contract_dfrom2.prop('disabled', false);
            }
        });

        contract_dfrom2.datepicker({
            onSelect: function(date) {
                contract_dfrom2_selected = new Date(Date.parse(contract_dfrom2.datepicker('getDate')));

                var minDate = (contract_dfrom1_selected > contract_dfrom2_selected) ? contract_dfrom2_selected : contract_dfrom1_selected;

                personal_dfrom1.datepicker('option', 'minDate', minDate);
                personal_dfrom2.datepicker('option', 'minDate', minDate);
                contract_dto2.datepicker('option', 'minDate', contract_dfrom2_selected);
                if (contract_dfrom2_selected < contract_dfrom1_selected) {
                    contract_dto2.datepicker('option', 'maxDate', new Date(contract_dfrom1_selected.getFullYear(), contract_dfrom1_selected.getMonth(), contract_dfrom1_selected.getDate() - 1));
                } else {
                    contract_dto2.datepicker('option', 'maxDate', maxDate);
                }

                if (!contract_dto2.val()) {
                    contract_dto2.val('').prop('disabled', false);
                }
            },
            beforeShowDay: beforeContract,
        });

        contract_dto2.datepicker({
            onSelect: function(date) {
                contract_dto2_selected = new Date(Date.parse(contract_dto2.datepicker('getDate')));

                var maxDate = (contract_dto1_selected > contract_dto2_selected) ? contract_dto1_selected : contract_dto2_selected;
                personal_dfrom1.datepicker('option', 'maxDate', maxDate);
                personal_dto1.datepicker('option', 'maxDate', maxDate);
                personal_dfrom2.datepicker('option', 'maxDate', maxDate);
                personal_dto2.datepicker('option', 'maxDate', maxDate);
            },
            beforeShowDay: beforeContract,
        });

        personal_dfrom1.datepicker({
            onSelect: function(date) {
                personal_dfrom1_selected = new Date(Date.parse(personal_dfrom1.datepicker('getDate')));

                var maxDate;
                if (personal_dfrom1_selected >= contract_dfrom1_selected && personal_dfrom1_selected <= contract_dto1_selected) {
                    maxDate = contract_dto1_selected;
                } else { // if (personal_dfrom1_selected >= contract_dfrom2_selected && personal_dfrom1_selected <= contract_dto2_selected)
                    maxDate = contract_dto2_selected;
                }

                personal_dto1.datepicker('option', 'minDate', personal_dfrom1_selected);
                personal_dto1.datepicker('option', 'maxDate', maxDate);
                personal_dto1.prop('disabled', false);
            },
            beforeShowDay: beforePersonal1,
        });

        personal_dto1.datepicker({
            onSelect: function(date) {
                personal_dto1_selected = new Date(Date.parse(personal_dto1.datepicker('getDate')));

                personal_dfrom2.prop('disabled', false);
            },
            beforeShowDay: beforePersonal1,
        });

        personal_dfrom2.datepicker({
            onSelect: function(date) {
                personal_dfrom2_selected = new Date(Date.parse(personal_dfrom2.datepicker('getDate')));

                var maxDate;
                if (personal_dfrom2_selected >= contract_dfrom1_selected && personal_dfrom2_selected <= contract_dto1_selected) {
                    if (personal_dfrom2_selected < personal_dfrom1_selected) {
                        if (personal_dfrom1_selected > contract_dto1_selected) {
                            maxDate = contract_dto1_selected;
                        } else {
                            maxDate = personal_dfrom1_selected;
                        }
                    } else {
                        maxDate = contract_dto1_selected;
                    }
                } else { // if (personal_dfrom2_selected >= contract_dfrom2_selected && personal_dfrom2_selected <= contract_dto2_selected)
                    if (personal_dfrom2_selected < personal_dfrom1_selected) {
                        if (personal_dfrom1_selected > contract_dto2_selected) {
                            maxDate = contract_dto2_selected;
                        } else {
                            maxDate = personal_dfrom1_selected;
                        }
                    } else {
                        maxDate = contract_dto2_selected;
                    }
                }

                personal_dto2.datepicker('option', 'minDate', personal_dfrom2_selected);
                personal_dto2.datepicker('option', 'maxDate', maxDate);
                personal_dto2.prop('disabled', false);
            },
            beforeShowDay: beforePersonal2,
        });

        personal_dto2.datepicker({
            beforeShowDay: beforePersonal2,
        });

        @if (isset($rentalContract))
            maxDate = '{{ $maxDate }}';
            contract_dfrom1.datepicker('option', 'maxDate', maxDate);
            contract_dfrom2.datepicker('option', 'maxDate', maxDate);
            personal_dfrom1.datepicker('option', 'minDate', '{{ min(array_filter([$rentalContract->contract_dfrom1, $rentalContract->contract_dfrom2])) }}');
            personal_dfrom1.datepicker('option', 'maxDate', '{{ max(array_filter([$rentalContract->contract_dto1, $rentalContract->contract_dto2])) }}');
            personal_dfrom2.datepicker('option', 'minDate', '{{ min(array_filter([$rentalContract->contract_dfrom1, $rentalContract->contract_dfrom2])) }}');
            personal_dfrom2.datepicker('option', 'maxDate', '{{ max(array_filter([$rentalContract->contract_dto1, $rentalContract->contract_dto2])) }}');

            @if ($rentalContract->contract_dfrom1)
                contract_dfrom1.prop('disabled', false);
                personal_dfrom1.prop('disabled', false);
                contract_dfrom1_selected = new Date('{{ $carbon->parse($rentalContract->contract_dfrom1) }}');
            @endif

            @if ($rentalContract->contract_dto1)
                contract_dto1.prop('disabled', false);
                contract_dfrom2.prop('disabled', false);
                contract_dto1_selected = new Date('{{ $carbon->parse($rentalContract->contract_dto1) }}');
                contract_dto1.datepicker('option', 'minDate', '{{ $rentalContract->contract_dfrom1 }}');
            @endif

            @if ($rentalContract->contract_dfrom2)
                contract_dfrom2.prop('disabled', false);
                contract_dfrom2_selected = new Date('{{ $carbon->parse($rentalContract->contract_dfrom2) }}');
            @endif

            @if ($rentalContract->contract_dto2)
                contract_dto2.prop('disabled', false);
                contract_dto2_selected = new Date('{{ $carbon->parse($rentalContract->contract_dto2) }}');
                contract_dto2.datepicker('option', 'minDate', '{{ $rentalContract->contract_dfrom2 }}');
                @if ($rentalContract->contract_dfrom1 > $rentalContract->contract_dfrom2)
                    contract_dto2.datepicker('option', 'maxDate', '{{ $rentalContract->contract_dfrom1 }}');
                @endif
            @endif

            @if ($rentalContract->personal_dfrom1)
                personal_dfrom1.prop('disabled', false);
                personal_dfrom1_selected = new Date('{{ $carbon->parse($rentalContract->personal_dfrom1) }}');
            @endif

            @if ($rentalContract->personal_dto1)
                personal_dto1.prop('disabled', false);
                personal_dfrom2.prop('disabled', false);
                personal_dto1_selected = new Date('{{ $carbon->parse($rentalContract->personal_dto1) }}');
                personal_dto1.datepicker('option', 'minDate', '{{ $rentalContract->personal_dfrom1 }}');
                personal_dto1.datepicker('option', 'maxDate', '{{ max(array_filter([$rentalContract->contract_dto1, $rentalContract->contract_dto2])) }}');
            @endif

            @if ($rentalContract->personal_dfrom2)
                personal_dfrom2.prop('disabled', false);
                personal_dfrom2_selected = new Date('{{ $carbon->parse($rentalContract->personal_dfrom2) }}');
            @endif

            @if ($rentalContract->personal_dto2)
                personal_dto2.prop('disabled', false);
                personal_dto2.datepicker('option', 'minDate', '{{ $rentalContract->personal_dfrom2 }}');
                personal_dto2.datepicker('option', 'maxDate', getMaxDate());
            @endif
        @endif

        function beforeContract(date) {
            if (date >= contract_dfrom1_selected && date <= contract_dto1_selected) {
                return [false, ''];
            }
            return [true, ''];
        }

        function beforePersonal1(date) {
            if (contract_dfrom1_selected > contract_dfrom2_selected) {
                if (date > contract_dto2_selected && date < contract_dfrom1_selected) {
                    return [false, ''];
                }
                return [true, ''];
            } else {
                if (date > contract_dto1_selected && date < contract_dfrom2_selected) {
                    return [false, ''];
                }
                return [true, ''];
            }
        }

        function beforePersonal2(date) {
            if (contract_dfrom1_selected > contract_dfrom2_selected) {
                if ((date > contract_dto2_selected && date < contract_dfrom1_selected) || (date >= personal_dfrom1_selected && date <= personal_dto1_selected)) {
                    return [false, ''];
                }
                return [true, ''];
            } else {
                if ((date > contract_dto1_selected && date < contract_dfrom2_selected) || (date >= personal_dfrom1_selected && date <= personal_dto1_selected)) {
                    return [false, ''];
                }
                return [true, ''];
            }
        }

        function getMaxDate() {
            var max;
            if (personal_dfrom2_selected >= contract_dfrom1_selected && personal_dfrom2_selected <= contract_dto1_selected) {
                if (personal_dfrom2_selected < personal_dfrom1_selected) {
                    if (personal_dfrom1_selected > contract_dto1_selected) {
                        max = contract_dto1_selected;
                    } else {
                        max = personal_dfrom1_selected;
                    }
                } else {
                    max = contract_dto1_selected;
                }
            } else { // if (personal_dfrom2_selected >= contract_dfrom2_selected && personal_dfrom2_selected <= contract_dto2_selected)
                if (personal_dfrom2_selected < personal_dfrom1_selected) {
                    if (personal_dfrom1_selected > contract_dto2_selected) {
                        max = contract_dto2_selected;
                    } else {
                        max = personal_dfrom1_selected;
                    }
                } else {
                    max = contract_dto2_selected;
                }
            }

            return max;
        }
    @show
    </script>
</div>
@endsection
