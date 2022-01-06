@inject('carbon', '\Carbon\Carbon')

@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    {!! Form::model($year, ['method' => 'put', 'url' => \Locales::route('contract-years/update'), 'id' => 'edit-contract-year-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    <div class="clearfix">
        <div class="form-group-left">

            <p class="text-center form-control">{{ trans(\Locales::getNamespace() . '/forms.sectionRentalPaymentOptions') }}</p>
            <div class="form-group">
                {!! Form::label('input-price', trans(\Locales::getNamespace() . '/forms.rentAmountLabel')) !!}
                {!! Form::text('price', null, ['id' => 'input-price', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.rentAmountPlaceholder')]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-mm_for_year', trans(\Locales::getNamespace() . '/forms.mmFeesForYearLabel')) !!}
                {!! Form::select('mm_for_year', $years, null, ['id' => 'input-mm_for_year', 'class' => 'form-control']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-is_exception', trans(\Locales::getNamespace() . '/forms.isExceptionLabel')) !!}
                {!! Form::select('is_exception', $exceptions, $year->is_exception, ['id' => 'input-is_exception', 'class' => 'form-control']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-comments', trans(\Locales::getNamespace() . '/forms.commentsLabel')) !!}
                {!! Form::textarea('comments', null, ['id' => 'input-comments', 'class' => 'form-control contract-year-comments', 'placeholder' => trans(\Locales::getNamespace() . '/forms.commentsPlaceholder')]) !!}
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

        </div>
    </div>

    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    {!! Form::close() !!}

    <script>
    @section('script')
        $.datepicker.regional.{{ \Locales::getCurrent() }} = unikat.variables.datepicker.{{ \Locales::getCurrent() }};
        $.datepicker.setDefaults($.datepicker.regional.{{ \Locales::getCurrent() }});

        var contract_dfrom1_selected = null;
        var contract_dto1_selected = null;
        var contract_dfrom2_selected = null;
        var contract_dto2_selected = null;
        var personal_dfrom1_selected = null;
        var personal_dto1_selected = null;
        var personal_dfrom2_selected = null;

        var minDate = '1.1.2007';
        var maxDate = '{{ $carbon->year($year->year)->endOfYear()->addYear()->format('d.m.Y') }}';

        @if ($year->contract_dfrom1)$('#input-personal_dfrom1').removeAttr('disabled');@endif
        @if ($year->contract_dto1)
            $('#input-contract_dto1').removeAttr('disabled');
            $('#input-contract_dfrom2').removeAttr('disabled');
        @endif
        @if ($year->contract_dfrom2)$('#input-contract_dfrom2').removeAttr('disabled');@endif
        @if ($year->contract_dto2)$('#input-contract_dto2').removeAttr('disabled');@endif
        @if ($year->personal_dfrom1)$('#input-personal_dfrom1').removeAttr('disabled');@endif
        @if ($year->personal_dto1)
            $('#input-personal_dto1').removeAttr('disabled');
            $('#input-personal_dfrom2').removeAttr('disabled');
        @endif
        @if ($year->personal_dfrom2)$('#input-personal_dfrom2').removeAttr('disabled');@endif
        @if ($year->personal_dto2)$('#input-personal_dto2').removeAttr('disabled');@endif

        @if ($year->contract_dfrom1)contract_dfrom1_selected = new Date('{{ $carbon->parse($year->contract_dfrom1) }}');@endif
        @if ($year->contract_dto1)contract_dto1_selected = new Date('{{ $carbon->parse($year->contract_dto1) }}');@endif
        @if ($year->contract_dfrom2)contract_dfrom2_selected = new Date('{{ $carbon->parse($year->contract_dfrom2) }}');@endif
        @if ($year->contract_dto2)contract_dto2_selected = new Date('{{ $carbon->parse($year->contract_dto2) }}');@endif
        @if ($year->personal_dfrom1)personal_dfrom1_selected = new Date('{{ $carbon->parse($year->personal_dfrom1) }}');@endif
        @if ($year->personal_dto1)personal_dto1_selected = new Date('{{ $carbon->parse($year->personal_dto1) }}');@endif
        @if ($year->personal_dfrom2)personal_dfrom2_selected = new Date('{{ $carbon->parse($year->personal_dfrom2) }}');@endif

        $('#input-contract_dfrom1').datepicker({
            onSelect: function(date) {
                contract_dfrom1_selected = new Date(Date.parse($('#input-contract_dfrom1').datepicker('getDate')));

                $('#input-personal_dfrom1').datepicker('option', 'minDate', contract_dfrom1_selected);
                $('#input-personal_dfrom2').datepicker('option', 'minDate', contract_dfrom1_selected);
                $('#input-contract_dto1').datepicker('option', 'minDate', contract_dfrom1_selected);
                $('#input-contract_dto1').removeAttr('disabled');
            }
        });

        $('#input-contract_dto1').datepicker({
            onSelect: function(date) {
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

                if (!$('#input-contract_dto2').val()) {
                    $('#input-contract_dto2').val('').removeAttr('disabled');
                }
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
                $('#input-personal_dto2').removeAttr('disabled');
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

        @if ($year->contract_dto1)$('#input-contract_dto1').datepicker('option', 'minDate', '{{ $year->contract_dfrom1 }}');@endif
        @if ($year->contract_dto2)$('#input-contract_dto2').datepicker('option', 'minDate', '{{ $year->contract_dfrom2 }}');@endif
        @if ($year->contract_dto2 && ($year->contract_dfrom1 > $year->contract_dfrom2))$('#input-contract_dto2').datepicker('option', 'maxDate', '{{ $year->contract_dfrom1 }}');@endif
        $('#input-personal_dfrom1').datepicker('option', 'minDate', '{{ min(array_filter([$year->contract_dfrom1, $year->contract_dfrom2])) }}');
        $('#input-personal_dfrom1').datepicker('option', 'maxDate', '{{ max(array_filter([$year->contract_dto1, $year->contract_dto2])) }}');
        $('#input-personal_dfrom2').datepicker('option', 'minDate', '{{ min(array_filter([$year->contract_dfrom1, $year->contract_dfrom2])) }}');
        $('#input-personal_dfrom2').datepicker('option', 'maxDate', '{{ max(array_filter([$year->contract_dto1, $year->contract_dto2])) }}');
        @if ($year->personal_dto1)$('#input-personal_dto1').datepicker('option', 'minDate', '{{ $year->personal_dfrom1 }}');@endif
        @if ($year->personal_dto1)$('#input-personal_dto1').datepicker('option', 'maxDate', '{{ max(array_filter([$year->contract_dto1, $year->contract_dto2])) }}');@endif
        @if ($year->personal_dto2)$('#input-personal_dto2').datepicker('option', 'minDate', '{{ $year->personal_dfrom2 }}');@endif

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
        @if ($year->personal_dto2)$('#input-personal_dto2').datepicker('option', 'maxDate', max);@endif

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
