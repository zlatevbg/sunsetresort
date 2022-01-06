<?php namespace App\Http\Controllers\Owners;

use App\Http\Controllers\Controller;
use App\Services\DataTable;
use App\Models\Owners\Apartment;
use App\Models\Owners\Year;
use App\Models\Owners\KeyLog;

class KeyLogController extends Controller {

    protected $route = 'key-log';
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
                'title' => trans(\Locales::getNamespace() . '/datatables.titleKeyLog'),
                'class' => 'table-striped table-bordered table-hover responsive no-wrap',
                'columns' => [
                    [
                        'id' => 'occupied_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.date'),
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'status',
                        'name' => trans(\Locales::getNamespace() . '/datatables.status'),
                        'order' => false,
                        'class' => 'vertical-center text-right',
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

        $entries = KeyLog::select('occupied_at', 'people')->where('apartment_id', $apartment->id)->whereYear('occupied_at', '=', $year->year)->orderBy('occupied_at')->get();

        $data = [];
        foreach ($entries as $entry) {
            array_push($data, [
                'occupied_at' => $entry->occupied_at,
                'status' => trans(\Locales::getNamespace() . '/datatables.occupied'),
            ]);
        }

        $datatable->setup(null, $this->route, $this->datatables[$this->route]);
        $datatable->setOption('data', $data);
        $datatables = $datatable->getTables();
        $datatables[$this->route]['ajax'] = false;

        return view(\Locales::getNamespace() . '.' . $this->route . '.index', compact('datatables'));
    }

}
