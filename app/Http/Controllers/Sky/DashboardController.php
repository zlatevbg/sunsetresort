<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;

class DashboardController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function dashboard()
    {
        $profile = env('ANALYTICS_VIEW_ID');
        $startDate = '2016-10-10';
        $client = new \Google_Client();
        $client->setApplicationName('sr-owners-info-portal');
        $analytics = new \Google_Service_Analytics($client);

        $scopes = [\Google_Service_Analytics::ANALYTICS_READONLY];
        $client->setScopes($scopes);
        $client->setAuthConfig(storage_path('app/google/analytics-service-account-credentials.json'));

        $accessToken = \Cache::remember('google-analytics-token', 60, function() use ($client) {
            if ($client->isAccessTokenExpired()) {
                $client->refreshTokenWithAssertion();
            }

            return $client->getAccessToken()['access_token'];
        });

        return view(\Locales::getNamespace() . '.dashboard', compact('profile', 'startDate', 'accessToken'));
    }

}
