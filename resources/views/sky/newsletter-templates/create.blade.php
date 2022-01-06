@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($template))
    {!! Form::model($template, ['method' => 'put', 'url' => \Locales::route('newsletter-templates/update'), 'id' => 'edit-template-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('newsletter-templates/store'), 'id' => 'create-template-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    <div class="form-group">
        {!! Form::label('input-template', trans(\Locales::getNamespace() . '/forms.templateLabel')) !!}
        {!! Form::select('template', $templates, null, ['id' => 'input-template', 'class' => 'form-control']) !!}
    </div>

    <p class="text-center form-control">{{ trans(\Locales::getNamespace() . '/forms.sectionNewsletterFilters') }}</p>

    <div class="clearfix">
        <div class="form-group-3-left">
            <div class="form-group ajax-lock">
                {!! Form::label('input-projects', trans(\Locales::getNamespace() . '/forms.projectLabel')) !!}
                {!! Form::multiselect('projects[]', $multiselect['projects'], ['id' => 'input-projects', 'class' => 'form-control', 'multiple' => 'multiple', 'data-ajax-queue' => 'sync', 'data-href' => \Locales::route('newsletters/get-buildings')]) !!}
            </div>
        </div>
        <div class="form-group-3-left">
            <div class="form-group ajax-lock">
                {!! Form::label('input-buildings', trans(\Locales::getNamespace() . '/forms.buildingLabel')) !!}
                {!! Form::multiselect('buildings[]', $multiselect['buildings'], ['id' => 'input-buildings', 'class' => 'form-control', 'multiple' => 'multiple', 'data-ajax-queue' => 'sync', 'data-href' => \Locales::route('newsletters/get-floors')] + ((isset($template) && count($multiselect['buildings']['options']) > 1) ? [] : ['disabled'])) !!}
            </div>
        </div>
        <div class="form-group-3-right">
            <div class="form-group ajax-lock">
                {!! Form::label('input-floors', trans(\Locales::getNamespace() . '/forms.floorLabel')) !!}
                {!! Form::multiselect('floors[]', $multiselect['floors'], ['id' => 'input-floors', 'class' => 'form-control', 'multiple' => 'multiple', 'data-ajax-queue' => 'sync', 'data-href' => \Locales::route('newsletters/get-apartments')] + ((isset($template) && count($multiselect['floors']['options']) > 1) ? [] : ['disabled'])) !!}
            </div>
        </div>
    </div>

    <div class="clearfix">
        <div class="form-group-3-left">
            <div class="form-group ajax-lock">
                {!! Form::label('input-rooms', trans(\Locales::getNamespace() . '/forms.roomTypeLabel')) !!}
                {!! Form::multiselect('rooms[]', $multiselect['rooms'], ['id' => 'input-rooms', 'class' => 'form-control', 'multiple' => 'multiple', 'data-ajax-queue' => 'sync', 'data-href' => \Locales::route('newsletters/get-apartments')]) !!}
            </div>
        </div>
        <div class="form-group-3-left">
            <div class="form-group ajax-lock">
                {!! Form::label('input-furniture', trans(\Locales::getNamespace() . '/forms.furnitureLabel')) !!}
                {!! Form::multiselect('furniture[]', $multiselect['furniture'], ['id' => 'input-furniture', 'class' => 'form-control', 'multiple' => 'multiple', 'data-ajax-queue' => 'sync', 'data-href' => \Locales::route('newsletters/get-apartments')]) !!}
            </div>
        </div>
        <div class="form-group-3-right">
            <div class="form-group ajax-lock">
                {!! Form::label('input-views', trans(\Locales::getNamespace() . '/forms.viewLabel')) !!}
                {!! Form::multiselect('views[]', $multiselect['views'], ['id' => 'input-views', 'class' => 'form-control', 'multiple' => 'multiple', 'data-ajax-queue' => 'sync', 'data-href' => \Locales::route('newsletters/get-apartments')]) !!}
            </div>
        </div>
    </div>

    <div class="clearfix">
        <div class="form-group-left">
            <div class="form-group ajax-lock">
                {!! Form::label('input-apartments', trans(\Locales::getNamespace() . '/forms.apartmentsLabel')) !!}
                {!! Form::multiselect('apartments[]', $multiselect['apartments'], ['id' => 'input-apartments', 'class' => 'form-control', 'multiple' => 'multiple', 'data-ajax-queue' => 'sync', 'data-href' => \Locales::route('newsletters/get-owners')]) !!}
            </div>
        </div>
        <div class="form-group-right">
            <div class="form-group">
                {!! Form::label('input-owners', trans(\Locales::getNamespace() . '/forms.ownersLabel')) !!}
                {!! Form::multiselect('owners[]', $multiselect['owners'], ['id' => 'input-owners', 'class' => 'form-control', 'multiple' => 'multiple']) !!}
            </div>
        </div>
    </div>

    <div class="clearfix">
        <div class="form-group-left">
            <div class="form-group">
                {!! Form::label('input-countries', trans(\Locales::getNamespace() . '/forms.countriesLabel')) !!}
                {!! Form::multiselect('countries[]', $multiselect['countries'], ['id' => 'input-countries', 'class' => 'form-control', 'multiple' => 'multiple']) !!}
            </div>
        </div>
        <div class="form-group-right">
            <div class="form-group">
                {!! Form::label('input-locale_id', trans(\Locales::getNamespace() . '/forms.languageLabel')) !!}
                {!! Form::multiselect('locale_id', $multiselect['locales'], ['id' => 'input-locale_id', 'class' => 'form-control']) !!}
            </div>
        </div>
    </div>

    <p class="text-center form-control">{{ trans(\Locales::getNamespace() . '/forms.sectionNewsletterDetails') }}</p>

    <div class="form-group">
        {!! Form::label('input-recipients', trans(\Locales::getNamespace() . '/forms.recipientsLabel')) !!}
        {!! Form::multiselect('recipients[]', $multiselect['recipients'], ['id' => 'input-recipients', 'class' => 'form-control', 'multiple' => 'multiple']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-signature_id', trans(\Locales::getNamespace() . '/forms.signatureLabel')) !!}
        {!! Form::select('signature_id', $signatures, null, ['id' => 'input-signature_id', 'class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-subject', trans(\Locales::getNamespace() . '/forms.subjectLabel')) !!}
        {!! Form::text('subject', null, ['id' => 'input-subject', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.subjectPlaceholder')]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-teaser', trans(\Locales::getNamespace() . '/forms.teaserLabel')) !!}
        {!! Form::text('teaser', null, ['id' => 'input-teaser', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.teaserPlaceholder')]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-body', trans(\Locales::getNamespace() . '/forms.bodyLabel')) !!}
        {!! Form::textarea('body', null, ['id' => 'input-body', 'class' => 'form-control ckeditor', 'placeholder' => trans(\Locales::getNamespace() . '/forms.bodyPlaceholder')]) !!}
    </div>

    @if (isset($template))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}

    <script>
    @section('script')
        var selectedProjects = null;
        var selectedBuildings = null;
        var selectedFloors = null;
        var selectedRooms = null;
        var selectedFurniture = null;
        var selectedViews = null;
        var selectedApartments = null;
        var selectedOwners = null;

        $('#input-recipients').change(function(event) {
            if ($(this).val() && $.inArray('all', $(this).val()) != -1) {
                $(this).find('option').not(':first').prop('selected', false);
                $(this).multiselect('refresh');
                $(this).multiselect('close');
            }
        });

        unikat.multiselect = {
            'input-countries': {},
            'input-recipients': {},
            'input-locale_id': {
                multiple: false,
            },
            'input-owners': {},
            'input-projects': {
                close: function() {
                    selectedProjects = $(this).val();

                    unikat.ajaxify({
                        that: $(this).closest('.ajax-lock'),
                        method: 'get',
                        queue: $(this).data('ajaxQueue'),
                        action: $(this).data('href'),
                        data: { projects: selectedProjects },
                        skipErrors: true,
                        functionParams: ['buildings', 'floors', 'apartments', 'owners'],
                        function: function(params) {
                            reloadBuildings(params);
                            reloadFloors(params);
                            reloadApartments(params);
                            reloadOwners(params);
                        },
                    });
                },
            },
            'input-buildings': {
                close: function() {
                    selectedBuildings = $(this).val();

                    unikat.ajaxify({
                        that: $(this).closest('.ajax-lock'),
                        method: 'get',
                        queue: $(this).data('ajaxQueue'),
                        action: $(this).data('href'),
                        data: { projects: selectedProjects, buildings: selectedBuildings },
                        skipErrors: true,
                        functionParams: ['floors', 'apartments', 'owners'],
                        function: function(params) {
                            reloadFloors(params);
                            reloadApartments(params);
                            reloadOwners(params);
                        },
                    });
                },
            },
            'input-floors': {
                close: function() {
                    selectedFloors = $(this).val();

                    unikat.ajaxify({
                        that: $(this).closest('.ajax-lock'),
                        method: 'get',
                        queue: $(this).data('ajaxQueue'),
                        action: $(this).data('href'),
                        data: { projects: selectedProjects, buildings: selectedBuildings, floors: selectedFloors },
                        skipErrors: true,
                        functionParams: ['apartments', 'owners'],
                        function: function(params) {
                            reloadApartments(params);
                            reloadOwners(params);
                        },
                    });
                },
            },
            'input-apartments': {
                close: function() {
                    selectedApartments = $(this).val();

                    unikat.ajaxify({
                        that: $(this).closest('.ajax-lock'),
                        queue: $(this).data('ajaxQueue'),
                        action: $(this).data('href'),
                        data: { projects: selectedProjects, buildings: selectedBuildings, floors: selectedFloors, rooms: selectedRooms, furniture: selectedFurniture, views: selectedViews, apartments: selectedApartments },
                        skipErrors: true,
                        functionParams: ['owners'],
                        function: reloadOwners,
                    });
                },
            },
            'input-rooms': {
                close: function() {
                    selectedRooms = $(this).val();

                    unikat.ajaxify({
                        that: $(this).closest('.ajax-lock'),
                        method: 'get',
                        queue: $(this).data('ajaxQueue'),
                        action: $(this).data('href'),
                        data: { projects: selectedProjects, buildings: selectedBuildings, floors: selectedFloors, rooms: selectedRooms, furniture: selectedFurniture, views: selectedViews },
                        skipErrors: true,
                        functionParams: ['apartments', 'owners'],
                        function: function(params) {
                            reloadApartments(params);
                            reloadOwners(params);
                        },
                    });
                },
            },
            'input-furniture': {
                close: function() {
                    selectedFurniture = $(this).val();

                    unikat.ajaxify({
                        that: $(this).closest('.ajax-lock'),
                        method: 'get',
                        queue: $(this).data('ajaxQueue'),
                        action: $(this).data('href'),
                        data: { projects: selectedProjects, buildings: selectedBuildings, floors: selectedFloors, rooms: selectedRooms, furniture: selectedFurniture, views: selectedViews },
                        skipErrors: true,
                        functionParams: ['apartments', 'owners'],
                        function: function(params) {
                            reloadApartments(params);
                            reloadOwners(params);
                        },
                    });
                },
            },
            'input-views': {
                close: function() {
                    selectedViews = $(this).val();

                    unikat.ajaxify({
                        that: $(this).closest('.ajax-lock'),
                        method: 'get',
                        queue: $(this).data('ajaxQueue'),
                        action: $(this).data('href'),
                        data: { projects: selectedProjects, buildings: selectedBuildings, floors: selectedFloors, rooms: selectedRooms, furniture: selectedFurniture, views: selectedViews },
                        skipErrors: true,
                        functionParams: ['apartments', 'owners'],
                        function: function(params) {
                            reloadApartments(params);
                            reloadOwners(params);
                        },
                    });
                },
            },
        };

        function reloadBuildings(params) {
            var buildings = $('#input-buildings');
            selectedBuildings = buildings.val();
            buildings.empty();

            $.each(params.buildings, function(key, value) {
                buildings.append($('<option></option>').attr('value', value).text(key));
            });

            buildings.val(selectedBuildings);

            if (parseInt(selectedProjects)) {
                buildings.multiselect('enable');
            } else {
                buildings.multiselect('disable');
                selectedBuildings = null;
            }

            buildings.multiselect('refresh');
            selectedBuildings = buildings.val();
        }

        function reloadFloors(params) {
            var floors = $('#input-floors');
            selectedFloors = floors.val();
            floors.empty();

            $.each(params.floors, function(key, value) {
                floors.append($('<option></option>').attr('value', value).text(key));
            });

            floors.val(selectedFloors);

            if (parseInt(selectedBuildings)) {
                floors.multiselect('enable');
            } else {
                floors.multiselect('disable');
            }

            floors.multiselect('refresh');
            selectedFloors = floors.val();
        }

        function reloadApartments(params) {
            var apartments = $('#input-apartments');
            selectedApartments = apartments.val();
            apartments.empty();

            $.each(params.apartments, function(key, value) {
                apartments.append($('<option></option>').attr('value', value).text(key));
            });

            apartments.val(selectedApartments);

            apartments.multiselect('refresh');
            selectedApartments = apartments.val();
        }

        function reloadOwners(params) {
            var owners = $('#input-owners');
            selectedOwners = owners.val();
            owners.empty();

            $.each(params.owners, function(key, value) {
                owners.append($('<option></option>').attr('value', value).text(key));
            });

            owners.val(selectedOwners);

            owners.multiselect('refresh');
        }
    @show
    </script>
</div>
@endsection
