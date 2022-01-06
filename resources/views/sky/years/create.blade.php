@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($year))
    {!! Form::model($year, ['method' => 'put', 'url' => \Locales::route('years/update'), 'id' => 'edit-year-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('years/store'), 'id' => 'create-year-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    <div class="form-group">
        {!! Form::label('input-year', trans(\Locales::getNamespace() . '/forms.yearLabel')) !!}
        {!! Form::text('year', null, ['id' => 'input-year', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.yearPlaceholder')]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-corporate_tax', trans(\Locales::getNamespace() . '/forms.corporateTaxLabel')) !!}
        {!! Form::text('corporate_tax', null, ['id' => 'input-corporate_tax', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.corporateTaxPlaceholder')]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-companies', trans(\Locales::getNamespace() . '/forms.rentalCompanyLabel')) !!}
        {!! Form::multiselect('companies[]', $multiselect['companies'], ['id' => 'input-companies', 'class' => 'form-control', 'multiple' => 'multiple']) !!}
    </div>

    <table class="table table-rental-rates">
        <thead>
            <tr>
                <th>{!! Form::label(null, trans(\Locales::getNamespace() . '/forms.roomTypeLabel')) !!}</th>
                @foreach ($feesTypes as $type)
                    <th class="text-center">{{ $type }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
        @foreach ($rooms as $room)
            <tr>
                <td>{{ $room->name }}</td>
                @foreach ($feesTypes as $type => $typeName)
                    <td>
                        <div class="form-group">
                            {!! Form::text('fees[' . $room->id . '][' . $type . ']', (isset($year) && isset($fees[$room->id][$type])) ? $fees[$room->id][$type] : null, ['id' => 'input-fees', 'class' => 'form-control']) !!} {{-- (isset($year) && isset($fees[$room->id][$type])) ? $fees[$room->id][$type] :  --}}
                        </div>
                    </td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>

    @if (isset($year))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}
    <script>
    @section('script')

    unikat.multiselect = {
        'input-companies': {},
    };

    @show
    </script>
</div>
@endsection
