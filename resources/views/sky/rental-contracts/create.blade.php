@inject('carbon', '\Carbon\Carbon')

@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($contract))
    {!! Form::model($contract, ['method' => 'put', 'url' => \Locales::route('rental-contracts/update'), 'id' => 'edit-rental-contract-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('rental-contracts/store'), 'id' => 'create-rental-contract-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    <div class="clearfix">
        <div class="form-group-left">
            <p class="text-center form-control">{{ trans(\Locales::getNamespace() . '/forms.sectionRentalContractDetails') }}</p>

            <div class="form-group">
                {!! Form::label('input-name', trans(\Locales::getNamespace() . '/forms.nameLabel')) !!}
                {!! Form::text('name', null, ['id' => 'input-name', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.namePlaceholder')]) !!}
            </div>

            @foreach (\Locales::getPublicTranslations() as $locale)
            <div class="form-group">
                {!! Form::label('input-name-' . $locale->locale, trans(\Locales::getNamespace() . '/forms.nameLabel')) !!}
                <small>({{ $locale->native }})</small>
                {!! Form::text('name_translations[' . $locale->locale . ']', (isset($contract) && $contract->hasTranslation($locale->locale)) ? $contract->translate($locale->locale)->name : null, ['id' => 'input-name-' . $locale->locale, 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.namePlaceholder') . ' - ' . $locale->name]) !!}
            </div>
            @endforeach

            <div class="form-group">
                {!! Form::label('input-benefits', trans(\Locales::getNamespace() . '/forms.benefitsLabel')) !!}
                {!! Form::text('benefits', null, ['id' => 'input-benefits', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.benefitsPlaceholder')]) !!}
            </div>

            @foreach (\Locales::getPublicTranslations() as $locale)
            <div class="form-group">
                {!! Form::label('input-benefits-' . $locale->locale, trans(\Locales::getNamespace() . '/forms.benefitsLabel')) !!}
                <small>({{ $locale->native }})</small>
                {!! Form::text('benefits_translations[' . $locale->locale . ']', (isset($contract) && $contract->hasTranslation($locale->locale)) ? $contract->translate($locale->locale)->benefits : null, ['id' => 'input-benefits-' . $locale->locale, 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.benefitsPlaceholder') . ' - ' . $locale->name]) !!}
            </div>
            @endforeach

            <div class="form-group">
                {!! Form::label('input-mm_covered', trans(\Locales::getNamespace() . '/forms.mmCoveredLabel')) !!}
                {!! Form::select('mm_covered', $mmCovered, null, ['id' => 'input-mm_covered', 'class' => 'form-control']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-deadline_at', trans(\Locales::getNamespace() . '/forms.deadlineAtLabel')) !!}
                {!! Form::text('deadline_at', null, ['id' => 'input-deadline_at', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.deadlineAtPlaceholder')]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-min_duration', trans(\Locales::getNamespace() . '/forms.minDurationLabel')) !!}
                {!! Form::number('min_duration', null, ['id' => 'input-min_duration', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.minDurationPlaceholder')]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-max_duration', trans(\Locales::getNamespace() . '/forms.maxDurationLabel')) !!}
                {!! Form::number('max_duration', null, ['id' => 'input-max_duration', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.maxDurationPlaceholder')]) !!}
            </div>
        </div>
        <div class="form-group-right">
            <p class="text-center form-control">{{ trans(\Locales::getNamespace() . '/forms.sectionRentalContractPeriod') }}</p>

            <div class="clearfix">
                <div class="form-group-left">
                    <div class="form-group">
                        {!! Form::label('input-contract_dfrom1', trans(\Locales::getNamespace() . '/forms.dfrom1Label')) !!}
                        {!! Form::text('contract_dfrom1', null, ['id' => 'input-contract_dfrom1', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.dfrom1Placeholder')]) !!}
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
                <p class="text-center form-control">{{ trans(\Locales::getNamespace() . '/forms.sectionRentalPayments') }}</p>

                <div class="form-group">
                    {!! Form::label('input-rental_payment_id', trans(\Locales::getNamespace() . '/forms.rentalPaymentLabel')) !!}
                    {!! Form::select('rental_payment_id', $rentalPayments, null, ['id' => 'input-rental_payment_id', 'class' => 'form-control']) !!}
                </div>
            </div>
        </div>
    </div>

    @if (isset($contract))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}

    <script>
    @section('script')
        $.datepicker.regional.{{ \Locales::getCurrent() }} = unikat.variables.datepicker.{{ \Locales::getCurrent() }};
        $.datepicker.setDefaults($.datepicker.regional.{{ \Locales::getCurrent() }});

        $('#input-deadline_at').datepicker({
            changeYear: true,
            changeMonth: true,
        });

        var contract_dfrom1_selected = null;
        var contract_dto1_selected = null;
        var contract_dfrom2_selected = null;
        var contract_dto2_selected = null;
        var personal_dfrom1_selected = null;
        var personal_dto1_selected = null;
        var personal_dfrom2_selected = null;

        var minDate = '1.1.2007';
        var maxDate = '{{ $carbon->now()->endOfYear()->addYear()->format('d.m.Y') }}';

        @if (isset($contract))
            @if ($contract->contract_dfrom1)$('#input-personal_dfrom1').removeAttr('disabled');@endif
            @if ($contract->contract_dto1)
                $('#input-contract_dto1').removeAttr('disabled');
                $('#input-contract_dfrom2').removeAttr('disabled');
            @endif
            @if ($contract->contract_dfrom2)$('#input-contract_dfrom2').removeAttr('disabled');@endif
            @if ($contract->contract_dto2)$('#input-contract_dto2').removeAttr('disabled');@endif
            @if ($contract->personal_dfrom1)$('#input-personal_dfrom1').removeAttr('disabled');@endif
            @if ($contract->personal_dto1)
                $('#input-personal_dto1').removeAttr('disabled');
                $('#input-personal_dfrom2').removeAttr('disabled');
            @endif
            @if ($contract->personal_dfrom2)$('#input-personal_dfrom2').removeAttr('disabled');@endif
            @if ($contract->personal_dto2)$('#input-personal_dto2').removeAttr('disabled');@endif

            @if ($contract->contract_dfrom1)contract_dfrom1_selected = new Date('{{ $carbon->parse($contract->contract_dfrom1) }}');@endif
            @if ($contract->contract_dto1)contract_dto1_selected = new Date('{{ $carbon->parse($contract->contract_dto1) }}');@endif
            @if ($contract->contract_dfrom2)contract_dfrom2_selected = new Date('{{ $carbon->parse($contract->contract_dfrom2) }}');@endif
            @if ($contract->contract_dto2)contract_dto2_selected = new Date('{{ $carbon->parse($contract->contract_dto2) }}');@endif
            @if ($contract->personal_dfrom1)personal_dfrom1_selected = new Date('{{ $carbon->parse($contract->personal_dfrom1) }}');@endif
            @if ($contract->personal_dto1)personal_dto1_selected = new Date('{{ $carbon->parse($contract->personal_dto1) }}');@endif
            @if ($contract->personal_dfrom2)personal_dfrom2_selected = new Date('{{ $carbon->parse($contract->personal_dfrom2) }}');@endif
        @endif

        $('#input-contract_dfrom1').datepicker({
            onSelect: function(date) {
                $('#input-contract_dto1, #input-contract_dfrom2, #input-contract_dto2, #input-personal_dfrom1, #input-personal_dto1, #input-personal_dfrom2, #input-personal_dto2').val('').attr('disabled', 'disabled');

                contract_dfrom1_selected = new Date(Date.parse($('#input-contract_dfrom1').datepicker('getDate')));

                $('#input-personal_dfrom1').datepicker('option', 'minDate', contract_dfrom1_selected);
                $('#input-personal_dfrom2').datepicker('option', 'minDate', contract_dfrom1_selected);
                $('#input-contract_dto1').datepicker('option', 'minDate', contract_dfrom1_selected);
                $('#input-contract_dto1').removeAttr('disabled');
            }
        });

        $('#input-contract_dto1').datepicker({
            onSelect: function(date) {
                $('#input-contract_dfrom2, #input-contract_dto2, #input-personal_dfrom1, #input-personal_dto1, #input-personal_dfrom2, #input-personal_dto2').val('').attr('disabled', 'disabled');

                contract_dto1_selected = new Date(Date.parse($('#input-contract_dto1').datepicker('getDate')));

                $('#input-personal_dfrom1').datepicker('option', 'maxDate', contract_dto1_selected);
                $('#input-personal_dfrom2').datepicker('option', 'maxDate', contract_dto1_selected);
                $('#input-personal_dto1').datepicker('option', 'maxDate', contract_dto1_selected);
                $('#input-personal_dto2').datepicker('option', 'maxDate', contract_dto1_selected);
                $('#input-personal_dfrom1').removeAttr('disabled');
                $('#input-contract_dfrom2').removeAttr('disabled');
            }
        });

        $('#input-contract_dfrom2').datepicker({
            onSelect: function(date) {
                contract_dfrom2_selected = new Date(Date.parse($('#input-contract_dfrom2').datepicker('getDate')));

                var minDate = (contract_dfrom1_selected > contract_dfrom2_selected) ? contract_dfrom2_selected : contract_dfrom1_selected;

                $('#input-personal_dfrom1').datepicker('option', 'minDate', minDate);
                $('#input-personal_dfrom2').datepicker('option', 'minDate', minDate);
                $('#input-contract_dto2').datepicker('option', 'minDate', contract_dfrom2_selected);
                if (contract_dfrom2_selected < contract_dfrom1_selected) {
                    $('#input-contract_dto2').datepicker('option', 'maxDate', new Date(contract_dfrom1_selected.getFullYear(), contract_dfrom1_selected.getMonth(), contract_dfrom1_selected.getDate() - 1));
                } else {
                    $('#input-contract_dto2').datepicker('option', 'maxDate', maxDate);
                }
                $('#input-contract_dto2').val('').removeAttr('disabled');
            },
            beforeShowDay: beforeContract,
        });

        $('#input-contract_dto2').datepicker({
            onSelect: function(date) {
                contract_dto2_selected = new Date(Date.parse($('#input-contract_dto2').datepicker('getDate')));

                var maxDate = (contract_dto1_selected > contract_dto2_selected) ? contract_dto1_selected : contract_dto2_selected;
                $('#input-personal_dfrom1').datepicker('option', 'maxDate', maxDate);
                $('#input-personal_dto1').datepicker('option', 'maxDate', maxDate);
                $('#input-personal_dfrom2').datepicker('option', 'maxDate', maxDate);
                $('#input-personal_dto2').datepicker('option', 'maxDate', maxDate);
            },
            beforeShowDay: beforeContract,
        });

        $('#input-personal_dfrom1').datepicker({
            onSelect: function(date) {
                $('#input-personal_dto1, #input-personal_dfrom2, #input-personal_dto2').val('').attr('disabled', 'disabled');

                personal_dfrom1_selected = new Date(Date.parse($('#input-personal_dfrom1').datepicker('getDate')));

                var maxDate;
                if (personal_dfrom1_selected >= contract_dfrom1_selected && personal_dfrom1_selected <= contract_dto1_selected) {
                    maxDate = contract_dto1_selected;
                } else { // if (personal_dfrom1_selected >= contract_dfrom2_selected && personal_dfrom1_selected <= contract_dto2_selected)
                    maxDate = contract_dto2_selected;
                }

                $('#input-personal_dto1').datepicker('option', 'minDate', personal_dfrom1_selected);
                $('#input-personal_dto1').datepicker('option', 'maxDate', maxDate);
                $('#input-personal_dto1').removeAttr('disabled');
            },
            beforeShowDay: beforePersonal1,
        });

        $('#input-personal_dto1').datepicker({
            onSelect: function(date) {
                $('#input-personal_dfrom2, #input-personal_dto2').val('').attr('disabled', 'disabled');

                personal_dto1_selected = new Date(Date.parse($('#input-personal_dto1').datepicker('getDate')));

                $('#input-personal_dfrom2').removeAttr('disabled');
            },
            beforeShowDay: beforePersonal1,
        });

        $('#input-personal_dfrom2').datepicker({
            onSelect: function(date) {
                personal_dfrom2_selected = new Date(Date.parse($('#input-personal_dfrom2').datepicker('getDate')));

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

                $('#input-personal_dto2').datepicker('option', 'minDate', personal_dfrom2_selected);
                $('#input-personal_dto2').datepicker('option', 'maxDate', maxDate);
                $('#input-personal_dto2').val('').removeAttr('disabled');
            },
            beforeShowDay: beforePersonal2,
        });

        $('#input-personal_dto2').datepicker({
            beforeShowDay: beforePersonal2,
        });

        $('#input-contract_dfrom1').datepicker('option', 'minDate', minDate);
        $('#input-contract_dfrom1').datepicker('option', 'maxDate', maxDate);
        $('#input-contract_dto1').datepicker('option', 'minDate', minDate);
        // $('#input-contract_dto1').datepicker('option', 'maxDate', maxDate);

        $('#input-contract_dfrom2').datepicker('option', 'minDate', minDate);
        $('#input-contract_dfrom2').datepicker('option', 'maxDate', maxDate);
        $('#input-contract_dto2').datepicker('option', 'minDate', minDate);
        // $('#input-contract_dto2').datepicker('option', 'maxDate', maxDate);

        $('#input-personal_dfrom1').datepicker('option', 'minDate', minDate);
        $('#input-personal_dfrom1').datepicker('option', 'maxDate', maxDate);
        $('#input-personal_dto1').datepicker('option', 'minDate', minDate);
        // $('#input-personal_dto1').datepicker('option', 'maxDate', maxDate);

        $('#input-personal_dfrom2').datepicker('option', 'minDate', minDate);
        $('#input-personal_dfrom2').datepicker('option', 'maxDate', maxDate);
        $('#input-personal_dto2').datepicker('option', 'minDate', minDate);
        // $('#input-personal_dto2').datepicker('option', 'maxDate', maxDate);

        @if (isset($contract))
            @if ($contract->contract_dto1)$('#input-contract_dto1').datepicker('option', 'minDate', '{{ $contract->contract_dfrom1 }}');@endif
            @if ($contract->contract_dto2)$('#input-contract_dto2').datepicker('option', 'minDate', '{{ $contract->contract_dfrom2 }}');@endif
            @if ($contract->contract_dto2 && ($contract->contract_dfrom1 > $contract->contract_dfrom2))$('#input-contract_dto2').datepicker('option', 'maxDate', '{{ $contract->contract_dfrom1 }}');@endif
            $('#input-personal_dfrom1').datepicker('option', 'minDate', '{{ min(array_filter([$contract->contract_dfrom1, $contract->contract_dfrom2])) }}');
            $('#input-personal_dfrom1').datepicker('option', 'maxDate', '{{ max(array_filter([$contract->contract_dto1, $contract->contract_dto2])) }}');
            $('#input-personal_dfrom2').datepicker('option', 'minDate', '{{ min(array_filter([$contract->contract_dfrom1, $contract->contract_dfrom2])) }}');
            $('#input-personal_dfrom2').datepicker('option', 'maxDate', '{{ max(array_filter([$contract->contract_dto1, $contract->contract_dto2])) }}');
            @if ($contract->personal_dto1)$('#input-personal_dto1').datepicker('option', 'minDate', '{{ $contract->personal_dfrom1 }}');@endif
            @if ($contract->personal_dto1)$('#input-personal_dto1').datepicker('option', 'maxDate', '{{ max(array_filter([$contract->contract_dto1, $contract->contract_dto2])) }}');@endif
            @if ($contract->personal_dto2)$('#input-personal_dto2').datepicker('option', 'minDate', '{{ $contract->personal_dfrom2 }}');@endif

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
            @if ($contract->personal_dto2)$('#input-personal_dto2').datepicker('option', 'maxDate', max);@endif
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
    @show
    </script>
</div>
@endsection
