<?php namespace App\Http\Controllers\Owners;

use App\Http\Controllers\Controller;
use App\Services\DataTable;
use App\Models\Owners\Apartment;
use App\Models\Owners\Year;
use App\Models\Owners\PoolUsagePayment;
use App\Models\Owners\PoolUsagePaymentDocuments;

class PoolUsageController extends Controller {

    protected $route = 'pool-usage';
    protected $datatables;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->datatables = [
            $this->route => [
                'dom' => 'tr',
                'title' => trans(\Locales::getNamespace() . '/datatables.titlePoolUsagePayments'),
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'columns' => [
                    [
                        'id' => 'paid_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.paidAt'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'amount',
                        'name' => trans(\Locales::getNamespace() . '/datatables.amount'),
                        'order' => false,
                        'class' => 'vertical-center text-right',
                    ],
                    [
                        'id' => 'method',
                        'name' => trans(\Locales::getNamespace() . '/datatables.paymentMethod'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'paidBy',
                        'name' => trans(\Locales::getNamespace() . '/datatables.paidBy'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'documents',
                        'name' => trans(\Locales::getNamespace() . '/datatables.documents'),
                        'order' => false,
                        'class' => 'text-center vertical-center',
                    ],
                ],
            ],
        ];
    }

    public function index(DataTable $datatable, $apartment = null, $year = null)
    {
        $owner_id = \Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user()->id;

        $apartment = Apartment::selectRaw('apartments.id, YEAR(ownership.created_at) as year')->leftJoin('ownership', 'ownership.apartment_id', '=', 'apartments.id')->where('ownership.owner_id', '=', $owner_id)->whereNull('ownership.deleted_at')->whereNull('apartments.deleted_at')->findOrFail($apartment);
        $year = Year::where('year', '>=', $apartment->year)->findOrFail($year);

        $payments = PoolUsagePayment::selectRaw('CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as owner_name, rental_company_translations.name as company_name, payment_method_translations.name as method, pool_usage_payments.id, pool_usage_payments.paid_at, pool_usage_payments.amount')->leftJoin('owners', 'owners.id', '=', 'pool_usage_payments.owner_id')->leftJoin('rental_company_translations', function($join) {
            $join->on('rental_company_translations.rental_company_id', '=', 'pool_usage_payments.rental_company_id')->where('rental_company_translations.locale', '=', \Locales::getCurrent());
        })->leftJoin('payment_method_translations', function($join) {
            $join->on('payment_method_translations.payment_method_id', '=', 'pool_usage_payments.payment_method_id')->where('payment_method_translations.locale', '=', \Locales::getCurrent());
        })->where('pool_usage_payments.apartment_id', $apartment->id)->where('pool_usage_payments.year_id', $year->id)->orderBy('pool_usage_payments.paid_at', 'desc')->get();

        $data = [];
        foreach ($payments as $payment) {
            $documents = '';
            foreach ($payment->documents as $document) {
                $documents .= '<a title="' . trans(\Locales::getNamespace() . '/multiselect.poolPaymentDocuments.' . $document->type) . ', ' . \App\Helpers\formatBytes($document->size) . '" href="' . \Locales::route('download-pool-usage-document', $document->id) . '">' . \HTML::image(\App\Helpers\autover('/img/' . \Locales::getNamespace() . '/ext/' . $document->extension . '.png')) . '</a>';
            }

            array_push($data, [
                'paid_at' => $payment->paid_at,
                'amount' => '&euro; ' . number_format($payment->amount, 2),
                'method' => $payment->method,
                'paidBy' => $payment->company_name ?: (\Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user()->email == 'dummy@sunsetresort.bg' ? 'Dummy Name' : $payment->owner_name),
                'documents' => $documents,
            ]);
        }

        $datatable->setup(null, $this->route, $this->datatables[$this->route]);
        $datatable->setOption('data', $data);
        $datatables = $datatable->getTables();
        $datatables[$this->route]['ajax'] = false;

        return view(\Locales::getNamespace() . '.' . $this->route . '.index', compact('datatables'));
    }

    public function download($id = null)
    {
        $file = PoolUsagePaymentDocuments::findOrFail($id);

        $owner_id = \Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user()->id;

        $apartment = Apartment::select('apartments.id')->leftJoin('ownership', 'ownership.apartment_id', '=', 'apartments.id')->where('ownership.owner_id', '=', $owner_id)->whereNull('ownership.deleted_at')->whereNull('apartments.deleted_at')->findOrFail($file->poolUsagePayment->apartment_id);

        $uploadDirectory = public_path('upload') . DIRECTORY_SEPARATOR . 'apartments' . DIRECTORY_SEPARATOR . $apartment->id . DIRECTORY_SEPARATOR . 'payments' . DIRECTORY_SEPARATOR . $file->uuid . DIRECTORY_SEPARATOR . $file->file;

        return response()->download($uploadDirectory);
    }

}
