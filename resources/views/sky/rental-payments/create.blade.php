@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($rentalPayment))
    {!! Form::model($rentalPayment, ['method' => 'put', 'url' => \Locales::route('rental-payments/update'), 'id' => 'edit-rental-payment-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('rental-payments/store'), 'id' => 'create-rental-payment-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    <div class="form-group">
        {!! Form::label('input-name', trans(\Locales::getNamespace() . '/forms.nameLabel')) !!}
        {!! Form::text('name', null, ['id' => 'input-name', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.namePlaceholder')]) !!}
    </div>

    {!! Form::label(null, trans(\Locales::getNamespace() . '/forms.rentalPaymentsLabel')) !!}
    @foreach ($rooms as $room)
    <p class="text-center form-control">{{ $room->name }}</p>
    <table class="table table-rental-contracts">
        <thead>
            <tr>
                <th></th>
                @foreach ($furnitures as $furniture)
                <th class="text-center">{{ $furniture->name }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($views as $view)
            <tr>
                <td>{{ $view->name }}</td>
                @foreach ($furnitures as $furniture)
                <td>
                    <div class="form-group">
                        {!! Form::text('prices[' . $room->id . '][' . $view->id . '][' . $furniture->id . ']', (isset($rentalPayment) && isset($prices[$room->id][$view->id][$furniture->id])) ? $prices[$room->id][$view->id][$furniture->id] : null, ['id' => 'input-prices', 'class' => 'form-control']) !!}
                    </div>
                </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
    @endforeach

    @if (isset($rentalPayment))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}
</div>
@endsection
