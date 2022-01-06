@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($maintenance))
    {!! Form::model($maintenance, ['method' => 'put', 'url' => \Locales::route('apartment-maintenance-issues/update'), 'id' => 'edit-maintenance-issue-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('apartment-maintenance-issues/save'), 'id' => 'add-maintenance-issue-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    {!! Form::hidden('apartment', $apartment, ['id' => 'input-apartment']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    <div class="clearfix">
        <div class="form-group-left">
            <div class="form-group">
                {!! Form::label('input-status', trans(\Locales::getNamespace() . '/forms.statusLabel')) !!}
                {!! Form::select('status', $status, null, ['id' => 'input-status', 'class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group-right">
            <div class="form-group">
                {!! Form::label('input-responsibility', trans(\Locales::getNamespace() . '/forms.responsibilityLabel')) !!}
                {!! Form::select('responsibility', $responsibility, null, ['id' => 'input-responsibility', 'class' => 'form-control']) !!}
            </div>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('input-title', trans(\Locales::getNamespace() . '/forms.titleLabel')) !!}
        {!! Form::text('title', null, ['id' => 'input-title', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.titlePlaceholder')]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('input-comments', trans(\Locales::getNamespace() . '/forms.commentsLabel')) !!}
        {!! Form::textarea('comments', null, ['id' => 'input-comments', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.commentsPlaceholder')]) !!}
    </div>

    @if (isset($maintenance))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}
</div>
@endsection
