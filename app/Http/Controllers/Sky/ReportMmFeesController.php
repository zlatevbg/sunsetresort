<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Models\Sky\Project;
use App\Models\Sky\Building;
use App\Models\Sky\Apartment;
use App\Models\Sky\Room;
use App\Models\Sky\Furniture;
use App\Models\Sky\View;
use App\Models\Sky\Year;
use App\Models\Sky\MmFeePayment;
use App\Services\DataTable;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportMmFeesController extends Controller
{

    protected $route = 'reports';
    protected $datatables;
    protected $multiselect;

    public function __construct()
    {
        $this->datatables = [
            $this->route => [
                'wrapperClass' => 'table-invisible',
                'dom' => "<'dataTableFull'l>tr<'clearfix'<'dataTableI'i><'dataTableP'p>>",
                'url' => \Locales::route('reports/mm-fees'),
                'class' => 'table-checkbox table-striped table-bordered table-hover table-report',
                'joins' => [
                    [
                        'table' => 'locales',
                        'localColumn' => 'locales.id',
                        'constrain' => '=',
                        'foreignColumn' => 'owners.locale_id',
                    ],
                    [
                        'table' => 'project_translations',
                        'localColumn' => 'project_translations.project_id',
                        'constrain' => '=',
                        'foreignColumn' => 'apartments.project_id',
                        'whereColumn' => 'project_translations.locale',
                        'whereConstrain' => '=',
                        'whereValue' => \Locales::getCurrent(),
                    ],
                    [
                        'table' => 'building_translations',
                        'localColumn' => 'building_translations.building_id',
                        'constrain' => '=',
                        'foreignColumn' => 'apartments.building_id',
                        'whereColumn' => 'building_translations.locale',
                        'whereConstrain' => '=',
                        'whereValue' => \Locales::getCurrent(),
                    ],
                    [
                        'table' => 'room_translations',
                        'localColumn' => 'room_translations.room_id',
                        'constrain' => '=',
                        'foreignColumn' => 'apartments.room_id',
                        'whereColumn' => 'room_translations.locale',
                        'whereConstrain' => '=',
                        'whereValue' => \Locales::getCurrent(),
                    ],
                    [
                        'table' => 'furniture_translations',
                        'localColumn' => 'furniture_translations.furniture_id',
                        'constrain' => '=',
                        'foreignColumn' => 'apartments.furniture_id',
                        'whereColumn' => 'furniture_translations.locale',
                        'whereConstrain' => '=',
                        'whereValue' => \Locales::getCurrent(),
                    ],
                    [
                        'table' => 'view_translations',
                        'localColumn' => 'view_translations.view_id',
                        'constrain' => '=',
                        'foreignColumn' => 'apartments.view_id',
                        'whereColumn' => 'view_translations.locale',
                        'whereConstrain' => '=',
                        'whereValue' => \Locales::getCurrent(),
                    ],
                ],
                'columns' => [
                    [
                        'selector' => 'apartments.number',
                        'id' => 'number',
                        'name' => trans(\Locales::getNamespace() . '/datatables.apartment'),
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'mmYears',
                        'name' => trans(\Locales::getNamespace() . '/datatables.mmYears'),
                        'class' => 'text-right vertical-center',
                        'order' => false,
                    ],
                    [
                        'id' => 'amount',
                        'name' => trans(\Locales::getNamespace() . '/datatables.amount'),
                        'class' => 'text-right vertical-center',
                        'order' => false,
                        'calculateMmFees' => true,
                    ],
                    [
                        'selectRaw' => 'CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) AS owner, apartments.room_id',
                        'id' => 'owner',
                        'name' => trans(\Locales::getNamespace() . '/datatables.owner'),
                        'order' => false,
                        'class' => 'vertical-center',
                        'info' => [
                            'array' => [
                                'phone' => trans(\Locales::getNamespace() . '/datatables.phone'),
                                'mobile' => trans(\Locales::getNamespace() . '/datatables.mobile'),
                                'email' => trans(\Locales::getNamespace() . '/datatables.email'),
                            ],
                        ],
                    ],
                    [
                        'selector' => 'locales.name AS language',
                        'id' => 'language',
                        'name' => trans(\Locales::getNamespace() . '/datatables.language'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'selector' => 'room_translations.name AS room',
                        'id' => 'room',
                        'name' => trans(\Locales::getNamespace() . '/datatables.room'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'selector' => 'view_translations.name AS view',
                        'id' => 'view',
                        'name' => trans(\Locales::getNamespace() . '/datatables.view'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'selector' => 'furniture_translations.name AS furniture',
                        'id' => 'furniture',
                        'name' => trans(\Locales::getNamespace() . '/datatables.furniture'),
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                ],
                'orderByColumn' => 'number',
                'orderByRaw' => 'CONCAT(SUBSTR(apartments.number, 1, LOCATE("-", apartments.number) - 1), LPAD(SUBSTR(apartments.number, LOCATE("-", apartments.number) + 1), 3, "0"))',
                'order' => 'asc',
                'footer' => [
                    'class' => 'bg-info',
                    'columns' => [
                        [ // apartment
                            'data' => '',
                        ],
                        [ // MM Years
                            'data' => '',
                        ],
                        [ // amount
                            'data' => '<span></span>',
                            'sum' => true,
                        ],
                        [ // owner
                            'data' => '',
                        ],
                        [ // language
                            'data' => '',
                        ],
                        [ // room
                            'data' => '',
                        ],
                        [ // view
                            'data' => '',
                        ],
                        [ // furniture
                            'data' => '',
                        ],
                    ],
                ],
            ],
        ];

        $this->multiselect = [
            'projects' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'buildings' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'apartments' => [
                'id' => 'id',
                'name' => 'number',
            ],
            'rooms' => [
                'id' => 'id',
                'name' => 'room',
            ],
            'furniture' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'views' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'years' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'languages' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'type' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'group' => [
                'id' => 'id',
                'name' => 'name',
            ],
        ];
    }

    public function index(DataTable $datatable, Request $request, Apartment $apartments)
    {
        $datatable->setOption('skipLoading', true, $this->route);
        $datatable->setup($apartments, $this->route, $this->datatables[$this->route]);

        $datatables = $datatable->getTables();

        $this->multiselect['projects']['options'] = Project::withTranslation()->select('project_translations.name', 'projects.id')->leftJoin('project_translations', 'project_translations.project_id', '=', 'projects.id')->where('project_translations.locale', \Locales::getCurrent())->orderBy('project_translations.name')->get()->toArray();
        $this->multiselect['projects']['selected'] = '';

        $this->multiselect['buildings']['options'] = Building::withTranslation()->select('building_translations.name', 'buildings.id')->leftJoin('building_translations', 'building_translations.building_id', '=', 'buildings.id')->where('building_translations.locale', \Locales::getCurrent())->orderBy('building_translations.name')->get()->toArray();
        $this->multiselect['buildings']['selected'] = '';

        $this->multiselect['apartments']['options'] = Apartment::distinct()->select('apartments.id', 'apartments.number')->join('ownership', 'ownership.apartment_id', '=', 'apartments.id')->whereNull('ownership.deleted_at')->orderBy('number')->get()->toArray();
        $this->multiselect['apartments']['selected'] = '';

        $this->multiselect['rooms']['options'] = Room::withTranslation()->selectRaw('CONCAT(room_translations.name, " (", room_translations.description, ")") as room, rooms.id')->leftJoin('room_translations', 'room_translations.room_id', '=', 'rooms.id')->where('room_translations.locale', \Locales::getCurrent())->orderBy('room_translations.name')->get()->toArray();
        $this->multiselect['rooms']['selected'] = '';

        $this->multiselect['furniture']['options'] = Furniture::withTranslation()->select('furniture_translations.name', 'furniture.id')->leftJoin('furniture_translations', 'furniture_translations.furniture_id', '=', 'furniture.id')->where('furniture_translations.locale', \Locales::getCurrent())->orderBy('furniture_translations.name')->get()->toArray();
        $this->multiselect['furniture']['selected'] = '';

        $this->multiselect['views']['options'] = View::withTranslation()->select('view_translations.name', 'views.id')->leftJoin('view_translations', 'view_translations.view_id', '=', 'views.id')->where('view_translations.locale', \Locales::getCurrent())->orderBy('view_translations.name')->get()->toArray();
        $this->multiselect['views']['selected'] = '';

        $type = [];
        foreach (trans(\Locales::getNamespace() . '/multiselect.reportMmFeesType') as $key => $value) {
            $type[] = ['id' => $key, 'name' => $value];
        }
        $this->multiselect['type']['options'] = $type;
        $this->multiselect['type']['selected'] = 'due';

        $group = [];
        foreach (trans(\Locales::getNamespace() . '/multiselect.reportGroupBy') as $key => $value) {
            $group[] = ['id' => $key, 'name' => $value];
        }
        $this->multiselect['group']['options'] = $group;
        $this->multiselect['group']['selected'] = '';

        $years = [];
        foreach (Year::orderBy('year', 'desc')->get() as $key => $value) {
            $years[$key]['id'] = $value['year'];
            $years[$key]['name'] = $value['year'];
        }

        $this->multiselect['years']['options'] = $years;
        $this->multiselect['years']['selected'] = date('Y');

        $languages = [];
        foreach (\Locales::getPublicDomain()->locales->toArray() as $key => $value) {
            $languages[$key]['id'] = $value['id'];
            $languages[$key]['name'] = $value['name'];
        }

        $this->multiselect['languages']['options'] = $languages;
        $this->multiselect['languages']['selected'] = '';

        $multiselect = $this->multiselect;

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($datatables);
        } else {
            return view(\Locales::getNamespace() . '.' . $this->route . '.mm-fees', compact('datatables', 'multiselect'));
        }
    }

    public function getBuildings(Request $request, $projects = null)
    {
        $projects = $request->input('projects', $projects);

        $buildings = Building::withTranslation()->select('building_translations.name', 'buildings.id')->leftJoin('building_translations', 'building_translations.building_id', '=', 'buildings.id')->where('building_translations.locale', \Locales::getCurrent())->whereIn('buildings.project_id', $projects)->orderBy('building_translations.name')->get()->pluck('id', 'name')->toArray();

        $apartments = $this->getApartments($request, false, $request->input('projects'), array_values($buildings));

        return response()->json([
            'success' => true,
            'buildings' => $buildings,
            'apartments' => $apartments,
        ]);
    }

    public function getApartments(Request $request, $json = true, $projects = null, $buildings = null)
    {
        $projects = $request->input('projects', $projects);
        $buildings = $request->input('buildings', $buildings);

        $apartments = Apartment::distinct()->select('apartments.id', 'apartments.number')->join('ownership', 'ownership.apartment_id', '=', 'apartments.id')->whereNull('ownership.deleted_at');

        if ($projects) {
            $apartments = $apartments->whereIn('apartments.project_id', $projects);
        }

        if ($buildings) {
            $apartments = $apartments->whereIn('apartments.building_id', $buildings);
        }

        $apartments = $apartments->orderBy('apartments.number')->get()->pluck('id', 'number')->toArray();

        if ($json) {
            return response()->json([
                'success' => true,
                'apartments' => $apartments,
            ]);
        } else {
            return $apartments;
        }
    }

    public function generate(DataTable $datatable, Request $request, Apartment $apartments)
    {
        set_time_limit(0);

        $year = Year::where('year', $request->input('year'))->firstOrFail();

        $apartments = $apartments->with(['buildingMM' => function ($query) use ($year) {
            // $query->where('building_mm.year_id', $year->id); // I need all BuildingMM years, not just the selected one!!!
        }, 'mmFeesPayments', 'contracts' => function ($query) use ($year) {
            if ($year->year < date('Y')) {
                $query->withTrashed();
            }
        }, 'contracts.contractYears' => function ($query) use ($year) {
            $query->where('contract_years.year', $year->year);

            if ($year->year < date('Y')) {
                $query->withTrashed();
            }
        }, 'contracts.rentalContract' => function ($query) {
            $query->withTrashed();
        }, 'rooms' => function ($query) {
            $query->withTranslation();
        }])->join('ownership', 'ownership.apartment_id', '=', 'apartments.id')->join('owners', 'owners.id', '=', 'ownership.owner_id')->whereYear('ownership.created_at', '<=', $year->year)->where(function ($query) use ($year) {
            $query->whereYear('ownership.deleted_at', '>=', $year->year)->orWhereNull('ownership.deleted_at');
        })/*->whereNull('ownership.deleted_at')->whereNull('owners.deleted_at')*/->groupBy('apartments.id');

        if ($request->input('projects')) {
            $apartments = $apartments->whereIn('apartments.project_id', $request->input('projects'));
        }

        if ($request->input('buildings')) {
            $apartments = $apartments->whereIn('apartments.building_id', $request->input('buildings'));
        }

        if ($request->input('apartments')) {
            $apartments = $apartments->whereIn('apartments.id', $request->input('apartments'));
        }

        if ($request->input('rooms')) {
            $apartments = $apartments->whereIn('apartments.room_id', $request->input('rooms'));
        }

        if ($request->input('furniture')) {
            $apartments = $apartments->whereIn('apartments.furniture_id', $request->input('furniture'));
        }

        if ($request->input('views')) {
            $apartments = $apartments->whereIn('apartments.view_id', $request->input('views'));
        }

        if ($request->input('languages')) {
            $apartments = $apartments->whereIn('owners.locale_id', $request->input('languages'));
        }

        $selectors = ['apartments.id', 'apartments.mm_tax_formula', 'apartments.apartment_area', 'apartments.common_area', 'apartments.balcony_area', 'apartments.extra_balcony_area', 'apartments.total_area', 'apartments.building_id', 'owners.phone', 'owners.mobile', 'owners.email', 'project_translations.slug AS projectSlug', 'room_translations.slug AS roomSlug', 'view_translations.slug AS viewSlug'];
        if ($request->input('group') === 'project') {
            $selectors = array_merge($selectors, ['project_translations.name AS project', 'apartments.project_id AS project_id1']);
        } elseif ($request->input('group') === 'building') {
            $selectors = array_merge($selectors, ['building_translations.name AS building', 'apartments.building_id AS building_id1']);
        }

        if ($request->input('generate')) {
            $datatable->setOption('skipInfo', true, $this->route);
        }

        $datatable->setOption('selectors', $selectors, $this->route);

        $datatable->setup($apartments, $this->route, $this->datatables[$this->route], true);
        $datatables = $datatable->getTables();

        if ($request->input('generate')) {
            $method = 'generate' . ucfirst($request->input('generate'));

            return response()->json([
                'success' => true,
                'uuid' => $this->$method($datatables[$this->route]['data'], $request->input('projects'), $request->input('buildings'), $request->input('group')),
            ]);
        } else {
            $enable = ['button-reset-report', 'button-download-report'];

            return response()->json($datatables + [
                'success' => true,
                'showTable' => true,
                'enable' => $enable,
            ]);
        }
    }

    public function generateExcel($rawData, $projects, $buildings, $group)
    {
        $columns = ['number', 'mmYears', 'amount', 'owner', 'language', 'room', 'furniture', 'view', 'phone', 'mobile', 'email'];

        $merge = $group ? array_merge($columns, ['project', 'project_id1', 'building', 'building_id1']) : $columns;
        foreach ($rawData as $key => $value) {
            $data[$key] = collect(array_replace(array_flip($columns), $rawData[$key]->toArray()))->only($merge)->map(function ($item, $key) {
                return $key === 'amount' ? (float)str_replace(',', '', $item) : $item;
            })->toArray();
        }

        $uuid = (string)\Uuid::generate();
        $filename = 'mm-fees-' . $uuid;

        $firstColumn = 'A';
        $firstRow = 1;
        $firstCell = $firstColumn . $firstRow;

        if (!isset($data)) {
            $data = [];
        }

        array_unshift($data, [
            'number' => trans(\Locales::getNamespace() . '/datatables.apartment'),
            'mmYears' => trans(\Locales::getNamespace() . '/datatables.mmYears'),
            'amount' => trans(\Locales::getNamespace() . '/datatables.amount'),
            'owner' => trans(\Locales::getNamespace() . '/datatables.owner'),
            'language' => trans(\Locales::getNamespace() . '/datatables.language'),
            'room' => trans(\Locales::getNamespace() . '/datatables.room'),
            'furniture' => trans(\Locales::getNamespace() . '/datatables.furniture'),
            'view' => trans(\Locales::getNamespace() . '/datatables.view'),
            'phone' => trans(\Locales::getNamespace() . '/datatables.phone'),
            'mobile' => trans(\Locales::getNamespace() . '/datatables.mobile'),
            'email' => trans(\Locales::getNamespace() . '/datatables.email'),
        ]);

        $data = collect($data);

        if ($group) {
            $content = $data->map(function ($item, $key) use ($group) {
                if (isset($item[$group])) {
                    unset($item[$group]);
                }

                if (isset($item[$group . '_id1'])) {
                    unset($item[$group . '_id1']);
                }

                return $item;
            });
        } else {
            $content = $data;
        }

        $columnWidth = [
            'A' => -1,
            'B' => -1,
            'C' => -1,
            'D' => -1,
            'E' => -1,
            'F' => -1,
            'G' => -1,
            'H' => -1,
            'I' => -1,
            'J' => -1,
            'K' => -1,
        ];

        foreach ($content as $id => $row) {
            $col = $firstColumn;
            foreach ($row as $value) {
                $width = mb_strlen($value);
                if ($width > $columnWidth[$col]) {
                    $columnWidth[$col] = $width;
                }

                $col++;
            }
        }

        \Excel::create('Report', function ($excel) use ($data, $content, $projects, $buildings, $group, $columnWidth, $firstColumn, $firstRow, $firstCell) {

            $reportName = 'Report';
            if ($group) {
                $reportName = 'All';
            }

            $excel->sheet($reportName, function ($sheet) use ($content, $columnWidth, $firstColumn, $firstRow, $firstCell) {

                $sheet->setColumnFormat([
                    'A' => '@',
                    'B' => '@',
                    'C' => '#,##0.00',
                    'D' => '@',
                    'E' => '@',
                    'F' => '@',
                    'G' => '@',
                    'H' => '@',
                    'I' => '@',
                    'J' => '@',
                    'K' => '@',
                ]);

                $sheet->fromArray($content, null, $firstCell, false, false);

                $lastRow = $sheet->getHighestDataRow() + 1; // plus footer
                $lastColumn = $sheet->getHighestDataColumn();
                $lastCell = $lastColumn . $lastRow;

                $sheet->setShowGridlines(false);

                $sheet->getStyle($firstCell . ':' . $lastCell)->applyFromArray([
                    'font' => [
                        'size' => 12,
                    ],
                    'alignment' => [
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allborders' => [
                            'style' => \PHPExcel_Style_Border::BORDER_THIN,
                            'color' => [
                                'rgb' => 'DDDDDD',
                            ]
                        ]
                    ]
                ]);

                $amount = chr(ord($firstColumn) + array_search('amount', array_keys($content[0])));
                $sheet->getStyle($amount . ($firstRow + 1) . ':' . $amount . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                    ]
                ]);

                $sheet->getStyle($firstCell . ':' . $lastCell)->getAlignment()->setWrapText(true);

                $sheet->getStyle($firstCell . ':' . $lastColumn . $firstRow)->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'style' => \PHPExcel_Style_Border::BORDER_THICK,
                            'color' => [
                                'rgb' => 'DDDDDD',
                            ]
                        ]
                    ]
                ]);

                $sheet->getStyle($firstColumn . ($firstRow + 1) . ':' . $lastColumn . ($firstRow + 1))->applyFromArray([
                    'borders' => [
                        'top' => [
                            'style' => \PHPExcel_Style_Border::BORDER_THICK,
                            'color' => [
                                'rgb' => 'DDDDDD',
                            ]
                        ]
                    ]
                ]);

                // Footer

                $sheet->getStyle($firstColumn . $lastRow . ':' . $lastColumn . $lastRow)->applyFromArray([
                    'borders' => [
                        'top' => [
                            'style' => \PHPExcel_Style_Border::BORDER_THICK,
                            'color' => [
                                'rgb' => 'DDDDDD',
                            ]
                        ]
                    ]
                ]);

                $sheet->getStyle($firstColumn . ($lastRow - 1) . ':' . $lastColumn . ($lastRow - 1))->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'style' => \PHPExcel_Style_Border::BORDER_THICK,
                            'color' => [
                                'rgb' => 'DDDDDD',
                            ]
                        ]
                    ]
                ]);

                $sheet->freezePane('A' . ($firstRow + 1));

                $sheet->setAutoFilter($firstCell . ':' . $lastColumn . $firstRow);

                /* Zebra styles
                foreach ($sheet->getRowIterator() as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                    foreach ($cellIterator as $key => $cell) {
                        if ($cell->getRow() % 2 == 0) {
                            $sheet->cell($cell->getCoordinate() . ':' . $lastColumn . $cell->getRow(), function ($cells) {
                                $cells->setBackground('#F9F9F9');
                            });
                        }

                        break;
                    }
                }*/

                /* Set font color for specific column
                foreach ($sheet->getColumnIterator($statusColumn) as $column) {
                    $cellIterator = $column->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                    foreach ($cellIterator as $key => $cell) {
                        if ($cell->getValue() == trans(\Locales::getNamespace() . '/datatables.statusSold')) {
                            $sheet->cell($cell->getCoordinate(), function ($cell) {
                                $cell->setFontColor('#ff0000');
                            });
                        }
                    }
                }*/

                $sheet->cells($firstCell . ':' . $lastColumn . $firstRow, function ($cells) {
                    $cells->setFontWeight('bold');
                    $cells->setBackground('#D9EDF7');
                });

                // bold footer

                $sheet->cells($firstColumn . $lastRow . ':' . $lastCell, function ($cells) {
                    $cells->setFontWeight('bold');
                    $cells->setBackground('#D9EDF7');
                });

                /* FORMULAS START */

                $sheet->cell('C' . $lastRow, function ($cell) use ($firstRow, $lastRow) {
                    $cell->setValue('=SUM(C' . ($firstRow + 1) . ':C' . ($lastRow - 1) . ')');
                });

                /* FORMULAS END */

                $sheet->setAutoSize(false);
                for ($col = 'A'; $col <= $lastColumn; $col++) {
                    $sheet->getColumnDimension($col)->setWidth($columnWidth[$col] + 8.43);
                }

                // $sheet->getDefaultRowDimension()->setRowHeight(-1); // auto row height but it doesn't work on merged cells or cells with new lines in content

                // $sheet->setSelectedCell('A1'); // doesn;t work with frozen panes
            });

            if ($group) {
                if ($group === 'project') {
                    $items = $projects;
                    if (!$items) {
                        $items = [1, 2];
                    }
                } elseif ($group === 'building') {
                    $items = $buildings;
                    if (!$items) {
                        $items = [1, 2, 3, 4, 5, 6, 7, 8];
                    }
                }

                foreach ($items as $item) {
                    $content = $data->filter(function ($value, $key) use ($group, $item) {
                        if (isset($value[$group . '_id1'])) {
                            return $value[$group . '_id1'] == $item;
                        } else {
                            return true;
                        }
                    });

                    if (isset($content->last()[$group])) {
                        $reportName = $content->last()[$group];

                        $content->transform(function ($item, $key) use ($group) {
                            if (isset($item[$group])) {
                                unset($item[$group]);
                            }

                            if (isset($item[$group . '_id1'])) {
                                unset($item[$group . '_id1']);
                            }

                            return $item;
                        });

                        $excel->sheet($reportName, function ($sheet) use ($content, $columnWidth, $firstColumn, $firstRow, $firstCell) {

                            $sheet->setColumnFormat([
                                'A' => '@',
                                'B' => '@',
                                'C' => '#,##0.00',
                                'D' => '@',
                                'E' => '@',
                                'F' => '@',
                                'G' => '@',
                                'H' => '@',
                                'I' => '@',
                                'J' => '@',
                                'K' => '@',
                            ]);

                            $sheet->fromArray($content, null, $firstCell, false, false);

                            $lastRow = $sheet->getHighestDataRow() + 1; // plus footer
                            $lastColumn = $sheet->getHighestDataColumn();
                            $lastCell = $lastColumn . $lastRow;

                            $sheet->setShowGridlines(false);

                            $sheet->getStyle($firstCell . ':' . $lastCell)->applyFromArray([
                                'font' => [
                                    'size' => 12,
                                ],
                                'alignment' => [
                                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                    'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                                ],
                                'borders' => [
                                    'allborders' => [
                                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                                        'color' => [
                                            'rgb' => 'DDDDDD',
                                        ]
                                    ]
                                ]
                            ]);

                            $amount = chr(ord($firstColumn) + array_search('amount', array_keys($content[0])));
                            $sheet->getStyle($amount . ($firstRow + 1) . ':' . $amount . $lastRow)->applyFromArray([
                                'alignment' => [
                                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                                ]
                            ]);

                            $sheet->getStyle($firstCell . ':' . $lastCell)->getAlignment()->setWrapText(true);

                            $sheet->getStyle($firstCell . ':' . $lastColumn . $firstRow)->applyFromArray([
                                'borders' => [
                                    'bottom' => [
                                        'style' => \PHPExcel_Style_Border::BORDER_THICK,
                                        'color' => [
                                            'rgb' => 'DDDDDD',
                                        ]
                                    ]
                                ]
                            ]);

                            $sheet->getStyle($firstColumn . ($firstRow + 1) . ':' . $lastColumn . ($firstRow + 1))->applyFromArray([
                                'borders' => [
                                    'top' => [
                                        'style' => \PHPExcel_Style_Border::BORDER_THICK,
                                        'color' => [
                                            'rgb' => 'DDDDDD',
                                        ]
                                    ]
                                ]
                            ]);

                            // Footer

                            $sheet->getStyle($firstColumn . $lastRow . ':' . $lastColumn . $lastRow)->applyFromArray([
                                'borders' => [
                                    'top' => [
                                        'style' => \PHPExcel_Style_Border::BORDER_THICK,
                                        'color' => [
                                            'rgb' => 'DDDDDD',
                                        ]
                                    ]
                                ]
                            ]);

                            $sheet->getStyle($firstColumn . ($lastRow - 1) . ':' . $lastColumn . ($lastRow - 1))->applyFromArray([
                                'borders' => [
                                    'bottom' => [
                                        'style' => \PHPExcel_Style_Border::BORDER_THICK,
                                        'color' => [
                                            'rgb' => 'DDDDDD',
                                        ]
                                    ]
                                ]
                            ]);

                            $sheet->freezePane('A' . ($firstRow + 1));

                            $sheet->setAutoFilter($firstCell . ':' . $lastColumn . $firstRow);

                            /* Zebra styles
                            foreach ($sheet->getRowIterator() as $row) {
                                $cellIterator = $row->getCellIterator();
                                $cellIterator->setIterateOnlyExistingCells(false);
                                foreach ($cellIterator as $key => $cell) {
                                    if ($cell->getRow() % 2 == 0) {
                                        $sheet->cell($cell->getCoordinate() . ':' . $lastColumn . $cell->getRow(), function ($cells) {
                                            $cells->setBackground('#F9F9F9');
                                        });
                                    }

                                    break;
                                }
                            }*/

                            /* Set font color for specific column
                            foreach ($sheet->getColumnIterator($statusColumn) as $column) {
                                $cellIterator = $column->getCellIterator();
                                $cellIterator->setIterateOnlyExistingCells(false);
                                foreach ($cellIterator as $key => $cell) {
                                    if ($cell->getValue() == trans(\Locales::getNamespace() . '/datatables.statusSold')) {
                                        $sheet->cell($cell->getCoordinate(), function ($cell) {
                                            $cell->setFontColor('#ff0000');
                                        });
                                    }
                                }
                            }*/

                            $sheet->cells($firstCell . ':' . $lastColumn . $firstRow, function ($cells) {
                                $cells->setFontWeight('bold');
                                $cells->setBackground('#D9EDF7');
                            });

                            // bold footer

                            $sheet->cells($firstColumn . $lastRow . ':' . $lastCell, function ($cells) {
                                $cells->setFontWeight('bold');
                                $cells->setBackground('#D9EDF7');
                            });

                            /* FORMULAS START */

                            $sheet->cell('C' . $lastRow, function ($cell) use ($firstRow, $lastRow) {
                                $cell->setValue('=SUM(C' . ($firstRow + 1) . ':C' . ($lastRow - 1) . ')');
                            });

                            /* FORMULAS END */

                            $sheet->setAutoSize(false);
                            for ($col = 'A'; $col <= $lastColumn; $col++) {
                                $sheet->getColumnDimension($col)->setWidth($columnWidth[$col] + 8.43);
                            }

                            // $sheet->getDefaultRowDimension()->setRowHeight(-1); // auto row height but it doesn't work on merged cells or cells with new lines in content

                            // $sheet->setSelectedCell('A1'); // doesn;t work with frozen panes
                        });
                    }
                }
            }
        })->setFilename($filename)->store('xlsx');

        return $uuid;
    }

    public function download(Request $request)
    {
        return response()->download(storage_path('app/reports/mm-fees-' . $request->input('uuid') . '.xlsx'))->deleteFileAfterSend(true);
    }
}
