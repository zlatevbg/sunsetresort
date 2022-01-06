@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($booking))
    {!! Form::model($booking, ['method' => 'put', 'url' => \Locales::route('bookings/update'), 'id' => 'edit-booking-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('bookings/store'), 'id' => 'create-booking-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    <p class="text-center form-control">{{ trans(\Locales::getNamespace() . '/forms.sectionBookingOwner') }}</p>

    <div class="clearfix">
        <div class="form-group-3-left">
            <div class="form-group ajax-lock">
                {!! Form::label('input-apartment_id', trans(\Locales::getNamespace() . '/forms.apartmentLabel')) !!}
                <div id="bookings-apartment-tooltip-wrapper" class="tooltip {{ isset($info) ? '' : 'hidden' }} tooltip-info-icon"><span class="glyphicon glyphicon-info-sign"></span><div id="bookings-apartment-tooltip" class="tooltip-content">{!! isset($info) ? $info : '' !!}</div></div>
                {!! Form::multiselect('apartment_id', $multiselect['apartments'], ['id' => 'input-apartment_id', 'class' => 'form-control', 'data-ajax-queue' => 'sync', 'data-href' => \Locales::route('bookings/get-info')]) !!}
            </div>
        </div>
        <div class="form-group-3-left">
            <div class="form-group ajax-lock">
                {!! Form::label('input-owner_id', trans(\Locales::getNamespace() . '/forms.ownerLabel')) !!}
                <div id="bookings-owner-tooltip-wrapper" class="tooltip {{ isset($ownerInfo) ? '' : 'hidden' }} tooltip-info-icon"><span class="glyphicon glyphicon-info-sign glyphicon-color-red"></span><div id="bookings-owner-tooltip" class="tooltip-content">{!! isset($ownerInfo) ? $ownerInfo : '' !!}</div></div>
                {!! Form::multiselect('owner_id', $multiselect['owners'], ['id' => 'input-owner_id', 'class' => 'form-control', 'data-ajax-queue' => 'sync', 'data-href' => \Locales::route('bookings/get-owner-info')]) !!}
            </div>
        </div>
        <div class="form-group-3-right">
            <div class="form-group">
                {!! Form::label('input-exception', trans(\Locales::getNamespace() . '/forms.exceptionLabel')) !!}
                {!! Form::multiselect('exception', $multiselect['exception'], ['id' => 'input-exception', 'class' => 'form-control']) !!}
            </div>
        </div>
    </div>

    <p class="text-center form-control">{{ trans(\Locales::getNamespace() . '/forms.sectionBookingDateTime') }}</p>

    <div class="clearfix">
        <div class="form-group-left">
            <div class="form-group">
                {!! Form::label('input-arrive_at', trans(\Locales::getNamespace() . '/forms.arrivalDateLabel')) !!}
                {!! Form::text('arrive_at', null, ['id' => 'input-arrive_at', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.arrivalDatePlaceholder'), 'autocomplete' => 'off'] + ((isset($booking) && $booking->apartment_id) ? [] : ['disabled'])) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-arrival_time', trans(\Locales::getNamespace() . '/forms.arrivalTimeLabel')) !!}
                {!! Form::text('arrival_time', null, ['id' => 'input-arrival_time', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.arrivalTimePlaceholder')]) !!}
            </div>
        </div>
        <div class="form-group-right">
            <div class="form-group">
                {!! Form::label('input-departure_at', trans(\Locales::getNamespace() . '/forms.departureDateLabel')) !!}
                {!! Form::text('departure_at', null, ['id' => 'input-departure_at', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.departureDatePlaceholder'), 'autocomplete' => 'off'] + ((isset($booking) && ($booking->arrive_at || $booking->departure_at)) ? [] : ['disabled'])) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-departure_time', trans(\Locales::getNamespace() . '/forms.departureTimeLabel')) !!}
                {!! Form::text('departure_time', null, ['id' => 'input-departure_time', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.departureTimePlaceholder')]) !!}
            </div>
        </div>
    </div>

    {{-- <p class="text-center form-control">{{ trans(\Locales::getNamespace() . '/forms.sectionBookingOptions') }}</p>

    <div class="clearfix">
        <div class="form-group-3-left">
            <div class="form-group">
                {!! Form::label('input-kitchen_items', trans(\Locales::getNamespace() . '/forms.kitchenItemsLabel')) !!}
                {!! Form::multiselect('kitchen_items', $multiselect['kitchen_items'], ['id' => 'input-kitchen_items', 'class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group-3-left">
            <div class="form-group">
                {!! Form::label('input-loyalty_card', trans(\Locales::getNamespace() . '/forms.loyaltyCardLabel')) !!}
                {!! Form::multiselect('loyalty_card', $multiselect['loyalty_card'], ['id' => 'input-loyalty_card', 'class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group-3-right">
            <div class="form-group">
                {!! Form::label('input-club_card', trans(\Locales::getNamespace() . '/forms.clubCardLabel')) !!}
                {!! Form::multiselect('club_card', $multiselect['club_card'], ['id' => 'input-club_card', 'class' => 'form-control']) !!}
            </div>
        </div>
    </div>
    <div class="clearfix">
        <div class="form-group-3-left">
            <div class="form-group">
                {!! Form::label('input-exception', trans(\Locales::getNamespace() . '/forms.exceptionLabel')) !!}
                {!! Form::multiselect('exception', $multiselect['exception'], ['id' => 'input-exception', 'class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group-3-left">
            <div class="form-group">
                {!! Form::label('input-deposit_paid', trans(\Locales::getNamespace() . '/forms.depositPaidLabel')) !!}
                {!! Form::multiselect('deposit_paid', $multiselect['deposit_paid'], ['id' => 'input-deposit_paid', 'class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group-3-right">
            <div class="form-group">
                {!! Form::label('input-hotel_card', trans(\Locales::getNamespace() . '/forms.hotelCardLabel')) !!}
                {!! Form::multiselect('hotel_card', $multiselect['hotel_card'], ['id' => 'input-hotel_card', 'class' => 'form-control']) !!}
            </div>
        </div>
    </div> --}}

    <p class="text-center form-control">{{ trans(\Locales::getNamespace() . '/forms.sectionBookingFlightDetails') }}</p>

    <div class="clearfix">
        <div class="form-group-left">
            <div class="form-group">
                {!! Form::label('input-arrival_airport_id', trans(\Locales::getNamespace() . '/forms.arrivalAirportLabel')) !!}
                {!! Form::multiselect('arrival_airport_id', $multiselect['arrival_airport_id'], ['id' => 'input-arrival_airport_id', 'class' => 'form-control']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-arrival_flight', trans(\Locales::getNamespace() . '/forms.arrivalFlightLabel')) !!}
                {!! Form::text('arrival_flight', null, ['id' => 'input-arrival_flight', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.arrivalFlightPlaceholder')] + ((isset($booking) && ($booking->arrival_airport_id || $booking->arrival_flight)) ? [] : ['disabled'])) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-arrival_transfer', trans(\Locales::getNamespace() . '/forms.arrivalTransferLabel')) !!}
                {!! Form::multiselect('arrival_transfer', $multiselect['arrival_transfer'], ['id' => 'input-arrival_transfer', 'class' => 'form-control'] + ((isset($booking) && ($booking->arrival_airport_id || $booking->arrival_transfer)) ? [] : ['disabled'])) !!}
            </div>
        </div>
        <div class="form-group-right">
            <div class="form-group">
                {!! Form::label('input-departure_airport_id', trans(\Locales::getNamespace() . '/forms.departureAirportLabel')) !!}
                {!! Form::multiselect('departure_airport_id', $multiselect['departure_airport_id'], ['id' => 'input-departure_airport_id', 'class' => 'form-control']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-departure_flight', trans(\Locales::getNamespace() . '/forms.departureFlightLabel')) !!}
                {!! Form::text('departure_flight', null, ['id' => 'input-departure_flight', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.departureFlightPlaceholder')] + ((isset($booking) && ($booking->departure_airport_id || $booking->departure_flight)) ? [] : ['disabled'])) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-departure_transfer', trans(\Locales::getNamespace() . '/forms.departureTransferLabel')) !!}
                {!! Form::multiselect('departure_transfer', $multiselect['departure_transfer'], ['id' => 'input-departure_transfer', 'class' => 'form-control'] + ((isset($booking) && ($booking->departure_airport_id || $booking->departure_transfer)) ? [] : ['disabled'])) !!}
            </div>
        </div>
    </div>

    <div class="clearfix">
        <div class="form-group-left">
            <p class="text-center form-control">{{ trans(\Locales::getNamespace() . '/forms.sectionBookingAdults') }}</p>

            <div id="adults-fields">
                @if (isset($booking))
                    @foreach($booking->adults as $key => $value)
                    <div class="form-group">
                        {!! Form::label('input-adult-' . $value->order, trans(\Locales::getNamespace() . '/forms.adultNameLabel') . ' ' . $value->order) !!}
                        <div class="input-group">
                            {!! Form::text('adults[]', $value->name, ['id' => 'input-adult-' . $value->order, 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.adultNamePlaceholder')]) !!}
                            <span class="input-group-btn">
                                <button data-num="{{ $value->order }}" class="btn btn-danger js-remove-adult" type="button">
                                    <span class="glyphicon glyphicon-remove"></span>
                                </button>
                            </span>
                        </div>
                    </div>
                    @endforeach
                @endif

                <div class="btn-group-wrapper text-center">
                    <div class="btn-group">
                        <a class="btn btn-success js-add-adult">
                            <span class="glyphicon glyphicon-plus"></span>
                            {{ trans(\Locales::getNamespace() . '/forms.addAdultButton') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group-right">
            <p class="text-center form-control">{{ trans(\Locales::getNamespace() . '/forms.sectionBookingChildren') }}</p>

            <div id="children-fields">
                @if (isset($booking))
                    @foreach($booking->children as $key => $value)
                    <div class="form-group">
                        {!! Form::label('input-child-' . $value->order, trans(\Locales::getNamespace() . '/forms.childNameLabel') . ' ' . $value->order) !!}
                        <div class="input-group">
                            {!! Form::text('children[]', $value->name, ['id' => 'input-child-' . $value->order, 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.childNamePlaceholder')]) !!}
                            <span class="input-group-btn">
                                <button data-num="{{ $value->order }}" class="btn btn-danger js-remove-child" type="button">
                                    <span class="glyphicon glyphicon-remove"></span>
                                </button>
                            </span>
                        </div>
                    </div>
                    @endforeach
                @endif

                <div class="btn-group-wrapper text-center">
                    <div class="btn-group">
                        <a class="btn btn-success js-add-child">
                            <span class="glyphicon glyphicon-plus"></span>
                            {{ trans(\Locales::getNamespace() . '/forms.addChildButton') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <p class="text-center form-control">{{ trans(\Locales::getNamespace() . '/forms.sectionBookingExtraServices') }}</p>

    <div class="clearfix">
        <div class="form-group-3-left">
            <div class="form-group">
                {!! Form::label('input-accommodation_costs', trans('bookings.accommodationCostsLabel')) !!}
                {!! Form::select('accommodation_costs', $tourists, null, ['id' => 'input-accommodation_costs', 'class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group-3-left">
            <div class="form-group">
                {!! Form::label('input-transfer_costs', trans('bookings.transferCostsLabel')) !!}
                {!! Form::select('transfer_costs', $tourists, null, ['id' => 'input-transfer_costs', 'class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group-3-right">
            <div class="form-group">
                {!! Form::label('input-services_costs', trans('bookings.servicesCostsLabel')) !!}
                {!! Form::select('services_costs', $tourists, null, ['id' => 'input-services_costs', 'class' => 'form-control']) !!}
            </div>
        </div>
    </div>

    <div class="clearfix">
        <div class="form-group-3-left">
            <div class="form-group">
                {!! Form::label('input-services0', $multiselect['services0']['label']) !!}
                {!! Form::multiselect('services[]', $multiselect['services0'], ['id' => 'input-services0', 'class' => 'form-control', 'multiple' => 'multiple']) !!}
            </div>
        </div>
        <div class="form-group-3-left">
            <div class="form-group">
                {!! Form::label('input-services1', $multiselect['services1']['label']) !!}
                {!! Form::multiselect('services[]', $multiselect['services1'], ['id' => 'input-services1', 'class' => 'form-control', 'multiple' => 'multiple']) !!}
            </div>
        </div>
        <div class="form-group-3-right">
            <div class="form-group">
                {!! Form::label('input-services2', $multiselect['services2']['label']) !!}
                {!! Form::multiselect('services[]', $multiselect['services2'], ['id' => 'input-services2', 'class' => 'form-control', 'multiple' => 'multiple']) !!}
            </div>
        </div>
    </div>

    <div class="clearfix">
        <div class="form-group-left">
            <p class="text-center form-control">{{ trans(\Locales::getNamespace() . '/forms.sectionBookingMessage') }}</p>

            <div class="form-group">
                {!! Form::label('input-message', trans(\Locales::getNamespace() . '/forms.ownerMessageLabel')) !!}
                {!! Form::textarea('message', null, ['id' => 'input-message', 'class' => 'form-control small-comments', 'placeholder' => trans(\Locales::getNamespace() . '/forms.ownerMessagePlaceholder')]) !!}
            </div>
        </div>
        <div class="form-group-right">
            <p class="text-center form-control">{{ trans(\Locales::getNamespace() . '/forms.sectionBookingComments') }}</p>

            <div class="form-group">
                {!! Form::label('input-comments', trans(\Locales::getNamespace() . '/forms.commentsLabel')) !!}
                {!! Form::textarea('comments', null, ['id' => 'input-comments', 'class' => 'form-control small-comments', 'placeholder' => trans(\Locales::getNamespace() . '/forms.commentsPlaceholder')]) !!}
            </div>
        </div>
    </div>

    @if (isset($booking))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}

    <script>
    @section('script')
        var selectedApartment = null;
        var selectedOwner = null;
        var dates = [];

        @if (isset($booking))
        unikat.adults = {{ $booking->adults->count() }};
        unikat.children = {{ $booking->children->count() }};
        dates = {!! json_encode($dates) !!};
        @endif

        unikat.multiselect = {
            'input-apartment_id': {
                multiple: false,
                // showSubText: true,
                close: function() {
                    selectedApartment = $(this).val();

                    unikat.ajaxify({
                        that: $(this).closest('.ajax-lock'),
                        queue: $(this).data('ajaxQueue'),
                        action: $(this).data('href'),
                        data: { apartment: selectedApartment },
                        skipErrors: true,
                        functionParams: ['info', 'owners', 'dates'],
                        function: function(params) {
                            reloadOwners(params);

                            if (selectedApartment) {
                                $('#input-arrive_at').prop('disabled', false);
                            } else {
                                $('#input-arrive_at').prop('disabled', true).val('');
                            }

                            if (params.dates) {
                                dates = params.dates;
                            }

                            if (params.info) {
                                $('#bookings-apartment-tooltip').html(params.info);
                                $('#bookings-apartment-tooltip-wrapper').removeClass('hidden');
                            } else {
                                $('#bookings-apartment-tooltip-wrapper').addClass('hidden');
                            }
                        },
                    });
                },
            },
            'input-owner_id': {
                multiple: false,
                close: function() {
                    selectedOwner = $(this).val();

                    unikat.ajaxify({
                        that: $(this).closest('.ajax-lock'),
                        queue: $(this).data('ajaxQueue'),
                        action: $(this).data('href'),
                        data: { owner: selectedOwner },
                        skipErrors: true,
                        functionParams: ['info'],
                        function: function(params) {
                            if (params.info) {
                                $('#bookings-owner-tooltip').html(params.info);
                                $('#bookings-owner-tooltip-wrapper').removeClass('hidden');
                            } else {
                                $('#bookings-owner-tooltip-wrapper').addClass('hidden');
                            }
                        },
                    });
                },
            },
            'input-kitchen_items': {
                multiple: false,
            },
            'input-loyalty_card': {
                multiple: false,
            },
            'input-club_card': {
                multiple: false,
            },
            'input-exception': {
                multiple: false,
            },
            'input-deposit_paid': {
                multiple: false,
            },
            'input-hotel_card': {
                multiple: false,
            },
            'input-arrival_airport_id': {
                multiple: false,
                close: function() {
                    if (parseInt($(this).val()) > 0) {
                        $('#input-arrival_flight, #input-arrival_transfer').prop('disabled', false);
                        $('#input-arrival_transfer').multiselect('enable');
                    } else {
                        $('#input-arrival_flight, #input-arrival_transfer').prop('disabled', true).val('');
                        $('#input-arrival_transfer').multiselect('disable').multiselect('refresh');
                    }
                },
            },
            'input-departure_airport_id': {
                multiple: false,
                close: function() {
                    if (parseInt($(this).val()) > 0) {
                        $('#input-departure_flight, #input-departure_transfer').prop('disabled', false);
                        $('#input-departure_transfer').multiselect('enable');
                    } else {
                        $('#input-departure_flight, #input-departure_transfer').prop('disabled', true).val('');
                        $('#input-departure_transfer').multiselect('disable').multiselect('refresh');
                    }
                },
            },
            'input-arrival_transfer': {
                multiple: false,
            },
            'input-departure_transfer': {
                multiple: false,
            },
            'input-services0': {},
            'input-services1': {},
            'input-services2': {},
        };

        function reloadOwners(params) {
            $('#bookings-owner-tooltip-wrapper').addClass('hidden');

            var owners = $('#input-owner_id');
            selectedOwner = owners.val();
            owners.empty();

            $.each(params.owners, function(key, value) {
                owners.append($('<option></option>').attr('value', value).text(key));
            });

            owners.val(selectedOwner);

            owners.multiselect('refresh');
        }

        $.datepicker.regional.{{ \Locales::getCurrent() }} = unikat.variables.datepicker.{{ \Locales::getCurrent() }};
        $.datepicker.setDefaults($.datepicker.regional.{{ \Locales::getCurrent() }});

        function addZ(n) {
            return n < 10 ? '0' + n : '' + n;
        }

        var highlightLast = false;

        $('#input-arrive_at').datepicker({
            changeYear: true,
            changeMonth: true,
            onSelect: function(date) {
                var d = new Date(Date.parse($("#input-arrive_at").datepicker("getDate")));
                $('#input-departure_at').datepicker('option', 'minDate', d);
                $('#input-departure_at').removeAttr('disabled');
            },
            beforeShowDay: function(date) {
                var current = date.getFullYear() + '' + addZ(date.getMonth() + 1) + '' + addZ(date.getDate());

                if (highlightLast) {
                    highlightLast = false;
                    return [true, 'last-day'];
                }

                for (var i = 0; i < dates.length; i++) {
                    if (current >= dates[i].arrive_at && current < dates[i].departure_at) {
                        if (parseInt(current) + 1 == dates[i].departure_at) {
                            highlightLast = true;
                        }

                        return [false, 'booked-day'];
                    }
                }
                return [true, 'available-day'];
            },
        });

        $('#input-departure_at').datepicker({
            changeYear: true,
            changeMonth: true,
            beforeShowDay: function(date) {
                var current = date.getFullYear() + '' + addZ(date.getMonth() + 1) + '' + addZ(date.getDate());

                if (highlightLast) {
                    highlightLast = false;
                    return [false, 'booked-day'];
                }

                for (var i = 0; i < dates.length; i++) {
                    if (current == dates[i].arrive_at) {
                        return [true, 'first-day'];
                    } else if (current > dates[i].arrive_at && current < dates[i].departure_at) {
                        if (parseInt(current) + 1 == dates[i].departure_at) {
                            highlightLast = true;
                        }

                        return [false, 'booked-day'];
                    }
                }
                return [true, 'available-day'];
            },
        });
    @show
    </script>
</div>
@endsection
