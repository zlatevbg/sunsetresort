@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup">
    <h1>{{ \Locales::getMetaTitle() }}</h1>

    @if (isset($owner))
    {!! Form::model($owner, ['method' => 'put', 'url' => \Locales::route('owners/update'), 'id' => 'edit-owner-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @else
    {!! Form::open(['url' => \Locales::route('owners/store'), 'id' => 'create-owner-form', 'data-ajax-queue' => 'sync', 'class' => 'ajax-lock', 'role' => 'form']) !!}
    @endif

    {!! Form::hidden('table', $table, ['id' => 'input-table']) !!}

    <div class="clearfix">
        <div class="form-group-left">
            <p class="text-center form-control">{{ trans(\Locales::getNamespace() . '/forms.sectionPersonalDetails') }}</p>

            <div class="form-group">
                {!! Form::label('input-first_name', trans(\Locales::getNamespace() . '/forms.firstNameLabel')) !!}
                {!! Form::text('first_name', null, ['id' => 'input-first_name', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.firstNamePlaceholder')]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-last_name', trans(\Locales::getNamespace() . '/forms.lastNameLabel')) !!}
                {!! Form::text('last_name', null, ['id' => 'input-last_name', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.lastNamePlaceholder')]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-sex', trans(\Locales::getNamespace() . '/forms.sexLabel')) !!}
                {!! Form::select('sex', $sex, null, ['id' => 'input-sex', 'class' => 'form-control']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-locale_id', trans(\Locales::getNamespace() . '/forms.languageLabel')) !!}
                {!! Form::select('locale_id', $locales, null, ['id' => 'input-locale_id', 'class' => 'form-control']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-phone', trans(\Locales::getNamespace() . '/forms.phoneLabel')) !!}
                {!! Form::tel('phone', null, ['id' => 'input-phone', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.phonePlaceholder')]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-mobile', trans(\Locales::getNamespace() . '/forms.mobileLabel')) !!}
                {!! Form::tel('mobile', null, ['id' => 'input-mobile', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.mobilePlaceholder')]) !!}
            </div>

            <div class="form-group">
                <label>&nbsp;</label>
                <p class="text-center form-control">{{ trans(\Locales::getNamespace() . '/forms.sectionAddress') }}</p>
            </div>

            <div class="form-group">
                {!! Form::label('input-country_id', trans(\Locales::getNamespace() . '/forms.countryLabel')) !!}
                {!! Form::select('country_id', $countries, null, ['id' => 'input-country_id', 'class' => 'form-control']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-city', trans(\Locales::getNamespace() . '/forms.cityLabel')) !!}
                {!! Form::text('city', null, ['id' => 'input-city', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.cityPlaceholder')]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-postcode', trans(\Locales::getNamespace() . '/forms.postcodeLabel')) !!}
                {!! Form::text('postcode', null, ['id' => 'input-postcode', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.postcodePlaceholder')]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-address1', trans(\Locales::getNamespace() . '/forms.address1Label')) !!}
                {!! Form::text('address1', null, ['id' => 'input-address1', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.address1Placeholder')]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-address2', trans(\Locales::getNamespace() . '/forms.address2Label')) !!}
                {!! Form::text('address2', null, ['id' => 'input-address2', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.address2Placeholder')]) !!}
            </div>
        </div>
        <div class="form-group-right">
            <p class="text-center form-control">{{ trans(\Locales::getNamespace() . '/forms.sectionAccountDetails') }}</p>

            <div class="form-group">
                {!! Form::label('input-email', trans(\Locales::getNamespace() . '/forms.emailLabel')) !!}
                {!! Form::email('email', null, ['id' => 'input-email', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.emailPlaceholder')]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-email_cc', trans(\Locales::getNamespace() . '/forms.emailCcLabel')) !!}
                {!! Form::email('email_cc', null, ['id' => 'input-email_cc', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.emailCcPlaceholder')]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-password', trans(\Locales::getNamespace() . '/forms.passwordLabel')) !!}
                <a id="generate-password">{{ trans(\Locales::getNamespace() . '/forms.generatePasswordButton') }}</a>
                {!! Form::password('password', ['id' => 'input-password', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.passwordPlaceholder')]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-password_confirmation', trans(\Locales::getNamespace() . '/forms.confirmPasswordLabel')) !!}
                {!! Form::password('password_confirmation', ['id' => 'input-password_confirmation', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.confirmPasswordPlaceholder')]) !!}
            </div>

            <div class="form-group">
                <label>&nbsp;</label>
                <p class="text-center form-control">{{ trans(\Locales::getNamespace() . '/forms.sectionStatus') }}</p>
            </div>

            <div class="form-group">
                {!! Form::label('input-is_subscribed', trans(\Locales::getNamespace() . '/forms.newsletterSubscriptionLabel')) !!}
                {!! Form::select('is_subscribed', $subscribed, $owner->is_subscribed ?? 1, ['id' => 'input-is_subscribed', 'class' => 'form-control']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-outstanding_bills', trans(\Locales::getNamespace() . '/forms.outstandingBillsLabel')) !!}
                {!! Form::select('outstanding_bills', $ob, $owner->outstanding_bills ?? 0, ['id' => 'input-outstanding_bills', 'class' => 'form-control']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-letting_offer', trans(\Locales::getNamespace() . '/forms.lettingOfferLabel')) !!}
                {!! Form::select('letting_offer', $ob, $owner->letting_offer ?? 0, ['id' => 'input-letting_offer', 'class' => 'form-control']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-srioc', trans(\Locales::getNamespace() . '/forms.sriocLabel')) !!}
                {!! Form::select('srioc', $srioc, $owner->srioc ?? 0, ['id' => 'input-srioc', 'class' => 'form-control']) !!}
            </div>

            <div class="form-group">
                <label>&nbsp;</label>
                <p class="text-center form-control">{{ trans(\Locales::getNamespace() . '/forms.sectionTaxDetails') }}</p>
            </div>

            <div class="form-group">
                {!! Form::label('input-apply_wt', trans(\Locales::getNamespace() . '/forms.applyWtLabel')) !!}
                {!! Form::select('apply_wt', $wt, $owner->apply_wt ?? 1, ['id' => 'input-apply_wt', 'class' => 'form-control']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-bulstat', trans(\Locales::getNamespace() . '/forms.bulstatLabel')) !!}
                {!! Form::text('bulstat', null, ['id' => 'input-bulstat', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.bulstatPlaceholder')]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('input-tax_pin', trans(\Locales::getNamespace() . '/forms.taxPinLabel')) !!}
                {!! Form::text('tax_pin', null, ['id' => 'input-tax_pin', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.taxPinPlaceholder')]) !!}
            </div>

            <div class="form-group">
                <label>&nbsp;</label>
                <p class="text-center form-control">{{ trans(\Locales::getNamespace() . '/forms.sectionComments') }}</p>
            </div>

            <div class="form-group">
                {!! Form::label('input-comments', trans(\Locales::getNamespace() . '/forms.commentsLabel')) !!}
                {!! Form::textarea('comments', null, ['id' => 'input-comments', 'class' => 'form-control', 'placeholder' => trans(\Locales::getNamespace() . '/forms.commentsPlaceholder')]) !!}
            </div>
        </div>
    </div>

    @if (isset($owner))
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.updateButton'), ['class' => 'btn btn-warning btn-block']) !!}
    @else
    {!! Form::submit(trans(\Locales::getNamespace() . '/forms.storeButton'), ['class' => 'btn btn-primary btn-block']) !!}
    @endif

    {!! Form::close() !!}
</div>
@endsection
