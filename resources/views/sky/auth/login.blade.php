@extends(\Locales::getNamespace() . '.auth.master')

@section('content')
    <h1>{{ trans(\Locales::getNamespace() . '/auth.signInTitle') }}</h1>

    @include(\Locales::getNamespace() . '/shared.errors')

    {!! Form::open(['id' => 'login-form', 'class' => 'ajax-lock', 'data-ajax-queue' => 'sync', 'role' => 'form']) !!}

    <div class="form-group{!! ($errors->has('email') ? ' has-error has-feedback' : '') !!}">
        {!! Form::label('input-email', trans(\Locales::getNamespace() . '/forms.emailLabel'), ['class' => 'sr-only']) !!}
        {!! Form::email('email', null, ['id' => 'input-email', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.emailPlaceholder')]) !!}
        @if ($errors->has('email'))<span class="glyphicon glyphicon-remove form-control-feedback"></span>@endif
    </div>

    <div class="form-group{!! ($errors->has('password') ? ' has-error has-feedback' : '') !!}">
        {!! Form::label('input-password', trans(\Locales::getNamespace() . '/forms.passwordLabel'), ['class' => 'sr-only']) !!}
        {!! Form::password('password', ['id' => 'input-password', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.passwordPlaceholder')]) !!}
        @if ($errors->has('password'))<span class="glyphicon glyphicon-remove form-control-feedback"></span>@endif
    </div>

    <div class="form-group">
        {!! Form::checkboxInline('remember', 1, null, ['id' => 'input-remember'], trans(\Locales::getNamespace() . '/forms.rememberOption'), ['class' => 'checkbox-inline']) !!}
    </div>

    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.signInButton'), ['class' => 'btn btn-primary btn-block']) !!}
    {!! Form::close() !!}
    <p>
        <a href="{{ \Locales::route('pf') }}">{{ trans(\Locales::getNamespace() . '/auth.passwordForgottenHelpText') }}</a>
    </p>
@endsection
