<?php namespace App\Http\Controllers\Owners;

use App\Http\Controllers\Controller;
use App\Services\DataTable;
use App\Models\Owners\RentalContract;
use App\Models\Owners\RentalPaymentPrices;
use App\Models\Owners\Navigation;

class RentalOptionsController extends Controller {

    protected $route = 'rental-options';
    protected $datatables;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->datatables = [
            'contracts' => [
                'dom' => 'tr',
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'columns' => [
                    [
                        'id' => 'mmFeesPaid',
                        'name' => trans(\Locales::getNamespace() . '/datatables.mmFeesPaid'),
                        'order' => false,
                        'class' => 'vertical-center text-center',
                    ],
                    [
                        'id' => 'maxDuration',
                        'name' => trans(\Locales::getNamespace() . '/datatables.maxDuration'),
                        'order' => false,
                        'class' => 'vertical-center text-center',
                    ],
                    [
                        'id' => 'contractDuration',
                        'name' => trans(\Locales::getNamespace() . '/datatables.contractDuration'),
                        'order' => false,
                        'class' => 'vertical-center text-center',
                    ],
                    [
                        'id' => 'personalUsage',
                        'name' => trans(\Locales::getNamespace() . '/datatables.personalUsage'),
                        'order' => false,
                        'class' => 'vertical-center text-center',
                    ],
                    [
                        'id' => 'rentalPaymentDate',
                        'name' => trans(\Locales::getNamespace() . '/datatables.rentalPaymentDate'),
                        'order' => false,
                        'class' => 'vertical-center text-center',
                    ],
                ],
            ],
            'payments' => [
                'dom' => 'tr',
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'columns' => [
                    [
                        'id' => 'room',
                        'name' => trans(\Locales::getNamespace() . '/datatables.room'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'furniture',
                        'name' => trans(\Locales::getNamespace() . '/datatables.furniture'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'view',
                        'name' => trans(\Locales::getNamespace() . '/datatables.view'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'rent',
                        'name' => trans(\Locales::getNamespace() . '/datatables.rent'),
                        'order' => false,
                        'class' => 'vertical-center text-right',
                    ],
                ],
            ],
        ];
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function index(DataTable $datatable, RentalContract $contracts, Navigation $page)
    {
        $page = $page->where('type', 'rental-options')->where('locale_id', \Locales::getId())->firstOrFail();

        $owner_id = \Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user()->id;

        $contracts = $contracts->select('rental_contracts.contract_dfrom1', 'rental_contracts.contract_dto1', 'rental_contracts.contract_dfrom2', 'rental_contracts.contract_dto2', 'rental_contracts.personal_dfrom1', 'rental_contracts.personal_dto1', 'rental_contracts.personal_dfrom2', 'rental_contracts.personal_dto2', 'rental_contracts.mm_covered', 'rental_contracts.max_duration', 'rental_contracts.deadline_at', 'rental_contracts.id', 'rental_contracts.rental_payment_id', 'rental_contract_translations.name as contract_name', 'rental_contract_translations.benefits as contract_benefits')->leftJoin('rental_contract_translations', function($join) {
            $join->on('rental_contract_translations.rental_contract_id', '=', 'rental_contracts.id')->where('rental_contract_translations.locale', '=', \Locales::getCurrent());
        })->get();

        foreach ($contracts as $contract) {
            $contractDates = '';
            if ($contract->contract_dfrom1) {
                $contractDates .= $contract->contract_dfrom1;

                if ($contract->contract_dto1) {
                    $contractDates .= ' / ' . $contract->contract_dto1;
                }

                if ($contract->contract_dfrom2) {
                    $contractDates .= '<br>' . $contract->contract_dfrom2;
                }

                if ($contract->contract_dto2) {
                    $contractDates .= ' / ' . $contract->contract_dto2;
                }
            }

            $usageDates = '';
            if ($contract->personal_dfrom1) {
                $usageDates .= $contract->personal_dfrom1;

                if ($contract->personal_dto1) {
                    $usageDates .= ' / ' . $contract->personal_dto1;
                }

                if ($contract->personal_dfrom2) {
                    $usageDates .= '<br>' . $contract->personal_dfrom2;
                }

                if ($contract->personal_dto2) {
                    $usageDates .= ' / ' . $contract->personal_dto2;
                }
            } else {
                $usageDates = trans(\Locales::getNamespace() . '/datatables.outsideContractDates');
            }

            $data = [];
            array_push($data, [
                'mmFeesPaid' => ($contract->mm_covered > 0 ? $contract->mm_covered . '%' : trans(\Locales::getNamespace() . '/messages.no')),
                'maxDuration' => $contract->max_duration . ' ' . trans_choice(\Locales::getNamespace() . '/datatables.choiceYears', $contract->max_duration),
                'contractDuration' => $contractDates,
                'personalUsage' => $usageDates,
                'rentalPaymentDate' => $contract->deadline_at,
            ]);

            $datatable->setup(null, 'contract-' . $contract->id, $this->datatables['contracts']);
            $datatable->setOption('data', $data);
            $datatable->setOption('title', $contract->contract_name);
            $datatable->setOption('smalltitle', $contract->contract_benefits ? trans(\Locales::getNamespace() . '/datatables.benefits') . ': ' . $contract->contract_benefits : '');

            if ($contract->rental_payment_id) {
                $data = [];

                $prices = RentalPaymentPrices::select('rental_payment_prices.price', 'room_translations.name as room', 'room_translations.description', 'furniture_translations.name as furniture', 'view_translations.name as view')->leftJoin('apartments', function($join) {
                    $join->on('apartments.room_id', '=', 'rental_payment_prices.room_id')->on('apartments.furniture_id', '=', 'rental_payment_prices.furniture_id')->on('apartments.view_id', '=', 'rental_payment_prices.view_id');
                })->leftJoin('ownership', function($join) {
                    $join->on('ownership.apartment_id', '=', 'apartments.id');
                })->leftJoin('room_translations', function($join) {
                    $join->on('room_translations.room_id', '=', 'rental_payment_prices.room_id')->where('room_translations.locale', '=', \Locales::getCurrent());
                })->leftJoin('furniture_translations', function($join) {
                    $join->on('furniture_translations.furniture_id', '=', 'rental_payment_prices.furniture_id')->where('furniture_translations.locale', '=', \Locales::getCurrent());
                })->leftJoin('view_translations', function($join) {
                    $join->on('view_translations.view_id', '=', 'rental_payment_prices.view_id')->where('view_translations.locale', '=', \Locales::getCurrent());
                })->where('ownership.owner_id', '=', $owner_id)->where('rental_payment_prices.rental_payment_id', $contract->rental_payment_id)->whereNull('ownership.deleted_at')->whereNull('apartments.deleted_at')->groupBy('rental_payment_prices.room_id', 'rental_payment_prices.furniture_id', 'rental_payment_prices.view_id')->get();

                foreach ($prices as $price) {
                    array_push($data, [
                        'room' => $price->room . ' (' . $price->description . ')',
                        'furniture' => $price->furniture,
                        'view' => $price->view,
                        'rent' => '&euro; ' . number_format($price->price, 2),
                    ]);
                }

                $datatable->setup(null, 'payments-' . $contract->id, $this->datatables['payments']);
                $datatable->setOption('data', $data);
                $datatable->setOption('subtitle', $contract->contract_name . ' ' . trans(\Locales::getNamespace() . '/datatables.rentalPayment'));
                $datatable->setOption('smalltitle', trans(\Locales::getNamespace() . '/datatables.rentalPaymentNote'));
            }
        }

        $datatables = $datatable->getTables();

        return view(\Locales::getNamespace() . '.' . $this->route . '.index', compact('datatables', 'page'));
    }

}
