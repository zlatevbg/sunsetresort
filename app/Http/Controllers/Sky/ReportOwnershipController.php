<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Models\Sky\Project;
use App\Models\Sky\Building;
use App\Models\Sky\Apartment;
use App\Models\Sky\Owner;
use App\Models\Sky\Ownership;
use App\Services\DataTable;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportOwnershipController extends Controller
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
                'url' => \Locales::route('reports/ownership'),
                'class' => 'table-checkbox table-striped table-bordered table-hover table-report',
                'joins' => [
                    [
                        'table' => 'owners',
                        'localColumn' => 'owners.id',
                        'constrain' => '=',
                        'foreignColumn' => 'ownership.owner_id',
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
                ],
                'columns' => [
                    [
                        'selector' => 'apartments.number AS apartment',
                        'id' => 'apartment',
                        'name' => trans(\Locales::getNamespace() . '/datatables.apartment'),
                        'class' => 'vertical-center',
                    ],
                    [
                        'selectRaw' => 'CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) AS owner',
                        'id' => 'owner',
                        'name' => trans(\Locales::getNamespace() . '/datatables.owner'),
                        'class' => 'vertical-center',
                    ],
                    [
                        'selector' => 'ownership.created_at',
                        'id' => 'created_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.dateFrom'),
                        'class' => 'vertical-center',
                        'data' => [
                            'type' => 'sort',
                            'id' => 'created_at',
                            'date' => 'YYmmdd',
                        ],
                    ],
                    [
                        'selector' => 'ownership.deleted_at',
                        'id' => 'deleted_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.dateTo'),
                        'class' => 'vertical-center',
                        'data' => [
                            'type' => 'sort',
                            'id' => 'deleted_at',
                            'date' => 'YYmmdd',
                        ],
                    ],
                ],
                'orderByColumn' => 'apartment',
                'order' => 'asc',
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
            'group' => [
                'id' => 'id',
                'name' => 'name',
            ],
        ];
    }

    public function index(DataTable $datatable, Request $request, Ownership $ownership)
    {
        $datatable->setOption('skipLoading', true, $this->route);
        $datatable->setup($ownership->withTrashed()->leftJoin('apartments', function ($join) {
             $join->on('apartments.id', '=', 'ownership.apartment_id');
        }), $this->route, $this->datatables[$this->route]);

        $datatables = $datatable->getTables();

        $this->multiselect['projects']['options'] = Project::withTranslation()->select('project_translations.name', 'projects.id')->leftJoin('project_translations', 'project_translations.project_id', '=', 'projects.id')->where('project_translations.locale', \Locales::getCurrent())->orderBy('project_translations.name')->get()->toArray();
        $this->multiselect['projects']['selected'] = '';

        $this->multiselect['buildings']['options'] = Building::withTranslation()->select('building_translations.name', 'buildings.id')->leftJoin('building_translations', 'building_translations.building_id', '=', 'buildings.id')->where('building_translations.locale', \Locales::getCurrent())->orderBy('building_translations.name')->get()->toArray();
        $this->multiselect['buildings']['selected'] = '';

        $this->multiselect['apartments']['options'] = Apartment::distinct()->select('apartments.id', 'apartments.number')->join('ownership', 'ownership.apartment_id', '=', 'apartments.id')->whereNull('ownership.deleted_at')->orderBy('number')->get()->toArray();
        $this->multiselect['apartments']['selected'] = '';

        $group = [];
        foreach (trans(\Locales::getNamespace() . '/multiselect.reportGroupByOwnership') as $key => $value) {
            $group[] = ['id' => $key, 'name' => $value];
        }
        $this->multiselect['group']['options'] = $group;
        $this->multiselect['group']['selected'] = '';

        $multiselect = $this->multiselect;

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($datatables);
        } else {
            return view(\Locales::getNamespace() . '.' . $this->route . '.ownership', compact('datatables', 'multiselect'));
        }
    }

    public function getBuildings(Request $request, $projects = null)
    {
        $projects = $request->input('projects', $projects);

        $buildings = Building::withTranslation()->select('building_translations.name', 'buildings.id')->leftJoin('building_translations', 'building_translations.building_id', '=', 'buildings.id')->where('building_translations.locale', \Locales::getCurrent());

        if ($projects) {
            $buildings = $buildings->whereIn('buildings.project_id', $projects);
        }

        $buildings = $buildings->orderBy('building_translations.name')->get()->pluck('id', 'name')->toArray();

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

    public function generate(DataTable $datatable, Request $request, Ownership $ownership)
    {
        set_time_limit(0);

        $ownership = $ownership->leftJoin('apartments', function ($join) {
             $join->on('apartments.id', '=', 'ownership.apartment_id');
        });

        if ($request->input('projects')) {
            $ownership = $ownership->whereIn('apartments.project_id', $request->input('projects'));
        }

        if ($request->input('buildings')) {
            $ownership = $ownership->whereIn('apartments.building_id', $request->input('buildings'));
        }

        if ($request->input('apartments')) {
            $ownership = $ownership->whereIn('apartments.id', $request->input('apartments'));
        }

        if ($request->input('dfrom')) {
            $ownership = $ownership->where('ownership.created_at', '>=', Carbon::parse($request->input('dfrom'))->toDateTimeString());
        }

        if ($request->input('dto')) {
            $ownership = $ownership->where('ownership.created_at', '<=', Carbon::parse($request->input('dto'))->toDateTimeString());
        }

        $selectors = [];
        if ($request->input('group') === 'project') {
            $selectors = array_merge($selectors, ['project_translations.name AS project', 'apartments.project_id AS project_id']);
        } elseif ($request->input('group') === 'building') {
            $selectors = array_merge($selectors, ['building_translations.name AS building', 'apartments.building_id AS building_id']);
        } elseif ($request->input('group') === 'owner') {
            $this->datatables[$this->route]['columns'][0]['selectRaw'] = 'GROUP_CONCAT(DISTINCT apartments.number ORDER BY apartments.number SEPARATOR ", ") as apartment';
            $ownership = $ownership->groupBy('ownership.owner_id');
        }

        if ($request->input('generate')) {
            $datatable->setOption('skipInfo', true, $this->route);
        }

        $datatable->setOption('selectors', $selectors, $this->route);

        $datatable->setup($ownership->withTrashed(), $this->route, $this->datatables[$this->route], true);
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
        $columns = ['apartment', 'owner', 'created_at', 'deleted_at'];

        foreach ($rawData as $key => $value) {
            $data[$key] = collect(array_replace(array_flip($columns), $rawData[$key]))->only(array_merge($columns, ['project', 'project_id', 'building', 'building_id']))->toArray();

            foreach ($data[$key] as $k => $v) {
                if (is_array($v)) {
                    $data[$key][$k] = $v['display'];
                }
            }
        }

        $uuid = (string)\Uuid::generate();
        $filename = 'ownership-' . $uuid;

        $firstColumn = 'A';
        $firstRow = 1;
        $firstCell = $firstColumn . $firstRow;

        array_unshift($data, [
            'apartment' => trans(\Locales::getNamespace() . '/datatables.apartment'),
            'owner' => trans(\Locales::getNamespace() . '/datatables.owner'),
            'created_at' => trans(\Locales::getNamespace() . '/datatables.dateFrom'),
            'deleted_at' => trans(\Locales::getNamespace() . '/datatables.dateTo'),
        ]);

        $data = collect($data);

        if ($group && in_array($group, ['project', 'building'])) {
            $content = $data->map(function ($item, $key) use ($group) {
                if (isset($item[$group])) {
                    unset($item[$group]);
                }

                if (isset($item[$group . '_id'])) {
                    unset($item[$group . '_id']);
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
        ];

        foreach ($content as $id => $row) {
            $col = $firstColumn;
            foreach ($row as $value) {
                $width = mb_strlen($value);
                if ($width > 100) {
                    $width = 100;
                }

                if ($width > $columnWidth[$col]) {
                    $columnWidth[$col] = $width;
                }

                $col++;
            }
        }

        \Excel::create('Report', function ($excel) use ($data, $content, $projects, $buildings, $group, $columnWidth, $firstColumn, $firstRow, $firstCell) {

            $reportName = 'Report';
            if ($group && in_array($group, ['project', 'building'])) {
                $reportName = 'All';
            }

            $excel->sheet($reportName, function ($sheet) use ($content, $columnWidth, $firstColumn, $firstRow, $firstCell) {

                $sheet->setColumnFormat([
                    'A' => '@',
                    'B' => '@',
                    'C' => '@',
                    'D' => '@',
                ]);

                $sheet->fromArray($content, null, $firstCell, false, false);

                $lastRow = $sheet->getHighestDataRow();
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

                $sheet->setAutoSize(false);
                for ($col = 'A'; $col <= $lastColumn; $col++) {
                    $sheet->getColumnDimension($col)->setWidth($columnWidth[$col] + 8.43);
                }

                // $sheet->getDefaultRowDimension()->setRowHeight(-1); // auto row height but it doesn't work on merged cells or cells with new lines in content

                // $sheet->setSelectedCell('A1'); // doesn;t work with frozen panes
            });

            if ($group && in_array($group, ['project', 'building'])) {
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
                        if (isset($value[$group . '_id'])) {
                            return $value[$group . '_id'] == $item;
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

                            if (isset($item[$group . '_id'])) {
                                unset($item[$group . '_id']);
                            }

                            return $item;
                        });

                        $excel->sheet($reportName, function ($sheet) use ($content, $columnWidth, $firstColumn, $firstRow, $firstCell) {

                            $sheet->setColumnFormat([
                                'A' => '@',
                                'B' => '@',
                                'C' => '@',
                                'D' => '@',
                            ]);

                            $sheet->fromArray($content, null, $firstCell, false, false);

                            $lastRow = $sheet->getHighestDataRow();
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
        return response()->download(storage_path('app/reports/ownership-' . $request->input('uuid') . '.xlsx'))->deleteFileAfterSend(true);
    }
}
