<?php

namespace App\Services;

class Newsletter
{
    protected $patterns = [
        '{OWNER_EMAIL}',
        '{OWNER_FIRST_NAME}',
        '{OWNER_LAST_NAME}',
        '{OWNER_FULL_NAME}',
        '{OWNER_PASSWORD}',
    ];

    protected $columns = [
        'email',
        'first_name',
        'last_name',
        'full_name',
        'temp_password',
    ];

    public function __construct()
    {
    }

    public function patterns()
    {
        return $this->patterns;
    }

    public function columns()
    {
        return $this->columns;
    }

    public function replaceHtml($body)
    {
        $body = preg_replace('/<table(.*?)(style="width:100%")(.*?)>/s', '<table $1 width="100%" $2>', $body);
        $body = preg_replace('/<h1>/', '<h1 style="color:#364f9d !important;display:block;font-family:Helvetica;font-size:26px;font-style:normal;font-weight:bold;line-height:100%;letter-spacing:normal;margin-top:0;margin-right:0;margin-bottom:20px;margin-left:0;text-align:left;">', $body);
        $body = preg_replace('/<h1 class="text-center">/', '<h1 class="text-center" style="color:#364f9d !important;display:block;font-family:Helvetica;font-size:26px;font-style:normal;font-weight:bold;line-height:100%;letter-spacing:normal;margin-top:0;margin-right:0;margin-bottom:20px;margin-left:0;text-align:center;">', $body);
        $body = preg_replace('/<h2>/', '<h2 style="color:#4664c0 !important;display:block;font-family:Helvetica;font-size:20px;font-style:normal;font-weight:bold;line-height:100%;letter-spacing:normal;margin-top:0;margin-right:0;margin-bottom:20px;margin-left:0;text-align:left;">', $body);
        $body = preg_replace('/<h2 class="text-center">/', '<h2 class="text-center" style="color:#4664c0 !important;display:block;font-family:Helvetica;font-size:20px;font-style:normal;font-weight:bold;line-height:100%;letter-spacing:normal;margin-top:0;margin-right:0;margin-bottom:20px;margin-left:0;text-align:center;">', $body);
        $body = preg_replace('/<h3>/', '<h3 style="color:#6c84cd !important;display:block;font-family:Helvetica;font-size:16px;font-style:italic;font-weight:normal;line-height:100%;letter-spacing:normal;margin-top:0;margin-right:0;margin-bottom:20px;margin-left:0;text-align:left;">', $body);
        $body = preg_replace('/<h3 class="text-center">/', '<h3 class="text-center" style="color:#6c84cd !important;display:block;font-family:Helvetica;font-size:16px;font-style:italic;font-weight:normal;line-height:100%;letter-spacing:normal;margin-top:0;margin-right:0;margin-bottom:20px;margin-left:0;text-align:center;">', $body);
        $body = preg_replace('/<h4>/', '<h4 style="color:#92a4da !important;display:block;font-family:Helvetica;font-size:14px;font-style:italic;font-weight:normal;line-height:100%;letter-spacing:normal;margin-top:0;margin-right:0;margin-bottom:20px;margin-left:0;text-align:left;">', $body);
        $body = preg_replace('/<h4 class="text-center">/', '<h4 class="text-center" style="color:#92a4da !important;display:block;font-family:Helvetica;font-size:14px;font-style:italic;font-weight:normal;line-height:100%;letter-spacing:normal;margin-top:0;margin-right:0;margin-bottom:20px;margin-left:0;text-align:center;">', $body);
        $body = preg_replace('/<p>/', '<p style="margin-top:0;margin-bottom:20px;">', $body);
        $body = preg_replace('/<p class="text-center">/', '<p class="text-center" style="margin-top:0;margin-bottom:20px;text-align:center;">', $body);
        $body = preg_replace('/<p class="text-right">/', '<p class="text-right" style="margin-top:0;margin-bottom:20px;text-align:right;">', $body);
        $body = preg_replace('/<ol>/', '<ol style="margin-top:20px;margin-bottom:0;margin-left:24px;padding:0;list-style-type:decimal;">', $body);
        $body = preg_replace('/<ul>/', '<ul style="margin-top:20px;margin-bottom:0;margin-left:24px;padding:0;list-style-type:disc;">', $body);
        $body = preg_replace('/<li>/', '<li style="margin-top:0;margin-bottom:0;margin-left:0;">', $body);
        $body = preg_replace('/<blockquote>/', '<blockquote style="margin-top:20px;margin-bottom:0;margin-left:0;margin-right:0;padding-left:14px;border-left:4px solid #808080;">', $body);
        $body = preg_replace('/<br \/>/', '<br style="margin-top:20px;" />', $body);
        $body = preg_replace('/<hr \/>/', '<hr height="1" style="height:1px; border:0 none; color: #cccccc; background-color: #cccccc; margin-top:10px; margin-bottom:10px;" />', $body);
        $body = preg_replace('/<a /', '<a style="text-decoration:underline;transition:opacity 0.1s ease-in;color:#0090d1;font-weight:normal;" ', $body);

        return $body;
    }

    public function replaceText($text)
    {
        $text = html_entity_decode(preg_replace(['/[\r\n]+[\s\t]*[\r\n]+/', '/[ \t]+/'], ["\r\n", ' '], strip_tags(preg_replace('/<br\s*\/?\s*>/Usi', "\r\n", $text)))); /* remove excessive spaces and tabs // strip blank lines (blank, with tabs or spaces)*/

        return $text;
    }
}
