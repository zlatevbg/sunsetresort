@extends(\Locales::getNamespace() . '.auth.master')

@section('content')
    <h1>{{ trans(\Locales::getNamespace() . '/auth.resetPasswordTitle') }}</h1>

    @include(\Locales::getNamespace() . '/shared.success')

    @include(\Locales::getNamespace() . '/shared.errors')

    {!! Form::open(['id' => 'reset-form', 'class' => 'ajax-lock', 'data-ajax-queue' => 'sync', 'role' => 'form']) !!}

    <div class="form-group{!! ($errors->has('email') ? ' has-error has-feedback' : '') !!}">
        {!! Form::label('input-email', trans(\Locales::getNamespace() . '/forms.emailLabel'), ['class' => 'sr-only']) !!}
        {!! Form::email('email', null, ['id' => 'input-email', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.emailPlaceholder')]) !!}
        @if ($errors->has('email'))<span class="glyphicon glyphicon-remove form-control-feedback"></span>@endif
    </div>

    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.sendPasswordReseLinktButton'), ['class' => 'btn btn-primary btn-block']) !!}
    {!! Form::close() !!}
@endsection
