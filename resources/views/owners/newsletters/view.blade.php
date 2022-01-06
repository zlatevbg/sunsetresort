@extends(\Locales::getNamespace() . '.master')

@section('content')
@if (isset($ajax))<div class="magnific-popup newsletter-wrapper">@endif
    <h1>{{ $newsletter->subject }}<br><small>{{ $newsletter->teaser }}</small></h1>
    @if ($newsletter->sent_at)<p class="newsletter-sent-date"><strong>{{ trans(\Locales::getNamespace() . '/messages.newsletterSent') }}:</strong> {{ $newsletter->sent_at->formatLocalized('%d.%m.%Y') }}</p>@endif
    <p><strong>{{ trans(\Locales::getNamespace() . '/messages.newsletterSender') }}:</strong> {{ $newsletter->signature->translate($language)->name . ' <' . $newsletter->signature->email . '>' }}</p>
    <p><strong>{{ trans(\Locales::getNamespace() . '/messages.newsletterTo') }}:</strong> {{ $owner->full_name . ' <' . $owner->email . '>' }}</p>
    @if ($owner->email_cc)<p><strong>{{ trans(\Locales::getNamespace() . '/messages.newsletterCopyTo') }}:</strong> {{ $owner->full_name . ' <' . $owner->email_cc . '>' }}</p>@endif

    <section class="newsletter-options">
        @if (count($newsletter->attachments) || count($newsletter->attachmentsApartment) || count($newsletter->attachmentsOwner))
        <article class="newsletter-attachments">
            <h1 class="h3">{{ trans(\Locales::getNamespace() . '/messages.newsletterAttachments') }}</h1>
            <table class="table table-bordered">
                <tbody>
                @foreach ($newsletter->attachments as $attachment)
                    <tr>
                        <th><a href="{{ \Locales::route('download-newsletter-attachments', $attachment['uuid']) }}">{!! \HTML::image(\App\Helpers\autover('/img/' . \Locales::getNamespace() . '/ext/' . $attachment['extension'] . '-small.png'), $attachment['file']) !!} {{ $attachment['file'] }}</a></th>
                        <td>{{ \App\Helpers\formatBytes($attachment['size']) }}</td>
                    </tr>
                @endforeach

                @foreach ($attachmentsApartment as $attachment)
                    <tr>
                        <th><a href="{{ \Locales::route('download-newsletter-attachments-apartment', $attachment['uuid']) }}">{!! \HTML::image(\App\Helpers\autover('/img/' . \Locales::getNamespace() . '/ext/' . $attachment['extension'] . '-small.png'), $attachment['file']) !!} {{ $attachment['file'] }}</a></th>
                        <td>{{ \App\Helpers\formatBytes($attachment['size']) }}</td>
                    </tr>
                @endforeach

                @foreach ($attachmentsOwner as $attachment)
                    <tr>
                        <th><a href="{{ \Locales::route('download-newsletter-attachments-owner', $attachment['uuid']) }}">{!! \HTML::image(\App\Helpers\autover('/img/' . \Locales::getNamespace() . '/ext/' . $attachment['extension'] . '-small.png'), $attachment['file']) !!} {{ $attachment['file'] }}</a></th>
                        <td>{{ \App\Helpers\formatBytes($attachment['size']) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </article>
        @endif
    </section>

    <div class="text">{!! $newsletter->body !!}</div>
@if (isset($ajax))</div>@endif
@endsection
