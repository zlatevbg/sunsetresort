<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Models\Sky\Booking;
use App\Models\Sky\Apartment;
use App\Models\Sky\Owner;
use App\Models\Sky\Airport;
use App\Models\Sky\Signature;
use App\Models\Sky\Year;
use App\Models\Sky\BookingGuest;
use App\Models\Sky\ExtraService;
use App\Models\Sky\NewsletterTemplates;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Http\Requests\Sky\BookingRequest;
use App\Services\Newsletter as NewsletterService;
use Mail;
use File;
use Carbon\Carbon;
use Dompdf\Dompdf;

class BookingController extends Controller {

    protected $route = 'bookings';
    protected $datatables;
    protected $multiselect;

    public function __construct()
    {
        $this->datatables = [
            $this->route => [
                'title' => trans(\Locales::getNamespace() . '/datatables.titleBookings'),
                'url' => \Locales::route($this->route),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'selectors' => [$this->route . '.comments'],
                // 'count' => $this->route . '.id',
                'columns' => [
                    [
                        'selector' => $this->route . '.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center vertical-center',
                    ],
                    [
                        'selector' => $this->route . '.id as booking',
                        'id' => 'booking',
                        'name' => trans(\Locales::getNamespace() . '/datatables.id'),
                        'class' => 'text-center vertical-center',
                        'search' => true,
                    ],
                    [
                        'selector' => 'apartments.number',
                        'id' => 'number',
                        'name' => trans(\Locales::getNamespace() . '/datatables.apartment'),
                        'search' => true,
                        'order' => false,
                        'class' => 'vertical-center',
                        'join' => [
                            'table' => 'apartments',
                            'localColumn' => 'apartments.id',
                            'constrain' => '=',
                            'foreignColumn' => $this->route . '.apartment_id',
                        ],
                        'info' => 'comments',
                    ],
                    [
                        'selectRaw' => 'CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as owner',
                        'id' => 'owner',
                        'name' => trans(\Locales::getNamespace() . '/datatables.owner'),
                        'search' => true,
                        'order' => false,
                        'class' => 'vertical-center',
                        'join' => [
                            'table' => 'owners',
                            'localColumn' => 'owners.id',
                            'constrain' => '=',
                            'foreignColumn' => $this->route . '.owner_id',
                        ],
                    ],
                    [
                        'selectRaw' => 'GROUP_CONCAT(booking_guests.name SEPARATOR ", ") as guests',
                        'id' => 'guests',
                        'name' => trans(\Locales::getNamespace() . '/datatables.guests'),
                        'search' => true,
                        'order' => false,
                        'class' => 'vertical-center',
                        'join' => [
                            'table' => 'booking_guests',
                            'localColumn' => 'booking_guests.booking_id',
                            'constrain' => '=',
                            'foreignColumn' => $this->route . '.id',
                            'whereColumn' => 'booking_guests.type',
                            'whereConstrain' => '=',
                            'whereValue' => 'adult',
                            'group' => $this->route . '.id',
                        ],
                    ],
                    [
                        'selector' => $this->route . '.arrive_at',
                        'id' => 'arrive_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.arrival'),
                        'class' => 'text-center vertical-center',
                        'search' => true,
                        'data' => [
                            'type' => 'sort',
                            'id' => 'arrive_at',
                            'date' => 'YYmmdd',
                        ],
                    ],
                    [
                        'selector' => $this->route . '.departure_at',
                        'id' => 'departure_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.departure'),
                        'class' => 'text-center vertical-center',
                        'search' => true,
                        'data' => [
                            'type' => 'sort',
                            'id' => 'departure_at',
                            'date' => 'YYmmdd',
                        ],
                    ],
                    [
                        'id' => 'adults',
                        'name' => trans(\Locales::getNamespace() . '/datatables.adults'),
                        'aggregate' => 'adultsCount',
                        'class' => 'text-center vertical-center',
                        'order' => false,
                    ],
                    [
                        'id' => 'children',
                        'name' => trans(\Locales::getNamespace() . '/datatables.children'),
                        'aggregate' => 'childrenCount',
                        'class' => 'text-center vertical-center',
                        'order' => false,
                    ],
                    [
                        'selector' => $this->route . '.is_confirmed',
                        'id' => 'is_confirmed',
                        'name' => trans(\Locales::getNamespace() . '/datatables.status'),
                        'class' => 'text-center',
                        'order' => false,
                        'status' => [
                            'class' => 'change-status',
                            'queue' => 'async-change-status',
                            'route' => $this->route . '/change-status',
                            'rules' => [
                                0 => [
                                    'status' => 1,
                                    'icon' => 'off.gif',
                                    'title' => trans(\Locales::getNamespace() . '/datatables.statusOff'),
                                ],
                                1 => [
                                    'status' => 0,
                                    'icon' => 'on.gif',
                                    'title' => trans(\Locales::getNamespace() . '/datatables.statusOn'),
                                ],
                            ],
                        ],
                    ],
                ],
                'orderByColumn' => 'arrive_at',
                'orderByRaw' => 'is_confirmed',
                'order' => 'desc',
                'buttons' => [
                    [
                        'url' => \Locales::route($this->route . '/send'),
                        'class' => 'btn-success js-send hidden',
                        'icon' => 'send',
                        'name' => trans(\Locales::getNamespace() . '/forms.sendButton'),
                    ],
                    [
                        'url' => \Locales::route($this->route . '/test'),
                        'class' => 'btn-info js-test hidden',
                        'icon' => 'repeat',
                        'name' => trans(\Locales::getNamespace() . '/forms.testButton'),
                    ],
                    [
                        'url' => \Locales::route($this->route . '/print'),
                        'class' => 'btn-default js-print hidden',
                        'icon' => 'print',
                        'name' => trans(\Locales::getNamespace() . '/forms.printButton'),
                    ],
                    [
                        'url' => \Locales::route($this->route . '/create'),
                        'class' => 'btn-primary js-create',
                        'icon' => 'plus',
                        'name' => trans(\Locales::getNamespace() . '/forms.createButton'),
                    ],
                    [
                        'url' => \Locales::route($this->route . '/edit'),
                        'class' => 'btn-warning disabled js-edit',
                        'icon' => 'edit',
                        'name' => trans(\Locales::getNamespace() . '/forms.editButton'),
                    ],
                    [
                        'url' => \Locales::route($this->route . '/delete'),
                        'class' => 'btn-danger disabled js-destroy',
                        'icon' => 'trash',
                        'name' => trans(\Locales::getNamespace() . '/forms.deleteButton'),
                    ],
                ],
            ],
        ];

        $this->multiselect = [
            'apartments' => [
                'id' => 'id',
                'name' => 'number',
                'subText' => 'info',
            ],
            'owners' => [
                'id' => 'id',
                'name' => 'name',
            ],
            /*'kitchen_items' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'loyalty_card' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'club_card' => [
                'id' => 'id',
                'name' => 'name',
            ],*/
            'exception' => [
                'id' => 'id',
                'name' => 'name',
            ],
            /*'deposit_paid' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'hotel_card' => [
                'id' => 'id',
                'name' => 'name',
            ],*/
            'arrival_airport_id' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'departure_airport_id' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'arrival_transfer' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'departure_transfer' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'services' => [
                'id' => 'id',
                'name' => 'name',
                'subText' => 'price',
            ],
        ];
    }

    public function index(DataTable $datatable, Booking $booking, Request $request)
    {
        $breadcrumbs = [];

        $datatable->setup($booking->whereYear('departure_at', '>=', date('Y')), $this->route, $this->datatables[$this->route]);

        $datatables = $datatable->getTables();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($datatables);
        } else {
            return view(\Locales::getNamespace() . '.' . $this->route . '.index', compact('datatables', 'breadcrumbs'));
        }
    }

    public function create(Request $request)
    {
        $table = $this->route;
        if ($request->input('table')) { // magnific popup request
            $table = $request->input('table');
        }

        $apartments[] = ['id' => '', 'number' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        $apartments = array_merge($apartments, Apartment::selectRaw('apartments.id, apartments.number, CONCAT(room_translations.name, " ", CONCAT(COALESCE(CONCAT("(<strong>' . date('Y') . '</strong>: <u>", (SELECT GROUP_CONCAT(DISTINCT rental_contract_translations.name SEPARATOR ", ") FROM `rental_contract_translations` LEFT JOIN `rental_contracts` ON `rental_contracts`.`id` = `rental_contract_translations`.`rental_contract_id` LEFT JOIN `contracts` ON `rental_contracts`.`id` = `contracts`.`rental_contract_id` LEFT JOIN `contract_years` ON `contract_years`.`contract_id` = `contracts`.`id` WHERE `rental_contract_translations`.`locale` = \'' . \Locales::getCurrent() . '\' AND `contracts`.`apartment_id` = `apartments`.`id` AND `contract_years`.`year` = \'' . date('Y') . '\'), "</u>) "), ""), COALESCE(CONCAT("(<strong>' . ((int)date('Y') + 1) . '</strong>: <u>", (SELECT GROUP_CONCAT(DISTINCT rental_contract_translations.name SEPARATOR ", ") FROM `rental_contract_translations` LEFT JOIN `rental_contracts` ON `rental_contracts`.`id` = `rental_contract_translations`.`rental_contract_id` LEFT JOIN `contracts` ON `rental_contracts`.`id` = `contracts`.`rental_contract_id` LEFT JOIN `contract_years` ON `contract_years`.`contract_id` = `contracts`.`id` WHERE `rental_contract_translations`.`locale` = \'' . \Locales::getCurrent() . '\' AND `contracts`.`apartment_id` = `apartments`.`id` AND `contract_years`.`year` = \'' . ((int)date('Y') + 1) . '\'), "</u>)"), ""))) AS info')->join('ownership', 'ownership.apartment_id', '=', 'apartments.id')->leftJoin('room_translations', function($join) {
            $join->on('room_translations.room_id', '=', 'apartments.room_id')->where('room_translations.locale', '=', \Locales::getCurrent());
        })->whereNull('ownership.deleted_at')->groupBy('apartments.id')->orderBy('number')->get()->toArray());
        // $apartments = array_merge($apartments, Apartment::select('apartments.id', 'apartments.number')->join('ownership', 'ownership.apartment_id', '=', 'apartments.id')->whereNull('ownership.deleted_at')->groupBy('apartments.id')->orderBy('number')->get()->toArray());
        $this->multiselect['apartments']['options'] = $apartments;
        $this->multiselect['apartments']['selected'] = '';

        $owners[] = ['id' => '', 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        $owners = array_merge($owners, Owner::distinct()->selectRaw('owners.id, CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as name')->join('ownership', 'ownership.owner_id', '=', 'owners.id')->whereNull('ownership.deleted_at')->orderBy('name')->get()->toArray());
        $this->multiselect['owners']['options'] = $owners;
        $this->multiselect['owners']['selected'] = '';

        /*$kitchenItems[] = ['id' => '', 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        foreach (trans('bookings.kitchenItemsOptions') as $key => $value) {
            $kitchenItems[] = ['id' => $key, 'name' => $value];
        }
        $this->multiselect['kitchen_items']['options'] = $kitchenItems;
        $this->multiselect['kitchen_items']['selected'] = 0;

        $clubCard[] = ['id' => '', 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        foreach (trans('bookings.clubCardOptions') as $key => $value) {
            $clubCard[] = ['id' => $key, 'name' => $value];
        }
        $this->multiselect['club_card']['options'] = $clubCard;
        $this->multiselect['club_card']['selected'] = 0;*/

        $exception[] = ['id' => '', 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        foreach (trans('bookings.exceptionOptions') as $key => $value) {
            $exception[] = ['id' => $key, 'name' => $value];
        }
        $this->multiselect['exception']['options'] = $exception;
        $this->multiselect['exception']['selected'] = 0;

        /*$depositPaid[] = ['id' => '', 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        foreach (trans('bookings.depositPaidOptions') as $key => $value) {
            $depositPaid[] = ['id' => $key, 'name' => $value];
        }
        $this->multiselect['deposit_paid']['options'] = $depositPaid;
        $this->multiselect['deposit_paid']['selected'] = 0;

        $hotelCard[] = ['id' => '', 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        foreach (trans('bookings.hotelCardOptions') as $key => $value) {
            $hotelCard[] = ['id' => $key, 'name' => $value];
        }
        $this->multiselect['hotel_card']['options'] = $hotelCard;
        $this->multiselect['hotel_card']['selected'] = 0;

        $loyaltyCard[] = ['id' => '', 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        foreach (trans('bookings.loyaltyCardOptions') as $key => $value) {
            $loyaltyCard[] = ['id' => $key, 'name' => $value];
        }
        $this->multiselect['loyalty_card']['options'] = $loyaltyCard;
        $this->multiselect['loyalty_card']['selected'] = 0;*/

        $airports[] = ['id' => '', 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        $airports = array_merge($airports, Airport::withTranslation()->select('airport_translations.name', 'airports.id')->leftJoin('airport_translations', 'airport_translations.airport_id', '=', 'airports.id')->where('airport_translations.locale', \Locales::getCurrent())->orderBy('airport_translations.name')->get()->toArray());

        $this->multiselect['arrival_airport_id']['options'] = $airports;
        $this->multiselect['arrival_airport_id']['selected'] = '';

        $this->multiselect['departure_airport_id']['options'] = $airports;
        $this->multiselect['departure_airport_id']['selected'] = '';

        $transfers[] = ['id' => '', 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        foreach (trans('bookings.transferOptions') as $key => $value) {
            $transfers[] = ['id' => $key, 'name' => $value];
        }

        $this->multiselect['arrival_transfer']['options'] = $transfers;
        $this->multiselect['arrival_transfer']['selected'] = '';

        $this->multiselect['departure_transfer']['options'] = $transfers;
        $this->multiselect['departure_transfer']['selected'] = '';

        $services = [];
        $extraServices = ExtraService::withTranslation()->selectRaw('extra_service_translations.name, extra_services.id, CONCAT(extra_services.price, " BGN") as price, extra_services.parent')->leftJoin('extra_service_translations', 'extra_service_translations.extra_service_id', '=', 'extra_services.id')->where('extra_service_translations.locale', \Locales::getCurrent())->orderBy('extra_service_translations.name')->get()->toArray();
        foreach (\App\Helpers\arrayToTree($extraServices) as $service) {
            array_push($services, [
                'name' => $service['name'],
                'optgroup' => $service['children'],
            ]);
        }

        foreach ($services as $key => $value) {
            $this->multiselect['services' . $key] = $this->multiselect['services'];
            $this->multiselect['services' . $key]['label'] = $value['name'];
            $this->multiselect['services' . $key]['options'] = $value['optgroup'];
            $this->multiselect['services' . $key]['selected'] = '';
        }
        $this->multiselect['services'] = count($services);

        $multiselect = $this->multiselect;

        $tourists = trans('bookings.touristsOptions');

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('table', 'multiselect', 'tourists'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function store(DataTable $datatable, Booking $booking, BookingRequest $request)
    {
        $apartment = Apartment::findOrFail($request->input('apartment_id'));

        $request->merge([
            'project_id' => $apartment->project_id,
            'building_id' => $apartment->building_id,
        ]);

        $newBooking = Booking::create($request->all());

        if ($newBooking->id) {
            if ($request->input('adults')) {
                $adults = [];
                $i = 1;
                foreach ($request->input('adults') as $value) {
                    array_push($adults, new BookingGuest([
                        'name' => $value,
                        'order' => $i,
                        'type' => 'adult',
                        'booking_id' => $newBooking->id,
                    ]));
                    $i++;
                }
                $newBooking->adults()->saveMany($adults);
            }

            if ($request->input('children')) {
                $children = [];
                $i = 1;
                foreach ($request->input('children') as $value) {
                    array_push($children, new BookingGuest([
                        'name' => $value,
                        'order' => $i,
                        'type' => 'child',
                        'booking_id' => $newBooking->id,
                    ]));
                    $i++;
                }
                $newBooking->children()->saveMany($children);
            }

            $successMessage = trans(\Locales::getNamespace() . '/forms.storedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityBookings', 1)]);

            $datatable->setup($booking->whereYear('departure_at', '>=', date('Y')), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.createError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityBookings', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function delete(Request $request)
    {
        $table = $this->route;
        if ($request->input('table')) { // magnific popup request
            $table = $request->input('table');
        }

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.delete', compact('table'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function destroy(DataTable $datatable, Booking $booking, Request $request)
    {
        $count = count($request->input('id'));

        if ($count > 0 && $booking->destroy($request->input('id'))) {
            $datatable->setup($booking->whereYear('departure_at', '>=', date('Y')), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => trans(\Locales::getNamespace() . '/forms.destroyedSuccessfully'),
                'closePopup' => true
            ]);
        } else {
            if ($count > 0) {
                $errorMessage = trans(\Locales::getNamespace() . '/forms.deleteError');
            } else {
                $errorMessage = trans(\Locales::getNamespace() . '/forms.countError');
            }

            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function edit(Request $request, $id = null)
    {
        $booking = Booking::findOrFail($id);

        $table = $this->route;
        if ($request->input('table')) { // magnific popup request
            $table = $request->input('table');
        }

        $apartments[] = ['id' => '', 'number' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        $apartments = array_merge($apartments, Apartment::selectRaw('apartments.id, apartments.number, CONCAT(room_translations.name, " ", CONCAT(COALESCE(CONCAT("(<strong>' . date('Y') . '</strong>: <u>", (SELECT GROUP_CONCAT(DISTINCT rental_contract_translations.name SEPARATOR ", ") FROM `rental_contract_translations` LEFT JOIN `rental_contracts` ON `rental_contracts`.`id` = `rental_contract_translations`.`rental_contract_id` LEFT JOIN `contracts` ON `rental_contracts`.`id` = `contracts`.`rental_contract_id` LEFT JOIN `contract_years` ON `contract_years`.`contract_id` = `contracts`.`id` WHERE `rental_contract_translations`.`locale` = \'' . \Locales::getCurrent() . '\' AND `contracts`.`apartment_id` = `apartments`.`id` AND `contracts`.`deleted_at` IS NULL AND `contract_years`.`year` = \'' . date('Y') . '\' AND `contract_years`.`deleted_at` IS NULL), "</u>) "), ""), COALESCE(CONCAT("(<strong>' . ((int)date('Y') + 1) . '</strong>: <u>", (SELECT GROUP_CONCAT(DISTINCT rental_contract_translations.name SEPARATOR ", ") FROM `rental_contract_translations` LEFT JOIN `rental_contracts` ON `rental_contracts`.`id` = `rental_contract_translations`.`rental_contract_id` LEFT JOIN `contracts` ON `rental_contracts`.`id` = `contracts`.`rental_contract_id` LEFT JOIN `contract_years` ON `contract_years`.`contract_id` = `contracts`.`id` WHERE `rental_contract_translations`.`locale` = \'' . \Locales::getCurrent() . '\' AND `contracts`.`apartment_id` = `apartments`.`id` AND `contracts`.`deleted_at` IS NULL AND `contract_years`.`year` = \'' . ((int)date('Y') + 1) . '\' AND `contract_years`.`deleted_at` IS NULL), "</u>)"), ""))) AS info')->join('ownership', 'ownership.apartment_id', '=', 'apartments.id')->leftJoin('room_translations', function($join) {
            $join->on('room_translations.room_id', '=', 'apartments.room_id')->where('room_translations.locale', '=', \Locales::getCurrent());
        })->whereNull('ownership.deleted_at')->groupBy('apartments.id')->orderBy('number')->get()->toArray());
        $this->multiselect['apartments']['options'] = $apartments;
        $this->multiselect['apartments']['selected'] = $booking->apartment_id;

        $owners[] = ['id' => '', 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        $owners = array_merge($owners, Owner::distinct()->selectRaw('owners.id, CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as name')->join('ownership', 'ownership.owner_id', '=', 'owners.id')->whereNull('ownership.deleted_at')->orderBy('name')->get()->toArray());
        $this->multiselect['owners']['options'] = $owners;
        $this->multiselect['owners']['selected'] = $booking->owner_id;

        /*$kitchenItems[] = ['id' => '', 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        foreach (trans('bookings.kitchenItemsOptions') as $key => $value) {
            $kitchenItems[] = ['id' => $key, 'name' => $value];
        }
        $this->multiselect['kitchen_items']['options'] = $kitchenItems;
        $this->multiselect['kitchen_items']['selected'] = $booking->kitchen_items;

        $clubCard[] = ['id' => '', 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        foreach (trans('bookings.clubCardOptions') as $key => $value) {
            $clubCard[] = ['id' => $key, 'name' => $value];
        }
        $this->multiselect['club_card']['options'] = $clubCard;
        $this->multiselect['club_card']['selected'] = $booking->club_card;*/

        $exception[] = ['id' => '', 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        foreach (trans('bookings.exceptionOptions') as $key => $value) {
            $exception[] = ['id' => $key, 'name' => $value];
        }
        $this->multiselect['exception']['options'] = $exception;
        $this->multiselect['exception']['selected'] = $booking->exception;

        /*$depositPaid[] = ['id' => '', 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        foreach (trans('bookings.depositPaidOptions') as $key => $value) {
            $depositPaid[] = ['id' => $key, 'name' => $value];
        }
        $this->multiselect['deposit_paid']['options'] = $depositPaid;
        $this->multiselect['deposit_paid']['selected'] = $booking->deposit_paid;

        $hotelCard[] = ['id' => '', 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        foreach (trans('bookings.hotelCardOptions') as $key => $value) {
            $hotelCard[] = ['id' => $key, 'name' => $value];
        }
        $this->multiselect['hotel_card']['options'] = $hotelCard;
        $this->multiselect['hotel_card']['selected'] = $booking->hotel_card;

        $loyaltyCard[] = ['id' => '', 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        foreach (trans('bookings.loyaltyCardOptions') as $key => $value) {
            $loyaltyCard[] = ['id' => $key, 'name' => $value];
        }
        $this->multiselect['loyalty_card']['options'] = $loyaltyCard;
        $this->multiselect['loyalty_card']['selected'] = $booking->loyalty_card;*/

        $airports[] = ['id' => '', 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        $airports = array_merge($airports, Airport::withTranslation()->select('airport_translations.name', 'airports.id')->leftJoin('airport_translations', 'airport_translations.airport_id', '=', 'airports.id')->where('airport_translations.locale', \Locales::getCurrent())->orderBy('airport_translations.name')->get()->toArray());

        $this->multiselect['arrival_airport_id']['options'] = $airports;
        $this->multiselect['arrival_airport_id']['selected'] = $booking->arrival_airport_id;

        $this->multiselect['departure_airport_id']['options'] = $airports;
        $this->multiselect['departure_airport_id']['selected'] = $booking->departure_airport_id;

        $transfers[] = ['id' => '', 'name' => trans(\Locales::getNamespace() . '/forms.selectOption')];
        foreach (trans('bookings.transferOptions') as $key => $value) {
            $transfers[] = ['id' => $key, 'name' => $value];
        }

        $this->multiselect['arrival_transfer']['options'] = $transfers;
        $this->multiselect['arrival_transfer']['selected'] = $booking->arrival_transfer;

        $this->multiselect['departure_transfer']['options'] = $transfers;
        $this->multiselect['departure_transfer']['selected'] = $booking->departure_transfer;

        $services = [];
        $extraServices = ExtraService::withTranslation()->selectRaw('extra_service_translations.name, extra_services.id, CONCAT(extra_services.price, " BGN") as price, extra_services.parent')->leftJoin('extra_service_translations', 'extra_service_translations.extra_service_id', '=', 'extra_services.id')->where('extra_service_translations.locale', \Locales::getCurrent())->orderBy('extra_service_translations.name')->get()->toArray();
        foreach (\App\Helpers\arrayToTree($extraServices) as $service) {
            array_push($services, [
                'name' => $service['name'],
                'optgroup' => $service['children'],
            ]);
        }

        foreach ($services as $key => $value) {
            $this->multiselect['services' . $key] = $this->multiselect['services'];
            $this->multiselect['services' . $key]['label'] = $value['name'];
            $this->multiselect['services' . $key]['options'] = $value['optgroup'];
            $this->multiselect['services' . $key]['selected'] = explode(',', $booking->services);
        }
        $this->multiselect['services'] = count($services);

        $multiselect = $this->multiselect;

        $tourists = trans('bookings.touristsOptions');

        $dates = Booking::select('arrive_at', 'departure_at')->where('apartment_id', $booking->apartment_id)->where('id', '!=', $booking->id)->whereNotNull('arrive_at')->whereNotNull('departure_at')->get()->toArray();
        array_walk($dates, function(&$date) {
            $date['arrive_at'] = Carbon::parse($date['arrive_at'])->toAtomString();
            $date['departure_at'] = Carbon::parse($date['departure_at'])->toAtomString();
        });

        $info = $this->getInfo($request, $booking->apartment_id);
        $ownerInfo = $this->getOwnerInfo($request, $booking->owner_id);

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('booking', 'table', 'multiselect', 'dates', 'tourists', 'info', 'ownerInfo'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, Booking $bookings, BookingRequest $request)
    {
        $booking = Booking::findOrFail($request->input('id'))->first();

        $apartment = Apartment::findOrFail($request->input('apartment_id'));

        $request->merge([
            'project_id' => $apartment->project_id,
            'building_id' => $apartment->building_id,
        ]);

        if ($booking->update($request->all())) {
            $booking->adults()->delete();
            if ($request->input('adults')) {
                $adults = [];
                $i = 1;
                foreach ($request->input('adults') as $value) {
                    array_push($adults, new BookingGuest([
                        'name' => $value,
                        'order' => $i,
                        'type' => 'adult',
                        'booking_id' => $booking->id,
                    ]));
                    $i++;
                }
                $booking->adults()->saveMany($adults);
            }

            $booking->children()->delete();
            if ($request->input('children')) {
                $children = [];
                $i = 1;
                foreach ($request->input('children') as $value) {
                    array_push($children, new BookingGuest([
                        'name' => $value,
                        'order' => $i,
                        'type' => 'child',
                        'booking_id' => $booking->id,
                    ]));
                    $i++;
                }
                $booking->children()->saveMany($children);
            }

            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityBookings', 1)]);

            $datatable->setup($bookings->whereYear('departure_at', '>=', date('Y')), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityBookings', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function getInfo(Request $request, $apartment_id = null)
    {
        $apartment = $apartment_id ?: $request->input('apartment');
        $apartment = Apartment::with(['buildingMM', 'mmFeesPayments', 'room'])->findOrFail($apartment);

        if (!$apartment_id) {
            $owners = Owner::distinct()->selectRaw('owners.id, CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as name')->join('ownership', 'ownership.owner_id', '=', 'owners.id')->whereNull('ownership.deleted_at')->where('ownership.apartment_id', $apartment->id)->orderBy('name')->get()->pluck('id', 'name')->toArray();

            $dates = Booking::select('arrive_at', 'departure_at')->where('apartment_id', $apartment->id)->whereNotNull('arrive_at')->whereNotNull('departure_at')->get()->toArray();
            array_walk($dates, function (&$date) {
                $date['arrive_at'] = Carbon::parse($date['arrive_at'])->format('Ymd');
                $date['departure_at'] = Carbon::parse($date['departure_at'])->format('Ymd');
            });
        }

        $info = '<p>' . trans(\Locales::getNamespace() . '/forms.roomTypeLabel') . ': <strong>' . $apartment->room->name . '</strong></p>';
        $info .= '<p>' . trans(\Locales::getNamespace() . '/forms.rentalContractLabel') . ':<ul>';
        foreach ($apartment->contracts as $contract) {
            $info .= '<li><strong>' . $contract->rentalContract->name . '</strong></li>';
        }
        $info .= '</ul></p>';

        $years = Year::whereIn('year', [((int)date('Y') - 1), date('Y')])->orderBy('year')->get();
        if (count($years)) {
            $info .= '<p>' . trans(\Locales::getNamespace() . '/forms.mmFeesForYearLabel') . ':<ul>';
            foreach ($years as $year) {
                $mm = $apartment->buildingMM->where('year_id', $year->id)->first();
                if ($mm) {
                    if ($year->year > 2020) {
                        $mmFeeTax = round(($apartment->room->capacity * $mm->mm_tax) / 1.95583);
                    } else {
                        if ($apartment->mm_tax_formula == 0) {
                            $mmFeeTax = (($apartment->apartment_area + $apartment->common_area + $apartment->balcony_area) * $mm->mm_tax) + ($apartment->extra_balcony_area * ($mm->mm_tax / 2));
                        } elseif ($apartment->mm_tax_formula == 1) {
                            $mmFeeTax = $apartment->total_area * $mm->mm_tax;
                        }
                    }

                    $balance = round($mmFeeTax - $apartment->mmFeesPayments->where('year_id', $year->id)->sum('amount'), 2);
                    $info .= '<li>' . $year->year . ': <strong>&euro; ' . number_format($balance, 2) . '</strong></li>';
                }
            }
            $info .= '</ul></p>';
        }

        if (!$apartment_id) {
            return response()->json([
                'success' => true,
                'owners' => $owners,
                'dates' => $dates,
                'info' => $info,
            ]);
        } else {
            return $info;
        }
    }

    public function getOwnerInfo(Request $request, $owner_id = null)
    {
        $owner = $owner_id ?: $request->input('owner');
        $owner = Owner::findOrFail($owner);

        if (!$owner_id) {
            return response()->json([
                'success' => true,
                'info' => $owner->outstanding_bills ? 'Outstanding Bills<br>' . $owner->comments : null,
            ]);
        } else {
            return $owner->outstanding_bills ? 'Outstanding Bills<br>' . $owner->comments : null;
        }
    }

    public function changeStatus($id, $status)
    {
        $booking = Booking::findOrFail($id);

        $booking->is_confirmed = $status;
        $booking->save();

        $href = '';
        $img = '';
        foreach ($this->datatables[$this->route]['columns'] as $column) {
            if ($column['id'] == 'is_confirmed') {
                foreach ($column['status']['rules'] as $key => $value) {
                    if ($key == $status) {
                        $href = \Locales::route($column['status']['route'], [$id, $value['status']]);
                        $img = \HTML::image(\App\Helpers\autover('/img/' . \Locales::getNamespace() . '/' . $value['icon']), $value['title']);
                        break 2;
                    }
                }
            }
        }

        return response()->json(['success' => true, 'href' => $href, 'img' => $img]);
    }

    public function printBooking(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $apartment = $booking->apartment;
        $owner = $booking->owner;
        $locale = $owner->locale->locale;

        \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);

        $templateProcessor = new \App\Extensions\PhpOffice\PhpWord\CustomTemplateProcessor(storage_path('app/templates/booking-form-' . $locale . '.docx'));

        $templateProcessor->setValue('ID', $booking->id);
        $templateProcessor->setValue('OWNER', $owner->full_name);
        $templateProcessor->setValue('BUILDING', trim(preg_replace('/^(.*)\((.*)\)(.*)$/', '$2', $apartment->building->translate($locale)->name)));
        $templateProcessor->setValue('APARTMENT', $apartment->number);
        $templateProcessor->setValue('TYPE', $apartment->room->translate($locale)->name);
        $templateProcessor->setValue('FURNITURE', $apartment->furniture->translate($locale)->name);
        $templateProcessor->setValue('VIEW', $apartment->view->translate($locale)->name);

        $contracts = [];
        $mm = [];
        $year = substr($booking->arrive_at, -4);
        if ($apartment->contracts->count()) {
            foreach ($apartment->contracts as $contract) {
                if ($contract->contractYears->contains('year', $year)) {
                    array_push($contracts, $contract->rentalContract->translate($locale)->name);
                    array_push($mm, \Lang::get('bookings.mmPayable.1', [], $locale) . ' - ' . \Lang::get('bookings.mmCoveredOptions.' . $contract->rentalContract->mm_covered, [], $locale));
                }
            }
        } else {
            array_push($contracts, \Lang::get('bookings.mmCoveredOptions.0', [], $locale));
            array_push($mm, \Lang::get('bookings.mmPayable.0', [], $locale));
        }

        $communalFeeBalance = 0;
        $poolUsageBalance = 0;
        $year = Year::where('year', $year)->first();
        if ($year) {
            $fees = $year->fees->where('room_id', $apartment->room_id)->first();
            if ($fees) {
                $communalFeeTax = round($fees->annual_communal_tax / 1.95583);
                $communalFeeBalance = round($communalFeeTax, 2) - round($apartment->communalFeesPayments->where('year_id', $year->id)->sum('amount'), 2);

                $poolUsageTax = round($fees->pool_tax / 1.95583);
                $poolUsageBalance = round($poolUsageTax, 2) - round($apartment->poolUsagePayments->where('year_id', $year->id)->sum('amount'), 2);
            }
        }

        $templateProcessor->setValue('RC', implode('\n', $contracts));
        $templateProcessor->setValue('MM', implode('\n', $mm));
        $templateProcessor->setValue('CT', $communalFeeBalance ? \Lang::get('bookings.feesOptions.no', [], $locale) : \Lang::get('bookings.feesOptions.yes', [], $locale));
        $templateProcessor->setValue('PU', $poolUsageBalance ? \Lang::get('bookings.feesOptions.no', [], $locale) : \Lang::get('bookings.feesOptions.yes', [], $locale));
        /*$templateProcessor->setValue('KI', \Lang::get('bookings.kitchenItemsOptions.' . (int)$booking->kitchen_items, [], $locale));
        $templateProcessor->setValue('LC', \Lang::get('bookings.loyaltyCardOptions.' . (int)$booking->loyalty_card, [], $locale));
        $templateProcessor->setValue('CC', \Lang::get('bookings.clubCardOptions.' . (int)$booking->club_card, [], $locale));*/
        $templateProcessor->setValue('EX', \Lang::get('bookings.exceptionOptions.' . (int)$booking->exception, [], $locale));
        /*$templateProcessor->setValue('DP', \Lang::get('bookings.depositPaidOptions.' . (int)$booking->deposit_paid, [], $locale));
        $templateProcessor->setValue('HC', \Lang::get('bookings.hotelCardOptions.' . (int)$booking->hotel_card, [], $locale));*/
        $templateProcessor->setValue('ADATE', $booking->arrive_at . ($booking->arrival_time ? ' ' . $booking->arrival_time : ''));
        $templateProcessor->setValue('AFLIGHT', $booking->arrival_flight);
        $templateProcessor->setValue('AAIRPORT', $booking->arrivalAirport ? $booking->arrivalAirport->translate($locale)->name : '');
        $templateProcessor->setValue('ATRANSFER', $booking->arrival_transfer ? \Lang::get('bookings.transferOptions.' . $booking->arrival_transfer, [], $locale) : '');
        $templateProcessor->setValue('DDATE', $booking->departure_at . ($booking->departure_time ? ' ' . $booking->departure_time : ''));
        $templateProcessor->setValue('DFLIGHT', $booking->departure_flight);
        $templateProcessor->setValue('DAIRPORT', $booking->departureAirport ? $booking->departureAirport->translate($locale)->name : '');
        $templateProcessor->setValue('DTRANSFER', $booking->departure_transfer ? \Lang::get('bookings.transferOptions.' . $booking->departure_transfer, [], $locale) : '');
        $templateProcessor->setValue('ADULTS', $booking->adults->count());
        $templateProcessor->setValue('CHILDREN', $booking->children->count());

        $guests = max($booking->adults->count(), $booking->children->count());
        $templateProcessor->cloneRow('ADULT', $guests);

        $i = 1;
        foreach ($booking->adults as $adult) {
            $templateProcessor->setValue('ADULT#' . $i, $adult->name);
            $i++;
        }

        $i = 1;
        foreach ($booking->children as $child) {
            $templateProcessor->setValue('CHILD#' . $i, $child->name);
            $i++;
        }

        for ($i = 1; $i <= $guests; $i++) {
            $templateProcessor->setValue('ADULT#' . $i, '');
            $templateProcessor->setValue('CHILD#' . $i, '');
        }

        $services = ExtraService::withTranslation()->select('extra_service_translations.name', 'extra_services.id')->leftJoin('extra_service_translations', 'extra_service_translations.extra_service_id', '=', 'extra_services.id')->where('extra_service_translations.locale', $locale)->whereIn('extra_services.id', explode(',', $booking->services))->orderBy('extra_service_translations.name')->get()->pluck('name')->toArray();

        $costs = '<strong>' . \Lang::get('bookings.costsLabel', [], $locale) . '</strong>: ' . \Lang::get('bookings.accommodationCostsLabel', [], $locale) . ' <u>' . \Lang::get('bookings.touristsOptions.' . $booking->accommodation_costs, [], $locale) . '</u>.';
        if ($booking->arrival_transfer || $booking->departure_transfer) {
            $costs .= ' ' . \Lang::get('bookings.transferCostsLabel', [], $locale) . ' <u>' . \Lang::get('bookings.touristsOptions.' . $booking->transfer_costs, [], $locale) . '</u>.';
        }

        if ($services) {
            $costs .= ' ' . \Lang::get('bookings.servicesCostsLabel', [], $locale) . ' <u>' . \Lang::get('bookings.touristsOptions.' . $booking->services_costs, [], $locale) . '</u>.';
        }

        $templateProcessor->setValue('SERVICES', $costs . ($services ? '\n<strong>' . \Lang::get('bookings.servicesLabel', [], $locale) . '</strong>: ' . implode(', ', $services) : ''));
        $templateProcessor->setValue('REMARKS', $booking->comments);
        $templateProcessor->setValue('DATE', Carbon::now('Europe/Sofia')->format('d.m.Y H:i:s'));
        $templateProcessor->setValue('USER', \Auth::user()->name);

        return response()->stream(function () use ($templateProcessor) {
            $templateProcessor->saveAs("php://output");
        }, 200, [
            'Content-Disposition' => 'attachment; filename="booking-form.docx"',
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Transfer-Encoding' => 'binary',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => 0,
        ]);
    }

    public function test(Request $request, NewsletterService $newsletterService, $id)
    {
        set_time_limit(300); // 5 mins.

        $booking = Booking::findOrFail($id);
        $apartment = $booking->apartment;
        $owner = $booking->owner;
        $locale = $owner->locale->locale;

        define("DOMPDF_ENABLE_AUTOLOAD", false);
        $dompdf = new Dompdf();
        $dompdf->set_paper('A4');
        $dompdf->set_option('isFontSubsettingEnabled', true);

        $html = file_get_contents(storage_path('app/templates/booking-form-' . $locale . '.html'));

        $html = preg_replace('/\${LOGO}/', storage_path('app/templates/logo.png'), $html);
        $html = preg_replace('/\${ID}/', $booking->id, $html);
        $html = preg_replace('/\${OWNER}/', $owner->full_name, $html);
        $html = preg_replace('/\${BUILDING}/', trim(preg_replace('/^(.*)\((.*)\)(.*)$/', '$2', $apartment->building->translate($locale)->name)), $html);
        $html = preg_replace('/\${APARTMENT}/', $apartment->number, $html);
        $html = preg_replace('/\${TYPE}/', $apartment->room->translate($locale)->name, $html);
        $html = preg_replace('/\${FURNITURE}/', $apartment->furniture->translate($locale)->name, $html);
        $html = preg_replace('/\${VIEW}/', $apartment->view->translate($locale)->name, $html);

        $contracts = [];
        $mm = [];
        $year = substr($booking->arrive_at, -4);
        if ($apartment->contracts->count()) {
            foreach ($apartment->contracts as $contract) {
                if ($contract->contractYears->contains('year', $year)) {
                    array_push($contracts, $contract->rentalContract->translate($locale)->name);
                    array_push($mm, \Lang::get('bookings.mmPayable.1', [], $locale) . ' - ' . \Lang::get('bookings.mmCoveredOptions.' . $contract->rentalContract->mm_covered, [], $locale));
                }
            }
        } else {
            array_push($mm, \Lang::get('bookings.mmPayable.0', [], $locale));
        }

        $communalFeeBalance = 0;
        $poolUsageBalance = 0;
        $year = Year::where('year', $year)->first();
        if ($year) {
            $fees = $year->fees->where('room_id', $apartment->room_id)->first();
            if ($fees) {
                $communalFeeTax = round($fees->annual_communal_tax / 1.95583);
                $communalFeeBalance = round($communalFeeTax, 2) - round($apartment->communalFeesPayments->where('year_id', $year->id)->sum('amount'), 2);

                $poolUsageTax = round($fees->pool_tax / 1.95583);
                $poolUsageBalance = round($poolUsageTax, 2) - round($apartment->poolUsagePayments->where('year_id', $year->id)->sum('amount'), 2);
            }
        }

        $html = preg_replace('/\${RC}/', $contracts ? implode('<br>', $contracts) : \Lang::get('bookings.mmCoveredOptions.0', [], $locale), $html);
        $html = preg_replace('/\${MM}/', implode('<br>', $mm), $html);
        $html = preg_replace('/\${CT}/', $communalFeeBalance ? \Lang::get('bookings.feesOptions.no', [], $locale) : \Lang::get('bookings.feesOptions.yes', [], $locale), $html);
        $html = preg_replace('/\${PU}/', $poolUsageBalance ? \Lang::get('bookings.feesOptions.no', [], $locale) : \Lang::get('bookings.feesOptions.yes', [], $locale), $html);
        /*$html = preg_replace('/\${KI}/', \Lang::get('bookings.kitchenItemsOptions.' . (int)$booking->kitchen_items, [], $locale), $html);
        $html = preg_replace('/\${LC}/', \Lang::get('bookings.loyaltyCardOptions.' . (int)$booking->loyalty_card, [], $locale), $html);
        $html = preg_replace('/\${CC}/', \Lang::get('bookings.clubCardOptions.' . (int)$booking->club_card, [], $locale), $html);*/
        $html = preg_replace('/\${EX}/', \Lang::get('bookings.exceptionOptions.' . (int)$booking->exception, [], $locale), $html);
        /*$html = preg_replace('/\${DP}/', \Lang::get('bookings.depositPaidOptions.' . (int)$booking->deposit_paid, [], $locale), $html);
        $html = preg_replace('/\${HC}/', \Lang::get('bookings.hotelCardOptions.' . (int)$booking->hotel_card, [], $locale), $html);*/
        $html = preg_replace('/\${ADATE}/', $booking->arrive_at . ($booking->arrival_time ? ' ' . $booking->arrival_time : ''), $html);
        $html = preg_replace('/\${AFLIGHT}/', $booking->arrival_flight, $html);
        $html = preg_replace('/\${AAIRPORT}/', $booking->arrivalAirport ? $booking->arrivalAirport->translate($locale)->name : '', $html);
        $html = preg_replace('/\${ATRANSFER}/', $booking->arrival_transfer ? \Lang::get('bookings.transferOptions.' . $booking->arrival_transfer, [], $locale) : '', $html);
        $html = preg_replace('/\${DDATE}/', $booking->departure_at . ($booking->departure_time ? ' ' . $booking->departure_time : ''), $html);
        $html = preg_replace('/\${DFLIGHT}/', $booking->departure_flight, $html);
        $html = preg_replace('/\${DAIRPORT}/', $booking->departureAirport ? $booking->departureAirport->translate($locale)->name : '', $html);
        $html = preg_replace('/\${DTRANSFER}/', $booking->departure_transfer ? \Lang::get('bookings.transferOptions.' . $booking->departure_transfer, [], $locale) : '', $html);
        $html = preg_replace('/\${ADULTS}/', $booking->adults->count(), $html);
        $html = preg_replace('/\${CHILDREN}/', $booking->children->count(), $html);

        $guests = max($booking->adults->count(), $booking->children->count());
        if ($guests > 1) {
            $html = preg_replace('/<!--guests-start-->(.*)<!--guests-end-->/s', str_repeat('$1', $guests), $html);
        }

        foreach ($booking->adults as $adult) {
            $html = preg_replace('/\${ADULT}/', $adult->name, $html, 1);
        }

        foreach ($booking->children as $child) {
            $html = preg_replace('/\${CHILD}/', $child->name, $html, 1);
        }

        $html = preg_replace('/\${ADULT}/', '', $html);
        $html = preg_replace('/\${CHILD}/', '', $html);

        $services = ExtraService::withTranslation()->select('extra_service_translations.name', 'extra_services.id')->leftJoin('extra_service_translations', 'extra_service_translations.extra_service_id', '=', 'extra_services.id')->where('extra_service_translations.locale', $locale)->whereIn('extra_services.id', explode(',', $booking->services))->orderBy('extra_service_translations.name')->get()->pluck('name')->toArray();

        $costs = '<strong>' . \Lang::get('bookings.costsLabel', [], $locale) . '</strong>: ' . \Lang::get('bookings.accommodationCostsLabel', [], $locale) . ' <u>' . \Lang::get('bookings.touristsOptions.' . $booking->accommodation_costs, [], $locale) . '</u>.';
        if ($booking->arrival_transfer || $booking->departure_transfer) {
            $costs .= ' ' . \Lang::get('bookings.transferCostsLabel', [], $locale) . ' <u>' . \Lang::get('bookings.touristsOptions.' . $booking->transfer_costs, [], $locale) . '</u>.';
        }

        if ($services) {
            $costs .= ' ' . \Lang::get('bookings.servicesCostsLabel', [], $locale) . ' <u>' . \Lang::get('bookings.touristsOptions.' . $booking->services_costs, [], $locale) . '</u>.';
        }

        if ($services) {
            $html = preg_replace('/\${SERVICES}/', $costs . '<br><strong>' . \Lang::get('bookings.servicesLabel', [], $locale) . '</strong>: ' . implode(', ', $services), $html);
        } else {
            $html = preg_replace('/<!--services-start-->.*<!--services-end-->/s', '', $html);
        }

        if ($booking->message) {
            $html = preg_replace('/\${REMARKS}/', $booking->message, $html);
        } else {
            $html = preg_replace('/<!--remarks-start-->.*<!--remarks-end-->/s', '', $html);
        }

        $html = preg_replace('/\${DATE}/', Carbon::now('Europe/Sofia')->format('d.m.Y H:i:s'), $html);
        $html = preg_replace('/\${NOTE}/', $contracts ? \Lang::get('bookings.booking-note-pdf', [], $locale) : '', $html);
        $html = preg_replace('/\${USER}/', \Auth::user()->name, $html);

        $dompdf->load_html($html);
        $dompdf->render();
        $data = $dompdf->output();

        $template = NewsletterTemplates::where('template', 'booking-form')->where('locale_id', $owner->locale_id)->firstOrFail();

        $directory = public_path('upload') . DIRECTORY_SEPARATOR . 'newsletter-templates' . DIRECTORY_SEPARATOR . $template->id . DIRECTORY_SEPARATOR;

        $attachments = [];
        foreach ($template->attachments as $attachment) {
            array_push($attachments, $directory . \Config::get('upload.attachmentsDirectory') . DIRECTORY_SEPARATOR . $attachment->uuid . DIRECTORY_SEPARATOR . $attachment->file);
        }

        $note = '';
        if (!$contracts) {
            $note = \Lang::get('bookings.booking-note-body', [], $locale);
        }

        $body = $newsletterService->replaceHtml($template->body . $note);

        $body = preg_replace('/\[\[ID\]\]/', $booking->id, $body);

        foreach ($newsletterService->patterns() as $key => $pattern) {
            if (strpos($body, $pattern) !== false) {
                $body = preg_replace('/' . $pattern . '/', '<span style="background-color: #ff0;">' . $owner->{$newsletterService->columns()[$key]} . '</span>', $body);
            }
        }

        $signature = $template->signature->translate($locale)->content;

        $onlineView = '';
        $links = \Lang::get(\Locales::getPublicNamespace() . '/newsletters.links', [], $locale);
        $copyright = \Lang::get(\Locales::getPublicNamespace() . '/newsletters.copyright', [], $locale);
        $disclaimer = \Lang::get(\Locales::getPublicNamespace() . '/newsletters.disclaimer', [], $locale);

        $html = \View::make(\Locales::getNamespace() . '.newsletters.templates.default', compact('body', 'signature', 'onlineView', 'links', 'copyright', 'disclaimer'))->with('newsletter', $template)->render();
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

        Mail::send([], [], function ($message) use ($data, $owner, $template, $apartment, $images, $attachments, $locale, $html, $text) {
            $message->from($template->signature->email, $template->signature->translate($locale)->name);
            $message->sender($template->signature->email, $template->signature->translate($locale)->name);
            $message->replyTo($template->signature->email, $template->signature->translate($locale)->name);
            $message->returnPath('mitko@sunsetresort.bg');
            // https://github.com/swiftmailer/swiftmailer/issues/58#issuecomment-210032031
            $message->to((\Config::get('mail.to.address') ? \Config::get('mail.to.address') : \Auth::user()->email), "=?UTF-8?B?" . base64_encode($owner->full_name) . "?=");
            $message->subject($template->subject . ': ' . $apartment->number);

            foreach ($images as $image) {
                $html = preg_replace('/' . preg_quote('cid:' . File::name($image) . '.' . File::extension($image)) . '/', $message->embed($image), $html, 1);
            }

            foreach ($attachments as $attachment) {
                $message->attach($attachment);
            }

            $message->setBody($html, 'text/html');
            $message->addPart($text, 'text/plain');
            $message->attachData($data, 'booking-form.pdf');
        });

        if (count(Mail::failures()) > 0) {
            $msg = '';
            foreach (Mail::failures() as $email) {
                $msg .= '<br />' . $email;
            }
            return response()->json([
                'errors' => [trans(\Locales::getNamespace() . '/forms.bookingSentError') . $msg],
            ]);
        } else {
            return response()->json([
                'success' => [trans(\Locales::getNamespace() . '/forms.bookingSentSuccessfully')],
            ]);
        }
    }

    public function send(Request $request, NewsletterService $newsletterService, $id)
    {
        set_time_limit(300); // 5 mins.

        $booking = Booking::findOrFail($id);
        $apartment = $booking->apartment;
        $owner = $booking->owner;
        $owners = $apartment->owners;
        $locale = $owner->locale->locale;

        define("DOMPDF_ENABLE_AUTOLOAD", false);
        $dompdf = new Dompdf();
        $dompdf->set_paper('A4');
        $dompdf->set_option('isFontSubsettingEnabled', true);

        $html = file_get_contents(storage_path('app/templates/booking-form-' . $locale . '.html'));

        $html = preg_replace('/\${LOGO}/', storage_path('app/templates/logo.png'), $html);
        $html = preg_replace('/\${ID}/', $booking->id, $html);
        $html = preg_replace('/\${OWNER}/', $owner->full_name, $html);
        $html = preg_replace('/\${BUILDING}/', trim(preg_replace('/^(.*)\((.*)\)(.*)$/', '$2', $apartment->building->translate($locale)->name)), $html);
        $html = preg_replace('/\${APARTMENT}/', $apartment->number, $html);
        $html = preg_replace('/\${TYPE}/', $apartment->room->translate($locale)->name, $html);
        $html = preg_replace('/\${FURNITURE}/', $apartment->furniture->translate($locale)->name, $html);
        $html = preg_replace('/\${VIEW}/', $apartment->view->translate($locale)->name, $html);

        $contracts = [];
        $mm = [];
        $year = substr($booking->arrive_at, -4);
        if ($apartment->contracts->count()) {
            foreach ($apartment->contracts as $contract) {
                if ($contract->contractYears->contains('year', $year)) {
                    array_push($contracts, $contract->rentalContract->translate($locale)->name);
                    array_push($mm, \Lang::get('bookings.mmPayable.1', [], $locale) . ' - ' . \Lang::get('bookings.mmCoveredOptions.' . $contract->rentalContract->mm_covered, [], $locale));
                }
            }
        } else {
            array_push($mm, \Lang::get('bookings.mmPayable.0', [], $locale));
        }

        $communalFeeBalance = 0;
        $poolUsageBalance = 0;
        $year = Year::where('year', $year)->first();
        if ($year) {
            $fees = $year->fees->where('room_id', $apartment->room_id)->first();
            if ($fees) {
                $communalFeeTax = round($fees->annual_communal_tax / 1.95583);
                $communalFeeBalance = round($communalFeeTax, 2) - round($apartment->communalFeesPayments->where('year_id', $year->id)->sum('amount'), 2);

                $poolUsageTax = round($fees->pool_tax / 1.95583);
                $poolUsageBalance = round($poolUsageTax, 2) - round($apartment->poolUsagePayments->where('year_id', $year->id)->sum('amount'), 2);
            }
        }

        $html = preg_replace('/\${RC}/', $contracts ? implode('<br>', $contracts) : \Lang::get('bookings.mmCoveredOptions.0', [], $locale), $html);
        $html = preg_replace('/\${MM}/', implode('<br>', $mm), $html);
        $html = preg_replace('/\${CT}/', $communalFeeBalance ? \Lang::get('bookings.feesOptions.no', [], $locale) : \Lang::get('bookings.feesOptions.yes', [], $locale), $html);
        $html = preg_replace('/\${PU}/', $poolUsageBalance ? \Lang::get('bookings.feesOptions.no', [], $locale) : \Lang::get('bookings.feesOptions.yes', [], $locale), $html);
        /*$html = preg_replace('/\${KI}/', \Lang::get('bookings.kitchenItemsOptions.' . (int)$booking->kitchen_items, [], $locale), $html);
        $html = preg_replace('/\${LC}/', \Lang::get('bookings.loyaltyCardOptions.' . (int)$booking->loyalty_card, [], $locale), $html);
        $html = preg_replace('/\${CC}/', \Lang::get('bookings.clubCardOptions.' . (int)$booking->club_card, [], $locale), $html);*/
        $html = preg_replace('/\${EX}/', \Lang::get('bookings.exceptionOptions.' . (int)$booking->exception, [], $locale), $html);
        /*$html = preg_replace('/\${DP}/', \Lang::get('bookings.depositPaidOptions.' . (int)$booking->deposit_paid, [], $locale), $html);
        $html = preg_replace('/\${HC}/', \Lang::get('bookings.hotelCardOptions.' . (int)$booking->hotel_card, [], $locale), $html);*/
        $html = preg_replace('/\${ADATE}/', $booking->arrive_at . ($booking->arrival_time ? ' ' . $booking->arrival_time : ''), $html);
        $html = preg_replace('/\${AFLIGHT}/', $booking->arrival_flight, $html);
        $html = preg_replace('/\${AAIRPORT}/', $booking->arrivalAirport ? $booking->arrivalAirport->translate($locale)->name : '', $html);
        $html = preg_replace('/\${ATRANSFER}/', $booking->arrival_transfer ? \Lang::get('bookings.transferOptions.' . $booking->arrival_transfer, [], $locale) : '', $html);
        $html = preg_replace('/\${DDATE}/', $booking->departure_at . ($booking->departure_time ? ' ' . $booking->departure_time : ''), $html);
        $html = preg_replace('/\${DFLIGHT}/', $booking->departure_flight, $html);
        $html = preg_replace('/\${DAIRPORT}/', $booking->departureAirport ? $booking->departureAirport->translate($locale)->name : '', $html);
        $html = preg_replace('/\${DTRANSFER}/', $booking->departure_transfer ? \Lang::get('bookings.transferOptions.' . $booking->departure_transfer, [], $locale) : '', $html);
        $html = preg_replace('/\${ADULTS}/', $booking->adults->count(), $html);
        $html = preg_replace('/\${CHILDREN}/', $booking->children->count(), $html);

        $guests = max($booking->adults->count(), $booking->children->count());
        if ($guests > 1) {
            $html = preg_replace('/<!--guests-start-->(.*)<!--guests-end-->/s', str_repeat('$1', $guests), $html);
        }

        foreach ($booking->adults as $adult) {
            $html = preg_replace('/\${ADULT}/', $adult->name, $html, 1);
        }

        foreach ($booking->children as $child) {
            $html = preg_replace('/\${CHILD}/', $child->name, $html, 1);
        }

        $html = preg_replace('/\${ADULT}/', '', $html);
        $html = preg_replace('/\${CHILD}/', '', $html);

        $services = ExtraService::withTranslation()->select('extra_service_translations.name', 'extra_services.id')->leftJoin('extra_service_translations', 'extra_service_translations.extra_service_id', '=', 'extra_services.id')->where('extra_service_translations.locale', \Locales::getCurrent())->whereIn('extra_services.id', explode(',', $booking->services))->orderBy('extra_service_translations.name')->get()->pluck('name')->toArray();
        $costs = '<strong>' . \Lang::get('bookings.costsLabel', [], $locale) . '</strong>: ' . \Lang::get('bookings.accommodationCostsLabel', [], $locale) . ' <u>' . \Lang::get('bookings.touristsOptions.' . $booking->accommodation_costs, [], $locale) . '</u>.';
        if ($booking->arrival_transfer || $booking->departure_transfer) {
            $costs .= ' ' . \Lang::get('bookings.transferCostsLabel', [], $locale) . ' <u>' . \Lang::get('bookings.touristsOptions.' . $booking->transfer_costs, [], $locale) . '</u>.';
        }

        if ($services) {
            $costs .= ' ' . \Lang::get('bookings.servicesCostsLabel', [], $locale) . ' <u>' . \Lang::get('bookings.touristsOptions.' . $booking->services_costs, [], $locale) . '</u>.';
        }

        if ($services) {
            $html = preg_replace('/\${SERVICES}/', $costs . '<br><strong>' . \Lang::get('bookings.servicesLabel', [], $locale) . '</strong>: ' . implode(', ', $services), $html);
        } else {
            $html = preg_replace('/<!--services-start-->.*<!--services-end-->/s', '', $html);
        }

        if ($booking->message) {
            $html = preg_replace('/\${REMARKS}/', $booking->message, $html);
        } else {
            $html = preg_replace('/<!--remarks-start-->.*<!--remarks-end-->/s', '', $html);
        }

        $html = preg_replace('/\${DATE}/', Carbon::now('Europe/Sofia')->format('d.m.Y H:i:s'), $html);
        $html = preg_replace('/\${NOTE}/', $contracts ? \Lang::get('bookings.booking-note-pdf', [], $locale) : '', $html);
        $html = preg_replace('/\${USER}/', \Auth::user()->name, $html);

        $dompdf->load_html($html);
        $dompdf->render();
        $data = $dompdf->output();

        $template = NewsletterTemplates::where('template', 'booking-form')->where('locale_id', $owner->locale_id)->firstOrFail();

        $directory = public_path('upload') . DIRECTORY_SEPARATOR . 'newsletter-templates' . DIRECTORY_SEPARATOR . $template->id . DIRECTORY_SEPARATOR;

        $attachments = [];
        foreach ($template->attachments as $attachment) {
            array_push($attachments, $directory . \Config::get('upload.attachmentsDirectory') . DIRECTORY_SEPARATOR . $attachment->uuid . DIRECTORY_SEPARATOR . $attachment->file);
        }

        $note = '';
        if (!$contracts) {
            $note = \Lang::get('bookings.booking-note-body', [], $locale);
        }

        $body = $newsletterService->replaceHtml($template->body);

        $body = preg_replace('/\[\[ID\]\]/', $booking->id, $body);

        foreach ($newsletterService->patterns() as $key => $pattern) {
            if (strpos($body, $pattern) !== false) {
                $body = preg_replace('/' . $pattern . '/', $owner->{$newsletterService->columns()[$key]}, $body);
            }
        }

        $signature = $template->signature->translate($locale)->content;

        $onlineView = '';
        $links = \Lang::get(\Locales::getPublicNamespace() . '/newsletters.links', [], $locale);
        $copyright = \Lang::get(\Locales::getPublicNamespace() . '/newsletters.copyright', [], $locale);
        $disclaimer = \Lang::get(\Locales::getPublicNamespace() . '/newsletters.disclaimer', [], $locale);

        $html = \View::make(\Locales::getNamespace() . '.newsletters.templates.default', compact('body', 'signature', 'onlineView', 'links', 'copyright', 'disclaimer'))->with('newsletter', $template)->render();
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

        Mail::send([], [], function ($message) use ($data, $owners, $owner, $template, $apartment, $images, $attachments, $locale, $html, $text) {
            $message->from($template->signature->email, $template->signature->translate($locale)->name);
            $message->sender($template->signature->email, $template->signature->translate($locale)->name);
            $message->replyTo($template->signature->email, $template->signature->translate($locale)->name);
            $message->returnPath('mitko@sunsetresort.bg');

            // https://github.com/swiftmailer/swiftmailer/issues/58#issuecomment-210032031
            $message->to((\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $owner->email), "=?UTF-8?B?" . base64_encode($owner->full_name) . "?=");
            if ($owner->email_cc) {
                $message->to((\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $owner->email_cc), "=?UTF-8?B?" . base64_encode($owner->full_name) . "?=");
            }

            foreach ($owners as $o) {
                if ($owner->id != $o->owner->id && $o->owner->email) {
                    $message->cc((\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $o->owner->email), "=?UTF-8?B?" . base64_encode($o->owner->full_name) . "?=");
                    if ($o->owner->email_cc) {
                        $message->cc((\Config::get('mail.to.address') ? \Config::get('mail.to.address') : $o->owner->email_cc), "=?UTF-8?B?" . base64_encode($o->owner->full_name) . "?=");
                    }
                }
            }

            $message->subject($template->subject . ': ' . $apartment->number);

            foreach ($images as $image) {
                $html = preg_replace('/' . preg_quote('cid:' . File::name($image) . '.' . File::extension($image)) . '/', $message->embed($image), $html, 1);
            }

            foreach ($attachments as $attachment) {
                $message->attach($attachment);
            }

            $message->setBody($html, 'text/html');
            $message->addPart($text, 'text/plain');
            $message->attachData($data, 'booking-form.pdf');
        });

        if (count(Mail::failures()) > 0) {
            $msg = '';
            foreach (Mail::failures() as $email) {
                $msg .= '<br />' . $email;
            }
            return response()->json([
                'errors' => [trans(\Locales::getNamespace() . '/forms.bookingSentError') . $msg],
            ]);
        } else {
            $booking->is_confirmed = 1;
            $booking->save();

            Mail::send([], [], function ($message) use ($data, $template, $booking, $apartment, $attachments, $locale, $newsletterService) {
                $message->from($template->signature->email, $template->signature->translate($locale)->name);
                $message->sender($template->signature->email, $template->signature->translate($locale)->name);
                $message->replyTo($template->signature->email, $template->signature->translate($locale)->name);
                $message->returnPath('mitko@sunsetresort.bg');

                // https://github.com/swiftmailer/swiftmailer/issues/58#issuecomment-210032031
                $message->to((\Config::get('mail.to.address') ? \Config::get('mail.to.address') : 'receptionowners@sunsetresort.bg'), "=?UTF-8?B?" . base64_encode('Reception Owners') . "?=");
                $message->cc((\Config::get('mail.to.address') ? \Config::get('mail.to.address') : \Auth::user()->email), "=?UTF-8?B?" . base64_encode(\Auth::user()->name) . "?=");

                $message->subject($template->subject . ': ' . $apartment->number);

                foreach ($attachments as $attachment) {
                    $message->attach($attachment);
                }

                $message->setBody($booking->comments, 'text/html');
                $message->addPart($newsletterService->replaceText($booking->comments), 'text/plain');
                $message->attachData($data, 'booking-form.pdf');
            });

            return response()->json([
                'success' => [trans(\Locales::getNamespace() . '/forms.bookingSentSuccessfully')],
            ]);
        }
    }
}
