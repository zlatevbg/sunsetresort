@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($contract))
    {!! Form::model($contract, ['method' => 'put', 'url' => \Locales::route('contracts/update'), 'id' => 'edit-contract-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('contracts/save'), 'id' => 'add-contract-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    {!! Form::hidden('apartment', $apartment, ['id' => 'input-apartment']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    @if (!isset($contract))
    <div class="form-group">
        {!! Form::label('input-rental_contract_id', trans(\Locales::getNamespace() . '/forms.rentalContractLabel')) !!}
        {!! Form::multiselect('rental_contract_id', $multiselect['contracts'], ['id' => 'input-rental_contract_id', 'class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-mm_for_year', trans(\Locales::getNamespace() . '/forms.mmFeesForYearLabel')) !!}
        {!! Form::select('mm_for_year', [], null, ['id' => 'input-mm_for_year', 'class' => 'form-control', 'disabled']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-is_exception', trans(\Locales::getNamespace() . '/forms.isExceptionLabel')) !!}
        {!! Form::select('is_exception', $exceptions, 0, ['id' => 'input-is_exception', 'class' => 'form-control']) !!}
    </div>
    @endif

    <div class="form-group">
        {!! Form::label('input-duration', trans(\Locales::getNamespace() . '/forms.durationLabel')) !!}
        {!! Form::multiselect('duration', $multiselect['duration'], ['id' => 'input-duration', 'class' => 'form-control'] + (isset($contract) ? [] : ['disabled'])) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-signed_at', trans(\Locales::getNamespace() . '/forms.signedAtLabel')) !!}
        {!! Form::text('signed_at', null, ['id' => 'input-signed_at', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.signedAtPlaceholder')]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-comments', trans(\Locales::getNamespace() . '/forms.commentsLabel')) !!}
        {!! Form::textarea('comments', null, ['id' => 'input-comments', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.commentsPlaceholder')]) !!}
    </div>

    @if (isset($contract))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}

    <script>
    @section('script')
        var mm_for_year = $('#input-mm_for_year');
        var duration = $('#input-duration');

        unikat.multiselect = {
            @if (!isset($contract))
            'input-rental_contract_id': {
                multiple: false,
                open: function() {
                    unikat.multiselect.default_contract_id = $($(this).multiselect('getChecked')).val();
                },
                close: function() {
                    if (unikat.multiselect.default_contract_id != $($(this).multiselect('getChecked')).val()) {
                        duration.empty();

                        var minDuration = $(this).find(':selected').data('minDuration');
                        var maxDuration = $(this).find(':selected').data('maxDuration');
                        for (var i = minDuration; i <= maxDuration; i++) {
                            duration.append($('<option></option>').attr('value', i).text(i));
                        }

                        if (parseInt($(this).val())) {
                            duration.multiselect('enable');

                            mm_for_year.prop('disabled', false).empty();
                            var year = $(this).find(':selected').data('startYear');
                            mm_for_year.append($('<option></option>').attr('value', year).text(year));
                            mm_for_year.append($('<option></option>').attr('value', year + 1).text(year + 1));
                        } else {
                            duration.multiselect('disable');
                            mm_for_year.prop('disabled', true).empty();
                        }

                        duration.multiselect('refresh');
                    }
                },
            },
            @endif
            'input-duration': {
                multiple: false,
            }
        };

        $.datepicker.regional.{{ \Locales::getCurrent() }} = unikat.variables.datepicker.{{ \Locales::getCurrent() }};
        $.datepicker.setDefaults($.datepicker.regional.{{ \Locales::getCurrent() }});

        $('#input-signed_at').datepicker({
            changeYear: true,
            changeMonth: true,
        });
    @show
    </script>
</div>
@endsection
