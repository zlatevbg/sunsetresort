<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use Mailgun\Mailgun;
use Carbon\Carbon;
use Storage;

class MailgunController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        set_time_limit(0);
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function mailgun()
    {
        $mg = new Mailgun(env('MAILGUN_SECRET'), new \Http\Adapter\Guzzle6\Client());
        $queryString = [
            'begin' => 'Thu, 16 Dec 2021 00:00:00 +0300', // 1566888000
            'end' => 'Fri, 17 Dec 2021 10:59:59 +0300', // 1566888000
            'ascending' => 'yes',
            'limit' =>  300,
            'pretty' => 'yes',
            'event' => 'delivered OR opened OR failed',
        ];

        $url = env('MAILGUN_DOMAIN') . '/events';
        do {
            $result = $mg->get($url, $queryString);
            if ($result->http_response_body->items) {
                foreach ($result->http_response_body->items as $event) {
                    $data = '
<tr>
    <td width="70" class="text-right">
        <button type="button" class="btn btn-' . ($event->event == 'opened' ? 'info' : ($event->event == 'delivered' ? 'success' : 'danger')) . '">
            <svg viewBox="0 0 512 512">
                <path fill="currentColor" d="M207.029 381.476L12.686 187.132c-9.373-9.373-9.373-24.569 0-33.941l22.667-22.667c9.357-9.357 24.522-9.375 33.901-.04L224 284.505l154.745-154.021c9.379-9.335 24.544-9.317 33.901.04l22.667 22.667c9.373 9.373 9.373 24.569 0 33.941L240.971 381.476c-9.373 9.372-24.569 9.372-33.942 0z"/>
            </svg>
        </button>
    </td>
    <td class="text-nowrap">' . Carbon::createFromTimestamp($event->timestamp, 'Europe/Sofia')->format('d.m.Y H:i') . '</td>
    <td class="text-nowrap">' . $event->recipient . '</td>
    <td class="text-nowrap">' . $event->event . '</td>
    <td>' . ($event->event == 'opened' ? '' : (isset($event->message) ? $event->message->headers->subject : '')) . '</td>
</tr>
<tr class="data sr-only">
    <td colspan="5">
        <pre>' . json_encode($event, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>
    </td>
</tr>';

                    Storage::append('file.txt', $data);
                }

                $url = str_replace(['https://api.mailgun.net/v2/', 'https://api.mailgun.net/v3/'], '', $result->http_response_body->paging->next);
            } else {
                break;
            }
        } while (true);

        return response('DONE');
    }

}
