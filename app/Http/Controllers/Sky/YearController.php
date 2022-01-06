<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Models\Sky\Year;
use App\Models\Sky\Fee;
use App\Models\Sky\Room;
use App\Models\Sky\RentalCompany;
use App\Http\Requests\Sky\YearRequest;

class YearController extends Controller {

    protected $route = 'years';
    protected $datatables;
    protected $multiselect;

    public function __construct()
    {
        $this->datatables = [
            $this->route => [
                'title' => trans(\Locales::getNamespace() . '/datatables.titleYears'),
                'url' => \Locales::route($this->route),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'joins' => [
                    [
                        'table' => 'rental_company_year',
                        'localColumn' => 'rental_company_year.year_id',
                        'constrain' => '=',
                        'foreignColumn' => $this->route . '.id',
                    ],
                    [
                        'table' => 'rental_company_translations',
                        'localColumn' => 'rental_company_translations.rental_company_id',
                        'constrain' => '=',
                        'foreignColumn' => 'rental_company_year.rental_company_id',
                        'whereColumn' => 'rental_company_translations.locale',
                        'whereConstrain' => '=',
                        'whereValue' => \Locales::getCurrent(),
                        'group' => $this->route . '.id',
                    ],
                ],
                'columns' => [
                    [
                        'selector' => $this->route . '.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center',
                    ],
                    [
                        'selector' => $this->route . '.year',
                        'id' => 'year',
                        'name' => trans(\Locales::getNamespace() . '/datatables.year'),
                        'search' => true,
                    ],
                    [
                        'selector' => $this->route . '.corporate_tax',
                        'id' => 'corporate_tax',
                        'name' => trans(\Locales::getNamespace() . '/datatables.corporateTax'),
                    ],
                    [
                        'selectRaw' => 'GROUP_CONCAT(rental_company_translations.name ORDER BY rental_company_translations.name SEPARATOR ", ") as companies',
                        'id' => 'companies',
                        'name' => trans(\Locales::getNamespace() . '/datatables.rentalCompany'),
                    ],
                ],
                'orderByColumn' => 1,
                'order' => 'desc',
                'buttons' => [
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
                ],
            ],
        ];

        $this->multiselect = [
            'companies' => [
                'id' => 'id',
                'name' => 'name',
            ],
        ];
    }

    public function index(DataTable $datatable, Year $year, Request $request)
    {
        $datatable->setup($year->with('rentalCompanies'), $this->route, $this->datatables[$this->route]);

        $datatables = $datatable->getTables();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($datatables);
        } else {
            return view(\Locales::getNamespace() . '.' . $this->route . '.index', compact('datatables'));
        }
    }

    public function create(Request $request)
    {
        $table = $request->input('table') ?: $this->route;

        $rooms = Room::withTranslation()->select('room_translations.name', 'rooms.id')->leftJoin('room_translations', 'room_translations.room_id', '=', 'rooms.id')->where('room_translations.locale', \Locales::getCurrent())->orderBy('room_translations.name')->get();
        $feesTypes = trans(\Locales::getNamespace() . '/multiselect.feesTypes');
        $this->multiselect['companies']['options'] = RentalCompany::withTranslation()->select('rental_company_translations.name', 'rental_companies.id')->leftJoin('rental_company_translations', 'rental_company_translations.rental_company_id', '=', 'rental_companies.id')->where('rental_company_translations.locale', \Locales::getCurrent())->orderBy('rental_company_translations.name')->get()->toArray();
        $this->multiselect['companies']['selected'] = '';

        $multiselect = $this->multiselect;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('table', 'multiselect', 'rooms', 'feesTypes'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function store(DataTable $datatable, Year $year, YearRequest $request)
    {
        $newYear = Year::create($request->all());

        if ($newYear->id) {
            $newYear->rentalCompanies()->sync($request->input('companies'));

            $fees = [];
            foreach ($request->input('fees') as $room => $rooms) {
                $data = [];
                foreach ($rooms as $type => $fee) {
                    $data[$type] = $fee ?: 0;
                }

                array_push($fees, $data + [
                    'year_id' => $newYear->id,
                    'room_id' => $room,
                ]);
            }

            Fee::insert($fees);

            $successMessage = trans(\Locales::getNamespace() . '/forms.storedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityYears', 1)]);

            $datatable->setup($year, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'reset' => true,
                'resetMultiselect' => [
                    'input-companies' => ['refresh'],
                ],
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.createError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityYears', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function edit(Request $request, $id = null)
    {
        $year = Year::with('fees')->findOrFail($id);

        $table = $request->input('table') ?: $this->route;

        $fees = [];
        foreach ($year->fees as $fee) {
            foreach (trans(\Locales::getNamespace() . '/multiselect.feesTypes') as $type => $name) {
                $fees[$fee->room_id][$type] = $fee->{$type};
            }
        }

        $rooms = Room::withTranslation()->select('room_translations.name', 'rooms.id')->leftJoin('room_translations', 'room_translations.room_id', '=', 'rooms.id')->where('room_translations.locale', \Locales::getCurrent())->orderBy('room_translations.name')->get();
        $feesTypes = trans(\Locales::getNamespace() . '/multiselect.feesTypes');
        $this->multiselect['companies']['options'] = RentalCompany::withTranslation()->select('rental_company_translations.name', 'rental_companies.id')->leftJoin('rental_company_translations', 'rental_company_translations.rental_company_id', '=', 'rental_companies.id')->where('rental_company_translations.locale', \Locales::getCurrent())->orderBy('rental_company_translations.name')->get()->toArray();
        $this->multiselect['companies']['selected'] = $year->rentalCompanies->pluck('id')->toArray();

        $multiselect = $this->multiselect;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('year', 'table', 'multiselect', 'rooms', 'feesTypes', 'fees'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, YearRequest $request)
    {
        $year = Year::findOrFail($request->input('id'))->first();

        if ($year->update($request->all())) {
            $year->rentalCompanies()->sync($request->input('companies'));

            Fee::where('year_id', $year->id)->forceDelete();

            $fees = [];
            foreach ($request->input('fees') as $room => $rooms) {
                $data = [];
                foreach ($rooms as $type => $fee) {
                    $data[$type] = $fee ?: 0;
                }

                array_push($fees, $data + [
                    'year_id' => $year->id,
                    'room_id' => $room,
                ]);
            }

            Fee::insert($fees);

            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityYears', 1)]);

            $datatable->setup($year, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityYears', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

}
