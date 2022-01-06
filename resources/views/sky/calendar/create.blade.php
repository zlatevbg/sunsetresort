@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($calendar))
    {!! Form::model($calendar, ['method' => 'put', 'url' => \Locales::route('calendar/update'), 'id' => 'edit-calendar-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('calendar/store'), 'id' => 'create-calendar-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    <div class="form-group">
        {!! Form::label('input-date', trans(\Locales::getNamespace() . '/forms.dateLabel')) !!}
        {!! Form::text('date', null, ['id' => 'input-date', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.datePlaceholder'), 'autocomplete' => 'off']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-description', trans(\Locales::getNamespace() . '/forms.descriptionLabel')) !!}
        {!! Form::text('description', null, ['id' => 'input-description', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.descriptionPlaceholder')]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-admins', trans(\Locales::getNamespace() . '/forms.adminsRemainderLabel')) !!}
        {!! Form::multiselect('admins[]', $multiselect['admins'], ['id' => 'input-admins', 'class' => 'form-control', 'multiple' => 'multiple']) !!}
    </div>

    @if (isset($calendar))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}

    <script>
    @section('script')
        unikat.multiselect = {
            'input-admins': {},
        };

        $.datepicker.regional.{{ \Locales::getCurrent() }} = unikat.variables.datepicker.{{ \Locales::getCurrent() }};
        $.datepicker.setDefaults($.datepicker.regional.{{ \Locales::getCurrent() }});

        $('#input-date').datepicker({
            changeMonth: true,
        });
    @show
    </script>
</div>
@endsection
