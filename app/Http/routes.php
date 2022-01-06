<?php

/*
|--------------------------------------------------------------------------
| Dynamic Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

foreach (\Locales::getDomains() as $domain) {
    if ($domain->domain == env('APP_SKY_SUBDOMAIN')) {
        \Locales::setRoutesDomain($domain->domain);

        Route::group(['domain' => $domain->domain . '.' . config('app.domain'), 'namespace' => studly_case($domain->domain)], function () use ($domain) {

            foreach ($domain->locales as $locale) {
                \Locales::setRoutesLocale($locale->locale);

                Route::group(['middleware' => 'guest:admins'], function () {
                    Route::get(\Locales::getRoute('/'), 'AuthController@getLogin')->name(\Locales::getRoutePrefix('/'));
                    Route::post(\Locales::getRoute('/'), 'AuthController@postLogin');

                    Route::get(\Locales::getRoute('pf'), 'PasswordController@getEmail')->name(\Locales::getRoutePrefix('pf'));
                    Route::post(\Locales::getRoute('pf'), 'PasswordController@postEmail');

                    Route::get(\Locales::getRoute('reset') . '/{token}', 'PasswordController@getReset')->name(\Locales::getRoutePrefix('reset'));
                    Route::post(\Locales::getRoute('reset'), 'PasswordController@postReset')->name(\Locales::getRoutePrefix('reset-post'));
                });

                Route::group(['middleware' => 'auth:admins'], function () {
                    Route::get(\Locales::getRoute('signout'), 'AuthController@getLogout')->name(\Locales::getRoutePrefix('signout'));

                    /*Route::get('import/taxes', 'ImportController@taxes')->name(\Locales::getRoutePrefix('import/taxes'));
                    Route::get('import/rooms', 'ImportController@rooms')->name(\Locales::getRoutePrefix('import/rooms'));
                    Route::get('import/furniture', 'ImportController@furniture')->name(\Locales::getRoutePrefix('import/furniture'));
                    Route::get('import/views', 'ImportController@views')->name(\Locales::getRoutePrefix('import/views'));
                    Route::get('import/buildings', 'ImportController@buildings')->name(\Locales::getRoutePrefix('import/buildings'));
                    Route::get('import/floors', 'ImportController@floors')->name(\Locales::getRoutePrefix('import/floors'));
                    Route::get('import/countries', 'ImportController@countries')->name(\Locales::getRoutePrefix('import/countries'));
                    Route::get('import/owners', 'ImportController@owners')->name(\Locales::getRoutePrefix('import/owners'));
                    Route::get('import/apartments', 'ImportController@apartments')->name(\Locales::getRoutePrefix('import/apartments'));
                    Route::get('import/agent-access', 'ImportController@agentAccess')->name(\Locales::getRoutePrefix('import/agent-access'));
                    Route::get('import/keyholder-access', 'ImportController@keyholderAccess')->name(\Locales::getRoutePrefix('import/keyholder-access'));
                    Route::get('import/keyholders', 'ImportController@keyholders')->name(\Locales::getRoutePrefix('import/keyholders'));
                    Route::get('import/bank-accounts', 'ImportController@bankAccounts')->name(\Locales::getRoutePrefix('import/bank-accounts'));
                    Route::get('import/ownership', 'ImportController@ownership')->name(\Locales::getRoutePrefix('import/ownership'));
                    Route::get('import/years', 'ImportController@years')->name(\Locales::getRoutePrefix('import/years'));
                    Route::get('import/buildings-mm', 'ImportController@buildingsMm')->name(\Locales::getRoutePrefix('import/buildings-mm'));
                    Route::get('import/deductions', 'ImportController@deductions')->name(\Locales::getRoutePrefix('import/deductions'));
                    Route::get('import/payment-methods', 'ImportController@paymentMethods')->name(\Locales::getRoutePrefix('import/payment-methods'));
                    Route::get('import/rental-companies', 'ImportController@rentalCompanies')->name(\Locales::getRoutePrefix('import/rental-companies'));
                    Route::get('import/rental-contracts', 'ImportController@rentalContracts')->name(\Locales::getRoutePrefix('import/rental-contracts'));
                    Route::get('import/mm', 'ImportController@mm')->name(\Locales::getRoutePrefix('import/mm'));
                    Route::get('import/signatures', 'ImportController@signatures')->name(\Locales::getRoutePrefix('import/signatures'));
                    Route::get('import/recipients', 'ImportController@recipients')->name(\Locales::getRoutePrefix('import/recipients'));
                    Route::get('import/bookings', 'ImportController@bookings')->name(\Locales::getRoutePrefix('import/bookings'));*/

                    Route::get(\Locales::getRoute('reports/bookings'), 'ReportBookingsController@index')->name(\Locales::getRoutePrefix('reports/bookings'));
                    Route::post('reports/bookings/generate', 'ReportBookingsController@generate')->name(\Locales::getRoutePrefix('reports/bookings/generate'));
                    Route::get('reports/bookings/download', 'ReportBookingsController@download')->name(\Locales::getRoutePrefix('reports/bookings/download'));
                    Route::get('reports/bookings/get-buildings', 'ReportBookingsController@getBuildings')->name(\Locales::getRoutePrefix('reports/bookings/get-buildings'));
                    Route::get('reports/bookings/get-apartments', 'ReportBookingsController@getApartments')->name(\Locales::getRoutePrefix('reports/bookings/get-apartments'));

                    Route::get(\Locales::getRoute('reports/availability'), 'ReportAvailabilityController@index')->name(\Locales::getRoutePrefix('reports/availability'));
                    Route::post('reports/availability/generate', 'ReportAvailabilityController@generate')->name(\Locales::getRoutePrefix('reports/availability/generate'));
                    Route::get('reports/availability/download', 'ReportAvailabilityController@download')->name(\Locales::getRoutePrefix('reports/availability/download'));
                    Route::get('reports/availability/get-buildings', 'ReportAvailabilityController@getBuildings')->name(\Locales::getRoutePrefix('reports/availability/get-buildings'));
                    Route::get('reports/availability/get-apartments', 'ReportAvailabilityController@getApartments')->name(\Locales::getRoutePrefix('reports/availability/get-apartments'));

                    Route::get(\Locales::getRoute('reports/key-log'), 'ReportKeyLogController@index')->name(\Locales::getRoutePrefix('reports/key-log'));
                    Route::post('reports/key-log/generate', 'ReportKeyLogController@generate')->name(\Locales::getRoutePrefix('reports/key-log/generate'));
                    Route::get('reports/key-log/download', 'ReportKeyLogController@download')->name(\Locales::getRoutePrefix('reports/key-log/download'));
                    Route::get('reports/key-log/get-buildings', 'ReportKeyLogController@getBuildings')->name(\Locales::getRoutePrefix('reports/key-log/get-buildings'));
                    Route::get('reports/key-log/get-apartments', 'ReportKeyLogController@getApartments')->name(\Locales::getRoutePrefix('reports/key-log/get-apartments'));

                    Route::get(\Locales::getRoute('reports/mm-fees'), 'ReportMmFeesController@index')->name(\Locales::getRoutePrefix('reports/mm-fees'));
                    Route::post('reports/mm-fees/generate', 'ReportMmFeesController@generate')->name(\Locales::getRoutePrefix('reports/mm-fees/generate'));
                    Route::get('reports/mm-fees/download', 'ReportMmFeesController@download')->name(\Locales::getRoutePrefix('reports/mm-fees/download'));
                    Route::get('reports/mm-fees/get-buildings', 'ReportMmFeesController@getBuildings')->name(\Locales::getRoutePrefix('reports/mm-fees/get-buildings'));
                    Route::get('reports/mm-fees/get-apartments', 'ReportMmFeesController@getApartments')->name(\Locales::getRoutePrefix('reports/mm-fees/get-apartments'));

                    Route::get(\Locales::getRoute('reports/communal-fees'), 'ReportCommunalFeesController@index')->name(\Locales::getRoutePrefix('reports/communal-fees'));
                    Route::post('reports/communal-fees/generate', 'ReportCommunalFeesController@generate')->name(\Locales::getRoutePrefix('reports/communal-fees/generate'));
                    Route::get('reports/communal-fees/download', 'ReportCommunalFeesController@download')->name(\Locales::getRoutePrefix('reports/communal-fees/download'));
                    Route::get('reports/communal-fees/get-buildings', 'ReportCommunalFeesController@getBuildings')->name(\Locales::getRoutePrefix('reports/communal-fees/get-buildings'));
                    Route::get('reports/communal-fees/get-apartments', 'ReportCommunalFeesController@getApartments')->name(\Locales::getRoutePrefix('reports/communal-fees/get-apartments'));

                    Route::get(\Locales::getRoute('reports/pool-usage'), 'ReportPoolUsageController@index')->name(\Locales::getRoutePrefix('reports/pool-usage'));
                    Route::post('reports/pool-usage/generate', 'ReportPoolUsageController@generate')->name(\Locales::getRoutePrefix('reports/pool-usage/generate'));
                    Route::get('reports/pool-usage/download', 'ReportPoolUsageController@download')->name(\Locales::getRoutePrefix('reports/pool-usage/download'));
                    Route::get('reports/pool-usage/get-buildings', 'ReportPoolUsageController@getBuildings')->name(\Locales::getRoutePrefix('reports/pool-usage/get-buildings'));
                    Route::get('reports/pool-usage/get-apartments', 'ReportPoolUsageController@getApartments')->name(\Locales::getRoutePrefix('reports/pool-usage/get-apartments'));

                    Route::get(\Locales::getRoute('reports/rental-payments'), 'ReportRentalPaymentsController@index')->name(\Locales::getRoutePrefix('reports/rental-payments'));
                    Route::post('reports/rental-payments/generate', 'ReportRentalPaymentsController@generate')->name(\Locales::getRoutePrefix('reports/rental-payments/generate'));
                    Route::get('reports/rental-payments/download', 'ReportRentalPaymentsController@download')->name(\Locales::getRoutePrefix('reports/rental-payments/download'));
                    Route::get('reports/rental-payments/get-buildings', 'ReportRentalPaymentsController@getBuildings')->name(\Locales::getRoutePrefix('reports/rental-payments/get-buildings'));
                    Route::get('reports/rental-payments/get-apartments', 'ReportRentalPaymentsController@getApartments')->name(\Locales::getRoutePrefix('reports/rental-payments/get-apartments'));
                    Route::get('reports/rental-payments/get-rental-options', 'ReportRentalPaymentsController@getRentalOptions')->name(\Locales::getRoutePrefix('reports/rental-payments/get-rental-options'));

                    Route::get(\Locales::getRoute('reports/rental-pool'), 'ReportRentalPoolController@index')->name(\Locales::getRoutePrefix('reports/rental-pool'));
                    Route::post('reports/rental-pool/generate', 'ReportRentalPoolController@generate')->name(\Locales::getRoutePrefix('reports/rental-pool/generate'));
                    Route::get('reports/rental-pool/download', 'ReportRentalPoolController@download')->name(\Locales::getRoutePrefix('reports/rental-pool/download'));
                    Route::get('reports/rental-pool/get-buildings', 'ReportRentalPoolController@getBuildings')->name(\Locales::getRoutePrefix('reports/rental-pool/get-buildings'));
                    Route::get('reports/rental-pool/get-apartments', 'ReportRentalPoolController@getApartments')->name(\Locales::getRoutePrefix('reports/rental-pool/get-apartments'));
                    Route::get('reports/rental-pool/get-rental-options', 'ReportRentalPoolController@getRentalOptions')->name(\Locales::getRoutePrefix('reports/rental-pool/get-rental-options'));

                    Route::get(\Locales::getRoute('reports/poa'), 'ReportPoaController@index')->name(\Locales::getRoutePrefix('reports/poa'));
                    Route::post('reports/poa/generate', 'ReportPoaController@generate')->name(\Locales::getRoutePrefix('reports/poa/generate'));
                    Route::get('reports/poa/download', 'ReportPoaController@download')->name(\Locales::getRoutePrefix('reports/poa/download'));
                    Route::get('reports/poa/get-buildings', 'ReportPoaController@getBuildings')->name(\Locales::getRoutePrefix('reports/poa/get-buildings'));
                    Route::get('reports/poa/get-apartments', 'ReportPoaController@getApartments')->name(\Locales::getRoutePrefix('reports/poa/get-apartments'));

                    Route::get(\Locales::getRoute('reports/bank-accounts'), 'ReportBankAccountsController@index')->name(\Locales::getRoutePrefix('reports/bank-accounts'));
                    Route::post('reports/bank-accounts/generate', 'ReportBankAccountsController@generate')->name(\Locales::getRoutePrefix('reports/bank-accounts/generate'));
                    Route::get('reports/bank-accounts/download', 'ReportBankAccountsController@download')->name(\Locales::getRoutePrefix('reports/bank-accounts/download'));
                    Route::get('reports/bank-accounts/get-buildings', 'ReportBankAccountsController@getBuildings')->name(\Locales::getRoutePrefix('reports/bank-accounts/get-buildings'));
                    Route::get('reports/bank-accounts/get-apartments', 'ReportBankAccountsController@getApartments')->name(\Locales::getRoutePrefix('reports/bank-accounts/get-apartments'));

                    Route::get(\Locales::getRoute('reports/owners'), 'ReportOwnersController@index')->name(\Locales::getRoutePrefix('reports/owners'));
                    Route::post('reports/owners/generate', 'ReportOwnersController@generate')->name(\Locales::getRoutePrefix('reports/owners/generate'));
                    Route::get('reports/owners/download', 'ReportOwnersController@download')->name(\Locales::getRoutePrefix('reports/owners/download'));
                    Route::get('reports/owners/get-buildings', 'ReportOwnersController@getBuildings')->name(\Locales::getRoutePrefix('reports/owners/get-buildings'));
                    Route::get('reports/owners/get-apartments', 'ReportOwnersController@getApartments')->name(\Locales::getRoutePrefix('reports/owners/get-apartments'));

                    Route::get(\Locales::getRoute('reports/agents'), 'ReportAgentsController@index')->name(\Locales::getRoutePrefix('reports/agents'));
                    Route::post('reports/agents/generate', 'ReportAgentsController@generate')->name(\Locales::getRoutePrefix('reports/agents/generate'));
                    Route::get('reports/agents/download', 'ReportAgentsController@download')->name(\Locales::getRoutePrefix('reports/agents/download'));
                    Route::get('reports/agents/get-buildings', 'ReportAgentsController@getBuildings')->name(\Locales::getRoutePrefix('reports/agents/get-buildings'));
                    Route::get('reports/agents/get-apartments', 'ReportAgentsController@getApartments')->name(\Locales::getRoutePrefix('reports/agents/get-apartments'));

                    Route::get(\Locales::getRoute('reports/keyholders'), 'ReportKeyholdersController@index')->name(\Locales::getRoutePrefix('reports/keyholders'));
                    Route::post('reports/keyholders/generate', 'ReportKeyholdersController@generate')->name(\Locales::getRoutePrefix('reports/keyholders/generate'));
                    Route::get('reports/keyholders/download', 'ReportKeyholdersController@download')->name(\Locales::getRoutePrefix('reports/keyholders/download'));
                    Route::get('reports/keyholders/get-buildings', 'ReportKeyholdersController@getBuildings')->name(\Locales::getRoutePrefix('reports/keyholders/get-buildings'));
                    Route::get('reports/keyholders/get-apartments', 'ReportKeyholdersController@getApartments')->name(\Locales::getRoutePrefix('reports/keyholders/get-apartments'));

                    Route::get(\Locales::getRoute('reports/ownership'), 'ReportOwnershipController@index')->name(\Locales::getRoutePrefix('reports/ownership'));
                    Route::post('reports/ownership/generate', 'ReportOwnershipController@generate')->name(\Locales::getRoutePrefix('reports/ownership/generate'));
                    Route::get('reports/ownership/download', 'ReportOwnershipController@download')->name(\Locales::getRoutePrefix('reports/ownership/download'));
                    Route::get('reports/ownership/get-buildings', 'ReportOwnershipController@getBuildings')->name(\Locales::getRoutePrefix('reports/ownership/get-buildings'));
                    Route::get('reports/ownership/get-apartments', 'ReportOwnershipController@getApartments')->name(\Locales::getRoutePrefix('reports/ownership/get-apartments'));

                    Route::get(\Locales::getRoute('reportslegal-representative'), 'ReportLegalRepresentativesController@index')->name(\Locales::getRoutePrefix('reports/legal-representatives'));
                    Route::post('reports/legal-representatives/generate', 'ReportLegalRepresentativesController@generate')->name(\Locales::getRoutePrefix('reports/legal-representatives/generate'));
                    Route::get('reports/legal-representatives/download', 'ReportLegalRepresentativesController@download')->name(\Locales::getRoutePrefix('reports/legal-representatives/download'));
                    Route::get('reports/legal-representatives/get-buildings', 'ReportLegalRepresentativesController@getBuildings')->name(\Locales::getRoutePrefix('reports/legal-representatives/get-buildings'));
                    Route::get('reports/legal-representatives/get-apartments', 'ReportLegalRepresentativesController@getApartments')->name(\Locales::getRoutePrefix('reports/legal-representatives/get-apartments'));

                    Route::get(\Locales::getRoute('reports/maintenance-issues'), 'ReportMaintenanceIssuesController@index')->name(\Locales::getRoutePrefix('reports/maintenance-issues'));
                    Route::post('reports/maintenance-issues/generate', 'ReportMaintenanceIssuesController@generate')->name(\Locales::getRoutePrefix('reports/maintenance-issues/generate'));
                    Route::get('reports/maintenance-issues/download', 'ReportMaintenanceIssuesController@download')->name(\Locales::getRoutePrefix('reports/maintenance-issues/download'));
                    Route::get('reports/maintenance-issues/get-buildings', 'ReportMaintenanceIssuesController@getBuildings')->name(\Locales::getRoutePrefix('reports/maintenance-issues/get-buildings'));
                    Route::get('reports/maintenance-issues/get-apartments', 'ReportMaintenanceIssuesController@getApartments')->name(\Locales::getRoutePrefix('reports/maintenance-issues/get-apartments'));

                    Route::get(\Locales::getRoute('reports/rental-contracts-tracker'), 'ReportRentalContractTrackerController@index')->name(\Locales::getRoutePrefix('reports/rental-contracts-tracker'));
                    Route::post('reports/rental-contracts-tracker/generate', 'ReportRentalContractTrackerController@generate')->name(\Locales::getRoutePrefix('reports/rental-contracts-tracker/generate'));
                    Route::get('reports/rental-contracts-tracker/download', 'ReportRentalContractTrackerController@download')->name(\Locales::getRoutePrefix('reports/rental-contracts-tracker/download'));
                    Route::get('reports/rental-contracts-tracker/get-buildings', 'ReportRentalContractTrackerController@getBuildings')->name(\Locales::getRoutePrefix('reports/rental-contracts-tracker/get-buildings'));
                    Route::get('reports/rental-contracts-tracker/get-apartments', 'ReportRentalContractTrackerController@getApartments')->name(\Locales::getRoutePrefix('reports/rental-contracts-tracker/get-apartments'));

                    Route::get(\Locales::getRoute('reports/communal-fee-contracts-tracker'), 'ReportCommunalFeeContractTrackerController@index')->name(\Locales::getRoutePrefix('reports/communal-fee-contracts-tracker'));
                    Route::post('reports/communal-fee-contracts-tracker/generate', 'ReportCommunalFeeContractTrackerController@generate')->name(\Locales::getRoutePrefix('reports/communal-fee-contracts-tracker/generate'));
                    Route::get('reports/communal-fee-contracts-tracker/download', 'ReportCommunalFeeContractTrackerController@download')->name(\Locales::getRoutePrefix('reports/communal-fee-contracts-tracker/download'));
                    Route::get('reports/communal-fee-contracts-tracker/get-buildings', 'ReportCommunalFeeContractTrackerController@getBuildings')->name(\Locales::getRoutePrefix('reports/communal-fee-contracts-tracker/get-buildings'));
                    Route::get('reports/communal-fee-contracts-tracker/get-apartments', 'ReportCommunalFeeContractTrackerController@getApartments')->name(\Locales::getRoutePrefix('reports/communal-fee-contracts-tracker/get-apartments'));

                    Route::get(\Locales::getRoute('reports/pool-usage-contracts-tracker'), 'ReportPoolUsageContractTrackerController@index')->name(\Locales::getRoutePrefix('reports/pool-usage-contracts-tracker'));
                    Route::post('reports/pool-usage-contracts-tracker/generate', 'ReportPoolUsageContractTrackerController@generate')->name(\Locales::getRoutePrefix('reports/pool-usage-contracts-tracker/generate'));
                    Route::get('reports/pool-usage-contracts-tracker/download', 'ReportPoolUsageContractTrackerController@download')->name(\Locales::getRoutePrefix('reports/pool-usage-contracts-tracker/download'));
                    Route::get('reports/pool-usage-contracts-tracker/get-buildings', 'ReportPoolUsageContractTrackerController@getBuildings')->name(\Locales::getRoutePrefix('reports/pool-usage-contracts-tracker/get-buildings'));
                    Route::get('reports/pool-usage-contracts-tracker/get-apartments', 'ReportPoolUsageContractTrackerController@getApartments')->name(\Locales::getRoutePrefix('reports/pool-usage-contracts-tracker/get-apartments'));

                    Route::get(\Locales::getRoute('dashboard'), 'DashboardController@dashboard')->name(\Locales::getRoutePrefix('dashboard'));
                    Route::get(\Locales::getRoute('mailgun'), 'MailgunController@mailgun')->name(\Locales::getRoutePrefix('mailgun'));

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('admins/create') ? Route::get(\Locales::getRoute('admins/create'), 'AdminController@create')->name(\Locales::getRoutePrefix('admins/create')) : '';
                        Route::post('admins/store', 'AdminController@store')->name(\Locales::getRoutePrefix('admins/store'));
                        \Locales::isTranslatedRoute('admins/edit') ? Route::get(\Locales::getRoute('admins/edit') . '/{id?}', 'AdminController@edit')->name(\Locales::getRoutePrefix('admins/edit'))->where('id', '[0-9]+') : '';
                        Route::put('admins/update', 'AdminController@update')->name(\Locales::getRoutePrefix('admins/update'));
                        \Locales::isTranslatedRoute('admins/delete') ? Route::get(\Locales::getRoute('admins/delete'), 'AdminController@delete')->name(\Locales::getRoutePrefix('admins/delete')) : '';
                        Route::delete('admins/destroy', 'AdminController@destroy')->name(\Locales::getRoutePrefix('admins/destroy'));
                    });
                    \Locales::isTranslatedRoute('admins') ? Route::get(\Locales::getRoute('admins'), 'AdminController@index')->name(\Locales::getRoutePrefix('admins')) : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('owners/create') ? Route::get(\Locales::getRoute('owners/create'), 'OwnerController@create')->name(\Locales::getRoutePrefix('owners/create')) : '';
                        Route::post('owners/store', 'OwnerController@store')->name(\Locales::getRoutePrefix('owners/store'));
                        \Locales::isTranslatedRoute('owners/edit') ? Route::get(\Locales::getRoute('owners/edit') . '/{owner?}', 'OwnerController@edit')->name(\Locales::getRoutePrefix('owners/edit'))->where('owner', '[0-9]+') : '';
                        Route::put('owners/update', 'OwnerController@update')->name(\Locales::getRoutePrefix('owners/update'));
                        \Locales::isTranslatedRoute('owners/delete') ? Route::get(\Locales::getRoute('owners/delete'), 'OwnerController@delete')->name(\Locales::getRoutePrefix('owners/delete')) : '';
                        Route::delete('owners/destroy', 'OwnerController@destroy')->name(\Locales::getRoutePrefix('owners/destroy'));
                        Route::get('owners/change-status/{id}/{status}', 'OwnerController@changeStatus')->name(\Locales::getRoutePrefix('owners/change-status'))->where('id', '[0-9]+')->where('status', '[0-9]+');
                        Route::get('owners/send-profile/{id?}', 'OwnerController@sendProfile')->name(\Locales::getRoutePrefix('owners/send-profile'))->where('id', '[0-9]+');
                    });
                    \Locales::isTranslatedRoute('owners') ? Route::get(\Locales::getRoute('owners') . '/{id?}', 'OwnerController@index')->name(\Locales::getRoutePrefix('owners'))->where('id', '[0-9]+') : '';
                    Route::get('owners/impersonate/{id}', 'OwnerController@impersonate')->name(\Locales::getRoutePrefix('owners/impersonate'))->where('id', '[0-9]+');

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('apartments/create') ? Route::get(\Locales::getRoute('apartments/create'), 'ApartmentController@create')->name(\Locales::getRoutePrefix('apartments/create')) : '';
                        Route::post('apartments/store', 'ApartmentController@store')->name(\Locales::getRoutePrefix('apartments/store'));
                        \Locales::isTranslatedRoute('apartments/edit') ? Route::get(\Locales::getRoute('apartments/edit') . '/{apartment?}', 'ApartmentController@edit')->name(\Locales::getRoutePrefix('apartments/edit'))->where('apartment', '[0-9]+') : '';
                        Route::put('apartments/update', 'ApartmentController@update')->name(\Locales::getRoutePrefix('apartments/update'));
                        \Locales::isTranslatedRoute('apartments/delete') ? Route::get(\Locales::getRoute('apartments/delete'), 'ApartmentController@delete')->name(\Locales::getRoutePrefix('apartments/delete')) : '';
                        Route::delete('apartments/destroy', 'ApartmentController@destroy')->name(\Locales::getRoutePrefix('apartments/destroy'));
                        Route::get('apartments/get-buildings/{project?}', 'ApartmentController@getBuildings')->name(\Locales::getRoutePrefix('apartments/get-buildings'))->where('project', '[0-9]+');
                        Route::get('apartments/get-floors/{building?}', 'ApartmentController@getFloors')->name(\Locales::getRoutePrefix('apartments/get-floors'))->where('building', '[0-9]+');
                    });
                    \Locales::isTranslatedRoute('apartments') ? Route::get(\Locales::getRoute('apartments') . '/{id?}', 'ApartmentController@index')->name(\Locales::getRoutePrefix('apartments'))->where('id', '[0-9]+') : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('owner-apartments/add') ? Route::get(\Locales::getRoute('owner-apartments/add'), 'OwnerApartmentsController@add')->name(\Locales::getRoutePrefix('owner-apartments/add')) : '';
                        Route::post('owner-apartments/save', 'OwnerApartmentsController@save')->name(\Locales::getRoutePrefix('owner-apartments/save'));
                        \Locales::isTranslatedRoute('owner-apartments/remove') ? Route::get(\Locales::getRoute('owner-apartments/remove'), 'OwnerApartmentsController@remove')->name(\Locales::getRoutePrefix('owner-apartments/remove')) : '';
                        Route::delete('owner-apartments/destroy', 'OwnerApartmentsController@destroy')->name(\Locales::getRoutePrefix('owner-apartments/destroy'));
                        Route::get('owner-apartments/get-buildings/{owner?}/{project?}', 'OwnerApartmentsController@getBuildings')->name(\Locales::getRoutePrefix('owner-apartments/get-buildings'))->where('owner', '[0-9]+')->where('project', '[0-9]+');
                        Route::get('owner-apartments/get-floors/{owner?}/{building?}', 'OwnerApartmentsController@getFloors')->name(\Locales::getRoutePrefix('owner-apartments/get-floors'))->where('owner', '[0-9]+')->where('building', '[0-9]+');
                        Route::get('owner-apartments/get-apartments/{owner?}/{floor?}', 'OwnerApartmentsController@getApartments')->name(\Locales::getRoutePrefix('owner-apartments/get-apartments'))->where('owner', '[0-9]+')->where('floor', '[0-9]+');
                    });
                    \Locales::isTranslatedRoute('owner-apartments') ? Route::get(\Locales::getRoute('owner-apartments') . '/{id?}/apartments', 'OwnerApartmentsController@index')->name(\Locales::getRoutePrefix('owner-apartments'))->where('id', '[0-9]+') : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('owner-former-apartments/remove') ? Route::get(\Locales::getRoute('owner-former-apartments/remove'), 'OwnerFormerApartmentsController@remove')->name(\Locales::getRoutePrefix('owner-former-apartments/remove')) : '';
                        Route::delete('owner-former-apartments/destroy', 'OwnerFormerApartmentsController@destroy')->name(\Locales::getRoutePrefix('owner-former-apartments/destroy'));
                    });
                    \Locales::isTranslatedRoute('owner-former-apartments') ? Route::get(\Locales::getRoute('owner-former-apartments') . '/{id?}/former-apartments', 'OwnerFormerApartmentsController@index')->name(\Locales::getRoutePrefix('owner-former-apartments'))->where('id', '[0-9]+') : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('bank-accounts/add') ? Route::get(\Locales::getRoute('bank-accounts/add'), 'BankAccountsController@add')->name(\Locales::getRoutePrefix('bank-accounts/add')) : '';
                        Route::post('bank-accounts/save', 'BankAccountsController@save')->name(\Locales::getRoutePrefix('bank-accounts/save'));
                        \Locales::isTranslatedRoute('bank-accounts/edit') ? Route::get(\Locales::getRoute('bank-accounts/edit') . '/{id?}', 'BankAccountsController@edit')->name(\Locales::getRoutePrefix('bank-accounts/edit'))->where('id', '[0-9]+') : '';
                        Route::put('bank-accounts/update', 'BankAccountsController@update')->name(\Locales::getRoutePrefix('bank-accounts/update'));
                        \Locales::isTranslatedRoute('bank-accounts/remove') ? Route::get(\Locales::getRoute('bank-accounts/remove'), 'BankAccountsController@remove')->name(\Locales::getRoutePrefix('bank-accounts/remove')) : '';
                        Route::delete('bank-accounts/destroy', 'BankAccountsController@destroy')->name(\Locales::getRoutePrefix('bank-accounts/destroy'));
                    });
                    \Locales::isTranslatedRoute('bank-accounts') ? Route::get(\Locales::getRoute('bank-accounts') . '/{id?}/bank-accounts', 'BankAccountsController@index')->name(\Locales::getRoutePrefix('bank-accounts'))->where('id', '[0-9]+') : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('council-tax/add') ? Route::get(\Locales::getRoute('council-tax/add'), 'CouncilTaxController@add')->name(\Locales::getRoutePrefix('council-tax/add')) : '';
                        Route::post('council-tax/save', 'CouncilTaxController@save')->name(\Locales::getRoutePrefix('council-tax/save'));
                        \Locales::isTranslatedRoute('council-tax/edit') ? Route::get(\Locales::getRoute('council-tax/edit') . '/{id?}', 'CouncilTaxController@edit')->name(\Locales::getRoutePrefix('council-tax/edit'))->where('id', '[0-9]+') : '';
                        Route::put('council-tax/update', 'CouncilTaxController@update')->name(\Locales::getRoutePrefix('council-tax/update'));
                        \Locales::isTranslatedRoute('council-tax/remove') ? Route::get(\Locales::getRoute('council-tax/remove'), 'CouncilTaxController@remove')->name(\Locales::getRoutePrefix('council-tax/remove')) : '';
                        Route::delete('council-tax/destroy', 'CouncilTaxController@destroy')->name(\Locales::getRoutePrefix('council-tax/destroy'));
                    });
                    \Locales::isTranslatedRoute('council-tax') ? Route::get(\Locales::getRoute('council-tax') . '/{id?}/council-tax', 'CouncilTaxController@index')->name(\Locales::getRoutePrefix('council-tax'))->where('id', '[0-9]+') : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('apartment-owners/add') ? Route::get(\Locales::getRoute('apartment-owners/add'), 'ApartmentOwnersController@add')->name(\Locales::getRoutePrefix('apartment-owners/add')) : '';
                        Route::post('apartment-owners/save', 'ApartmentOwnersController@save')->name(\Locales::getRoutePrefix('apartment-owners/save'));
                        \Locales::isTranslatedRoute('apartment-owners/remove') ? Route::get(\Locales::getRoute('apartment-owners/remove'), 'ApartmentOwnersController@remove')->name(\Locales::getRoutePrefix('apartment-owners/remove')) : '';
                        Route::delete('apartment-owners/destroy', 'ApartmentOwnersController@destroy')->name(\Locales::getRoutePrefix('apartment-owners/destroy'));
                    });
                    \Locales::isTranslatedRoute('apartment-owners') ? Route::get(\Locales::getRoute('apartment-owners') . '/{id?}/owners', 'ApartmentOwnersController@index')->name(\Locales::getRoutePrefix('apartment-owners'))->where('id', '[0-9]+') : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('contracts/add') ? Route::get(\Locales::getRoute('contracts/add'), 'ContractController@add')->name(\Locales::getRoutePrefix('contracts/add')) : '';
                        Route::post('contracts/save', 'ContractController@save')->name(\Locales::getRoutePrefix('contracts/save'));
                        \Locales::isTranslatedRoute('contracts/edit') ? Route::get(\Locales::getRoute('contracts/edit') . '/{id?}', 'ContractController@edit')->name(\Locales::getRoutePrefix('contracts/edit'))->where('id', '[0-9]+') : '';
                        Route::put('contracts/update', 'ContractController@update')->name(\Locales::getRoutePrefix('contracts/update'));
                        \Locales::isTranslatedRoute('contracts/cancel') ? Route::get(\Locales::getRoute('contracts/cancel'), 'ContractController@cancel')->name(\Locales::getRoutePrefix('contracts/cancel')) : '';
                        Route::delete('contracts/destroy', 'ContractController@destroy')->name(\Locales::getRoutePrefix('contracts/destroy'));
                    });
                    \Locales::isTranslatedRoute('contracts') ? Route::get(\Locales::getRoute('apartments') . '/{apartment?}/{contractsSlug?}', 'ContractController@index')->name(\Locales::getRoutePrefix('contracts'))->where('apartment', '[0-9]+')->where('contractsSlug', 'contracts') : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('contract-years/edit') ? Route::get(\Locales::getRoute('contract-years/edit') . '/{id?}', 'ContractYearController@edit')->name(\Locales::getRoutePrefix('contract-years/edit'))->where('id', '[0-9]+') : '';
                        Route::put('contract-years/update', 'ContractYearController@update')->name(\Locales::getRoutePrefix('contract-years/update'));
                    });
                    \Locales::isTranslatedRoute('contract-years') ? Route::get(\Locales::getRoute('apartments') . '/{apartment?}/{contractsSlug?}/{contract?}/{yearsSlug?}/{year?}', 'ContractYearController@index')->name(\Locales::getRoutePrefix('contract-years'))->where('apartment', '[0-9]+')->where('contractsSlug', 'contracts')->where('contract', '[0-9]+')->where('yearsSlug', 'years')->where('year', '[0-9]+') : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('contract-documents/delete') ? Route::get(\Locales::getRoute('contract-documents/delete'), 'ContractDocumentsController@delete')->name(\Locales::getRoutePrefix('contract-documents/delete')) : '';
                        Route::delete('contract-documents/destroy', 'ContractDocumentsController@destroy')->name(\Locales::getRoutePrefix('contract-documents/destroy'));
                        \Locales::isTranslatedRoute('contract-documents/edit') ? Route::get(\Locales::getRoute('contract-documents/edit') . '/{file?}', 'ContractDocumentsController@edit')->name(\Locales::getRoutePrefix('contract-documents/edit'))->where('file', '[0-9]+') : '';
                        Route::put('contract-documents/update', 'ContractDocumentsController@update')->name(\Locales::getRoutePrefix('contract-documents/update'));
                    });
                    \Locales::isTranslatedRoute('contract-documents') ? Route::get(\Locales::getRoute('apartments') . '/{apartment?}/{contractsSlug?}/{contract?}/{yearsSlug?}/{year?}/{documentsSlug?}', 'ContractDocumentsController@index')->name(\Locales::getRoutePrefix('contract-documents'))->where('apartment', '[0-9]+')->where('contractsSlug', 'contracts')->where('contract', '[0-9]+')->where('yearsSlug', 'years')->where('year', '[0-9]+')->where('documentsSlug', 'documents') : '';
                    Route::post('contract-documents/upload/{chunk?}', 'ContractDocumentsController@upload')->name(\Locales::getRoutePrefix('contract-documents/upload'))->where('chunk', 'done');
                    Route::get('contract-documents/download/{id}', 'ContractDocumentsController@download')->name(\Locales::getRoutePrefix('contract-documents/download'))->where('id', '[0-9]+');

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('contract-deductions/add') ? Route::get(\Locales::getRoute('contract-deductions/add'), 'ContractDeductionsController@add')->name(\Locales::getRoutePrefix('contract-deductions/add')) : '';
                        Route::post('contract-deductions/save', 'ContractDeductionsController@save')->name(\Locales::getRoutePrefix('contract-deductions/save'));
                        \Locales::isTranslatedRoute('contract-deductions/edit') ? Route::get(\Locales::getRoute('contract-deductions/edit') . '/{id?}', 'ContractDeductionsController@edit')->name(\Locales::getRoutePrefix('contract-deductions/edit'))->where('id', '[0-9]+') : '';
                        Route::put('contract-deductions/update', 'ContractDeductionsController@update')->name(\Locales::getRoutePrefix('contract-deductions/update'));
                        \Locales::isTranslatedRoute('contract-deductions/remove') ? Route::get(\Locales::getRoute('contract-deductions/remove'), 'ContractDeductionsController@remove')->name(\Locales::getRoutePrefix('contract-deductions/remove')) : '';
                        Route::delete('contract-deductions/destroy', 'ContractDeductionsController@destroy')->name(\Locales::getRoutePrefix('contract-deductions/destroy'));
                    });
                    \Locales::isTranslatedRoute('contract-deductions') ? Route::get(\Locales::getRoute('apartments') . '/{apartment?}/{contractsSlug?}/{contract?}/{yearsSlug?}/{year?}/{deductionsSlug?}', 'ContractDeductionsController@index')->name(\Locales::getRoutePrefix('contract-deductions'))->where('apartment', '[0-9]+')->where('contractsSlug', 'contracts')->where('contract', '[0-9]+')->where('yearsSlug', 'years')->where('year', '[0-9]+')->where('deductionsSlug', 'deductions') : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('contract-payments/add') ? Route::get(\Locales::getRoute('contract-payments/add'), 'ContractPaymentsController@add')->name(\Locales::getRoutePrefix('contract-payments/add')) : '';
                        Route::post('contract-payments/save', 'ContractPaymentsController@save')->name(\Locales::getRoutePrefix('contract-payments/save'));
                        \Locales::isTranslatedRoute('contract-payments/edit') ? Route::get(\Locales::getRoute('contract-payments/edit') . '/{id?}', 'ContractPaymentsController@edit')->name(\Locales::getRoutePrefix('contract-payments/edit'))->where('id', '[0-9]+') : '';
                        Route::put('contract-payments/update', 'ContractPaymentsController@update')->name(\Locales::getRoutePrefix('contract-payments/update'));
                        \Locales::isTranslatedRoute('contract-payments/remove') ? Route::get(\Locales::getRoute('contract-payments/remove'), 'ContractPaymentsController@remove')->name(\Locales::getRoutePrefix('contract-payments/remove')) : '';
                        Route::delete('contract-payments/destroy', 'ContractPaymentsController@destroy')->name(\Locales::getRoutePrefix('contract-payments/destroy'));
                    });
                    \Locales::isTranslatedRoute('contract-payments') ? Route::get(\Locales::getRoute('apartments') . '/{apartment?}/{contractsSlug?}/{contract?}/{yearsSlug?}/{year?}/{paymentsSlug?}', 'ContractPaymentsController@index')->name(\Locales::getRoutePrefix('contract-payments'))->where('apartment', '[0-9]+')->where('contractsSlug', 'contracts')->where('contract', '[0-9]+')->where('yearsSlug', 'years')->where('year', '[0-9]+')->where('paymentsSlug', 'payments') : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('contract-payment-documents/delete') ? Route::get(\Locales::getRoute('contract-payment-documents/delete'), 'ContractPaymentDocumentsController@delete')->name(\Locales::getRoutePrefix('contract-payment-documents/delete')) : '';
                        Route::delete('contract-payment-documents/destroy', 'ContractPaymentDocumentsController@destroy')->name(\Locales::getRoutePrefix('contract-payment-documents/destroy'));
                        \Locales::isTranslatedRoute('contract-payment-documents/edit') ? Route::get(\Locales::getRoute('contract-payment-documents/edit') . '/{file?}', 'ContractPaymentDocumentsController@edit')->name(\Locales::getRoutePrefix('contract-payment-documents/edit'))->where('file', '[0-9]+') : '';
                        Route::put('contract-payment-documents/update', 'ContractPaymentDocumentsController@update')->name(\Locales::getRoutePrefix('contract-payment-documents/update'));
                    });
                    \Locales::isTranslatedRoute('contract-payment-documents') ? Route::get(\Locales::getRoute('apartments') . '/{apartment?}/{contractsSlug?}/{contract?}/{yearsSlug?}/{year?}/{paymentsSlug?}/{payment?}', 'ContractPaymentDocumentsController@index')->name(\Locales::getRoutePrefix('contract-payment-documents'))->where('apartment', '[0-9]+')->where('contractsSlug', 'contracts')->where('contract', '[0-9]+')->where('yearsSlug', 'years')->where('year', '[0-9]+')->where('paymentsSlug', 'payments')->where('payment', '[0-9]+') : '';
                    Route::post('contract-payment-documents/upload/{chunk?}', 'ContractPaymentDocumentsController@upload')->name(\Locales::getRoutePrefix('contract-payment-documents/upload'))->where('chunk', 'done');
                    Route::get('contract-payment-documents/download/{id}', 'ContractPaymentDocumentsController@download')->name(\Locales::getRoutePrefix('contract-payment-documents/download'))->where('id', '[0-9]+');

                    \Locales::isTranslatedRoute('mm-fees') ? Route::get(\Locales::getRoute('apartments') . '/{apartment?}/{mmFeesSlug?}', 'MmFeeController@index')->name(\Locales::getRoutePrefix('mm-fees'))->where('apartment', '[0-9]+')->where('mmFeesSlug', 'mm-fees') : '';
                    \Locales::isTranslatedRoute('communal-fees') ? Route::get(\Locales::getRoute('apartments') . '/{apartment?}/{communalFeesSlug?}', 'CommunalFeeController@index')->name(\Locales::getRoutePrefix('communal-fees'))->where('apartment', '[0-9]+')->where('communalFeesSlug', 'communal-fees') : '';
                    \Locales::isTranslatedRoute('pool-usage') ? Route::get(\Locales::getRoute('apartments') . '/{apartment?}/{poolUsageSlug?}', 'PoolUsageController@index')->name(\Locales::getRoutePrefix('pool-usage'))->where('apartment', '[0-9]+')->where('poolUsageSlug', 'pool-usage') : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('pay-mm-fees') ? Route::get(\Locales::getRoute('pay-mm-fees'), 'MmFeeController@selectYear')->name(\Locales::getRoutePrefix('pay-mm-fees')) : '';
                        Route::post('pay-mm-fees', 'MmFeeController@payMmFees')->name(\Locales::getRoutePrefix('pay-mm-fees'));

                        \Locales::isTranslatedRoute('pay-communal-fees') ? Route::get(\Locales::getRoute('pay-communal-fees'), 'CommunalFeeController@selectYear')->name(\Locales::getRoutePrefix('pay-communal-fees')) : '';
                        Route::post('pay-communal-fees', 'CommunalFeeController@payCommunalFees')->name(\Locales::getRoutePrefix('pay-communal-fees'));

                        \Locales::isTranslatedRoute('pay-pool-usage') ? Route::get(\Locales::getRoute('pay-pool-usage'), 'PoolUsageController@selectYear')->name(\Locales::getRoutePrefix('pay-pool-usage')) : '';
                        Route::post('pay-pool-usage', 'PoolUsageController@payPoolUsage')->name(\Locales::getRoutePrefix('pay-pool-usage'));

                        \Locales::isTranslatedRoute('pay-rental') ? Route::get(\Locales::getRoute('pay-rental'), 'RentalContractController@payRental')->name(\Locales::getRoutePrefix('pay-rental')) : '';
                        Route::post('pay-rental/upload/{chunk?}', 'RentalContractController@upload')->name(\Locales::getRoutePrefix('pay-rental/upload'))->where('chunk', 'done');
                        \Locales::isTranslatedRoute('cancel-rental') ? Route::get(\Locales::getRoute('cancel-rental'), 'RentalContractController@cancelRental')->name(\Locales::getRoutePrefix('cancel-rental')) : '';
                        Route::post('cancel-rental-contracts', 'RentalContractController@cancelRentalContracts')->name(\Locales::getRoutePrefix('cancel-rental-contracts'));
                    });

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('mm-fees-payments/add') ? Route::get(\Locales::getRoute('mm-fees-payments/add'), 'MmFeePaymentController@add')->name(\Locales::getRoutePrefix('mm-fees-payments/add')) : '';
                        Route::post('mm-fees-payments/save', 'MmFeePaymentController@save')->name(\Locales::getRoutePrefix('mm-fees-payments/save'));
                        \Locales::isTranslatedRoute('mm-fees-payments/edit') ? Route::get(\Locales::getRoute('mm-fees-payments/edit') . '/{id?}', 'MmFeePaymentController@edit')->name(\Locales::getRoutePrefix('mm-fees-payments/edit'))->where('id', '[0-9]+') : '';
                        Route::put('mm-fees-payments/update', 'MmFeePaymentController@update')->name(\Locales::getRoutePrefix('mm-fees-payments/update'));
                        \Locales::isTranslatedRoute('mm-fees-payments/remove') ? Route::get(\Locales::getRoute('mm-fees-payments/remove'), 'MmFeePaymentController@remove')->name(\Locales::getRoutePrefix('mm-fees-payments/remove')) : '';
                        Route::delete('mm-fees-payments/destroy', 'MmFeePaymentController@destroy')->name(\Locales::getRoutePrefix('mm-fees-payments/destroy'));
                    });
                    \Locales::isTranslatedRoute('mm-fees-payments') ? Route::get(\Locales::getRoute('apartments') . '/{apartment?}/{mmFeesSlug?}/{year?}', 'MmFeePaymentController@index')->name(\Locales::getRoutePrefix('mm-fees-payments'))->where('apartment', '[0-9]+')->where('mmFeesSlug', 'mm-fees')->where('year', '[0-9]+') : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('mm-fees-payment-documents/delete') ? Route::get(\Locales::getRoute('mm-fees-payment-documents/delete'), 'MmFeePaymentDocumentsController@delete')->name(\Locales::getRoutePrefix('mm-fees-payment-documents/delete')) : '';
                        Route::delete('mm-fees-payment-documents/destroy', 'MmFeePaymentDocumentsController@destroy')->name(\Locales::getRoutePrefix('mm-fees-payment-documents/destroy'));
                        \Locales::isTranslatedRoute('mm-fees-payment-documents/edit') ? Route::get(\Locales::getRoute('mm-fees-payment-documents/edit') . '/{file?}', 'MmFeePaymentDocumentsController@edit')->name(\Locales::getRoutePrefix('mm-fees-payment-documents/edit'))->where('file', '[0-9]+') : '';
                        Route::put('mm-fees-payment-documents/update', 'MmFeePaymentDocumentsController@update')->name(\Locales::getRoutePrefix('mm-fees-payment-documents/update'));
                    });
                    \Locales::isTranslatedRoute('mm-fees-payment-documents') ? Route::get(\Locales::getRoute('apartments') . '/{apartment?}/{mmFeesSlug?}/{year?}/{payment?}', 'MmFeePaymentDocumentsController@index')->name(\Locales::getRoutePrefix('mm-fees-payment-documents'))->where('apartment', '[0-9]+')->where('mmFeesSlug', 'mm-fees')->where('year', '[0-9]+')->where('payment', '[0-9]+') : '';
                    Route::post('mm-fees-payment-documents/upload/{chunk?}', 'MmFeePaymentDocumentsController@upload')->name(\Locales::getRoutePrefix('mm-fees-payment-documents/upload'))->where('chunk', 'done');
                    Route::get('mm-fees-payment-documents/download/{id}', 'MmFeePaymentDocumentsController@download')->name(\Locales::getRoutePrefix('mm-fees-payment-documents/download'))->where('id', '[0-9]+');

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('communal-fees-payments/add') ? Route::get(\Locales::getRoute('communal-fees-payments/add'), 'CommunalFeePaymentController@add')->name(\Locales::getRoutePrefix('communal-fees-payments/add')) : '';
                        Route::post('communal-fees-payments/save', 'CommunalFeePaymentController@save')->name(\Locales::getRoutePrefix('communal-fees-payments/save'));
                        \Locales::isTranslatedRoute('communal-fees-payments/edit') ? Route::get(\Locales::getRoute('communal-fees-payments/edit') . '/{id?}', 'CommunalFeePaymentController@edit')->name(\Locales::getRoutePrefix('communal-fees-payments/edit'))->where('id', '[0-9]+') : '';
                        Route::put('communal-fees-payments/update', 'CommunalFeePaymentController@update')->name(\Locales::getRoutePrefix('communal-fees-payments/update'));
                        \Locales::isTranslatedRoute('communal-fees-payments/remove') ? Route::get(\Locales::getRoute('communal-fees-payments/remove'), 'CommunalFeePaymentController@remove')->name(\Locales::getRoutePrefix('communal-fees-payments/remove')) : '';
                        Route::delete('communal-fees-payments/destroy', 'CommunalFeePaymentController@destroy')->name(\Locales::getRoutePrefix('communal-fees-payments/destroy'));
                    });
                    \Locales::isTranslatedRoute('communal-fees-payments') ? Route::get(\Locales::getRoute('apartments') . '/{apartment?}/{communalFeesSlug?}/{year?}', 'CommunalFeePaymentController@index')->name(\Locales::getRoutePrefix('communal-fees-payments'))->where('apartment', '[0-9]+')->where('communalFeesSlug', 'communal-fees')->where('year', '[0-9]+') : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('communal-fees-payment-documents/delete') ? Route::get(\Locales::getRoute('communal-fees-payment-documents/delete'), 'CommunalFeePaymentDocumentsController@delete')->name(\Locales::getRoutePrefix('communal-fees-payment-documents/delete')) : '';
                        Route::delete('communal-fees-payment-documents/destroy', 'CommunalFeePaymentDocumentsController@destroy')->name(\Locales::getRoutePrefix('communal-fees-payment-documents/destroy'));
                        \Locales::isTranslatedRoute('communal-fees-payment-documents/edit') ? Route::get(\Locales::getRoute('communal-fees-payment-documents/edit') . '/{file?}', 'CommunalFeePaymentDocumentsController@edit')->name(\Locales::getRoutePrefix('communal-fees-payment-documents/edit'))->where('file', '[0-9]+') : '';
                        Route::put('communal-fees-payment-documents/update', 'CommunalFeePaymentDocumentsController@update')->name(\Locales::getRoutePrefix('communal-fees-payment-documents/update'));
                    });
                    \Locales::isTranslatedRoute('communal-fees-payment-documents') ? Route::get(\Locales::getRoute('apartments') . '/{apartment?}/{communalFeesSlug?}/{year?}/{payment?}', 'CommunalFeePaymentDocumentsController@index')->name(\Locales::getRoutePrefix('communal-fees-payment-documents'))->where('apartment', '[0-9]+')->where('communalFeesSlug', 'communal-fees')->where('year', '[0-9]+')->where('payment', '[0-9]+') : '';
                    Route::post('communal-fees-payment-documents/upload/{chunk?}', 'CommunalFeePaymentDocumentsController@upload')->name(\Locales::getRoutePrefix('communal-fees-payment-documents/upload'))->where('chunk', 'done');
                    Route::get('communal-fees-payment-documents/download/{id}', 'CommunalFeePaymentDocumentsController@download')->name(\Locales::getRoutePrefix('communal-fees-payment-documents/download'))->where('id', '[0-9]+');

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('pool-usage-payments/add') ? Route::get(\Locales::getRoute('pool-usage-payments/add'), 'PoolUsagePaymentController@add')->name(\Locales::getRoutePrefix('pool-usage-payments/add')) : '';
                        Route::post('pool-usage-payments/save', 'PoolUsagePaymentController@save')->name(\Locales::getRoutePrefix('pool-usage-payments/save'));
                        \Locales::isTranslatedRoute('pool-usage-payments/edit') ? Route::get(\Locales::getRoute('pool-usage-payments/edit') . '/{id?}', 'PoolUsagePaymentController@edit')->name(\Locales::getRoutePrefix('pool-usage-payments/edit'))->where('id', '[0-9]+') : '';
                        Route::put('pool-usage-payments/update', 'PoolUsagePaymentController@update')->name(\Locales::getRoutePrefix('pool-usage-payments/update'));
                        \Locales::isTranslatedRoute('pool-usage-payments/remove') ? Route::get(\Locales::getRoute('pool-usage-payments/remove'), 'PoolUsagePaymentController@remove')->name(\Locales::getRoutePrefix('pool-usage-payments/remove')) : '';
                        Route::delete('pool-usage-payments/destroy', 'PoolUsagePaymentController@destroy')->name(\Locales::getRoutePrefix('pool-usage-payments/destroy'));
                    });
                    \Locales::isTranslatedRoute('pool-usage-payments') ? Route::get(\Locales::getRoute('apartments') . '/{apartment?}/{poolUsageSlug?}/{year?}', 'PoolUsagePaymentController@index')->name(\Locales::getRoutePrefix('pool-usage-payments'))->where('apartment', '[0-9]+')->where('poolUsageSlug', 'pool-usage')->where('year', '[0-9]+') : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('pool-usage-payment-documents/delete') ? Route::get(\Locales::getRoute('pool-usage-payment-documents/delete'), 'PoolUsagePaymentDocumentsController@delete')->name(\Locales::getRoutePrefix('pool-usage-payment-documents/delete')) : '';
                        Route::delete('pool-usage-payment-documents/destroy', 'PoolUsagePaymentDocumentsController@destroy')->name(\Locales::getRoutePrefix('pool-usage-payment-documents/destroy'));
                        \Locales::isTranslatedRoute('pool-usage-payment-documents/edit') ? Route::get(\Locales::getRoute('pool-usage-payment-documents/edit') . '/{file?}', 'PoolUsagePaymentDocumentsController@edit')->name(\Locales::getRoutePrefix('pool-usage-payment-documents/edit'))->where('file', '[0-9]+') : '';
                        Route::put('pool-usage-payment-documents/update', 'PoolUsagePaymentDocumentsController@update')->name(\Locales::getRoutePrefix('pool-usage-payment-documents/update'));
                    });
                    \Locales::isTranslatedRoute('pool-usage-payment-documents') ? Route::get(\Locales::getRoute('apartments') . '/{apartment?}/{poolUsageSlug?}/{year?}/{payment?}', 'PoolUsagePaymentDocumentsController@index')->name(\Locales::getRoutePrefix('pool-usage-payment-documents'))->where('apartment', '[0-9]+')->where('poolUsageSlug', 'pool-usage')->where('year', '[0-9]+')->where('payment', '[0-9]+') : '';
                    Route::post('pool-usage-payment-documents/upload/{chunk?}', 'PoolUsagePaymentDocumentsController@upload')->name(\Locales::getRoutePrefix('pool-usage-payment-documents/upload'))->where('chunk', 'done');
                    Route::get('pool-usage-payment-documents/download/{id}', 'PoolUsagePaymentDocumentsController@download')->name(\Locales::getRoutePrefix('pool-usage-payment-documents/download'))->where('id', '[0-9]+');

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('apartment-agents/add') ? Route::get(\Locales::getRoute('apartment-agents/add'), 'ApartmentAgentsController@add')->name(\Locales::getRoutePrefix('apartment-agents/add')) : '';
                        Route::post('apartment-agents/save', 'ApartmentAgentsController@save')->name(\Locales::getRoutePrefix('apartment-agents/save'));
                        \Locales::isTranslatedRoute('apartment-agents/edit') ? Route::get(\Locales::getRoute('apartment-agents/edit') . '/{id?}', 'ApartmentAgentsController@edit')->name(\Locales::getRoutePrefix('apartment-agents/edit'))->where('id', '[0-9]+') : '';
                        Route::put('apartment-agents/update', 'ApartmentAgentsController@update')->name(\Locales::getRoutePrefix('apartment-agents/update'));
                        \Locales::isTranslatedRoute('apartment-agents/remove') ? Route::get(\Locales::getRoute('apartment-agents/remove'), 'ApartmentAgentsController@remove')->name(\Locales::getRoutePrefix('apartment-agents/remove')) : '';
                        Route::delete('apartment-agents/destroy', 'ApartmentAgentsController@destroy')->name(\Locales::getRoutePrefix('apartment-agents/destroy'));
                    });
                    \Locales::isTranslatedRoute('apartment-agents') ? Route::get(\Locales::getRoute('apartment-agents') . '/{id?}/agents', 'ApartmentAgentsController@index')->name(\Locales::getRoutePrefix('apartment-agents'))->where('id', '[0-9]+') : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('apartment-legal-representatives/add') ? Route::get(\Locales::getRoute('apartment-legal-representatives/add'), 'ApartmentLegalRepresentativesController@add')->name(\Locales::getRoutePrefix('apartment-legal-representatives/add')) : '';
                        Route::post('apartment-legal-representatives/save', 'ApartmentLegalRepresentativesController@save')->name(\Locales::getRoutePrefix('apartment-legal-representatives/save'));
                        \Locales::isTranslatedRoute('apartment-legal-representatives/edit') ? Route::get(\Locales::getRoute('apartment-legal-representatives/edit') . '/{id?}', 'ApartmentLegalRepresentativesController@edit')->name(\Locales::getRoutePrefix('apartment-legal-representatives/edit'))->where('id', '[0-9]+') : '';
                        Route::put('apartment-legal-representatives/update', 'ApartmentLegalRepresentativesController@update')->name(\Locales::getRoutePrefix('apartment-legal-representatives/update'));
                        \Locales::isTranslatedRoute('apartment-legal-representatives/remove') ? Route::get(\Locales::getRoute('apartment-legal-representatives/remove'), 'ApartmentLegalRepresentativesController@remove')->name(\Locales::getRoutePrefix('apartment-legal-representatives/remove')) : '';
                        Route::delete('apartment-legal-representatives/destroy', 'ApartmentLegalRepresentativesController@destroy')->name(\Locales::getRoutePrefix('apartment-legal-representatives/destroy'));
                    });
                    \Locales::isTranslatedRoute('apartment-legal-representatives') ? Route::get(\Locales::getRoute('apartment-legal-representatives') . '/{id?}/legal-representatives', 'ApartmentLegalRepresentativesController@index')->name(\Locales::getRoutePrefix('apartment-legal-representatives'))->where('id', '[0-9]+') : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('apartment-keyholders/add') ? Route::get(\Locales::getRoute('apartment-keyholders/add'), 'ApartmentKeyholdersController@add')->name(\Locales::getRoutePrefix('apartment-keyholders/add')) : '';
                        Route::post('apartment-keyholders/save', 'ApartmentKeyholdersController@save')->name(\Locales::getRoutePrefix('apartment-keyholders/save'));
                        \Locales::isTranslatedRoute('apartment-keyholders/edit') ? Route::get(\Locales::getRoute('apartment-keyholders/edit') . '/{id?}', 'ApartmentKeyholdersController@edit')->name(\Locales::getRoutePrefix('apartment-keyholders/edit'))->where('id', '[0-9]+') : '';
                        Route::put('apartment-keyholders/update', 'ApartmentKeyholdersController@update')->name(\Locales::getRoutePrefix('apartment-keyholders/update'));
                        \Locales::isTranslatedRoute('apartment-keyholders/remove') ? Route::get(\Locales::getRoute('apartment-keyholders/remove'), 'ApartmentKeyholdersController@remove')->name(\Locales::getRoutePrefix('apartment-keyholders/remove')) : '';
                        Route::delete('apartment-keyholders/destroy', 'ApartmentKeyholdersController@destroy')->name(\Locales::getRoutePrefix('apartment-keyholders/destroy'));
                    });
                    \Locales::isTranslatedRoute('apartment-keyholders') ? Route::get(\Locales::getRoute('apartment-keyholders') . '/{id?}/keyholders', 'ApartmentKeyholdersController@index')->name(\Locales::getRoutePrefix('apartment-keyholders'))->where('id', '[0-9]+') : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('apartment-maintenance-issues/add') ? Route::get(\Locales::getRoute('apartment-maintenance-issues/add'), 'ApartmentMaintenanceIssueController@add')->name(\Locales::getRoutePrefix('apartment-maintenance-issues/add')) : '';
                        Route::post('apartment-maintenance-issues/save', 'ApartmentMaintenanceIssueController@save')->name(\Locales::getRoutePrefix('apartment-maintenance-issues/save'));
                        \Locales::isTranslatedRoute('apartment-maintenance-issues/edit') ? Route::get(\Locales::getRoute('apartment-maintenance-issues/edit') . '/{id?}', 'ApartmentMaintenanceIssueController@edit')->name(\Locales::getRoutePrefix('apartment-maintenance-issues/edit'))->where('id', '[0-9]+') : '';
                        Route::put('apartment-maintenance-issues/update', 'ApartmentMaintenanceIssueController@update')->name(\Locales::getRoutePrefix('apartment-maintenance-issues/update'));
                        \Locales::isTranslatedRoute('apartment-maintenance-issues/remove') ? Route::get(\Locales::getRoute('apartment-maintenance-issues/remove'), 'ApartmentMaintenanceIssueController@remove')->name(\Locales::getRoutePrefix('apartment-maintenance-issues/remove')) : '';
                        Route::delete('apartment-maintenance-issues/destroy', 'ApartmentMaintenanceIssueController@destroy')->name(\Locales::getRoutePrefix('apartment-maintenance-issues/destroy'));
                        Route::get('apartment-maintenance-issues/change-status/{id}/{status}', 'ApartmentMaintenanceIssueController@changeStatus')->name(\Locales::getRoutePrefix('apartment-maintenance-issues/change-status'))->where('id', '[0-9]+')->where('status', '[0-9]+');
                    });
                    \Locales::isTranslatedRoute('apartment-maintenance-issues') ? Route::get(\Locales::getRoute('apartment-maintenance-issues') . '/{id?}/maintenance-issues', 'ApartmentMaintenanceIssueController@index')->name(\Locales::getRoutePrefix('apartment-maintenance-issues'))->where('id', '[0-9]+') : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('apartment-former-owners/remove') ? Route::get(\Locales::getRoute('apartment-former-owners/remove'), 'ApartmentFormerOwnersController@remove')->name(\Locales::getRoutePrefix('apartment-former-owners/remove')) : '';
                        Route::delete('apartment-former-owners/destroy', 'ApartmentFormerOwnersController@destroy')->name(\Locales::getRoutePrefix('apartment-former-owners/destroy'));
                    });
                    \Locales::isTranslatedRoute('apartment-former-owners') ? Route::get(\Locales::getRoute('apartment-former-owners') . '/{id?}/former-owners', 'ApartmentFormerOwnersController@index')->name(\Locales::getRoutePrefix('apartment-former-owners'))->where('id', '[0-9]+') : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('owner-notices/add') ? Route::get(\Locales::getRoute('owner-notices/add'), 'OwnerNoticesController@add')->name(\Locales::getRoutePrefix('owner-notices/add')) : '';
                        Route::post('owner-notices/save', 'OwnerNoticesController@save')->name(\Locales::getRoutePrefix('owner-notices/save'));
                        \Locales::isTranslatedRoute('owner-notices/remove') ? Route::get(\Locales::getRoute('owner-notices/remove'), 'OwnerNoticesController@remove')->name(\Locales::getRoutePrefix('owner-notices/remove')) : '';
                        Route::delete('owner-notices/destroy', 'OwnerNoticesController@destroy')->name(\Locales::getRoutePrefix('owner-notices/destroy'));
                    });
                    \Locales::isTranslatedRoute('owner-notices') ? Route::get(\Locales::getRoute('owner-notices') . '/{id?}/notices', 'OwnerNoticesController@index')->name(\Locales::getRoutePrefix('owner-notices'))->where('id', '[0-9]+') : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('owner-files/delete') ? Route::get(\Locales::getRoute('owner-files/delete'), 'OwnerFilesController@delete')->name(\Locales::getRoutePrefix('owner-files/delete')) : '';
                        Route::delete('owner-files/destroy', 'OwnerFilesController@destroy')->name(\Locales::getRoutePrefix('owner-files/destroy'));
                        \Locales::isTranslatedRoute('owner-files/edit') ? Route::get(\Locales::getRoute('owner-files/edit') . '/{attachment?}', 'OwnerFilesController@edit')->name(\Locales::getRoutePrefix('owner-files/edit'))->where('attachment', '[0-9]+') : '';
                        Route::put('owner-files/update', 'OwnerFilesController@update')->name(\Locales::getRoutePrefix('owner-files/update'));
                    });
                    \Locales::isTranslatedRoute('owner-files') ? Route::get(\Locales::getRoute('owner-files') . '/{id?}/attachments', 'OwnerFilesController@index')->name(\Locales::getRoutePrefix('owner-files'))->where('id', '[0-9]+') : '';
                    Route::post('owner-files/upload/{chunk?}', 'OwnerFilesController@upload')->name(\Locales::getRoutePrefix('owner-files/upload'))->where('chunk', 'done');
                    Route::get('owner-files/download/{id}', 'OwnerFilesController@download')->name(\Locales::getRoutePrefix('owner-files/download'))->where('id', '[0-9]+');
                    \Locales::isTranslatedRoute('owner-files') ? Route::get(\Locales::getRoute('owner-files') . '/{id?}/files', 'OwnerFilesController@index')->name(\Locales::getRoutePrefix('owner-files'))->where('id', '[0-9]+') : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        Route::get('notices/preview/{id}', 'NoticeController@preview')->name(\Locales::getRoutePrefix('notices/preview'))->where('id', '[0-9]+');
                        \Locales::isTranslatedRoute('notices/create') ? Route::get(\Locales::getRoute('notices/create'), 'NoticeController@create')->name(\Locales::getRoutePrefix('notices/create')) : '';
                        \Locales::isTranslatedRoute('notices/store') ? Route::post(\Locales::getRoute('notices/store'), 'NoticeController@store')->name(\Locales::getRoutePrefix('notices/store')) : '';
                        \Locales::isTranslatedRoute('notices/edit') ? Route::get(\Locales::getRoute('notices/edit') . '/{id?}', 'NoticeController@edit')->name(\Locales::getRoutePrefix('notices/edit'))->where('id', '[0-9]+') : '';
                        \Locales::isTranslatedRoute('notices/update') ? Route::put(\Locales::getRoute('notices/update'), 'NoticeController@update')->name(\Locales::getRoutePrefix('notices/update')) : '';
                        \Locales::isTranslatedRoute('notices/delete') ? Route::get(\Locales::getRoute('notices/delete'), 'NoticeController@delete')->name(\Locales::getRoutePrefix('notices/delete')) : '';
                        \Locales::isTranslatedRoute('notices/destroy') ? Route::delete(\Locales::getRoute('notices/destroy'), 'NoticeController@destroy')->name(\Locales::getRoutePrefix('notices/destroy')) : '';
                        \Locales::isTranslatedRoute('notices/change-status') ? Route::get(\Locales::getRoute('notices/change-status') . '/{id}/{status}', 'NoticeController@changeStatus')->name(\Locales::getRoutePrefix('notices/change-status'))->where('id', '[0-9]+')->where('status', '[0-9]+') : '';
                    });
                    \Locales::isTranslatedRoute('notices') ? Route::get(\Locales::getRoute('notices') . '/{locale?}', 'NoticeController@index')->name(\Locales::getRoutePrefix('notices'))->where('locale', '[a-z-]+') : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        Route::get('bookings/test/{id?}', 'BookingController@test')->name(\Locales::getRoutePrefix('bookings/test'))->where('id', '[0-9]+');
                        Route::get('bookings/send/{id?}', 'BookingController@send')->name(\Locales::getRoutePrefix('bookings/send'))->where('id', '[0-9]+');
                        \Locales::isTranslatedRoute('bookings/create') ? Route::get(\Locales::getRoute('bookings/create'), 'BookingController@create')->name(\Locales::getRoutePrefix('bookings/create')) : '';
                        Route::post('bookings/store', 'BookingController@store')->name(\Locales::getRoutePrefix('bookings/store'));
                        \Locales::isTranslatedRoute('bookings/edit') ? Route::get(\Locales::getRoute('bookings/edit') . '/{id?}', 'BookingController@edit')->name(\Locales::getRoutePrefix('bookings/edit'))->where('id', '[0-9]+') : '';
                        Route::put('bookings/update', 'BookingController@update')->name(\Locales::getRoutePrefix('bookings/update'));
                        \Locales::isTranslatedRoute('bookings/delete') ? Route::get(\Locales::getRoute('bookings/delete'), 'BookingController@delete')->name(\Locales::getRoutePrefix('bookings/delete')) : '';
                        Route::delete('bookings/destroy', 'BookingController@destroy')->name(\Locales::getRoutePrefix('bookings/destroy'));
                        Route::get('bookings/get-buildings', 'BookingController@getBuildings')->name(\Locales::getRoutePrefix('bookings/get-buildings'));
                        Route::post('bookings/get-apartments', 'BookingController@getApartments')->name(\Locales::getRoutePrefix('bookings/get-apartments'));
                        Route::post('bookings/get-info', 'BookingController@getInfo')->name(\Locales::getRoutePrefix('bookings/get-info'));
                        Route::post('bookings/get-owner-info', 'BookingController@getOwnerInfo')->name(\Locales::getRoutePrefix('bookings/get-owner-info'));
                        \Locales::isTranslatedRoute('bookings/change-status') ? Route::get(\Locales::getRoute('bookings/change-status') . '/{id}/{status}', 'BookingController@changeStatus')->name(\Locales::getRoutePrefix('bookings/change-status'))->where('id', '[0-9]+')->where('status', '[0-9]+') : '';
                    });
                    Route::get('bookings/print/{id?}', 'BookingController@printBooking')->name(\Locales::getRoutePrefix('bookings/print'))->where('id', '[0-9]+');
                    \Locales::isTranslatedRoute('bookings') ? Route::get(\Locales::getRoute('bookings'), 'BookingController@index')->name(\Locales::getRoutePrefix('bookings')) : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        Route::get('rental-contracts-tracker/test/{id?}', 'RentalContractTrackerController@test')->name(\Locales::getRoutePrefix('rental-contracts-tracker/test'))->where('id', '[0-9]+');
                        Route::get('rental-contracts-tracker/send/{id?}', 'RentalContractTrackerController@send')->name(\Locales::getRoutePrefix('rental-contracts-tracker/send'))->where('id', '[0-9]+');
                        \Locales::isTranslatedRoute('rental-contracts-tracker/confirm-send-to-all') ? Route::get(\Locales::getRoute('rental-contracts-tracker/confirm-send-to-all'), 'RentalContractTrackerController@confirmSendToAll')->name(\Locales::getRoutePrefix('rental-contracts-tracker/confirm-send-to-all')) : '';
                        Route::post('rental-contracts-tracker/send-to-all', 'RentalContractTrackerController@sendToAll')->name(\Locales::getRoutePrefix('rental-contracts-tracker/send-to-all'));
                        \Locales::isTranslatedRoute('rental-contracts-tracker/confirm-activate') ? Route::get(\Locales::getRoute('rental-contracts-tracker/confirm-activate'), 'RentalContractTrackerController@confirmActivate')->name(\Locales::getRoutePrefix('rental-contracts-tracker/confirm-activate')) : '';
                        Route::post('rental-contracts-tracker/activate', 'RentalContractTrackerController@activate')->name(\Locales::getRoutePrefix('rental-contracts-tracker/activate'));
                        \Locales::isTranslatedRoute('rental-contracts-tracker/create') ? Route::get(\Locales::getRoute('rental-contracts-tracker/create'), 'RentalContractTrackerController@create')->name(\Locales::getRoutePrefix('rental-contracts-tracker/create')) : '';
                        Route::post('rental-contracts-tracker/store', 'RentalContractTrackerController@store')->name(\Locales::getRoutePrefix('rental-contracts-tracker/store'));
                        \Locales::isTranslatedRoute('rental-contracts-tracker/edit') ? Route::get(\Locales::getRoute('rental-contracts-tracker/edit') . '/{id?}', 'RentalContractTrackerController@edit')->name(\Locales::getRoutePrefix('rental-contracts-tracker/edit'))->where('id', '[0-9]+') : '';
                        Route::put('rental-contracts-tracker/update', 'RentalContractTrackerController@update')->name(\Locales::getRoutePrefix('rental-contracts-tracker/update'));
                        \Locales::isTranslatedRoute('rental-contracts-tracker/delete') ? Route::get(\Locales::getRoute('rental-contracts-tracker/delete'), 'RentalContractTrackerController@delete')->name(\Locales::getRoutePrefix('rental-contracts-tracker/delete')) : '';
                        Route::delete('rental-contracts-tracker/destroy', 'RentalContractTrackerController@destroy')->name(\Locales::getRoutePrefix('rental-contracts-tracker/destroy'));
                        Route::get('rental-contracts-tracker/get-contracts/{apartment?}', 'RentalContractTrackerController@getContracts')->name(\Locales::getRoutePrefix('rental-contracts-tracker/get-contracts'))->where('apartment', '[0-9]+');
                        Route::get('rental-contracts-tracker/get-contract-data/{apartment?}/{owner?}/{contract?}', 'RentalContractTrackerController@getContractData')->name(\Locales::getRoutePrefix('rental-contracts-tracker/get-contract-data'))->where('apartment', '[0-9]+')->where('owner', '[0-9]+')->where('contract', '[0-9]+');
                        \Locales::isTranslatedRoute('rental-contracts-tracker/change-status') ? Route::get(\Locales::getRoute('rental-contracts-tracker/change-status') . '/{id}/{status}', 'RentalContractTrackerController@changeStatus')->name(\Locales::getRoutePrefix('rental-contracts-tracker/change-status'))->where('id', '[0-9]+')->where('status', '[0-9]+') : '';
                    });
                    Route::get('rental-contracts-tracker/print/{id?}', 'RentalContractTrackerController@print')->name(\Locales::getRoutePrefix('rental-contracts-tracker/print'))->where('id', '[0-9]+');
                    \Locales::isTranslatedRoute('rental-contracts-tracker') ? Route::get(\Locales::getRoute('rental-contracts-tracker'), 'RentalContractTrackerController@index')->name(\Locales::getRoutePrefix('rental-contracts-tracker')) : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        Route::get('communal-fee-contracts-tracker/test/{id?}', 'CommunalFeeContractTrackerController@test')->name(\Locales::getRoutePrefix('communal-fee-contracts-tracker/test'))->where('id', '[0-9]+');
                        Route::get('communal-fee-contracts-tracker/send/{id?}', 'CommunalFeeContractTrackerController@send')->name(\Locales::getRoutePrefix('communal-fee-contracts-tracker/send'))->where('id', '[0-9]+');
                        \Locales::isTranslatedRoute('communal-fee-contracts-tracker/confirm-send-to-all') ? Route::get(\Locales::getRoute('communal-fee-contracts-tracker/confirm-send-to-all'), 'CommunalFeeContractTrackerController@confirmSendToAll')->name(\Locales::getRoutePrefix('communal-fee-contracts-tracker/confirm-send-to-all')) : '';
                        Route::post('communal-fee-contracts-tracker/send-to-all', 'CommunalFeeContractTrackerController@sendToAll')->name(\Locales::getRoutePrefix('communal-fee-contracts-tracker/send-to-all'));
                        \Locales::isTranslatedRoute('communal-fee-contracts-tracker/confirm-activate') ? Route::get(\Locales::getRoute('communal-fee-contracts-tracker/confirm-activate'), 'CommunalFeeContractTrackerController@confirmActivate')->name(\Locales::getRoutePrefix('communal-fee-contracts-tracker/confirm-activate')) : '';
                        Route::post('communal-fee-contracts-tracker/activate', 'CommunalFeeContractTrackerController@activate')->name(\Locales::getRoutePrefix('communal-fee-contracts-tracker/activate'));
                        \Locales::isTranslatedRoute('communal-fee-contracts-tracker/create') ? Route::get(\Locales::getRoute('communal-fee-contracts-tracker/create'), 'CommunalFeeContractTrackerController@create')->name(\Locales::getRoutePrefix('communal-fee-contracts-tracker/create')) : '';
                        Route::post('communal-fee-contracts-tracker/store', 'CommunalFeeContractTrackerController@store')->name(\Locales::getRoutePrefix('communal-fee-contracts-tracker/store'));
                        \Locales::isTranslatedRoute('communal-fee-contracts-tracker/edit') ? Route::get(\Locales::getRoute('communal-fee-contracts-tracker/edit') . '/{id?}', 'CommunalFeeContractTrackerController@edit')->name(\Locales::getRoutePrefix('communal-fee-contracts-tracker/edit'))->where('id', '[0-9]+') : '';
                        Route::put('communal-fee-contracts-tracker/update', 'CommunalFeeContractTrackerController@update')->name(\Locales::getRoutePrefix('communal-fee-contracts-tracker/update'));
                        \Locales::isTranslatedRoute('communal-fee-contracts-tracker/delete') ? Route::get(\Locales::getRoute('communal-fee-contracts-tracker/delete'), 'CommunalFeeContractTrackerController@delete')->name(\Locales::getRoutePrefix('communal-fee-contracts-tracker/delete')) : '';
                        Route::delete('communal-fee-contracts-tracker/destroy', 'CommunalFeeContractTrackerController@destroy')->name(\Locales::getRoutePrefix('communal-fee-contracts-tracker/destroy'));
                        Route::get('communal-fee-contracts-tracker/get-owners/{apartment?}', 'CommunalFeeContractTrackerController@getOwners')->name(\Locales::getRoutePrefix('communal-fee-contracts-tracker/get-owners'))->where('apartment', '[0-9]+');
                        \Locales::isTranslatedRoute('communal-fee-contracts-tracker/change-status') ? Route::get(\Locales::getRoute('communal-fee-contracts-tracker/change-status') . '/{id}/{status}', 'CommunalFeeContractTrackerController@changeStatus')->name(\Locales::getRoutePrefix('communal-fee-contracts-tracker/change-status'))->where('id', '[0-9]+')->where('status', '[0-9]+') : '';
                    });
                    Route::get('communal-fee-contracts-tracker/print/{id?}', 'CommunalFeeContractTrackerController@print')->name(\Locales::getRoutePrefix('communal-fee-contracts-tracker/print'))->where('id', '[0-9]+');
                    \Locales::isTranslatedRoute('communal-fee-contracts-tracker') ? Route::get(\Locales::getRoute('communal-fee-contracts-tracker'), 'CommunalFeeContractTrackerController@index')->name(\Locales::getRoutePrefix('communal-fee-contracts-tracker')) : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        Route::get('pool-usage-contracts-tracker/test/{id?}', 'PoolUsageContractTrackerController@test')->name(\Locales::getRoutePrefix('pool-usage-contracts-tracker/test'))->where('id', '[0-9]+');
                        Route::get('pool-usage-contracts-tracker/send/{id?}', 'PoolUsageContractTrackerController@send')->name(\Locales::getRoutePrefix('pool-usage-contracts-tracker/send'))->where('id', '[0-9]+');
                        \Locales::isTranslatedRoute('pool-usage-contracts-tracker/confirm-send-to-all') ? Route::get(\Locales::getRoute('pool-usage-contracts-tracker/confirm-send-to-all'), 'PoolUsageContractTrackerController@confirmSendToAll')->name(\Locales::getRoutePrefix('pool-usage-contracts-tracker/confirm-send-to-all')) : '';
                        Route::post('pool-usage-contracts-tracker/send-to-all', 'PoolUsageContractTrackerController@sendToAll')->name(\Locales::getRoutePrefix('pool-usage-contracts-tracker/send-to-all'));
                        \Locales::isTranslatedRoute('pool-usage-contracts-tracker/confirm-activate') ? Route::get(\Locales::getRoute('pool-usage-contracts-tracker/confirm-activate'), 'PoolUsageContractTrackerController@confirmActivate')->name(\Locales::getRoutePrefix('pool-usage-contracts-tracker/confirm-activate')) : '';
                        Route::post('pool-usage-contracts-tracker/activate', 'PoolUsageContractTrackerController@activate')->name(\Locales::getRoutePrefix('pool-usage-contracts-tracker/activate'));
                        \Locales::isTranslatedRoute('pool-usage-contracts-tracker/create') ? Route::get(\Locales::getRoute('pool-usage-contracts-tracker/create'), 'PoolUsageContractTrackerController@create')->name(\Locales::getRoutePrefix('pool-usage-contracts-tracker/create')) : '';
                        Route::post('pool-usage-contracts-tracker/store', 'PoolUsageContractTrackerController@store')->name(\Locales::getRoutePrefix('pool-usage-contracts-tracker/store'));
                        \Locales::isTranslatedRoute('pool-usage-contracts-tracker/edit') ? Route::get(\Locales::getRoute('pool-usage-contracts-tracker/edit') . '/{id?}', 'PoolUsageContractTrackerController@edit')->name(\Locales::getRoutePrefix('pool-usage-contracts-tracker/edit'))->where('id', '[0-9]+') : '';
                        Route::put('pool-usage-contracts-tracker/update', 'PoolUsageContractTrackerController@update')->name(\Locales::getRoutePrefix('pool-usage-contracts-tracker/update'));
                        \Locales::isTranslatedRoute('pool-usage-contracts-tracker/delete') ? Route::get(\Locales::getRoute('pool-usage-contracts-tracker/delete'), 'PoolUsageContractTrackerController@delete')->name(\Locales::getRoutePrefix('pool-usage-contracts-tracker/delete')) : '';
                        Route::delete('pool-usage-contracts-tracker/destroy', 'PoolUsageContractTrackerController@destroy')->name(\Locales::getRoutePrefix('pool-usage-contracts-tracker/destroy'));
                        Route::get('pool-usage-contracts-tracker/get-owners/{apartment?}', 'PoolUsageContractTrackerController@getOwners')->name(\Locales::getRoutePrefix('pool-usage-contracts-tracker/get-owners'))->where('apartment', '[0-9]+');
                        \Locales::isTranslatedRoute('pool-usage-contracts-tracker/change-status') ? Route::get(\Locales::getRoute('pool-usage-contracts-tracker/change-status') . '/{id}/{status}', 'PoolUsageContractTrackerController@changeStatus')->name(\Locales::getRoutePrefix('pool-usage-contracts-tracker/change-status'))->where('id', '[0-9]+')->where('status', '[0-9]+') : '';
                    });
                    Route::get('pool-usage-contracts-tracker/print/{id?}', 'PoolUsageContractTrackerController@print')->name(\Locales::getRoutePrefix('pool-usage-contracts-tracker/print'))->where('id', '[0-9]+');
                    \Locales::isTranslatedRoute('pool-usage-contracts-tracker') ? Route::get(\Locales::getRoute('pool-usage-contracts-tracker'), 'PoolUsageContractTrackerController@index')->name(\Locales::getRoutePrefix('pool-usage-contracts-tracker')) : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        Route::get('poa/test/{id?}', 'PoaController@test')->name(\Locales::getRoutePrefix('poa/test'))->where('id', '[0-9]+');
                        Route::get('poa/send/{id?}', 'PoaController@send')->name(\Locales::getRoutePrefix('poa/send'))->where('id', '[0-9]+');
                        \Locales::isTranslatedRoute('poa/confirm-send-to-all') ? Route::get(\Locales::getRoute('poa/confirm-send-to-all'), 'PoaController@confirmSendToAll')->name(\Locales::getRoutePrefix('poa/confirm-send-to-all')) : '';
                        Route::post('poa/send-to-all', 'PoaController@sendToAll')->name(\Locales::getRoutePrefix('poa/send-to-all'));
                        \Locales::isTranslatedRoute('poa/create') ? Route::get(\Locales::getRoute('poa/create'), 'PoaController@create')->name(\Locales::getRoutePrefix('poa/create')) : '';
                        Route::post('poa/store', 'PoaController@store')->name(\Locales::getRoutePrefix('poa/store'));
                        \Locales::isTranslatedRoute('poa/edit') ? Route::get(\Locales::getRoute('poa/edit') . '/{id?}', 'PoaController@edit')->name(\Locales::getRoutePrefix('poa/edit'))->where('id', '[0-9]+') : '';
                        Route::put('poa/update', 'PoaController@update')->name(\Locales::getRoutePrefix('poa/update'));
                        \Locales::isTranslatedRoute('poa/delete') ? Route::get(\Locales::getRoute('poa/delete'), 'PoaController@delete')->name(\Locales::getRoutePrefix('poa/delete')) : '';
                        Route::delete('poa/destroy', 'PoaController@destroy')->name(\Locales::getRoutePrefix('poa/destroy'));
                        Route::get('poa/get-owners/{apartment?}/{poa?}', 'PoaController@getOwners')->name(\Locales::getRoutePrefix('poa/get-owners'))->where('apartment', '[0-9]+')->where('poa', '[0-9]+');
                        Route::get('poa/get-proxies/{owner?}/{apartment?}/{poa?}', 'PoaController@getProxies')->name(\Locales::getRoutePrefix('poa/get-proxies'))->where('owner', '[0-9]+')->where('apartment', '[0-9]+')->where('poa', '[0-9]+');
                        \Locales::isTranslatedRoute('poa/change-status') ? Route::get(\Locales::getRoute('poa/change-status') . '/{id}/{status}', 'PoaController@changeStatus')->name(\Locales::getRoutePrefix('poa/change-status'))->where('id', '[0-9]+')->where('status', '[0-9]+') : '';
                    });
                    Route::get('poa/print/{id?}', 'PoaController@print')->name(\Locales::getRoutePrefix('poa/print'))->where('id', '[0-9]+');
                    \Locales::isTranslatedRoute('poa') ? Route::get(\Locales::getRoute('poa'), 'PoaController@index')->name(\Locales::getRoutePrefix('poa')) : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        Route::post('key-log/upload/{chunk?}', 'KeyLogController@upload')->name(\Locales::getRoutePrefix('key-log/upload'))->where('chunk', 'done');
                        \Locales::isTranslatedRoute('key-log/create') ? Route::get(\Locales::getRoute('key-log/create'), 'KeyLogController@create')->name(\Locales::getRoutePrefix('key-log/create')) : '';
                        Route::post('key-log/store', 'KeyLogController@store')->name(\Locales::getRoutePrefix('key-log/store'));
                        \Locales::isTranslatedRoute('key-log/edit') ? Route::get(\Locales::getRoute('key-log/edit') . '/{id?}', 'KeyLogController@edit')->name(\Locales::getRoutePrefix('key-log/edit'))->where('id', '[0-9]+') : '';
                        Route::put('key-log/update', 'KeyLogController@update')->name(\Locales::getRoutePrefix('key-log/update'));
                        \Locales::isTranslatedRoute('key-log/delete') ? Route::get(\Locales::getRoute('key-log/delete'), 'KeyLogController@delete')->name(\Locales::getRoutePrefix('key-log/delete')) : '';
                        Route::delete('key-log/destroy', 'KeyLogController@destroy')->name(\Locales::getRoutePrefix('key-log/destroy'));
                    });
                    \Locales::isTranslatedRoute('key-log') ? Route::get(\Locales::getRoute('key-log'), 'KeyLogController@index')->name(\Locales::getRoutePrefix('key-log')) : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('calendar/create') ? Route::get(\Locales::getRoute('calendar/create'), 'CalendarController@create')->name(\Locales::getRoutePrefix('calendar/create')) : '';
                        Route::post('calendar/store', 'CalendarController@store')->name(\Locales::getRoutePrefix('calendar/store'));
                        \Locales::isTranslatedRoute('calendar/edit') ? Route::get(\Locales::getRoute('calendar/edit') . '/{id?}', 'CalendarController@edit')->name(\Locales::getRoutePrefix('calendar/edit'))->where('id', '[0-9]+') : '';
                        Route::put('calendar/update', 'CalendarController@update')->name(\Locales::getRoutePrefix('calendar/update'));
                        \Locales::isTranslatedRoute('calendar/delete') ? Route::get(\Locales::getRoute('calendar/delete'), 'CalendarController@delete')->name(\Locales::getRoutePrefix('calendar/delete')) : '';
                        Route::delete('calendar/destroy', 'CalendarController@destroy')->name(\Locales::getRoutePrefix('calendar/destroy'));
                    });
                    \Locales::isTranslatedRoute('calendar') ? Route::get(\Locales::getRoute('calendar'), 'CalendarController@index')->name(\Locales::getRoutePrefix('calendar')) : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        Route::get('newsletters/test/{id?}', 'NewsletterController@test')->name(\Locales::getRoutePrefix('newsletters/test'))->where('id', '[0-9]+');
                        Route::get('newsletters/send/{id?}', 'NewsletterController@send')->name(\Locales::getRoutePrefix('newsletters/send'))->where('id', '[0-9]+');
                        Route::get('newsletters/preview/{id}', 'NewsletterController@preview')->name(\Locales::getRoutePrefix('newsletters/preview'))->where('id', '[0-9]+');
                        \Locales::isTranslatedRoute('newsletters/create') ? Route::get(\Locales::getRoute('newsletters/create'), 'NewsletterController@create')->name(\Locales::getRoutePrefix('newsletters/create')) : '';
                        Route::post('newsletters/store', 'NewsletterController@store')->name(\Locales::getRoutePrefix('newsletters/store'));
                        \Locales::isTranslatedRoute('newsletters/edit') ? Route::get(\Locales::getRoute('newsletters/edit') . '/{id?}', 'NewsletterController@edit')->name(\Locales::getRoutePrefix('newsletters/edit'))->where('id', '[0-9]+') : '';
                        Route::put('newsletters/update', 'NewsletterController@update')->name(\Locales::getRoutePrefix('newsletters/update'));
                        \Locales::isTranslatedRoute('newsletters/delete') ? Route::get(\Locales::getRoute('newsletters/delete'), 'NewsletterController@delete')->name(\Locales::getRoutePrefix('newsletters/delete')) : '';
                        Route::delete('newsletters/destroy', 'NewsletterController@destroy')->name(\Locales::getRoutePrefix('newsletters/destroy'));
                        Route::get('newsletters/get-template', 'NewsletterController@getTemplate')->name(\Locales::getRoutePrefix('newsletters/get-template'));
                        Route::get('newsletters/get-buildings', 'NewsletterController@getBuildings')->name(\Locales::getRoutePrefix('newsletters/get-buildings'));
                        Route::get('newsletters/get-floors', 'NewsletterController@getFloors')->name(\Locales::getRoutePrefix('newsletters/get-floors'));
                        Route::get('newsletters/get-apartments', 'NewsletterController@getApartments')->name(\Locales::getRoutePrefix('newsletters/get-apartments'));
                        Route::post('newsletters/get-owners', 'NewsletterController@getOwners')->name(\Locales::getRoutePrefix('newsletters/get-owners'));
                    });
                    \Locales::isTranslatedRoute('newsletters') ? Route::get(\Locales::getRoute('newsletters') . '/{id?}', 'NewsletterController@index')->name(\Locales::getRoutePrefix('newsletters'))->where('id', '[0-9]+') : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('newsletter-images/delete') ? Route::get(\Locales::getRoute('newsletter-images/delete'), 'NewsletterImagesController@delete')->name(\Locales::getRoutePrefix('newsletter-images/delete')) : '';
                        Route::delete('newsletter-images/destroy', 'NewsletterImagesController@destroy')->name(\Locales::getRoutePrefix('newsletter-images/destroy'));
                        \Locales::isTranslatedRoute('newsletter-images/edit') ? Route::get(\Locales::getRoute('newsletter-images/edit') . '/{image?}', 'NewsletterImagesController@edit')->name(\Locales::getRoutePrefix('newsletter-images/edit'))->where('image', '[0-9]+') : '';
                        Route::put('newsletter-images/update', 'NewsletterImagesController@update')->name(\Locales::getRoutePrefix('newsletter-images/update'));
                    });
                    \Locales::isTranslatedRoute('newsletter-images') ? Route::get(\Locales::getRoute('newsletter-images') . '/{id?}/images', 'NewsletterImagesController@index')->name(\Locales::getRoutePrefix('newsletter-images'))->where('id', '[0-9]+') : '';
                    Route::post('newsletter-images/upload/{chunk?}', 'NewsletterImagesController@upload')->name(\Locales::getRoutePrefix('newsletter-images/upload'))->where('chunk', 'done');

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('newsletter-attachments/delete') ? Route::get(\Locales::getRoute('newsletter-attachments/delete'), 'NewsletterAttachmentsController@delete')->name(\Locales::getRoutePrefix('newsletter-attachments/delete')) : '';
                        Route::delete('newsletter-attachments/destroy', 'NewsletterAttachmentsController@destroy')->name(\Locales::getRoutePrefix('newsletter-attachments/destroy'));
                        \Locales::isTranslatedRoute('newsletter-attachments/edit') ? Route::get(\Locales::getRoute('newsletter-attachments/edit') . '/{attachment?}', 'NewsletterAttachmentsController@edit')->name(\Locales::getRoutePrefix('newsletter-attachments/edit'))->where('attachment', '[0-9]+') : '';
                        Route::put('newsletter-attachments/update', 'NewsletterAttachmentsController@update')->name(\Locales::getRoutePrefix('newsletter-attachments/update'));
                    });
                    \Locales::isTranslatedRoute('newsletter-attachments') ? Route::get(\Locales::getRoute('newsletter-attachments') . '/{id?}/attachments', 'NewsletterAttachmentsController@index')->name(\Locales::getRoutePrefix('newsletter-attachments'))->where('id', '[0-9]+') : '';
                    Route::post('newsletter-attachments/upload/{chunk?}', 'NewsletterAttachmentsController@upload')->name(\Locales::getRoutePrefix('newsletter-attachments/upload'))->where('chunk', 'done');
                    Route::get('newsletter-attachments/download/{id}', 'NewsletterAttachmentsController@download')->name(\Locales::getRoutePrefix('newsletter-attachments/download'))->where('id', '[0-9]+');

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('newsletter-attachments-apartment/delete') ? Route::get(\Locales::getRoute('newsletter-attachments-apartment/delete'), 'NewsletterAttachmentsApartmentController@delete')->name(\Locales::getRoutePrefix('newsletter-attachments-apartment/delete')) : '';
                        Route::delete('newsletter-attachments-apartment/destroy', 'NewsletterAttachmentsApartmentController@destroy')->name(\Locales::getRoutePrefix('newsletter-attachments-apartment/destroy'));
                        \Locales::isTranslatedRoute('newsletter-attachments-apartment/edit') ? Route::get(\Locales::getRoute('newsletter-attachments-apartment/edit') . '/{attachment?}', 'NewsletterAttachmentsApartmentController@edit')->name(\Locales::getRoutePrefix('newsletter-attachments-apartment/edit'))->where('attachment', '[0-9]+') : '';
                        Route::put('newsletter-attachments-apartment/update', 'NewsletterAttachmentsApartmentController@update')->name(\Locales::getRoutePrefix('newsletter-attachments-apartment/update'));
                    });
                    \Locales::isTranslatedRoute('newsletter-attachments-apartment') ? Route::get(\Locales::getRoute('newsletter-attachments-apartment') . '/{id?}/attachments-apartment', 'NewsletterAttachmentsApartmentController@index')->name(\Locales::getRoutePrefix('newsletter-attachments-apartment'))->where('id', '[0-9]+') : '';
                    Route::post('newsletter-attachments-apartment/upload/{chunk?}', 'NewsletterAttachmentsApartmentController@upload')->name(\Locales::getRoutePrefix('newsletter-attachments-apartment/upload'))->where('chunk', 'done');
                    Route::get('newsletter-attachments-apartment/download/{id}', 'NewsletterAttachmentsApartmentController@download')->name(\Locales::getRoutePrefix('newsletter-attachments-apartment/download'))->where('id', '[0-9]+');

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('newsletter-attachments-owner/delete') ? Route::get(\Locales::getRoute('newsletter-attachments-owner/delete'), 'NewsletterAttachmentsOwnerController@delete')->name(\Locales::getRoutePrefix('newsletter-attachments-owner/delete')) : '';
                        Route::delete('newsletter-attachments-owner/destroy', 'NewsletterAttachmentsOwnerController@destroy')->name(\Locales::getRoutePrefix('newsletter-attachments-owner/destroy'));
                        \Locales::isTranslatedRoute('newsletter-attachments-owner/edit') ? Route::get(\Locales::getRoute('newsletter-attachments-owner/edit') . '/{attachment?}', 'NewsletterAttachmentsOwnerController@edit')->name(\Locales::getRoutePrefix('newsletter-attachments-owner/edit'))->where('attachment', '[0-9]+') : '';
                        Route::put('newsletter-attachments-owner/update', 'NewsletterAttachmentsOwnerController@update')->name(\Locales::getRoutePrefix('newsletter-attachments-owner/update'));
                    });
                    \Locales::isTranslatedRoute('newsletter-attachments-owner') ? Route::get(\Locales::getRoute('newsletter-attachments-owner') . '/{id?}/attachments-owner', 'NewsletterAttachmentsOwnerController@index')->name(\Locales::getRoutePrefix('newsletter-attachments-owner'))->where('id', '[0-9]+') : '';
                    Route::post('newsletter-attachments-owner/upload/{chunk?}', 'NewsletterAttachmentsOwnerController@upload')->name(\Locales::getRoutePrefix('newsletter-attachments-owner/upload'))->where('chunk', 'done');
                    Route::get('newsletter-attachments-owner/download/{id}', 'NewsletterAttachmentsOwnerController@download')->name(\Locales::getRoutePrefix('newsletter-attachments-owner/download'))->where('id', '[0-9]+');

                    Route::group(['middleware' => 'ajax'], function () {
                        Route::get('newsletter-templates/preview/{id}', 'NewsletterTemplatesController@preview')->name(\Locales::getRoutePrefix('newsletter-templates/preview'))->where('id', '[0-9]+');
                        \Locales::isTranslatedRoute('newsletter-templates/create') ? Route::get(\Locales::getRoute('newsletter-templates/create'), 'NewsletterTemplatesController@create')->name(\Locales::getRoutePrefix('newsletter-templates/create')) : '';
                        Route::post('newsletter-templates/store', 'NewsletterTemplatesController@store')->name(\Locales::getRoutePrefix('newsletter-templates/store'));
                        \Locales::isTranslatedRoute('newsletter-templates/edit') ? Route::get(\Locales::getRoute('newsletter-templates/edit') . '/{id?}', 'NewsletterTemplatesController@edit')->name(\Locales::getRoutePrefix('newsletter-templates/edit'))->where('id', '[0-9]+') : '';
                        Route::put('newsletter-templates/update', 'NewsletterTemplatesController@update')->name(\Locales::getRoutePrefix('newsletter-templates/update'));
                        \Locales::isTranslatedRoute('newsletter-templates/delete') ? Route::get(\Locales::getRoute('newsletter-templates/delete'), 'NewsletterTemplatesController@delete')->name(\Locales::getRoutePrefix('newsletter-templates/delete')) : '';
                        Route::delete('newsletter-templates/destroy', 'NewsletterTemplatesController@destroy')->name(\Locales::getRoutePrefix('newsletter-templates/destroy'));
                    });
                    \Locales::isTranslatedRoute('newsletter-templates') ? Route::get(\Locales::getRoute('newsletter-templates') . '/{id?}', 'NewsletterTemplatesController@index')->name(\Locales::getRoutePrefix('newsletter-templates'))->where('id', '[0-9]+') : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('newsletter-template-images/delete') ? Route::get(\Locales::getRoute('newsletter-template-images/delete'), 'NewsletterTemplateImagesController@delete')->name(\Locales::getRoutePrefix('newsletter-template-images/delete')) : '';
                        Route::delete('newsletter-template-images/destroy', 'NewsletterTemplateImagesController@destroy')->name(\Locales::getRoutePrefix('newsletter-template-images/destroy'));
                        \Locales::isTranslatedRoute('newsletter-template-images/edit') ? Route::get(\Locales::getRoute('newsletter-template-images/edit') . '/{image?}', 'NewsletterTemplateImagesController@edit')->name(\Locales::getRoutePrefix('newsletter-template-images/edit'))->where('image', '[0-9]+') : '';
                        Route::put('newsletter-template-images/update', 'NewsletterTemplateImagesController@update')->name(\Locales::getRoutePrefix('newsletter-template-images/update'));
                    });
                    \Locales::isTranslatedRoute('newsletter-template-images') ? Route::get(\Locales::getRoute('newsletter-template-images') . '/{id?}/images', 'NewsletterTemplateImagesController@index')->name(\Locales::getRoutePrefix('newsletter-template-images'))->where('id', '[0-9]+') : '';
                    Route::post('newsletter-template-images/upload/{chunk?}', 'NewsletterTemplateImagesController@upload')->name(\Locales::getRoutePrefix('newsletter-template-images/upload'))->where('chunk', 'done');

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('newsletter-template-attachments/delete') ? Route::get(\Locales::getRoute('newsletter-template-attachments/delete'), 'NewsletterTemplateAttachmentsController@delete')->name(\Locales::getRoutePrefix('newsletter-template-attachments/delete')) : '';
                        Route::delete('newsletter-template-attachments/destroy', 'NewsletterTemplateAttachmentsController@destroy')->name(\Locales::getRoutePrefix('newsletter-template-attachments/destroy'));
                        \Locales::isTranslatedRoute('newsletter-template-attachments/edit') ? Route::get(\Locales::getRoute('newsletter-template-attachments/edit') . '/{attachment?}', 'NewsletterTemplateAttachmentsController@edit')->name(\Locales::getRoutePrefix('newsletter-template-attachments/edit'))->where('attachment', '[0-9]+') : '';
                        Route::put('newsletter-template-attachments/update', 'NewsletterTemplateAttachmentsController@update')->name(\Locales::getRoutePrefix('newsletter-template-attachments/update'));
                    });
                    \Locales::isTranslatedRoute('newsletter-template-attachments') ? Route::get(\Locales::getRoute('newsletter-template-attachments') . '/{id?}/attachments', 'NewsletterTemplateAttachmentsController@index')->name(\Locales::getRoutePrefix('newsletter-template-attachments'))->where('id', '[0-9]+') : '';
                    Route::post('newsletter-template-attachments/upload/{chunk?}', 'NewsletterTemplateAttachmentsController@upload')->name(\Locales::getRoutePrefix('newsletter-template-attachments/upload'))->where('chunk', 'done');
                    Route::get('newsletter-template-attachments/download/{id}', 'NewsletterTemplateAttachmentsController@download')->name(\Locales::getRoutePrefix('newsletter-template-attachments/download'))->where('id', '[0-9]+');

                    \Locales::isTranslatedRoute('domains') ? Route::get(\Locales::getRoute('domains'), 'DomainController@index')->name(\Locales::getRoutePrefix('domains')) : '';
                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('domains/create') ? Route::get(\Locales::getRoute('domains/create'), 'DomainController@create')->name(\Locales::getRoutePrefix('domains/create')) : '';
                        Route::post('domains/store', 'DomainController@store')->name(\Locales::getRoutePrefix('domains/store'));
                        \Locales::isTranslatedRoute('domains/edit') ? Route::get(\Locales::getRoute('domains/edit') . '/{domain?}', 'DomainController@edit')->name(\Locales::getRoutePrefix('domains/edit'))->where('domain', '[0-9]+') : '';
                        Route::put('domains/update', 'DomainController@update')->name(\Locales::getRoutePrefix('domains/update'));
                        \Locales::isTranslatedRoute('domains/delete') ? Route::get(\Locales::getRoute('domains/delete'), 'DomainController@delete')->name(\Locales::getRoutePrefix('domains/delete')) : '';
                        Route::delete('domains/destroy', 'DomainController@destroy')->name(\Locales::getRoutePrefix('domains/destroy'));
                    });

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('rental-contracts/create') ? Route::get(\Locales::getRoute('rental-contracts/create'), 'RentalContractController@create')->name(\Locales::getRoutePrefix('rental-contracts/create')) : '';
                        Route::post('rental-contracts/store', 'RentalContractController@store')->name(\Locales::getRoutePrefix('rental-contracts/store'));
                        \Locales::isTranslatedRoute('rental-contracts/edit') ? Route::get(\Locales::getRoute('rental-contracts/edit') . '/{id?}', 'RentalContractController@edit')->name(\Locales::getRoutePrefix('rental-contracts/edit'))->where('id', '[0-9]+') : '';
                        Route::put('rental-contracts/update', 'RentalContractController@update')->name(\Locales::getRoutePrefix('rental-contracts/update'));
                        \Locales::isTranslatedRoute('rental-contracts/delete') ? Route::get(\Locales::getRoute('rental-contracts/delete'), 'RentalContractController@delete')->name(\Locales::getRoutePrefix('rental-contracts/delete')) : '';
                        Route::delete('rental-contracts/destroy', 'RentalContractController@destroy')->name(\Locales::getRoutePrefix('rental-contracts/destroy'));
                    });
                    \Locales::isTranslatedRoute('rental-contracts') ? Route::get(\Locales::getRoute('rental-contracts') . '/{id?}', 'RentalContractController@index')->name(\Locales::getRoutePrefix('rental-contracts'))->where('id', '[0-9]+') : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('navigation/create') ? Route::get(\Locales::getRoute('navigation/create'), 'NavigationController@create')->name(\Locales::getRoutePrefix('navigation/create')) : '';
                        \Locales::isTranslatedRoute('navigation/store') ? Route::post(\Locales::getRoute('navigation/store'), 'NavigationController@store')->name(\Locales::getRoutePrefix('navigation/store')) : '';
                        \Locales::isTranslatedRoute('navigation/edit') ? Route::get(\Locales::getRoute('navigation/edit') . '/{page?}', 'NavigationController@edit')->name(\Locales::getRoutePrefix('navigation/edit'))->where('page', '[0-9]+') : '';
                        \Locales::isTranslatedRoute('navigation/update') ? Route::put(\Locales::getRoute('navigation/update'), 'NavigationController@update')->name(\Locales::getRoutePrefix('navigation/update')) : '';
                        \Locales::isTranslatedRoute('navigation/delete') ? Route::get(\Locales::getRoute('navigation/delete'), 'NavigationController@delete')->name(\Locales::getRoutePrefix('navigation/delete')) : '';
                        \Locales::isTranslatedRoute('navigation/destroy') ? Route::delete(\Locales::getRoute('navigation/destroy'), 'NavigationController@destroy')->name(\Locales::getRoutePrefix('navigation/destroy')) : '';
                        \Locales::isTranslatedRoute('navigation/change-status') ? Route::get(\Locales::getRoute('navigation/change-status') . '/{id}/{status}', 'NavigationController@changeStatus')->name(\Locales::getRoutePrefix('navigation/change-status'))->where('id', '[0-9]+')->where('status', '[0-9]+') : '';
                    });
                    \Locales::isTranslatedRoute('navigation') ? Route::get(\Locales::getRoute('navigation') . '/{locale?}/{slugs?}', 'NavigationController@index')->name(\Locales::getRoutePrefix('navigation'))->where('locale', '[a-z-]+')->where('slugs', '(.*)') : '';

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('extra-services/create') ? Route::get(\Locales::getRoute('extra-services/create'), 'ExtraServiceController@create')->name(\Locales::getRoutePrefix('extra-services/create')) : '';
                        \Locales::isTranslatedRoute('extra-services/store') ? Route::post(\Locales::getRoute('extra-services/store'), 'ExtraServiceController@store')->name(\Locales::getRoutePrefix('extra-services/store')) : '';
                        \Locales::isTranslatedRoute('extra-services/edit') ? Route::get(\Locales::getRoute('extra-services/edit') . '/{id?}', 'ExtraServiceController@edit')->name(\Locales::getRoutePrefix('extra-services/edit'))->where('id', '[0-9]+') : '';
                        \Locales::isTranslatedRoute('extra-services/update') ? Route::put(\Locales::getRoute('extra-services/update'), 'ExtraServiceController@update')->name(\Locales::getRoutePrefix('extra-services/update')) : '';
                        \Locales::isTranslatedRoute('extra-services/delete') ? Route::get(\Locales::getRoute('extra-services/delete'), 'ExtraServiceController@delete')->name(\Locales::getRoutePrefix('extra-services/delete')) : '';
                        \Locales::isTranslatedRoute('extra-services/destroy') ? Route::delete(\Locales::getRoute('extra-services/destroy'), 'ExtraServiceController@destroy')->name(\Locales::getRoutePrefix('extra-services/destroy')) : '';
                    });
                    \Locales::isTranslatedRoute('extra-services') ? Route::get(\Locales::getRoute('extra-services') . '/{id?}', 'ExtraServiceController@index')->name(\Locales::getRoutePrefix('extra-services'))->where('id', '(.*)') : '';

                    \Locales::isTranslatedRoute('locales') ? Route::get(\Locales::getRoute('locales'), 'LocaleController@index')->name(\Locales::getRoutePrefix('locales')) : '';
                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('locales/create') ? Route::get(\Locales::getRoute('locales/create'), 'LocaleController@create')->name(\Locales::getRoutePrefix('locales/create')) : '';
                        Route::post('locales/store', 'LocaleController@store')->name(\Locales::getRoutePrefix('locales/store'));
                        \Locales::isTranslatedRoute('locales/edit') ? Route::get(\Locales::getRoute('locales/edit') . '/{locale?}', 'LocaleController@edit')->name(\Locales::getRoutePrefix('locales/edit'))->where('locale', '[0-9]+') : '';
                        Route::put('locales/update', 'LocaleController@update')->name(\Locales::getRoutePrefix('locales/update'));
                        \Locales::isTranslatedRoute('locales/delete') ? Route::get(\Locales::getRoute('locales/delete'), 'LocaleController@delete')->name(\Locales::getRoutePrefix('locales/delete')) : '';
                        Route::delete('locales/destroy', 'LocaleController@destroy')->name(\Locales::getRoutePrefix('locales/destroy'));
                    });

                    \Locales::isTranslatedRoute('countries') ? Route::get(\Locales::getRoute('countries'), 'CountryController@index')->name(\Locales::getRoutePrefix('countries')) : '';
                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('countries/create') ? Route::get(\Locales::getRoute('countries/create'), 'CountryController@create')->name(\Locales::getRoutePrefix('countries/create')) : '';
                        Route::post('countries/store', 'CountryController@store')->name(\Locales::getRoutePrefix('countries/store'));
                        \Locales::isTranslatedRoute('countries/edit') ? Route::get(\Locales::getRoute('countries/edit') . '/{country?}', 'CountryController@edit')->name(\Locales::getRoutePrefix('countries/edit'))->where('country', '[0-9]+') : '';
                        Route::put('countries/update', 'CountryController@update')->name(\Locales::getRoutePrefix('countries/update'));
                        \Locales::isTranslatedRoute('countries/delete') ? Route::get(\Locales::getRoute('countries/delete'), 'CountryController@delete')->name(\Locales::getRoutePrefix('countries/delete')) : '';
                        Route::delete('countries/destroy', 'CountryController@destroy')->name(\Locales::getRoutePrefix('countries/destroy'));
                    });

                    \Locales::isTranslatedRoute('deductions') ? Route::get(\Locales::getRoute('deductions'), 'DeductionController@index')->name(\Locales::getRoutePrefix('deductions')) : '';
                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('deductions/create') ? Route::get(\Locales::getRoute('deductions/create'), 'DeductionController@create')->name(\Locales::getRoutePrefix('deductions/create')) : '';
                        Route::post('deductions/store', 'DeductionController@store')->name(\Locales::getRoutePrefix('deductions/store'));
                        \Locales::isTranslatedRoute('deductions/edit') ? Route::get(\Locales::getRoute('deductions/edit') . '/{deduction?}', 'DeductionController@edit')->name(\Locales::getRoutePrefix('deductions/edit'))->where('deduction', '[0-9]+') : '';
                        Route::put('deductions/update', 'DeductionController@update')->name(\Locales::getRoutePrefix('deductions/update'));
                        \Locales::isTranslatedRoute('deductions/delete') ? Route::get(\Locales::getRoute('deductions/delete'), 'DeductionController@delete')->name(\Locales::getRoutePrefix('deductions/delete')) : '';
                        Route::delete('deductions/destroy', 'DeductionController@destroy')->name(\Locales::getRoutePrefix('deductions/destroy'));
                    });

                    \Locales::isTranslatedRoute('payment-methods') ? Route::get(\Locales::getRoute('payment-methods'), 'PaymentMethodController@index')->name(\Locales::getRoutePrefix('payment-methods')) : '';
                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('payment-methods/create') ? Route::get(\Locales::getRoute('payment-methods/create'), 'PaymentMethodController@create')->name(\Locales::getRoutePrefix('payment-methods/create')) : '';
                        Route::post('payment-methods/store', 'PaymentMethodController@store')->name(\Locales::getRoutePrefix('payment-methods/store'));
                        \Locales::isTranslatedRoute('payment-methods/edit') ? Route::get(\Locales::getRoute('payment-methods/edit') . '/{id?}', 'PaymentMethodController@edit')->name(\Locales::getRoutePrefix('payment-methods/edit'))->where('id', '[0-9]+') : '';
                        Route::put('payment-methods/update', 'PaymentMethodController@update')->name(\Locales::getRoutePrefix('payment-methods/update'));
                        \Locales::isTranslatedRoute('payment-methods/delete') ? Route::get(\Locales::getRoute('payment-methods/delete'), 'PaymentMethodController@delete')->name(\Locales::getRoutePrefix('payment-methods/delete')) : '';
                        Route::delete('payment-methods/destroy', 'PaymentMethodController@destroy')->name(\Locales::getRoutePrefix('payment-methods/destroy'));
                    });

                    \Locales::isTranslatedRoute('rental-companies') ? Route::get(\Locales::getRoute('rental-companies'), 'RentalCompanyController@index')->name(\Locales::getRoutePrefix('rental-companies')) : '';
                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('rental-companies/create') ? Route::get(\Locales::getRoute('rental-companies/create'), 'RentalCompanyController@create')->name(\Locales::getRoutePrefix('rental-companies/create')) : '';
                        Route::post('rental-companies/store', 'RentalCompanyController@store')->name(\Locales::getRoutePrefix('rental-companies/store'));
                        \Locales::isTranslatedRoute('rental-companies/edit') ? Route::get(\Locales::getRoute('rental-companies/edit') . '/{company?}', 'RentalCompanyController@edit')->name(\Locales::getRoutePrefix('rental-companies/edit'))->where('company', '[0-9]+') : '';
                        Route::put('rental-companies/update', 'RentalCompanyController@update')->name(\Locales::getRoutePrefix('rental-companies/update'));
                        \Locales::isTranslatedRoute('rental-companies/delete') ? Route::get(\Locales::getRoute('rental-companies/delete'), 'RentalCompanyController@delete')->name(\Locales::getRoutePrefix('rental-companies/delete')) : '';
                        Route::delete('rental-companies/destroy', 'RentalCompanyController@destroy')->name(\Locales::getRoutePrefix('rental-companies/destroy'));
                        \Locales::isTranslatedRoute('rental-companies/change-status') ? Route::get(\Locales::getRoute('rental-companies/change-status') . '/{id}/{status}', 'RentalCompanyController@changeStatus')->name(\Locales::getRoutePrefix('rental-companies/change-status'))->where('id', '[0-9]+')->where('status', '[0-9]+') : '';
                    });

                    \Locales::isTranslatedRoute('rental-rates') ? Route::get(\Locales::getRoute('rental-rates'), 'RentalRatesController@index')->name(\Locales::getRoutePrefix('rental-rates')) : '';
                    Route::group(['middleware' => 'ajax'], function() {
                        \Locales::isTranslatedRoute('rental-rates/create') ? Route::get(\Locales::getRoute('rental-rates/create'), 'RentalRatesController@create')->name(\Locales::getRoutePrefix('rental-rates/create')) : '';
                        Route::post('rental-rates/store', 'RentalRatesController@store')->name(\Locales::getRoutePrefix('rental-rates/store'));
                        \Locales::isTranslatedRoute('rental-rates/edit') ? Route::get(\Locales::getRoute('rental-rates/edit') . '/{id?}', 'RentalRatesController@edit')->name(\Locales::getRoutePrefix('rental-rates/edit'))->where('id', '[0-9]+') : '';
                        Route::put('rental-rates/update', 'RentalRatesController@update')->name(\Locales::getRoutePrefix('rental-rates/update'));
                        \Locales::isTranslatedRoute('rental-rates/delete') ? Route::get(\Locales::getRoute('rental-rates/delete'), 'RentalRatesController@delete')->name(\Locales::getRoutePrefix('rental-rates/delete')) : '';
                        Route::delete('rental-rates/destroy', 'RentalRatesController@destroy')->name(\Locales::getRoutePrefix('rental-rates/destroy'));
                        Route::post('rental-rates/save', 'RentalRatesController@save')->name(\Locales::getRoutePrefix('rental-rates/save'));
                    });

                    \Locales::isTranslatedRoute('airports') ? Route::get(\Locales::getRoute('airports'), 'AirportController@index')->name(\Locales::getRoutePrefix('airports')) : '';
                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('airports/create') ? Route::get(\Locales::getRoute('airports/create'), 'AirportController@create')->name(\Locales::getRoutePrefix('airports/create')) : '';
                        Route::post('airports/store', 'AirportController@store')->name(\Locales::getRoutePrefix('airports/store'));
                        \Locales::isTranslatedRoute('airports/edit') ? Route::get(\Locales::getRoute('airports/edit') . '/{airport?}', 'AirportController@edit')->name(\Locales::getRoutePrefix('airports/edit'))->where('airport', '[0-9]+') : '';
                        Route::put('airports/update', 'AirportController@update')->name(\Locales::getRoutePrefix('airports/update'));
                        \Locales::isTranslatedRoute('airports/delete') ? Route::get(\Locales::getRoute('airports/delete'), 'AirportController@delete')->name(\Locales::getRoutePrefix('airports/delete')) : '';
                        Route::delete('airports/destroy', 'AirportController@destroy')->name(\Locales::getRoutePrefix('airports/destroy'));
                    });

                    \Locales::isTranslatedRoute('proxies') ? Route::get(\Locales::getRoute('proxies'), 'ProxyController@index')->name(\Locales::getRoutePrefix('proxies')) : '';
                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('proxies/create') ? Route::get(\Locales::getRoute('proxies/create'), 'ProxyController@create')->name(\Locales::getRoutePrefix('proxies/create')) : '';
                        Route::post('proxies/store', 'ProxyController@store')->name(\Locales::getRoutePrefix('proxies/store'));
                        \Locales::isTranslatedRoute('proxies/edit') ? Route::get(\Locales::getRoute('proxies/edit') . '/{proxy?}', 'ProxyController@edit')->name(\Locales::getRoutePrefix('proxies/edit'))->where('proxy', '[0-9]+') : '';
                        Route::put('proxies/update', 'ProxyController@update')->name(\Locales::getRoutePrefix('proxies/update'));
                        \Locales::isTranslatedRoute('proxies/delete') ? Route::get(\Locales::getRoute('proxies/delete'), 'ProxyController@delete')->name(\Locales::getRoutePrefix('proxies/delete')) : '';
                        Route::delete('proxies/destroy', 'ProxyController@destroy')->name(\Locales::getRoutePrefix('proxies/destroy'));
                        Route::get('proxies/change-status/{id}/{status}', 'ProxyController@changeStatus')->name(\Locales::getRoutePrefix('proxies/change-status'))->where('id', '[0-9]+')->where('status', '[0-9]+');
                    });

                    \Locales::isTranslatedRoute('management-companies') ? Route::get(\Locales::getRoute('management-companies'), 'ManagementCompanyController@index')->name(\Locales::getRoutePrefix('management-companies')) : '';
                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('management-companies/create') ? Route::get(\Locales::getRoute('management-companies/create'), 'ManagementCompanyController@create')->name(\Locales::getRoutePrefix('management-companies/create')) : '';
                        Route::post('management-companies/store', 'ManagementCompanyController@store')->name(\Locales::getRoutePrefix('management-companies/store'));
                        \Locales::isTranslatedRoute('management-companies/edit') ? Route::get(\Locales::getRoute('management-companies/edit') . '/{company?}', 'ManagementCompanyController@edit')->name(\Locales::getRoutePrefix('management-companies/edit'))->where('company', '[0-9]+') : '';
                        Route::put('management-companies/update', 'ManagementCompanyController@update')->name(\Locales::getRoutePrefix('management-companies/update'));
                        \Locales::isTranslatedRoute('management-companies/delete') ? Route::get(\Locales::getRoute('management-companies/delete'), 'ManagementCompanyController@delete')->name(\Locales::getRoutePrefix('management-companies/delete')) : '';
                        Route::delete('management-companies/destroy', 'ManagementCompanyController@destroy')->name(\Locales::getRoutePrefix('management-companies/destroy'));
                    });

                    \Locales::isTranslatedRoute('furniture') ? Route::get(\Locales::getRoute('furniture'), 'FurnitureController@index')->name(\Locales::getRoutePrefix('furniture')) : '';
                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('furniture/create') ? Route::get(\Locales::getRoute('furniture/create'), 'FurnitureController@create')->name(\Locales::getRoutePrefix('furniture/create')) : '';
                        Route::post('furniture/store', 'FurnitureController@store')->name(\Locales::getRoutePrefix('furniture/store'));
                        \Locales::isTranslatedRoute('furniture/edit') ? Route::get(\Locales::getRoute('furniture/edit') . '/{id?}', 'FurnitureController@edit')->name(\Locales::getRoutePrefix('furniture/edit'))->where('id', '[0-9]+') : '';
                        Route::put('furniture/update', 'FurnitureController@update')->name(\Locales::getRoutePrefix('furniture/update'));
                        \Locales::isTranslatedRoute('furniture/delete') ? Route::get(\Locales::getRoute('furniture/delete'), 'FurnitureController@delete')->name(\Locales::getRoutePrefix('furniture/delete')) : '';
                        Route::delete('furniture/destroy', 'FurnitureController@destroy')->name(\Locales::getRoutePrefix('furniture/destroy'));
                    });

                    \Locales::isTranslatedRoute('rooms') ? Route::get(\Locales::getRoute('rooms'), 'RoomController@index')->name(\Locales::getRoutePrefix('rooms')) : '';
                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('rooms/create') ? Route::get(\Locales::getRoute('rooms/create'), 'RoomController@create')->name(\Locales::getRoutePrefix('rooms/create')) : '';
                        Route::post('rooms/store', 'RoomController@store')->name(\Locales::getRoutePrefix('rooms/store'));
                        \Locales::isTranslatedRoute('rooms/edit') ? Route::get(\Locales::getRoute('rooms/edit') . '/{room?}', 'RoomController@edit')->name(\Locales::getRoutePrefix('rooms/edit'))->where('room', '[0-9]+') : '';
                        Route::put('rooms/update', 'RoomController@update')->name(\Locales::getRoutePrefix('rooms/update'));
                        \Locales::isTranslatedRoute('rooms/delete') ? Route::get(\Locales::getRoute('rooms/delete'), 'RoomController@delete')->name(\Locales::getRoutePrefix('rooms/delete')) : '';
                        Route::delete('rooms/destroy', 'RoomController@destroy')->name(\Locales::getRoutePrefix('rooms/destroy'));
                    });

                    \Locales::isTranslatedRoute('polls') ? Route::get(\Locales::getRoute('polls'), 'PollController@index')->name(\Locales::getRoutePrefix('polls')) : '';
                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('polls/create') ? Route::get(\Locales::getRoute('polls/create'), 'PollController@create')->name(\Locales::getRoutePrefix('polls/create')) : '';
                        Route::post('polls/store', 'PollController@store')->name(\Locales::getRoutePrefix('polls/store'));
                        \Locales::isTranslatedRoute('polls/edit') ? Route::get(\Locales::getRoute('polls/edit') . '/{room?}', 'PollController@edit')->name(\Locales::getRoutePrefix('polls/edit'))->where('room', '[0-9]+') : '';
                        Route::put('polls/update', 'PollController@update')->name(\Locales::getRoutePrefix('polls/update'));
                        \Locales::isTranslatedRoute('polls/delete') ? Route::get(\Locales::getRoute('polls/delete'), 'PollController@delete')->name(\Locales::getRoutePrefix('polls/delete')) : '';
                        Route::delete('polls/destroy', 'PollController@destroy')->name(\Locales::getRoutePrefix('polls/destroy'));
                    });
                    \Locales::isTranslatedRoute('polls') ? Route::get(\Locales::getRoute('polls') . '/{id?}', 'PollController@index')->name(\Locales::getRoutePrefix('polls'))->where('id', '[0-9]+') : '';

                    \Locales::isTranslatedRoute('signatures') ? Route::get(\Locales::getRoute('signatures'), 'SignatureController@index')->name(\Locales::getRoutePrefix('signatures')) : '';
                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('signatures/create') ? Route::get(\Locales::getRoute('signatures/create'), 'SignatureController@create')->name(\Locales::getRoutePrefix('signatures/create')) : '';
                        Route::post('signatures/store', 'SignatureController@store')->name(\Locales::getRoutePrefix('signatures/store'));
                        \Locales::isTranslatedRoute('signatures/edit') ? Route::get(\Locales::getRoute('signatures/edit') . '/{signature?}', 'SignatureController@edit')->name(\Locales::getRoutePrefix('signatures/edit'))->where('signature', '[0-9]+') : '';
                        Route::put('signatures/update', 'SignatureController@update')->name(\Locales::getRoutePrefix('signatures/update'));
                        \Locales::isTranslatedRoute('signatures/delete') ? Route::get(\Locales::getRoute('signatures/delete'), 'SignatureController@delete')->name(\Locales::getRoutePrefix('signatures/delete')) : '';
                        Route::delete('signatures/destroy', 'SignatureController@destroy')->name(\Locales::getRoutePrefix('signatures/destroy'));
                    });

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('signature-files/delete') ? Route::get(\Locales::getRoute('signature-files/delete'), 'SignatureFilesController@delete')->name(\Locales::getRoutePrefix('signature-files/delete')) : '';
                        Route::delete('signature-files/destroy', 'SignatureFilesController@destroy')->name(\Locales::getRoutePrefix('signature-files/destroy'));
                        \Locales::isTranslatedRoute('signature-files/edit') ? Route::get(\Locales::getRoute('signature-files/edit') . '/{attachment?}', 'SignatureFilesController@edit')->name(\Locales::getRoutePrefix('signature-files/edit'))->where('attachment', '[0-9]+') : '';
                        Route::put('signature-files/update', 'SignatureFilesController@update')->name(\Locales::getRoutePrefix('signature-files/update'));
                    });
                    \Locales::isTranslatedRoute('signature-files') ? Route::get(\Locales::getRoute('signatures') . '/{id?}', 'SignatureFilesController@index')->name(\Locales::getRoutePrefix('signature-files'))->where('id', '[0-9]+') : '';
                    Route::post('signature-files/upload/{chunk?}', 'SignatureFilesController@upload')->name(\Locales::getRoutePrefix('signature-files/upload'))->where('chunk', 'done');

                    \Locales::isTranslatedRoute('views') ? Route::get(\Locales::getRoute('views'), 'ViewController@index')->name(\Locales::getRoutePrefix('views')) : '';
                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('views/create') ? Route::get(\Locales::getRoute('views/create'), 'ViewController@create')->name(\Locales::getRoutePrefix('views/create')) : '';
                        Route::post('views/store', 'ViewController@store')->name(\Locales::getRoutePrefix('views/store'));
                        \Locales::isTranslatedRoute('views/edit') ? Route::get(\Locales::getRoute('views/edit') . '/{view?}', 'ViewController@edit')->name(\Locales::getRoutePrefix('views/edit'))->where('view', '[0-9]+') : '';
                        Route::put('views/update', 'ViewController@update')->name(\Locales::getRoutePrefix('views/update'));
                        \Locales::isTranslatedRoute('views/delete') ? Route::get(\Locales::getRoute('views/delete'), 'ViewController@delete')->name(\Locales::getRoutePrefix('views/delete')) : '';
                        Route::delete('views/destroy', 'ViewController@destroy')->name(\Locales::getRoutePrefix('views/destroy'));
                    });

                    \Locales::isTranslatedRoute('projects') ? Route::get(\Locales::getRoute('projects'), 'ProjectController@index')->name(\Locales::getRoutePrefix('projects')) : '';
                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('projects/create') ? Route::get(\Locales::getRoute('projects/create'), 'ProjectController@create')->name(\Locales::getRoutePrefix('projects/create')) : '';
                        Route::post('projects/store', 'ProjectController@store')->name(\Locales::getRoutePrefix('projects/store'));
                        \Locales::isTranslatedRoute('projects/edit') ? Route::get(\Locales::getRoute('projects/edit') . '/{project?}', 'ProjectController@edit')->name(\Locales::getRoutePrefix('projects/edit'))->where('project', '[0-9]+') : '';
                        Route::put('projects/update', 'ProjectController@update')->name(\Locales::getRoutePrefix('projects/update'));
                        \Locales::isTranslatedRoute('projects/delete') ? Route::get(\Locales::getRoute('projects/delete'), 'ProjectController@delete')->name(\Locales::getRoutePrefix('projects/delete')) : '';
                        Route::delete('projects/destroy', 'ProjectController@destroy')->name(\Locales::getRoutePrefix('projects/destroy'));
                    });

                    \Locales::isTranslatedRoute('buildings') ? Route::get(\Locales::getRoute('projects') . '/{project?}/{buildingsSlug?}/{building?}', 'BuildingController@index')->name(\Locales::getRoutePrefix('buildings'))->where('project', '[0-9]+')->where('buildingsSlug', 'buildings')->where('building', '[0-9]+') : '';
                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('buildings/create') ? Route::get(\Locales::getRoute('buildings/create'), 'BuildingController@create')->name(\Locales::getRoutePrefix('buildings/create')) : '';
                        Route::post('buildings/store', 'BuildingController@store')->name(\Locales::getRoutePrefix('buildings/store'));
                        \Locales::isTranslatedRoute('buildings/edit') ? Route::get(\Locales::getRoute('buildings/edit') . '/{building?}', 'BuildingController@edit')->name(\Locales::getRoutePrefix('buildings/edit'))->where('building', '[0-9]+') : '';
                        Route::put('buildings/update', 'BuildingController@update')->name(\Locales::getRoutePrefix('buildings/update'));
                        \Locales::isTranslatedRoute('buildings/delete') ? Route::get(\Locales::getRoute('buildings/delete'), 'BuildingController@delete')->name(\Locales::getRoutePrefix('buildings/delete')) : '';
                        Route::delete('buildings/destroy', 'BuildingController@destroy')->name(\Locales::getRoutePrefix('buildings/destroy'));
                    });

                    \Locales::isTranslatedRoute('floors') ? Route::get(\Locales::getRoute('projects') . '/{project?}/{buildingsSlug?}/{building?}/{floorsSlug?}', 'FloorController@index')->name(\Locales::getRoutePrefix('floors'))->where('project', '[0-9]+')->where('buildingsSlug', 'buildings')->where('building', '[0-9]+')->where('floorsSlug', 'floors') : '';
                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('floors/create') ? Route::get(\Locales::getRoute('floors/create'), 'FloorController@create')->name(\Locales::getRoutePrefix('floors/create')) : '';
                        Route::post('floors/store', 'FloorController@store')->name(\Locales::getRoutePrefix('floors/store'));
                        \Locales::isTranslatedRoute('floors/edit') ? Route::get(\Locales::getRoute('floors/edit') . '/{floor?}', 'FloorController@edit')->name(\Locales::getRoutePrefix('floors/edit'))->where('floor', '[0-9]+') : '';
                        Route::put('floors/update', 'FloorController@update')->name(\Locales::getRoutePrefix('floors/update'));
                        \Locales::isTranslatedRoute('floors/delete') ? Route::get(\Locales::getRoute('floors/delete'), 'FloorController@delete')->name(\Locales::getRoutePrefix('floors/delete')) : '';
                        Route::delete('floors/destroy', 'FloorController@destroy')->name(\Locales::getRoutePrefix('floors/destroy'));
                    });

                    \Locales::isTranslatedRoute('project-apartments') ? Route::get(\Locales::getRoute('projects') . '/{project?}/{buildingsSlug?}/{building?}/{floorsSlug?}/{floor?}', 'ApartmentController@indexProject')->name(\Locales::getRoutePrefix('project-apartments'))->where('project', '[0-9]+')->where('buildingsSlug', 'buildings')->where('building', '[0-9]+')->where('floorsSlug', 'floors')->where('floor', '[0-9]+') : '';

                    \Locales::isTranslatedRoute('buildings-mm') ? Route::get(\Locales::getRoute('projects') . '/{project?}/{buildingsSlug?}/{building?}/{mmSlug?}/{year?}', 'BuildingMmController@index')->name(\Locales::getRoutePrefix('buildings-mm'))->where('project', '[0-9]+')->where('buildingsSlug', 'buildings')->where('building', '[0-9]+')->where('mmSlug', 'mm')->where('year', '[0-9]+') : '';
                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('buildings-mm/create') ? Route::get(\Locales::getRoute('buildings-mm/create'), 'BuildingMmController@create')->name(\Locales::getRoutePrefix('buildings-mm/create')) : '';
                        Route::post('buildings-mm/store', 'BuildingMmController@store')->name(\Locales::getRoutePrefix('buildings-mm/store'));
                        \Locales::isTranslatedRoute('buildings-mm/edit') ? Route::get(\Locales::getRoute('buildings-mm/edit') . '/{id?}', 'BuildingMmController@edit')->name(\Locales::getRoutePrefix('buildings-mm/edit'))->where('id', '[0-9]+') : '';
                        Route::put('buildings-mm/update', 'BuildingMmController@update')->name(\Locales::getRoutePrefix('buildings-mm/update'));
                        \Locales::isTranslatedRoute('buildings-mm/delete') ? Route::get(\Locales::getRoute('buildings-mm/delete'), 'BuildingMmController@delete')->name(\Locales::getRoutePrefix('buildings-mm/delete')) : '';
                        Route::delete('buildings-mm/destroy', 'BuildingMmController@destroy')->name(\Locales::getRoutePrefix('buildings-mm/destroy'));
                    });

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('buildings-mm-documents/delete') ? Route::get(\Locales::getRoute('buildings-mm-documents/delete'), 'BuildingMmDocumentsController@delete')->name(\Locales::getRoutePrefix('buildings-mm-documents/delete')) : '';
                        Route::delete('buildings-mm-documents/destroy', 'BuildingMmDocumentsController@destroy')->name(\Locales::getRoutePrefix('buildings-mm-documents/destroy'));
                        \Locales::isTranslatedRoute('buildings-mm-documents/edit') ? Route::get(\Locales::getRoute('buildings-mm-documents/edit') . '/{file?}', 'BuildingMmDocumentsController@edit')->name(\Locales::getRoutePrefix('buildings-mm-documents/edit'))->where('file', '[0-9]+') : '';
                        Route::put('buildings-mm-documents/update', 'BuildingMmDocumentsController@update')->name(\Locales::getRoutePrefix('buildings-mm-documents/update'));
                    });
                    \Locales::isTranslatedRoute('buildings-mm-documents') ? Route::get(\Locales::getRoute('projects') . '/{project?}/{buildingsSlug?}/{building?}/{mmSlug?}/{year?}/{mm?}', 'BuildingMmDocumentsController@index')->name(\Locales::getRoutePrefix('buildings-mm-documents'))->where('project', '[0-9]+')->where('buildingsSlug', 'buildings')->where('building', '[0-9]+')->where('mmSlug', 'mm')->where('year', '[0-9]+')->where('mm', '[0-9]+') : '';
                    Route::post('buildings-mm-documents/upload/{chunk?}', 'BuildingMmDocumentsController@upload')->name(\Locales::getRoutePrefix('buildings-mm-documents/upload'))->where('chunk', 'done');
                    Route::get('buildings-mm-documents/download/{id}', 'BuildingMmDocumentsController@download')->name(\Locales::getRoutePrefix('buildings-mm-documents/download'))->where('id', '[0-9]+');

                    \Locales::isTranslatedRoute('condominium') ? Route::get(\Locales::getRoute('projects') . '/{project?}/{buildingsSlug?}/{building?}/{cSlug?}/{year?}', 'CondominiumController@index')->name(\Locales::getRoutePrefix('condominium'))->where('project', '[0-9]+')->where('buildingsSlug', 'buildings')->where('building', '[0-9]+')->where('cSlug', 'condominium')->where('year', '[0-9]+') : '';
                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('condominium/create') ? Route::get(\Locales::getRoute('condominium/create'), 'CondominiumController@create')->name(\Locales::getRoutePrefix('condominium/create')) : '';
                        Route::post('condominium/store', 'CondominiumController@store')->name(\Locales::getRoutePrefix('condominium/store'));
                        \Locales::isTranslatedRoute('condominium/edit') ? Route::get(\Locales::getRoute('condominium/edit') . '/{id?}', 'CondominiumController@edit')->name(\Locales::getRoutePrefix('condominium/edit'))->where('id', '[0-9]+') : '';
                        Route::put('condominium/update', 'CondominiumController@update')->name(\Locales::getRoutePrefix('condominium/update'));
                        \Locales::isTranslatedRoute('condominium/delete') ? Route::get(\Locales::getRoute('condominium/delete'), 'CondominiumController@delete')->name(\Locales::getRoutePrefix('condominium/delete')) : '';
                        Route::delete('condominium/destroy', 'CondominiumController@destroy')->name(\Locales::getRoutePrefix('condominium/destroy'));
                    });

                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('condominium-documents/delete') ? Route::get(\Locales::getRoute('condominium-documents/delete'), 'CondominiumDocumentsController@delete')->name(\Locales::getRoutePrefix('condominium-documents/delete')) : '';
                        Route::delete('condominium-documents/destroy', 'CondominiumDocumentsController@destroy')->name(\Locales::getRoutePrefix('condominium-documents/destroy'));
                        \Locales::isTranslatedRoute('condominium-documents/edit') ? Route::get(\Locales::getRoute('condominium-documents/edit') . '/{file?}', 'CondominiumDocumentsController@edit')->name(\Locales::getRoutePrefix('condominium-documents/edit'))->where('file', '[0-9]+') : '';
                        Route::put('condominium-documents/update', 'CondominiumDocumentsController@update')->name(\Locales::getRoutePrefix('condominium-documents/update'));
                    });
                    \Locales::isTranslatedRoute('condominium-documents') ? Route::get(\Locales::getRoute('projects') . '/{project?}/{buildingsSlug?}/{building?}/{cSlug?}/{year?}/{condominium?}', 'CondominiumDocumentsController@index')->name(\Locales::getRoutePrefix('condominium-documents'))->where('project', '[0-9]+')->where('buildingsSlug', 'buildings')->where('building', '[0-9]+')->where('cSlug', 'condominium')->where('year', '[0-9]+')->where('condominium', '[0-9]+') : '';
                    Route::post('condominium-documents/upload/{chunk?}', 'CondominiumDocumentsController@upload')->name(\Locales::getRoutePrefix('condominium-documents/upload'))->where('chunk', 'done');
                    Route::get('condominium-documents/download/{id}', 'CondominiumDocumentsController@download')->name(\Locales::getRoutePrefix('condominium-documents/download'))->where('id', '[0-9]+');

                    \Locales::isTranslatedRoute('years') ? Route::get(\Locales::getRoute('years'), 'YearController@index')->name(\Locales::getRoutePrefix('years')) : '';
                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('years/create') ? Route::get(\Locales::getRoute('years/create'), 'YearController@create')->name(\Locales::getRoutePrefix('years/create')) : '';
                        Route::post('years/store', 'YearController@store')->name(\Locales::getRoutePrefix('years/store'));
                        \Locales::isTranslatedRoute('years/edit') ? Route::get(\Locales::getRoute('years/edit') . '/{id?}', 'YearController@edit')->name(\Locales::getRoutePrefix('years/edit'))->where('id', '[0-9]+') : '';
                        Route::put('years/update', 'YearController@update')->name(\Locales::getRoutePrefix('years/update'));
                    });

                    \Locales::isTranslatedRoute('recipients') ? Route::get(\Locales::getRoute('recipients'), 'RecipientController@index')->name(\Locales::getRoutePrefix('recipients')) : '';
                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('recipients/create') ? Route::get(\Locales::getRoute('recipients/create'), 'RecipientController@create')->name(\Locales::getRoutePrefix('recipients/create')) : '';
                        Route::post('recipients/store', 'RecipientController@store')->name(\Locales::getRoutePrefix('recipients/store'));
                        \Locales::isTranslatedRoute('recipients/edit') ? Route::get(\Locales::getRoute('recipients/edit') . '/{id?}', 'RecipientController@edit')->name(\Locales::getRoutePrefix('recipients/edit'))->where('id', '[0-9]+') : '';
                        Route::put('recipients/update', 'RecipientController@update')->name(\Locales::getRoutePrefix('recipients/update'));
                        \Locales::isTranslatedRoute('recipients/delete') ? Route::get(\Locales::getRoute('recipients/delete'), 'RecipientController@delete')->name(\Locales::getRoutePrefix('recipients/delete')) : '';
                        Route::delete('recipients/destroy', 'RecipientController@destroy')->name(\Locales::getRoutePrefix('recipients/destroy'));
                        \Locales::isTranslatedRoute('recipients/change-status') ? Route::get(\Locales::getRoute('recipients/change-status') . '/{id}/{status}', 'RecipientController@changeStatus')->name(\Locales::getRoutePrefix('recipients/change-status'))->where('id', '[0-9]+')->where('status', '[0-9]+') : '';
                    });

                    \Locales::isTranslatedRoute('agents') ? Route::get(\Locales::getRoute('agents'), 'AgentController@index')->name(\Locales::getRoutePrefix('agents')) : '';
                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('agents/create') ? Route::get(\Locales::getRoute('agents/create'), 'AgentController@create')->name(\Locales::getRoutePrefix('agents/create')) : '';
                        Route::post('agents/store', 'AgentController@store')->name(\Locales::getRoutePrefix('agents/store'));
                        \Locales::isTranslatedRoute('agents/edit') ? Route::get(\Locales::getRoute('agents/edit') . '/{id?}', 'AgentController@edit')->name(\Locales::getRoutePrefix('agents/edit'))->where('id', '[0-9]+') : '';
                        Route::put('agents/update', 'AgentController@update')->name(\Locales::getRoutePrefix('agents/update'));
                        \Locales::isTranslatedRoute('agents/delete') ? Route::get(\Locales::getRoute('agents/delete'), 'AgentController@delete')->name(\Locales::getRoutePrefix('agents/delete')) : '';
                        Route::delete('agents/destroy', 'AgentController@destroy')->name(\Locales::getRoutePrefix('agents/destroy'));
                    });

                    \Locales::isTranslatedRoute('legal-representatives') ? Route::get(\Locales::getRoute('legal-representatives'), 'LegalRepresentativeController@index')->name(\Locales::getRoutePrefix('legal-representatives')) : '';
                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('legal-representatives/create') ? Route::get(\Locales::getRoute('legal-representatives/create'), 'LegalRepresentativeController@create')->name(\Locales::getRoutePrefix('legal-representatives/create')) : '';
                        Route::post('legal-representatives/store', 'LegalRepresentativeController@store')->name(\Locales::getRoutePrefix('legal-representatives/store'));
                        \Locales::isTranslatedRoute('legal-representatives/edit') ? Route::get(\Locales::getRoute('legal-representatives/edit') . '/{id?}', 'LegalRepresentativeController@edit')->name(\Locales::getRoutePrefix('legal-representatives/edit'))->where('id', '[0-9]+') : '';
                        Route::put('legal-representatives/update', 'LegalRepresentativeController@update')->name(\Locales::getRoutePrefix('legal-representatives/update'));
                        \Locales::isTranslatedRoute('legal-representatives/delete') ? Route::get(\Locales::getRoute('legal-representatives/delete'), 'LegalRepresentativeController@delete')->name(\Locales::getRoutePrefix('legal-representatives/delete')) : '';
                        Route::delete('legal-representatives/destroy', 'LegalRepresentativeController@destroy')->name(\Locales::getRoutePrefix('legal-representatives/destroy'));
                    });

                    \Locales::isTranslatedRoute('keyholders') ? Route::get(\Locales::getRoute('keyholders'), 'KeyholderController@index')->name(\Locales::getRoutePrefix('keyholders')) : '';
                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('keyholders/create') ? Route::get(\Locales::getRoute('keyholders/create'), 'KeyholderController@create')->name(\Locales::getRoutePrefix('keyholders/create')) : '';
                        Route::post('keyholders/store', 'KeyholderController@store')->name(\Locales::getRoutePrefix('keyholders/store'));
                        \Locales::isTranslatedRoute('keyholders/edit') ? Route::get(\Locales::getRoute('keyholders/edit') . '/{id?}', 'KeyholderController@edit')->name(\Locales::getRoutePrefix('keyholders/edit'))->where('id', '[0-9]+') : '';
                        Route::put('keyholders/update', 'KeyholderController@update')->name(\Locales::getRoutePrefix('keyholders/update'));
                        \Locales::isTranslatedRoute('keyholders/delete') ? Route::get(\Locales::getRoute('keyholders/delete'), 'KeyholderController@delete')->name(\Locales::getRoutePrefix('keyholders/delete')) : '';
                        Route::delete('keyholders/destroy', 'KeyholderController@destroy')->name(\Locales::getRoutePrefix('keyholders/destroy'));
                    });

                    \Locales::isTranslatedRoute('rental-payments') ? Route::get(\Locales::getRoute('rental-payments'), 'RentalPaymentController@index')->name(\Locales::getRoutePrefix('rental-payments')) : '';
                    Route::group(['middleware' => 'ajax'], function () {
                        \Locales::isTranslatedRoute('rental-payments/create') ? Route::get(\Locales::getRoute('rental-payments/create'), 'RentalPaymentController@create')->name(\Locales::getRoutePrefix('rental-payments/create')) : '';
                        Route::post('rental-payments/store', 'RentalPaymentController@store')->name(\Locales::getRoutePrefix('rental-payments/store'));
                        \Locales::isTranslatedRoute('rental-payments/edit') ? Route::get(\Locales::getRoute('rental-payments/edit') . '/{id?}', 'RentalPaymentController@edit')->name(\Locales::getRoutePrefix('rental-payments/edit'))->where('id', '[0-9]+') : '';
                        Route::put('rental-payments/update', 'RentalPaymentController@update')->name(\Locales::getRoutePrefix('rental-payments/update'));
                        \Locales::isTranslatedRoute('rental-payments/delete') ? Route::get(\Locales::getRoute('rental-payments/delete'), 'RentalPaymentController@delete')->name(\Locales::getRoutePrefix('rental-payments/delete')) : '';
                        Route::delete('rental-payments/destroy', 'RentalPaymentController@destroy')->name(\Locales::getRoutePrefix('rental-payments/destroy'));
                    });
                });
            }
        });
    } elseif ($domain->domain == env('APP_OWNERS_SUBDOMAIN')) {
        \Locales::setRoutesDomain($domain->domain);

        Route::group(['domain' => $domain->domain . '.' . config('app.domain'), 'namespace' => studly_case($domain->domain)], function () use ($domain) {

            foreach ($domain->locales as $locale) {
                \Locales::setRoutesLocale($locale->locale);

                Route::group(['middleware' => 'guest:owners'], function () {
                    Route::get(\Locales::getRoute('/'), 'AuthController@getLogin')->name(\Locales::getRoutePrefix('/'));
                    Route::post(\Locales::getRoute('/'), 'AuthController@postLogin');

                    Route::get(\Locales::getRoute('pf'), 'PasswordController@getEmail')->name(\Locales::getRoutePrefix('pf'));
                    Route::post(\Locales::getRoute('pf'), 'PasswordController@postEmail');

                    Route::get(\Locales::getRoute('reset') . '/{token}', 'PasswordController@getReset')->name(\Locales::getRoutePrefix('reset'));
                    Route::post(\Locales::getRoute('reset'), 'PasswordController@postReset')->name(\Locales::getRoutePrefix('reset-post'));
                });

                Route::group(['middleware' => 'auth:owners'], function () {
                    Route::group(['middleware' => 'ajax'], function () {
                        Route::put(\Locales::getOwnerRoute('update-password'), 'ProfileController@updatePassword')->name(\Locales::getRoutePrefix('update-password'));
                        Route::get(\Locales::getOwnerRoute('notices/dismiss'), 'NoticeController@dismiss')->name(\Locales::getRoutePrefix('notices/dismiss'));
                        Route::get(\Locales::getOwnerRoute('mm-fees') . '/{apartment}/{year}', 'MmFeeController@index')->name(\Locales::getRoutePrefix('mm-fees'))->where('apartment', '[0-9]+')->where('year', '[0-9]+');
                        Route::get(\Locales::getOwnerRoute('communal-fees') . '/{apartment}/{year}', 'CommunalFeeController@index')->name(\Locales::getRoutePrefix('communal-fees'))->where('apartment', '[0-9]+')->where('year', '[0-9]+');
                        Route::get(\Locales::getOwnerRoute('pool-usage') . '/{apartment}/{year}', 'PoolUsageController@index')->name(\Locales::getRoutePrefix('pool-usage'))->where('apartment', '[0-9]+')->where('year', '[0-9]+');
                        Route::get(\Locales::getOwnerRoute('key-log') . '/{apartment}/{year}', 'KeyLogController@index')->name(\Locales::getRoutePrefix('key-log'))->where('apartment', '[0-9]+')->where('year', '[0-9]+');
                        Route::get(\Locales::getOwnerRoute('rental-contract') . '/{id}', 'RentalContractController@index')->name(\Locales::getRoutePrefix('rental-contract'))->where('id', '[0-9]+');
                        Route::post(\Locales::getOwnerRoute('vote') . '/{id}', 'PollController@vote')->name(\Locales::getRoutePrefix('vote'))->where('id', '[0-9]+');
                    });

                    Route::get(\Locales::getOwnerRoute('contract') . '/{id}', 'ContractController@index')->name(\Locales::getRoutePrefix('contract'))->where('id', '[0-9]+');

                    Route::get(\Locales::getOwnerRoute('download-booking') . '/{id}', 'BookingController@download')->name(\Locales::getRoutePrefix('download-booking'))->where('id', '[0-9-]+');
                    Route::get(\Locales::getOwnerRoute('download-booking-attachments') . '/{uuid}', 'BookingController@downloadAttachments')->name(\Locales::getRoutePrefix('download-booking-attachments'))->where('uuid', '[a-z0-9-]+');
                    Route::get(\Locales::getOwnerRoute('download-newsletter-attachments') . '/{uuid}', 'NewsletterController@download')->name(\Locales::getRoutePrefix('download-newsletter-attachments'))->where('uuid', '[a-z0-9-]+');
                    Route::get(\Locales::getOwnerRoute('download-newsletter-attachments-apartment') . '/{uuid}', 'NewsletterController@downloadApartment')->name(\Locales::getRoutePrefix('download-newsletter-attachments-apartment'))->where('uuid', '[a-z0-9-]+');
                    Route::get(\Locales::getOwnerRoute('download-newsletter-attachments-owner') . '/{uuid}', 'NewsletterController@downloadOwner')->name(\Locales::getRoutePrefix('download-newsletter-attachments-owner'))->where('uuid', '[a-z0-9-]+');
                    Route::get(\Locales::getOwnerRoute('download-mm-fees-document') . '/{id}', 'MmFeeController@download')->name(\Locales::getRoutePrefix('download-mm-fees-document'))->where('id', '[0-9]+');
                    Route::get(\Locales::getOwnerRoute('download-communal-fees-document') . '/{id}', 'CommunalFeeController@download')->name(\Locales::getRoutePrefix('download-communal-fees-document'))->where('id', '[0-9]+');
                    Route::get(\Locales::getOwnerRoute('download-pool-usage-document') . '/{id}', 'PoolUsageController@download')->name(\Locales::getRoutePrefix('download-pool-usage-document'))->where('id', '[0-9]+');
                    Route::get(\Locales::getOwnerRoute('download-contract-document') . '/{id}', 'ContractController@download')->name(\Locales::getRoutePrefix('download-contract-document'))->where('id', '[0-9]+');
                    Route::get(\Locales::getOwnerRoute('download-payment-document') . '/{id}', 'RentalContractController@download')->name(\Locales::getRoutePrefix('download-payment-document'))->where('id', '[0-9]+');
                    Route::get(\Locales::getOwnerRoute('download-mm-document') . '/{uuid}', 'CondominiumController@downloadMm')->name(\Locales::getRoutePrefix('download-mm-document'))->where('uuid', '[a-z0-9-]+');
                    Route::get(\Locales::getOwnerRoute('download-condominium-document') . '/{uuid}', 'CondominiumController@downloadCondominium')->name(\Locales::getRoutePrefix('download-condominium-document'))->where('uuid', '[a-z0-9-]+');

                    foreach (\Locales::getOwnerNavigation() as $key => $nav) {
                        if ($nav['route']) {
                            Route::get(\Locales::getOwnerRoute($nav['route']), $nav['route_method'])->name(\Locales::getRoutePrefix($nav['slug']));
                        }
                    }
                });
            }
        });
    }
}
