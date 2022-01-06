@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($apartment))
    {!! Form::model($apartment, ['method' => 'put', 'url' => \Locales::route('apartments/update'), 'id' => 'edit-apartment-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('apartments/store'), 'id' => 'create-apartment-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}
    {!! Form::hidden('project', $project, ['id' => 'input-project']) !!}
    {!! Form::hidden('building', $building, ['id' => 'input-building']) !!}
    {!! Form::hidden('floor', $floor, ['id' => 'input-floor']) !!}

    <div class="clearfix">
        <div class="form-group-left">
            <div class="form-group ajax-lock">
                {!! Form::label('input-project_id', trans(\Locales::getNamespace() . '/forms.projectLabel')) !!}
                {!! Form::multiselect('project_id', $multiselect['projects'], ['id' => 'input-project_id', 'class' => 'form-control', 'data-ajax-queue' => 'sync', 'data-href' => \Locales::route('apartments/get-buildings')] + ((isset($apartment) || (!$project && count($multiselect['projects']['options']) > 1)) ? [] : ['disabled'])) !!}
            </div>

            <div class="form-group ajax-lock">
                {!! Form::label('input-building_id', trans(\Locales::getNamespace() . '/forms.buildingLabel')) !!}
                {!! Form::multiselect('building_id', $multiselect['buildings'], ['id' => 'input-building_id', 'class' => 'form-control', 'data-ajax-queue' => 'sync', 'data-href' => \Locales::route('apartments/get-floors')] + ((isset($apartment) || (!$building && count($multiselect['buildings']['options']) > 1)) ? [] : ['disabled'])) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-floor_id', trans(\Locales::getNamespace() . '/forms.floorLabel')) !!}
                {!! Form::multiselect('floor_id', $multiselect['floors'], ['id' => 'input-floor_id', 'class' => 'form-control'] + ((isset($apartment) || (!$floor && count($multiselect['floors']['options']) > 1)) ? [] : ['disabled'])) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-room_id', trans(\Locales::getNamespace() . '/forms.roomTypeLabel')) !!}
                {!! Form::select('room_id', $rooms, null, ['id' => 'input-room_id', 'class' => 'form-control']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-furniture_id', trans(\Locales::getNamespace() . '/forms.furnitureLabel')) !!}
                {!! Form::select('furniture_id', $furniture, null, ['id' => 'input-furniture_id', 'class' => 'form-control']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-view_id', trans(\Locales::getNamespace() . '/forms.viewLabel')) !!}
                {!! Form::select('view_id', $views, null, ['id' => 'input-view_id', 'class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group-right">
            <div class="form-group">
                {!! Form::label('input-number', trans(\Locales::getNamespace() . '/forms.numberLabel')) !!}
                {!! Form::text('number', null, ['id' => 'input-number', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.numberPlaceholder')]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-apartment_area', trans(\Locales::getNamespace() . '/forms.apartmentAreaLabel')) !!}
                {!! Form::text('apartment_area', null, ['id' => 'input-apartment_area', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.apartmentAreaPlaceholder')]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-balcony_area', trans(\Locales::getNamespace() . '/forms.balconyAreaLabel')) !!}
                {!! Form::text('balcony_area', null, ['id' => 'input-balcony_area', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.balconyAreaPlaceholder')]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-extra_balcony_area', trans(\Locales::getNamespace() . '/forms.extraBalconyAreaLabel')) !!}
                {!! Form::text('extra_balcony_area', null, ['id' => 'input-extra_balcony_area', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.extraBalconyAreaPlaceholder')]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-common_area', trans(\Locales::getNamespace() . '/forms.commonAreaLabel')) !!}
                {!! Form::text('common_area', null, ['id' => 'input-common_area', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.commonAreaPlaceholder')]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-total_area', trans(\Locales::getNamespace() . '/forms.totalAreaLabel')) !!}
                {!! Form::text('total_area', null, ['id' => 'input-total_area', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.totalAreaPlaceholder')]) !!}
            </div>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('input-mm_tax_formula', trans(\Locales::getNamespace() . '/forms.mmTaxFormulaLabel')) !!}
        {!! Form::select('mm_tax_formula', $mmTaxFormula, null, ['id' => 'input-mm_tax_formula', 'class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-comments', trans(\Locales::getNamespace() . '/forms.commentsLabel')) !!}
        {!! Form::textarea('comments', null, ['id' => 'input-comments', 'class' => 'form-control apartments-comments', 'placeholder' => trans(\Locales::getNamespace() . '/forms.commentsPlaceholder')]) !!}
    </div>

    @if (isset($apartment))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}

    <script>
    @section('script')
        unikat.multiselect = {
            'input-project_id': {
                multiple: false,
                open: function() {
                    unikat.multiselect.default_project_id = $($(this).multiselect('getChecked')).val();
                },
                close: function() {
                    if (unikat.multiselect.default_project_id != $($(this).multiselect('getChecked')).val()) {
                        unikat.ajaxify({
                            that: $(this).closest('.ajax-lock'),
                            method: 'get',
                            queue: $(this).data('ajaxQueue'),
                            action: $(this).data('href') + ($(this).val() ? '/' + $(this).val() : ''),
                            skipErrors: true,
                            functionParams: ['buildings', 'project'],
                            function: function(params) {
                                var buildings = $('#input-building_id');
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

                                var floors = $('#input-floor_id');
                                floors.empty();
                                floors.append($('<option></option>').attr('value', 0).text('{{ trans(\Locales::getNamespace() . '/forms.selectOption') }}'));
                                floors.multiselect('disable');
                                floors.multiselect('refresh');
                            },
                        });
                    }
                },
            },
            'input-building_id': {
                multiple: false,
                open: function() {
                    unikat.multiselect.default_building_id = $($(this).multiselect('getChecked')).val();
                },
                close: function() {
                    if (unikat.multiselect.default_building_id != $($(this).multiselect('getChecked')).val()) {
                        unikat.ajaxify({
                            that: $(this).closest('.ajax-lock'),
                            method: 'get',
                            queue: $(this).data('ajaxQueue'),
                            action: $(this).data('href') + ($(this).val() ? '/' + $(this).val() : ''),
                            skipErrors: true,
                            functionParams: ['floors', 'building'],
                            function: function(params) {
                                var floors = $('#input-floor_id');
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
                            },
                        });
                    }
                },
            },
            'input-floor_id': {
                multiple: false,
            }
        };
    @show
    </script>
</div>
@endsection
