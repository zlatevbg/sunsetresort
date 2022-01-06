@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($period))
    {!! Form::model($period, ['method' => 'put', 'url' => \Locales::route('rental-rates/update'), 'id' => 'edit-rental-rates-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('rental-rates/store'), 'id' => 'create-rental-rates-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    <div class="clearfix">
        <div class="form-group-left">
            <div class="form-group">
                {!! Form::label('input-dfrom', trans(\Locales::getNamespace() . '/forms.dfromLabel')) !!}
                {!! Form::text('dfrom', null, ['id' => 'input-dfrom', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.dfromPlaceholder'), 'autocomplete' => 'off']) !!}
            </div>
        </div>
        <div class="form-group-right">
            <div class="form-group">
                {!! Form::label('input-dto', trans(\Locales::getNamespace() . '/forms.dtoLabel')) !!}
                {!! Form::text('dto', null, ['id' => 'input-dto', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.dtoPlaceholder'), 'autocomplete' => 'off'] + ((isset($period) && $period->dfrom) ? [] : ['disabled'])) !!}
            </div>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('input-type', trans(\Locales::getNamespace() . '/forms.typeLabel')) !!}
        {!! Form::select('type', $types, null, ['id' => 'input-type', 'class' => 'form-control']) !!}
    </div>

    @foreach ($projects as $project)
        <p class="text-center form-control">{{ $project->name }}</p>
        <table class="table table-rental-rates">
            <thead>
                <tr>
                    <th>{!! Form::label(null, trans(\Locales::getNamespace() . '/forms.rentalRatesLabel')) !!}</th>
                    @foreach ($views as $view)
                        <th class="text-center">{{ $view->name }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
            @foreach ($rooms as $room)
                <tr>
                    <td>{{ $room->name }}</td>
                    @foreach ($views as $view)
                        <td>
                            <div class="form-group">
                                {!! Form::text('rates[' . $project->slug . '][' . $room->slug . '][' . $view->slug . ']', (isset($period) && isset($rates[$project->slug][$room->slug][$view->slug])) ? $rates[$project->slug][$room->slug][$view->slug] : null, ['id' => 'input-rates', 'class' => 'form-control']) !!} {{-- (isset($period) && isset($rates[$room->slug][$view->slug])) ? $rates[$room->slug][$view->slug] :  --}}
                            </div>
                        </td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    @endforeach

    @if (isset($period))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}

    <script>
    @section('script')
        var dates = {!! json_encode($dates) !!};

        $.datepicker.regional.{{ \Locales::getCurrent() }} = unikat.variables.datepicker.{{ \Locales::getCurrent() }};
        $.datepicker.setDefaults($.datepicker.regional.{{ \Locales::getCurrent() }});

        function addZ(n) {
            return n < 10 ? '0' + n : '' + n;
        }

        var highlightLast = false;

        $('#input-dfrom').datepicker({
            minDate: 0,
            onSelect: function(date) {
                var d = new Date(Date.parse($("#input-dfrom").datepicker("getDate")));
                $('#input-dto').datepicker('option', 'minDate', d);
                $('#input-dto').datepicker('option', 'maxDate', null);

                for (var i = 0; i < dates.length; i++) {
                    var maxDate = new Date(dates[i].from.substr(0, 4), parseInt(dates[i].from.substr(4, 2)) - 1, dates[i].from.substr(6, 2));
                    if (d < maxDate) {
                        $('#input-dto').datepicker('option', 'maxDate', maxDate);
                        break;
                    }
                }
                $('#input-dto').removeAttr('disabled');
            },
            beforeShowDay: function(date) {
                var current = date.getFullYear() + '' + addZ(date.getMonth() + 1) + '' + addZ(date.getDate());

                /*if (highlightLast) {
                    highlightLast = false;
                    return [false, 'booked-day']; // return [true, 'last-day'];
                }*/

                for (var i = 0; i < dates.length; i++) {
                    if (current == dates[i].from) {
                        return [false, 'booked-day']; // return [true, 'first-day'];
                    } else if (current >= dates[i].from && current <= dates[i].to) {
                        /*if (parseInt(current) + 1 == dates[i].to) {
                            highlightLast = true;
                        }*/

                        return [false, 'booked-day'];
                    }
                }
                return [true, 'available-day'];
            },
        });

        $('#input-dto').datepicker({
            minDate: $("#input-dfrom").datepicker("getDate") ? new Date(Date.parse($("#input-dfrom").datepicker("getDate"))) : null,
            beforeShowDay: function(date) {
                var current = date.getFullYear() + '' + addZ(date.getMonth() + 1) + '' + addZ(date.getDate());

                if (highlightLast) {
                    highlightLast = false;
                    return [false, 'booked-day'];
                }

                for (var i = 0; i < dates.length; i++) {
                    if (current == dates[i].from) {
                        return [false, 'booked-day']; // return [true, 'first-day'];
                    } else if (current > dates[i].from && current < dates[i].to) {
                        if (parseInt(current) + 1 == dates[i].to) {
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
