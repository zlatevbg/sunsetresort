@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup newsletter-wrapper">
    <h1>{{ $template->subject }}<br><small>{{ $template->teaser }}</small></h1>
    <p><strong>{{ trans(\Locales::getNamespace() . '/messages.newsletterSender') }}:</strong> {{ $template->signature->translate($language)->name . ' <' . $template->signature->email . '>' }}</p>

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

        @if (count($template->attachments))
        <article class="newsletter-attachments">
            <h1 class="h3">{{ trans(\Locales::getNamespace() . '/messages.newsletterAttachments') }}</h1>
            <table class="table table-bordered">
                <tbody>
                @foreach ($template->attachments as $attachment)
                    <tr>
                        <th><a href="{{ \Locales::route('newsletter-template-attachments/download', $attachment['id']) }}">{!! \HTML::image(\App\Helpers\autover('/img/' . \Locales::getNamespace() . '/ext/' . $attachment['extension'] . '-small.png'), $attachment['file']) !!} {{ $attachment['file'] }}</a></th>
                        <td>{{ \App\Helpers\formatBytes($attachment['size']) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </article>
        @endif
    </section>

    <div class="text">{!! $template->body !!}</div>
</div>
@endsection
