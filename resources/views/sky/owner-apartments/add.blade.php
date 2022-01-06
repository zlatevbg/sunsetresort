@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    {!! Form::open(['url' => \Locales::route('owner-apartments/save'), 'id' => 'add-apartment-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}
    {!! Form::hidden('owner', $owner, ['id' => 'input-owner']) !!}

    <div class="form-group ajax-lock">
        {!! Form::label('input-project', trans(\Locales::getNamespace() . '/forms.projectLabel')) !!}
        {!! Form::multiselect('project', $multiselect['projects'], ['id' => 'input-project', 'class' => 'form-control', 'data-ajax-queue' => 'sync', 'data-href' => \Locales::route('owner-apartments/get-buildings')]) !!}
    </div>

    <div class="form-group ajax-lock">
        {!! Form::label('input-building', trans(\Locales::getNamespace() . '/forms.buildingLabel')) !!}
        {!! Form::multiselect('building', $multiselect['buildings'], ['id' => 'input-building', 'class' => 'form-control', 'data-ajax-queue' => 'sync', 'data-href' => \Locales::route('owner-apartments/get-floors'), 'disabled']) !!}
    </div>

    <div class="form-group ajax-lock">
        {!! Form::label('input-floor', trans(\Locales::getNamespace() . '/forms.floorLabel')) !!}
        {!! Form::multiselect('floor', $multiselect['floors'], ['id' => 'input-floor', 'class' => 'form-control', 'data-ajax-queue' => 'sync', 'data-href' => \Locales::route('owner-apartments/get-apartments'), 'disabled']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-apartments', trans(\Locales::getNamespace() . '/forms.apartmentsLabel')) !!}
        {!! Form::multiselect('apartments[]', $multiselect['apartments'], ['id' => 'input-apartments', 'class' => 'form-control', 'multiple']) !!}
    </div>

    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}

    {!! Form::close() !!}

    <script>
    @section('script')
        unikat.multiselect = {
            'input-project': {
                multiple: false,
                open: function() {
                    unikat.multiselect.default_project = $($(this).multiselect('getChecked')).val();
                },
                close: function() {
                    if (unikat.multiselect.default_project != $($(this).multiselect('getChecked')).val()) {
                        unikat.ajaxify({
                            that: $(this).closest('.ajax-lock'),
                            method: 'get',
                            queue: $(this).data('ajaxQueue'),
                            action: $(this).data('href') + '/{{ $owner }}' + ($(this).val() ? '/' + $(this).val() : ''),
                            skipErrors: true,
                            functionParams: ['buildings', 'apartments', 'project'],
                            function: function(params) {
                                var buildings = $('#input-building');
                                buildings.empty();

                                $.each(params.buildings, function(key, value) {
                                    buildings.append($('<option></option>').attr('value', key).text(value));
                                });

                                if (parseInt(params.project)) {
                                    buildings.multiselect('enable');
                                } else {
                                    buildings.multiselect('disable');
                                }

                                buildings.multiselect('refresh');

                                var floors = $('#input-floor');
                                floors.empty();
                                floors.append($('<option></option>').attr('value', 0).text('{{ trans(\Locales::getNamespace() . '/forms.selectOption') }}'));
                                floors.multiselect('disable');
                                floors.multiselect('refresh');

                                var apartments = $('#input-apartments');
                                apartments.empty();
                                $.each(params.apartments, function(key, value) {
                                    apartments.append($('<option></option>').attr('value', key).text(value));
                                });
                                apartments.multiselect('refresh');
                            },
                        });
                    }
                },
            },
            'input-building': {
                multiple: false,
                open: function() {
                    unikat.multiselect.default_building = $($(this).multiselect('getChecked')).val();
                },
                close: function() {
                    if (unikat.multiselect.default_building != $($(this).multiselect('getChecked')).val()) {
                        unikat.ajaxify({
                            that: $(this).closest('.ajax-lock'),
                            method: 'get',
                            queue: $(this).data('ajaxQueue'),
                            action: $(this).data('href') + '/{{ $owner }}' + ($(this).val() ? '/' + $(this).val() : ''),
                            skipErrors: true,
                            functionParams: ['floors', 'apartments', 'building'],
                            function: function(params) {
                                var floors = $('#input-floor');
                                floors.empty();

                                $.each(params.floors, function(key, value) {
                                    floors.append($('<option></option>').attr('value', key).text(value));
                                });

                                if (parseInt(params.building)) {
                                    floors.multiselect('enable');
                                } else {
                                    floors.multiselect('disable');
                                }

                                floors.multiselect('refresh');

                                var apartments = $('#input-apartments');
                                apartments.empty();
                                $.each(params.apartments, function(key, value) {
                                    apartments.append($('<option></option>').attr('value', key).text(value));
                                });
                                apartments.multiselect('refresh');
                            },
                        });
                    }
                },
            },
            'input-floor': {
                multiple: false,
                open: function() {
                    unikat.multiselect.default_floor = $($(this).multiselect('getChecked')).val();
                },
                close: function() {
                    if (unikat.multiselect.default_floor != $($(this).multiselect('getChecked')).val()) {
                        unikat.ajaxify({
                            that: $(this).closest('.ajax-lock'),
                            method: 'get',
                            queue: $(this).data('ajaxQueue'),
                            action: $(this).data('href') + '/{{ $owner }}' + ($(this).val() ? '/' + $(this).val() : ''),
                            skipErrors: true,
                            functionParams: ['apartments', 'floor'],
                            function: function(params) {
                                var apartments = $('#input-apartments');
                                apartments.empty();

                                $.each(params.apartments, function(key, value) {
                                    apartments.append($('<option></option>').attr('value', key).text(value));
                                });

                                apartments.multiselect('refresh');
                            },
                        });
                    }
                },
            },
            'input-apartments': {},
        };
    @show
    </script>
</div>
@endsection
