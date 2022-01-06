@extends(\Locales::getNamespace() . '.master')

@section('content')
<div class="magnific-popup newsletter-wrapper">
    <section class="newsletter-options">
        <article class="newsletter-attachments">
            <h1 class="h3">{{ trans(\Locales::getNamespace() . '/messages.newsletterAttachments') }}</h1>
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th colspan="2"><a href="{{ \Locales::route('download-booking', $booking->id) }}">{!! \HTML::image(\App\Helpers\autover('/img/' . \Locales::getNamespace() . '/ext/pdf-small.png')) !!} booking-form.pdf</a></th>
                    </tr>
                    @foreach ($template->attachments as $attachment)
                        <tr>
                            <th><a href="{{ \Locales::route('download-booking-attachments', $attachment['uuid']) }}">{!! \HTML::image(\App\Helpers\autover('/img/' . \Locales::getNamespace() . '/ext/' . $attachment['extension'] . '-small.png'), $attachment['file']) !!} {{ $attachment['file'] }}</a></th>
                            <td class="text-right">{{ \App\Helpers\formatBytes($attachment['size']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </article>
    </section>

    <div class="text">{!! $html !!}</div>
</div>
@endsection
