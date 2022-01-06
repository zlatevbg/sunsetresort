@inject('carbon', '\Carbon\Carbon')

@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($poolUsageContract))
    {!! Form::model($poolUsageContract, ['method' => 'put', 'url' => \Locales::route('pool-usage-contracts-tracker/update'), 'id' => 'edit-pool-usage-contract-tracker-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('pool-usage-contracts-tracker/store'), 'id' => 'create-pool-usage-contract-tracker-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    <div class="form-group ajax-lock">
        {!! Form::label('input-apartment_id', trans(\Locales::getNamespace() . '/forms.apartmentLabel')) !!}
        {!! Form::multiselect('apartment_id', $multiselect['apartments'], ['id' => 'input-apartment_id', 'class' => 'form-control', 'data-ajax-queue' => 'sync', 'data-href' => \Locales::route('pool-usage-contracts-tracker/get-owners')]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-owner_id', trans(\Locales::getNamespace() . '/forms.ownerLabel')) !!}
        {!! Form::multiselect('owner_id', $multiselect['owners'], ['id' => 'input-owner_id', 'class' => 'form-control'] + (isset($poolUsageContract) ? [] : ['disabled'])) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-year_id', trans(\Locales::getNamespace() . '/forms.yearLabel')) !!}
        {!! Form::multiselect('year_id', $multiselect['years'], ['id' => 'input-year_id', 'class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-comments', trans(\Locales::getNamespace() . '/forms.commentsLabel')) !!}
        {!! Form::textarea('comments', null, ['id' => 'input-comments', 'class' => 'form-control contract-year-comments', 'placeholder' => trans(\Locales::getNamespace() . '/forms.commentsPlaceholder')]) !!}
    </div>

    @if (isset($poolUsageContract))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}

    <script>
    @section('script')
        var selectedApartment = {!! isset($poolUsageContract) ? $poolUsageContract->apartment_id : 'null' !!};
        var selectedOwner = {!! isset($poolUsageContract) ? $poolUsageContract->owner_id : 'null' !!};
        var owner_id = $('#input-owner_id');

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
                            functionParams: ['owners'],
                            function: function(params) {
                                var selectedOwner = owner_id.val();
                                owner_id.empty();
                                $.each(params.owners, function(key, value) {
                                    owner_id.append($('<option></option>').attr('value', value).text(key));
                                });
                                owner_id.val(selectedOwner);

                                if (parseInt(selectedApartment)) {
                                    owner_id.multiselect('enable');
                                } else {
                                    owner_id.multiselect('disable');
                                }

                                owner_id.multiselect('refresh');
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
                    }
                },
            },
            'input-year_id': {
                multiple: false,
            },
        };
    @show
    </script>
</div>
@endsection
