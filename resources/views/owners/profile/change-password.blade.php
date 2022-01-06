@extends(\Locales::getNamespace() . '.master')

@section('content')
    <div class="change-password-wrapper">
        <h1>{{ \Locales::getMenu(\Slug::getSlug())['title'] }}</h1>

        {!! Form::open(['method' => 'put', 'url' => \Locales::route('update-password'), 'id' => 'change-password-form', 'class' => 'ajax-lock', 'data-ajax-queue' => 'sync', 'role' => 'form']) !!}

        <div class="form-group{!! ($errors->has('password') ? ' has-error has-feedback' : '') !!}">
            {!! Form::label('input-password', trans(\Locales::getNamespace() . '/forms.newPasswordLabel'), ['class' => 'sr-only']) !!}
            {!! Form::password('password', ['id' => 'input-password', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.newPasswordPlaceholder')]) !!}
            @if ($errors->has('password'))<span class="glyphicon glyphicon-remove form-control-feedback"></span>@endif
        </div>

        <div class="form-group{!! ($errors->has('password_confirmation') ? ' has-error has-feedback' : '') !!}">
            {!! Form::label('input-password_confirmation', trans(\Locales::getNamespace() . '/forms.confirmPasswordLabel'), ['class' => 'sr-only']) !!}
            {!! Form::password('password_confirmation', ['id' => 'input-password_confirmation', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.confirmPasswordPlaceholder')]) !!}
            @if ($errors->has('password_confirmation'))<span class="glyphicon glyphicon-remove form-control-feedback"></span>@endif
        </div>

        {!! Form::submit(trans(\Locales::getNamespace() . '/forms.changePasswordButton'), ['class' => 'btn btn-primary btn-block']) !!}
        {!! Form::close() !!}
    </div>
@endsection
