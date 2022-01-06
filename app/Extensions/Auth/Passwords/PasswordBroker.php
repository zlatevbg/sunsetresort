<?php

namespace App\Extensions\Auth\Passwords;

use Closure;
use Illuminate\Auth\Passwords\PasswordBroker as IlluminatePasswordBroker;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Mailgun\Mailgun;
use App\Models\Sky\NewsletterTemplates;
use App\Models\Sky\Signature;
use App\Services\Newsletter as NewsletterService;

class PasswordBroker extends IlluminatePasswordBroker
{

    protected $broker;

    /**
     * Create a new password broker instance.
     *
     * @param  \Illuminate\Auth\Passwords\TokenRepositoryInterface  $tokens
     * @param  \Illuminate\Contracts\Auth\UserProvider  $users
     * @param  \Illuminate\Contracts\Mail\Mailer  $mailer
     * @param  string  $emailView
     * @return void
     */
    public function __construct(TokenRepositoryInterface $tokens,
                                UserProvider $users,
                                MailerContract $mailer,
                                $emailView,
                                $broker)
    {
        $this->users = $users;
        $this->mailer = $mailer;
        $this->tokens = $tokens;
        $this->emailView = $emailView;
        $this->broker = $broker;
    }

    /**
     * Send the password reset link via e-mail.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $token
     * @param  \Closure|null  $callback
     * @return int
     */
    public function emailResetLink(CanResetPasswordContract $user, $token, Closure $callback = null)
    {
        $template = NewsletterTemplates::where('template', 'pf')->where('locale_id', \Locales::getId())->firstOrFail();

        $newsletterService = new NewsletterService();

        $directory = public_path('upload') . DIRECTORY_SEPARATOR . 'newsletter-templates' . DIRECTORY_SEPARATOR . $template->id . DIRECTORY_SEPARATOR;

        $attachments = [];
        foreach ($template->attachments as $attachment) {
            array_push($attachments, $directory . \Config::get('upload.attachmentsDirectory') . DIRECTORY_SEPARATOR . $attachment->uuid . DIRECTORY_SEPARATOR . $attachment->file);
        }

        $body = $newsletterService->replaceHtml($template->body);

        foreach ($newsletterService->patterns() as $key => $pattern) {
            if (strpos($body, $pattern) !== false) {
                $body = preg_replace('/' . $pattern . '/', $user->{$newsletterService->columns()[$key]}, $body);
            }
        }

        $body = preg_replace('/{TOKEN}/', url(\Locales::getLanguage() . 'reset/' . $token), $body);

        $signature = $template->signature->content;

        $links = trans(\Locales::getNamespace() . '/newsletters.links');
        $copyright = trans(\Locales::getNamespace() . '/newsletters.copyright');
        $disclaimer = trans(\Locales::getNamespace() . '/newsletters.disclaimer');

        $html = \View::make(\Locales::getNamespace() . '.newsletters.templates.pf', compact('template', 'body', 'signature', 'links', 'copyright', 'disclaimer'))->render();
        $text = preg_replace('/{IMAGE}/', '', $body);
        $text = $newsletterService->replaceText($text);

        $images = [storage_path('app/images/newsletter-logo.png')];
        foreach ($template->images as $image) {
            $path = $directory . \Config::get('upload.imagesDirectory') . DIRECTORY_SEPARATOR . $image->uuid . DIRECTORY_SEPARATOR . $image->file;
            if (strpos($html, '{IMAGE}') !== false) {
                if (strpos($html, '<td class="leftColumnContent">{IMAGE}</td>') !== false || strpos($html, '<td class="rightColumnContent">{IMAGE}</td>') !== false) {
                    $html = preg_replace('/{IMAGE}/', '<img src="cid:' . $image->file . '" class="columnImage" style="height:auto !important;max-width:260px !important;" />', $html, 1);
                } else {
                    $html = preg_replace('/{IMAGE}/', '<img src="cid:' . $image->file . '" class="responsiveImage" style="height:auto !important;max-width:' . \Image::make($path)->width() . 'px !important;" />', $html, 1);
                }

                array_push($images, $path);
            }
        }

        $directorySignatures = public_path('upload') . DIRECTORY_SEPARATOR . 'signatures' . DIRECTORY_SEPARATOR . $template->signature->id . DIRECTORY_SEPARATOR;
        foreach ($template->signature->images as $image) {
            $path = $directorySignatures . $image->uuid . DIRECTORY_SEPARATOR . $image->file;
            if (strpos($html, '{SIGNATURE}') !== false) {
                $html = preg_replace('/{SIGNATURE}/', '<img src="cid:' . $image->file . '" class="responsiveImage" style="height:auto !important;max-width:' . \Image::make($path)->width() . 'px !important;" />', $html, 1);
                array_push($images, $path);
            }
        }

        if ($this->broker == env('APP_OWNERS_SUBDOMAIN')) {
            // owners specific
        } else {
            // sky specific
        }

        $mg = new Mailgun(env('MAILGUN_SECRET'), new \Http\Adapter\Guzzle6\Client()); // , 'bin.mailgun.net'
        // $mg->setApiVersion('7fb5efa5'); // bin.mailgun.net/7fb5efa5
        // $mg->setSslEnabled(false);

        $result = $mg->sendMessage(env('MAILGUN_DOMAIN'),
            [
                'from' => \Config::get('mail.from.name') . ' <' . \Config::get('mail.from.address') . '>',
                'h:Sender' => \Config::get('mail.from.name') . ' <' . \Config::get('mail.from.address') . '>',
                'to' => $user->full_name . ' <' . (\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $user->email) . '>',
                'subject' => $template->subject,
                'html' => $html,
                'text' => $text,
                // 'o:testmode' => true,
                'o:tag' => 'pf-' . $this->broker,
                'v:' . $this->broker . 'Id' => $user->id,
            ],
            [
                'attachment' => $attachments,
                'inline' => $images,
            ]
        );

        if ($result->http_response_code == 200) {

        } else {

        }
    }

}
