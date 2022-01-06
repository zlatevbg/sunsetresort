@extends(\Locales::getNamespace() . '.auth.master')

@section('content')
    <h1>{{ trans(\Locales::getNamespace() . '/auth.resetPasswordTitle') }}</h1>

    @include(\Locales::getNamespace() . '/shared.errors')

    {!! Form::open(['id' => 'reset-form', 'class' => 'ajax-lock', 'data-ajax-queue' => 'sync', 'role' => 'form', 'url' => \Locales::route('reset-post')]) !!}
    {!! Form::hidden('token', $token) !!}

    <div class="form-group{!! ($errors->has('email') ? ' has-error has-feedback' : '') !!}">
        {!! Form::label('input-email', trans(\Locales::getNamespace() . '/forms.emailLabel'), ['class' => 'sr-only']) !!}
        {!! Form::email('email', $email, ['id' => 'input-email', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.emailPlaceholder')]) !!}
        @if ($errors->has('email'))<span class="glyphicon glyphicon-remove form-control-feedback"></span>@endif
    </div>

    <div class="form-group{!! ($errors->has('password') ? ' has-error has-feedback' : '') !!}">
        {!! Form::label('input-password', trans(\Locales::getNamespace() . '/forms.passwordLabel'), ['class' => 'sr-only']) !!}
        {!! Form::password('password', ['id' => 'input-password', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.passwordPlaceholder')]) !!}
        @if ($errors->has('password'))<span class="glyphicon glyphicon-remove form-control-feedback"></span>@endif
    </div>

    <div class="form-group{!! ($errors->has('password_confirmation') ? ' has-error has-feedback' : '') !!}">
        {!! Form::label('input-password_confirmation', trans(\Locales::getNamespace() . '/forms.confirmPasswordLabel'), ['class' => 'sr-only']) !!}
        {!! Form::password('password_confirmation', ['id' => 'input-password_confirmation', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.confirmPasswordPlaceholder')]) !!}
        @if ($errors->has('password_confirmation'))<span class="glyphicon glyphicon-remove form-control-feedback"></span>@endif
    </div>

    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.resetPasswordButton'), ['class' => 'btn btn-primary btn-block']) !!}
    {!! Form::close() !!}
@endsection
