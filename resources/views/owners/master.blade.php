<!doctype html>
<html class="no-js" lang="{{ \Locales::getCurrent() }}">
<head dir="{{ \Locales::getScript() }}">
	<meta charset="utf-8">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>{{ $metaTitle or \Locales::getMenu(\Slug::getRouteSlug())['title'] }}</title>
    <meta name="description" content="{{ $metaDescription or \Locales::getMenu(\Slug::getRouteSlug())['description'] }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="format-detection" content="telephone=no">

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
<body x-ms-format-detection="none">
    <header>
        <div id="header-wrapper">
            @include(\Locales::getNamespace() . '/partials.header')
            @include(\Locales::getNamespace() . '/partials.nav')
        </div>
    </header>
    <main>
        <div id="main-wrapper">
            @include(\Locales::getNamespace() . '/partials.breadcrumbs')
            @if (\Auth::check())
                <div class="alert-messages impersonate">
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close"><span aria-hidden="true">&times;</span></button>
                        {{ trans(\Locales::getNamespace() . '/messages.impersonateWarning') }}
                    </div>
                </div>
            @endif

            <div id="content-wrapper">
                @include(\Locales::getNamespace() . '/shared.errors')
                @include(\Locales::getNamespace() . '/shared.success')

                @yield('content')
            </div>
        </div>
    </main>
    <footer>
        <div id="footer-wrapper">
            @include(\Locales::getNamespace() . '/partials.footer')
        </div>
    </footer>

	<script>
    'use strict';

    var jsFiles = [];
    @section('jsFiles')
        jsFiles.push('{{ \App\Helpers\autover('/js/' . \Locales::getNamespace() . '/main.js') }}');
    @show

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
            test: window.matchMedia && window.matchMedia('all').addListener,
            nope: '{{ \App\Helpers\autover('/js/' . \Locales::getNamespace() . '/vendor/matchMedia.js') }}'
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
            load: jsFiles,
            complete: function() {
                $.extend(unikat.variables, {
                    ajaxErrorMessage: '{!! trans(\Locales::getNamespace() . '/js.ajaxErrorMessage') !!}',
                    loadingImageSrc: '{{ \App\Helpers\autover('/img/' . \Locales::getNamespace() . '/loading.gif') }}',
                    loadingImageAlt: '{{ trans(\Locales::getNamespace() . '/js.loadingImageAlt') }}',
                    loadingImageTitle: '{{ trans(\Locales::getNamespace() . '/js.loadingImageTitle') }}',
                    loadingText: '{{ trans(\Locales::getNamespace() . '/js.loadingText') }}',
                    language: '{{ \Locales::getCurrent() }}',
                    defaultLanguage: '{{ \Locales::getDefault() }}',
                    languageScript: '{{ \Locales::getScript() }}',
                    headroomOffset: 120,
                    headroomMobileOffset: 62,

                    @foreach (\Lang::get(\Locales::getNamespace() . '/plugins') as $plugin => $data)
                        {{ $plugin }}: {
                            @foreach (\Lang::get(\Locales::getNamespace() . '/plugins.' . $plugin) as $key => $value)
                                @if (is_array($value))
                                {{ $key }}: {
                                    @foreach (\Lang::get(\Locales::getNamespace() . '/plugins.' . $plugin . '.' . $key) as $subKey => $subValue)
                                        {{ $subKey }}: {!! json_encode($subValue) !!},
                                    @endforeach
                                },
                                @else
                                {{ $key }}: '{!! $value !!}',
                                @endif
                            @endforeach
                        },
                    @endforeach

                    @if (isset($datatables) && count($datatables) > 0)
                        datatables: true,
                        datatablesLanguage: '{{ \App\Helpers\autover('/lng/datatables/' . \Locales::getCurrent() . '.json') }}',
                        datatablesPipeline: {{ \Config::get('datatables.pipeline') }},
                        datatablesSearchDelay: {{ \Config::get('datatables.searchDelay') }},
                        datatablesPaging: {{ \Config::get('datatables.paging') }},
                        datatablesPagingType: {
                            @foreach (\Config::get('datatables.sizes') as $size)
                                {{ $size }}: '{{ \Config::get('datatables.pagingType.' . $size) }}',
                            @endforeach
                        },
                        datatablesPageLength: {
                            @foreach (\Config::get('datatables.sizes') as $size)
                                {{ $size }}: {{ \Config::get('datatables.pageLength.' . $size) }},
                            @endforeach
                        },
                        datatablesLengthMenu: {
                            @foreach (\Config::get('datatables.sizes') as $size)
                                {{ $size }}: {!! str_replace('all', trans(\Locales::getNamespace() . '/messages.all'), \Config::get('datatables.lengthMenu.' . $size)) !!},
                            @endforeach
                        },
                    @endif
                });

                @yield('script')

                unikat.run();
            }
        }
    ]);
    </script>
    @yield('scripts')
    <script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
      ga('create', 'UA-10298608-2', 'auto');
      ga('send', 'pageview');
    </script>

</body>
</html>
