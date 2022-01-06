@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup newsletter-wrapper">
    <h1>{{ $newsletter->subject }}<br><small>{{ $newsletter->teaser }}</small></h1>
    @if ($newsletter->sent_at)<p><strong>{{ trans(\Locales::getNamespace() . '/messages.newsletterSent') }}:</strong> {{ $newsletter->sent_at->formatLocalized('%d.%m.%Y') }}</p>@endif
    <p><strong>{{ trans(\Locales::getNamespace() . '/messages.newsletterSender') }}:</strong> {{ $newsletter->signature->translate($language)->name . ' <' . $newsletter->signature->email . '>' }}</p>

    <section class="newsletter-options">
        @if (count($filters))
        <article class="newsletter-filters">
            <h1 class="h3">{{ trans(\Locales::getNamespace() . '/messages.newsletterFilters') }}</h1>
            <table class="table table-bordered">
                <tbody>
                @foreach ($filters as $filter)
                    <tr>
                        <th>{{ $filter['title'] }}</th>
                        <td>{{ $filter['values'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </article>
        @endif

        @if (count($newsletter->merge))
        <article class="newsletter-merge">
            <h1 class="h3">{{ trans(\Locales::getNamespace() . '/messages.newsletterMerge') }}</h1>
            <table class="table table-bordered">
                <tbody>
                @foreach ($newsletter->merge as $key => $merge)
                    <tr>
                        <th>{{ trans(\Locales::getNamespace() . '/forms.mergeFieldLabel') . ' ' . ($key + 1) }}</th>
                        <td>{{ $merge['merge'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </article>
        @endif

        @if (count($newsletter->attachments))
        <article class="newsletter-attachments">
            <h1 class="h3">{{ trans(\Locales::getNamespace() . '/messages.newsletterAttachments') }}</h1>
            <table class="table table-bordered">
                <tbody>
                @foreach ($newsletter->attachments as $attachment)
                    <tr>
                        <th><a href="{{ \Locales::route('newsletter-attachments/download', $attachment['id']) }}">{!! \HTML::image(\App\Helpers\autover('/img/' . \Locales::getNamespace() . '/ext/' . $attachment['extension'] . '-small.png'), $attachment['file']) !!} {{ $attachment['file'] }}</a></th>
                        <td>{{ \App\Helpers\formatBytes($attachment['size']) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </article>
        @endif

        @if (count($newsletter->attachmentsApartment))
        <article class="newsletter-attachments">
            <h1 class="h3">{{ trans(\Locales::getNamespace() . '/messages.newsletterAttachmentsApartments') }}</h1>
            <table class="table table-bordered">
                <tbody>
                @foreach ($newsletter->attachmentsApartment as $attachment)
                    <tr>
                        <th><a href="{{ \Locales::route('newsletter-attachments-apartment/download', $attachment['id']) }}">{!! \HTML::image(\App\Helpers\autover('/img/' . \Locales::getNamespace() . '/ext/' . $attachment['extension'] . '-small.png'), $attachment['file']) !!} {{ $attachment['file'] }}</a></th>
                        <td>{{ \App\Helpers\formatBytes($attachment['size']) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </article>
        @endif

        @if (count($newsletter->attachmentsOwner))
        <article class="newsletter-attachments">
            <h1 class="h3">{{ trans(\Locales::getNamespace() . '/messages.newsletterAttachmentsOwners') }}</h1>
            <table class="table table-bordered">
                <tbody>
                @foreach ($newsletter->attachmentsOwner as $attachment)
                    <tr>
                        <th><a href="{{ \Locales::route('newsletter-attachments-owner/download', $attachment['id']) }}">{!! \HTML::image(\App\Helpers\autover('/img/' . \Locales::getNamespace() . '/ext/' . $attachment['extension'] . '-small.png'), $attachment['file']) !!} {{ $attachment['file'] }}</a></th>
                        <td>{{ \App\Helpers\formatBytes($attachment['size']) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </article>
        @endif
    </section>

    <div class="text">{!! $newsletter->body !!}</div>
</div>
@endsection
