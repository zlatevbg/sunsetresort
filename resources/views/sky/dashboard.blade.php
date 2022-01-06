@extends(\Locales::getNamespace() . '.master')

@section('content')
    @include(\Locales::getNamespace() . '/shared.errors')
    @include(\Locales::getNamespace() . '/shared.success')

    <h1>Statistics for the period: {{ \App\Helpers\localizedDate($startDate) }} - {{ \App\Helpers\localizedDate() }}</h1>
    <section class="google-analytics-wrapper">
        <div id="chart-1-container"></div>
        <article class="google-analytics-chart">
            <h1>Site Traffic <small>Users - last 30 days</small></h1>
            <div id="chart-2-container"></div>
        </article>
        <article class="google-analytics-chart">
            <h1>Top Content <small>By pageviews</small></h1>
            <div id="chart-3-container"></div>
        </article>
        <article class="google-analytics-chart">
            <h1>Top Countries <small>By sessions</small></h1>
            <div id="chart-4-container"></div>
        </article>
        <article class="google-analytics-chart">
            <h1>Top Browsers <small>By users</small></h1>
            <div id="chart-5-container"></div>
        </article>
    </section>
@endsection

@section('script')
(function(w,d,s,g,js,fs){
g=w.gapi||(w.gapi={});g.analytics={q:[],ready:function(f){this.q.push(f);}};
js=d.createElement(s);fs=d.getElementsByTagName(s)[0];
js.src='https://apis.google.com/js/platform.js';
fs.parentNode.insertBefore(js,fs);js.onload=function(){g.load('analytics');};
}(window,document,'script'));

gapi.analytics.ready(function() {

  gapi.analytics.auth.authorize({
    'serverAuth': {
      'access_token': '{{ $accessToken }}'
    }
  });

  var dataChart1 = new gapi.analytics.googleCharts.DataChart({
    query: {
      'ids': 'ga:{{ $profile }}',
      'start-date': '{{ $startDate }}',
      'end-date': 'yesterday',
      'metrics': 'ga:users,ga:sessions,ga:pageviews',
    },
    chart: {
      'container': 'chart-1-container',
      'type': 'TABLE',
      'options': {
        'width': '100%',
      }
    }
  });
  dataChart1.execute();

  var dataChart2 = new gapi.analytics.googleCharts.DataChart({
    query: {
      'ids': 'ga:{{ $profile }}',
      'start-date': '30daysAgo',
      'end-date': 'yesterday',
      'metrics': 'ga:users',
      'dimensions': 'ga:date',
    },
    chart: {
      'container': 'chart-2-container',
      'type': 'LINE',
      'options': {
        'width': '100%',
      }
    }
  });
  dataChart2.execute();

  var dataChart3 = new gapi.analytics.googleCharts.DataChart({
    query: {
      'ids': 'ga:{{ $profile }}',
      'start-date': '{{ $startDate }}',
      'end-date': 'yesterday',
      'metrics': 'ga:pageviews',
      'dimensions': 'ga:pagePath',
      'sort': '-ga:pageviews',
      'max-results': 8
    },
    chart: {
      'container': 'chart-3-container',
      'type': 'PIE',
      'options': {
        'width': '100%',
        'pieHole': 4/9,
      }
    }
  });
  dataChart3.execute();

  var dataChart4 = new gapi.analytics.googleCharts.DataChart({
    query: {
      'ids': 'ga:{{ $profile }}',
      'start-date': '{{ $startDate }}',
      'end-date': 'yesterday',
      'metrics': 'ga:sessions',
      'dimensions': 'ga:country',
      'sort': '-ga:sessions',
      'max-results': 7
    },
    chart: {
      'container': 'chart-4-container',
      'type': 'PIE',
      'options': {
        'width': '100%',
        'pieHole': 4/9,
      }
    }
  });
  dataChart4.execute();

  var dataChart5 = new gapi.analytics.googleCharts.DataChart({
    query: {
      'ids': 'ga:{{ $profile }}',
      'start-date': '{{ $startDate }}',
      'end-date': 'yesterday',
      'metrics': 'ga:users',
      'dimensions': 'ga:browser',
      'sort': '-ga:users',
      'max-results': 7
    },
    chart: {
      'container': 'chart-5-container',
      'type': 'PIE',
      'options': {
        'width': '100%',
        'pieHole': 4/9,
      }
    }
  });
  dataChart5.execute();

});
@endsection
