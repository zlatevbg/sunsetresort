@if ($errors->any() || \Session::has('session_expired'))
<div class="alert-messages">
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close"><span aria-hidden="true">&times;</span></button>
        @if (!\Session::has('session_expired'))
            {!! trans(\Locales::getNamespace() . '/js.ajaxErrorMessage') !!}
        @endif
        <ul>
            @foreach ($errors->all() as $error)
                <li><span class="glyphicon glyphicon-warning-sign"></span>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif
