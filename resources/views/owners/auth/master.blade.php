<!doctype html>
<html class="no-js" lang="{{ \Locales::getCurrent() }}">
<head dir="{{ \Locales::getScript() }}">
	<meta charset="utf-8">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>{{ $page->title }}</title>
    <meta name="description" content="{{ $page->description }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="apple-touch-icon" href="{{ \App\Helpers\autover('/apple-touch-icon.png') }}">

    <link href="{{ \App\Helpers\autover('/css/' . \Locales::getNamespace() . '/main.css') }}" rel="stylesheet">

    <script>
        if (sessionStorage.robotoStageOne) {
            document.documentElement.className += ' roboto-loaded';
        }

        if (sessionStorage.robotoStageTwo) {
            document.documentElement.className += ' roboto-all-loaded';
        }
    </script>

    <script src="{{ \App\Helpers\autover('/js/' . \Locales::getNamespace() . '/vendor/modernizr-2.8.3.min.js') }}"></script>
</head>
<body id="body-auth">
    <script>(function() { var pad = '000'; var random = Math.floor((Math.random() * 298) + 1); var img = pad.substring(0, pad.length - random.toString().length) + random.toString(); document.getElementById('body-auth').style.backgroundImage = "url('/img/auth/" + img + ".jpg')"; })();</script>
    <div class="auth-wrapper">
        <a href="{{ \Locales::route('/') }}">{!! HTML::image(\App\Helpers\autover('/img/' . \Locales::getNamespace() . '/logo.png'), trans(\Locales::getNamespace() . '/messages.altLogo')) !!}</a>

        <div class="auth-box">@yield('content')</div>

        @if (count(\Locales::getLocales()) > 1)
        <div class="languages-wrapper">
            <div class="submenu">
                <a href="#" class="btn btn-default dropdown-toggle dropdown-toggle-left">
                    {!! HTML::image(\App\Helpers\autover('/img/' . \Locales::getNamespace() . '/languages.png'), trans(\Locales::getNamespace() . '/messages.changeLanguage')) !!}
                    {{ trans(\Locales::getNamespace() . '/messages.changeLanguage') }}
                    <span class="caret caret-right"></span>
                </a>
                <ul class="dropdown-menu">
                @foreach (\Locales::getLanguages() as $key => $language)
                    <li{!! $language['active'] ? ' class="active"' : '' !!}>
                        <a href="{{ $language['link'] }}">
                            {{ $language['native'] }}@if ($language['name'])<span class="sub-text">/ {{ $language['name'] }}</span>@endif
                        </a>
                    </li>
                @endforeach
                </ul>
            </div>
        </div>
        @endif

        @if ($page)
        {!! $page->content !!}
        @endif
    </div>

	<script>
    'use strict';

    Modernizr.load([
        {
            load: ['{{ \App\Helpers\autover('/js/' . \Locales::getNamespace() . '/vendor/fontfaceobserver.min.js') }}'],
            complete: function() {
                (function(doc) {
                    if (!('geolocation' in navigator) || sessionStorage.robotoStageOne && sessionStorage.robotoStageTwo) {
                        return;
                    }

                    var robotoObserver = new FontFaceObserver("Roboto", {
                        weight: 400,
                    });

                    robotoObserver.load().then(function() {
                        doc.documentElement.className += " roboto-loaded";
                        sessionStorage.robotoStageOne = true;

                        Promise.all([
                            (new FontFaceObserver("Roboto", {
                                weight: 400,
                            })).load(),
                            (new FontFaceObserver("Roboto", {
                                weight: 700,
                            })).load(),
                            (new FontFaceObserver("Roboto", {
                                weight: 400,
                                style: "italic",
                            })).load(),
                            (new FontFaceObserver("Roboto", {
                                weight: 700,
                                style: "italic",
                            })).load(),
                        ]).then(function() {
                            doc.documentElement.className += " roboto-all-loaded";
                            sessionStorage.robotoStageTwo = true;
                        }).catch(function () {
                            sessionStorage.robotoStageTwo = false;
                        });
                    }).catch(function () {
                        sessionStorage.robotoStageOne = false;
                    });
                })(document);
            }
        },
        {
            load: ['//ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js'],
            complete: function() {
                if (!window.jQuery) {
                    Modernizr.load('{{ \App\Helpers\autover('/js/' . \Locales::getNamespace() . '/vendor/jquery-2.2.0.min.js') }}');
                }
            }
        },
        {
            load: ['{{ \App\Helpers\autover('/js/' . \Locales::getNamespace() . '/main.js') }}'],
            complete: function() {
                $.extend(unikat.variables, {
                    is_auth: true,
                    ajaxErrorMessage: '{!! trans(\Locales::getNamespace() . '/js.ajaxErrorMessage') !!}',
                    loadingImageSrc: '{{ \App\Helpers\autover('/img/' . \Locales::getNamespace() . '/loading.gif') }}',
                    loadingImageAlt: '{{ trans(\Locales::getNamespace() . '/js.loadingImageAlt') }}',
                    loadingImageTitle: '{{ trans(\Locales::getNamespace() . '/js.loadingImageTitle') }}',
                    loadingText: '{{ trans(\Locales::getNamespace() . '/js.loadingText') }}',
                });

                @yield('script')

                unikat.run();
            }
        }
    ]);
    </script>

</body>
</html>
