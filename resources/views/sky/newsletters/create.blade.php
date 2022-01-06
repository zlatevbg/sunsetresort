@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($newsletter))
    {!! Form::model($newsletter, ['method' => 'put', 'url' => \Locales::route('newsletters/update'), 'id' => 'edit-newsletter-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('newsletters/store'), 'id' => 'create-newsletter-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    @if (!isset($newsletter))
    <div class="form-group ajax-lock">
        {!! Form::label('input-template', trans(\Locales::getNamespace() . '/forms.templateLabel')) !!}
        {!! Form::select('template', $templates, null, ['id' => 'input-template', 'class' => 'form-control', 'disabled', 'data-ajax-queue' => 'sync', 'data-href' => \Locales::route('newsletters/get-template')]) !!}
    </div>
    @endif

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
                {!! Form::multiselect('buildings[]', $multiselect['buildings'], ['id' => 'input-buildings', 'class' => 'form-control', 'multiple' => 'multiple', 'data-ajax-queue' => 'sync', 'data-href' => \Locales::route('newsletters/get-floors')] + ((isset($newsletter) && count($multiselect['buildings']['options']) > 1) ? [] : ['disabled'])) !!}
            </div>
        </div>
        <div class="form-group-3-right">
            <div class="form-group ajax-lock">
                {!! Form::label('input-floors', trans(\Locales::getNamespace() . '/forms.floorLabel')) !!}
                {!! Form::multiselect('floors[]', $multiselect['floors'], ['id' => 'input-floors', 'class' => 'form-control', 'multiple' => 'multiple', 'data-ajax-queue' => 'sync', 'data-href' => \Locales::route('newsletters/get-apartments')] + ((isset($newsletter) && count($multiselect['floors']['options']) > 1) ? [] : ['disabled'])) !!}
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
        <div class="form-group-3-left">
            <div class="form-group ajax-lock">
                {!! Form::label('input-apartments', trans(\Locales::getNamespace() . '/forms.apartmentsLabel')) !!}
                {!! Form::multiselect('apartments[]', $multiselect['apartments'], ['id' => 'input-apartments', 'class' => 'form-control', 'multiple' => 'multiple', 'data-ajax-queue' => 'sync', 'data-href' => \Locales::route('newsletters/get-owners')]) !!}
            </div>
        </div>
        <div class="form-group-3-left">
            <div class="form-group">
                {!! Form::label('input-owners', trans(\Locales::getNamespace() . '/forms.ownersLabel')) !!}
                {!! Form::multiselect('owners[]', $multiselect['owners'], ['id' => 'input-owners', 'class' => 'form-control', 'multiple' => 'multiple']) !!}
            </div>
        </div>
        <div class="form-group-3-right">
            <div class="form-group">
                {!! Form::label('input-recipients', trans(\Locales::getNamespace() . '/forms.recipientsLabel')) !!}
                {!! Form::multiselect('recipients[]', $multiselect['recipients'], ['id' => 'input-recipients', 'class' => 'form-control', 'multiple' => 'multiple']) !!}
            </div>
        </div>
    </div>

    <div class="clearfix">
        <div class="form-group-3-left">
            <div class="form-group">
                {!! Form::label('input-countries', trans(\Locales::getNamespace() . '/forms.countriesLabel')) !!}
                {!! Form::multiselect('countries[]', $multiselect['countries'], ['id' => 'input-countries', 'class' => 'form-control', 'multiple' => 'multiple']) !!}
            </div>
        </div>
        <div class="form-group-3-left">
            <div class="form-group">
                {!! Form::label('input-locale_id', trans(\Locales::getNamespace() . '/forms.languageLabel')) !!}
                {!! Form::multiselect('locale_id', $multiselect['locales'], ['id' => 'input-locale_id', 'class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group-3-right">
            <div class="form-group">
                {!! Form::label('input-year_id', trans(\Locales::getNamespace() . '/forms.yearLabel')) !!}
                {!! Form::multiselect('year_id', $multiselect['years'], ['id' => 'input-year_id', 'class' => 'form-control']) !!}
            </div>
        </div>
    </div>

    <p class="text-center form-control">{{ trans(\Locales::getNamespace() . '/forms.sectionNewsletterMerge') }}</p>

    <div class="form-group">
        {!! Form::label('input-merge-by', trans(\Locales::getNamespace() . '/forms.mergeByOption'), ['class' => 'center-block']) !!}
        {!! Form::radioInline('merge_by', 0, 1, ['id' => 'input-merge-none'], trans(\Locales::getNamespace() . '/forms.newsletterMergeBy.0'), ['class' => 'radio-inline']) !!}
        {!! Form::radioInline('merge_by', 1, null, ['id' => 'input-merge-apartments'], trans(\Locales::getNamespace() . '/forms.newsletterMergeBy.1'), ['class' => 'radio-inline']) !!}
        {!! Form::radioInline('merge_by', 2, null, ['id' => 'input-merge-owners'], trans(\Locales::getNamespace() . '/forms.newsletterMergeBy.2'), ['class' => 'radio-inline']) !!}
    </div>

    <div id="merge-fields" class="{{ (isset($newsletter) && $newsletter->merge_by !== null) ? '' : 'hidden' }}">
        <div class="btn-group-wrapper text-center">
            <div class="btn-group">
                <a class="btn btn-success js-add-merge">
                    <span class="glyphicon glyphicon-plus"></span>
                    {{ trans(\Locales::getNamespace() . '/forms.addMergeButton') }}
                </a>
            </div>
        </div>

        <p class="text-danger text-center">{{ trans(\Locales::getNamespace() . '/forms.mergeSeparator') }}</p>

        @if (isset($newsletter))
            @foreach($newsletter->merge as $key => $value)
            <div class="form-group">
                {!! Form::label('input-merge-' . $value->order, trans(\Locales::getNamespace() . '/forms.mergeFieldLabel') . ' ' . $value->order) !!}
                <div class="input-group">
                    {!! Form::text('merge[]', $value->merge, ['id' => 'input-merge-' . $value->order, 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.mergeFieldPlaceholder')]) !!}
                    <span class="input-group-btn">
                        <button data-num="{{ $value->order }}" class="btn btn-danger js-remove-merge" type="button">
                            <span class="glyphicon glyphicon-remove"></span>
                        </button>
                    </span>
                </div>
            </div>
            @endforeach
        @endif
    </div>

    <p class="text-center form-control">{{ trans(\Locales::getNamespace() . '/forms.sectionNewsletterDetails') }}</p>

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

    @if (isset($newsletter))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}

    <script>
    @section('script')
        var selectedLocale = null;
        var selectedProjects = null;
        var selectedBuildings = null;
        var selectedFloors = null;
        var selectedRooms = null;
        var selectedFurniture = null;
        var selectedViews = null;
        var selectedApartments = null;
        var selectedOwners = null;

        @if (!isset($newsletter))
        $('#input-template').change(function(event) {
            if ($(this).val()) {
                unikat.ajaxify({
                    that: $(this).closest('.ajax-lock'),
                    method: 'get',
                    queue: $(this).data('ajaxQueue'),
                    action: $(this).data('href'),
                    data: { template: $(this).val(), locale: selectedLocale },
                    skipErrors: true,
                    functionParams: ['template', 'buildings', 'floors', 'apartments', 'owners'],
                    function: function(params) {
                        if (params.template) {
                            if (params.template.projects) {
                                selectedProjects = params.template.projects.split(',');
                                $('#input-projects').val(selectedProjects).multiselect('refresh');
                            }

                            if (params.buildings) {
                                reloadBuildings(params);
                            }

                            if (params.template.buildings) {
                                selectedBuildings = params.template.buildings.split(',');
                                $('#input-buildings').val(selectedBuildings).multiselect('refresh');
                            }

                            if (params.floors) {
                                reloadFloors(params);
                            }

                            if (params.template.floors) {
                                selectedFloors = params.template.floors.split(',');
                                $('#input-floors').val(selectedFloors).multiselect('refresh');
                            }

                            if (params.template.rooms) {
                                selectedRooms = params.template.rooms.split(',');
                                $('#input-rooms').val(selectedRooms).multiselect('refresh');
                            }

                            if (params.template.furniture) {
                                selectedFurniture = params.template.furniture.split(',');
                                $('#input-furniture').val(selectedFurniture).multiselect('refresh');
                            }

                            if (params.template.views) {
                                selectedViews = params.template.views.split(',');
                                $('#input-views').val(selectedViews).multiselect('refresh');
                            }

                            if (params.apartments) {
                                reloadApartments(params);
                            }

                            if (params.template.apartments) {
                                selectedApartments = params.template.apartments.split(',');
                                $('#input-apartments').val(selectedApartments).multiselect('refresh');
                            }

                            if (params.owners) {
                                reloadOwners(params);
                            }

                            if (params.template.owners) {
                                selectedOwners = params.template.owners.split(',');
                                $('#input-owners').val(selectedOwners).multiselect('refresh');
                            }

                            if (params.template.countries) {
                                $('#input-countries').val(params.template.countries.split(',')).multiselect('refresh');
                            }

                            if (params.template.recipients) {
                                $('#input-recipients').val(params.template.recipients.split(',')).multiselect('refresh');
                            }

                            $('#input-signature_id').val(params.template.signature_id);

                            $('#input-subject').val(params.template.subject);
                            $('#input-teaser').val(params.template.teaser);
                            CKEDITOR.instances['input-body'].setData(params.template.body);
                        }
                    },
                });
            }
        });
        @endif

        @if (isset($newsletter))
        unikat.merge = {{ $newsletter->merge->count() }};
        @endif

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
                close: function() {
                    selectedLocale = $(this).val();

                    if (selectedLocale > 0) {
                        $('#input-template').removeAttr("disabled");
                    } else {
                        $('#input-template').attr("disabled", "disabled")
                    }
                },
            },
            'input-year_id': {
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
