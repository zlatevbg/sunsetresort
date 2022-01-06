<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Models\Sky\Project;
use App\Models\Sky\Building;
use App\Models\Sky\Apartment;
use App\Models\Sky\Owner;
use App\Services\DataTable;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportOwnersController extends Controller
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
                'url' => \Locales::route('reports/owners'),
                'class' => 'table-checkbox table-striped table-bordered table-hover table-report',
                'joins' => [
                    [
                        'table' => 'apartments',
                        'localColumn' => 'apartments.id',
                        'constrain' => '=',
                        'foreignColumn' => 'ownership.apartment_id',
                        'whereNull' => 'apartments.deleted_at',
                    ],
                    [
                        'table' => 'locales',
                        'localColumn' => 'locales.id',
                        'constrain' => '=',
                        'foreignColumn' => 'owners.locale_id',
                    ],
                    [
                        'table' => 'country_translations',
                        'localColumn' => 'country_translations.country_id',
                        'constrain' => '=',
                        'foreignColumn' => 'owners.country_id',
                        'whereColumn' => 'country_translations.locale',
                        'whereConstrain' => '=',
                        'whereValue' => \Locales::getCurrent(),
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
                        'selectRaw' => ' CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) AS owner, GROUP_CONCAT(apartments.number SEPARATOR ", ") AS apartments, REPLACE(TRIM(", " FROM CONCAT(COALESCE(owners.city, ""), ", ", COALESCE(owners.postcode, ""), ", ", COALESCE(owners.address1, ""), ", ", COALESCE(owners.address2, ""))), ", ,", ",") AS address',
                        'id' => 'owner',
                        'name' => trans(\Locales::getNamespace() . '/datatables.owner'),
                        'class' => 'vertical-center',
                        'info' => [
                            'array' => [
                                'apartments' => trans(\Locales::getNamespace() . '/datatables.apartments'),
                                'is_subscribed' => trans(\Locales::getNamespace() . '/datatables.newsletterSubscription'),
                                'outstanding_bills' => trans(\Locales::getNamespace() . '/datatables.outstandingBills'),
                                'letting_offer' => trans(\Locales::getNamespace() . '/datatables.lettingOffer'),
                                'srioc' => trans(\Locales::getNamespace() . '/datatables.srioc'),
                                'address' => trans(\Locales::getNamespace() . '/datatables.address'),
                                'comments' => trans(\Locales::getNamespace() . '/datatables.comments'),
                            ],
                        ],
                    ],
                    [
                        'selector' => 'locales.name as language',
                        'id' => 'language',
                        'name' => trans(\Locales::getNamespace() . '/datatables.language'),
                        'class' => 'vertical-center',
                    ],
                    [
                        'selector' => 'owners.phone',
                        'id' => 'phone',
                        'name' => trans(\Locales::getNamespace() . '/datatables.phone'),
                        'class' => 'vertical-center',
                        'order' => false,
                    ],
                    [
                        'selector' => 'owners.mobile',
                        'id' => 'mobile',
                        'name' => trans(\Locales::getNamespace() . '/datatables.mobile'),
                        'class' => 'vertical-center',
                        'order' => false,
                    ],
                    [
                        'selector' => 'owners.email',
                        'id' => 'email',
                        'name' => trans(\Locales::getNamespace() . '/datatables.email'),
                        'class' => 'vertical-center',
                        'order' => false,
                    ],
                    [
                        'selector' => 'country_translations.name as country',
                        'id' => 'country',
                        'name' => trans(\Locales::getNamespace() . '/datatables.country'),
                        'class' => 'vertical-center',
                    ],
                ],
                'orderByColumn' => 'owner',
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
            'languages' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'group' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'subscribed' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'ob' => [
                'id' => 'id',
                'name' => 'name',
            ],
            'srioc' => [
                'id' => 'id',
                'name' => 'name',
            ],
        ];
    }

    public function index(DataTable $datatable, Request $request, Owner $owners)
    {
        $datatable->setOption('skipLoading', true, $this->route);
        $datatable->setup($owners, $this->route, $this->datatables[$this->route]);

        $datatables = $datatable->getTables();

        $this->multiselect['projects']['options'] = Project::withTranslation()->select('project_translations.name', 'projects.id')->leftJoin('project_translations', 'project_translations.project_id', '=', 'projects.id')->where('project_translations.locale', \Locales::getCurrent())->orderBy('project_translations.name')->get()->toArray();
        $this->multiselect['projects']['selected'] = '';

        $this->multiselect['buildings']['options'] = Building::withTranslation()->select('building_translations.name', 'buildings.id')->leftJoin('building_translations', 'building_translations.building_id', '=', 'buildings.id')->where('building_translations.locale', \Locales::getCurrent())->orderBy('building_translations.name')->get()->toArray();
        $this->multiselect['buildings']['selected'] = '';

        $this->multiselect['apartments']['options'] = Apartment::distinct()->select('apartments.id', 'apartments.number')->join('ownership', 'ownership.apartment_id', '=', 'apartments.id')->whereNull('ownership.deleted_at')->orderBy('number')->get()->toArray();
        $this->multiselect['apartments']['selected'] = '';

        $group = [];
        foreach (trans(\Locales::getNamespace() . '/multiselect.reportGroupBy') as $key => $value) {
            $group[] = ['id' => $key, 'name' => $value];
        }
        $this->multiselect['group']['options'] = $group;
        $this->multiselect['group']['selected'] = '';

        $languages = [];
        foreach (\Locales::getPublicDomain()->locales->toArray() as $key => $value) {
            $languages[$key]['id'] = $value['id'];
            $languages[$key]['name'] = $value['name'];
        }

        $this->multiselect['languages']['options'] = $languages;
        $this->multiselect['languages']['selected'] = '';

        $subscribed = [];
        foreach (['' => 'Not set'] + trans(\Locales::getNamespace() . '/multiselect.newsletterSubscription') as $key => $value) {
            $subscribed[] = ['id' => $key, 'name' => $value];
        }
        $this->multiselect['subscribed']['options'] = $subscribed;
        $this->multiselect['subscribed']['selected'] = '';

        $ob = [];
        foreach (['' => 'Not set'] + trans(\Locales::getNamespace() . '/multiselect.outstandingBills') as $key => $value) {
            $ob[] = ['id' => $key, 'name' => $value];
        }
        $this->multiselect['ob']['options'] = $ob;
        $this->multiselect['ob']['selected'] = '';

        $srioc = [];
        foreach (['' => 'Not set'] + trans(\Locales::getNamespace() . '/multiselect.srioc') as $key => $value) {
            $srioc[] = ['id' => $key, 'name' => $value];
        }
        $this->multiselect['srioc']['options'] = $srioc;
        $this->multiselect['srioc']['selected'] = '';

        $multiselect = $this->multiselect;

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($datatables);
        } else {
            return view(\Locales::getNamespace() . '.' . $this->route . '.owners', compact('datatables', 'multiselect'));
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

    public function generate(DataTable $datatable, Request $request, Owner $owners)
    {
        set_time_limit(0);

        $owners = $owners->join('ownership', 'ownership.owner_id', '=', 'owners.id')->whereNull('ownership.deleted_at')->groupBy('owners.id');

        if ($request->input('projects')) {
            $owners = $owners->whereIn('apartments.project_id', $request->input('projects'));
        }

        if ($request->input('buildings')) {
            $owners = $owners->whereIn('apartments.building_id', $request->input('buildings'));
        }

        if ($request->input('apartments')) {
            $owners = $owners->whereIn('apartments.id', $request->input('apartments'));
        }

        if ($request->input('languages')) {
            $owners = $owners->whereIn('owners.locale_id', $request->input('languages'));
        }

        if ($request->has('is_subscribed')) {
            $owners = $owners->where('owners.is_subscribed', $request->input('is_subscribed'));
        }

        if ($request->has('outstanding_bills')) {
            $owners = $owners->where('owners.outstanding_bills', $request->input('outstanding_bills'));
        }

        if ($request->has('letting_offer')) {
            $owners = $owners->where('owners.letting_offer', $request->input('letting_offer'));
        }

        if ($request->has('srioc')) {
            $owners = $owners->where('owners.srioc', $request->input('srioc'));
        }

        $selectors = ['owners.comments', 'owners.is_subscribed', 'owners.outstanding_bills', 'owners.letting_offer', 'owners.srioc'];
        if ($request->input('group') === 'project') {
            $selectors = array_merge($selectors, ['project_translations.name AS project', 'apartments.project_id AS project_id']);
        } elseif ($request->input('group') === 'building') {
            $selectors = array_merge($selectors, ['building_translations.name AS building', 'apartments.building_id AS building_id']);
        }

        if ($request->input('generate')) {
            $datatable->setOption('skipInfo', true, $this->route);
        }

        $datatable->setOption('selectors', $selectors, $this->route);

        $datatable->setup($owners, $this->route, $this->datatables[$this->route], true);
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
        $columns = ['owner', 'language', 'phone', 'mobile', 'email', 'country', 'apartments', 'address', 'is_subscribed', 'outstanding_bills', 'letting_offer', 'srioc', 'comments'];

        foreach ($rawData as $key => $value) {
            $data[$key] = collect(array_replace(array_flip($columns), $rawData[$key]))->only(array_merge($columns, ['project', 'project_id', 'building', 'building_id']))->toArray();
            $data[$key]['is_subscribed'] = trans(\Locales::getNamespace() . '/multiselect.newsletterSubscription.' . $data[$key]['is_subscribed']);
            $data[$key]['outstanding_bills'] = trans(\Locales::getNamespace() . '/multiselect.outstandingBills.' . $data[$key]['outstanding_bills']);
            $data[$key]['letting_offer'] = trans(\Locales::getNamespace() . '/multiselect.lettingOffer.' . $data[$key]['letting_offer']);
            $data[$key]['srioc'] = trans(\Locales::getNamespace() . '/multiselect.srioc.' . $data[$key]['srioc']);
        }

        $uuid = (string)\Uuid::generate();
        $filename = 'owners-' . $uuid;

        $firstColumn = 'A';
        $firstRow = 1;
        $firstCell = $firstColumn . $firstRow;

        array_unshift($data, [
            'owner' => trans(\Locales::getNamespace() . '/datatables.owner'),
            'language' => trans(\Locales::getNamespace() . '/datatables.language'),
            'phone' => trans(\Locales::getNamespace() . '/datatables.phone'),
            'mobile' => trans(\Locales::getNamespace() . '/datatables.mobile'),
            'email' => trans(\Locales::getNamespace() . '/datatables.email'),
            'country' => trans(\Locales::getNamespace() . '/datatables.country'),
            'apartments' => trans(\Locales::getNamespace() . '/datatables.apartments'),
            'address' => trans(\Locales::getNamespace() . '/datatables.address'),
            'is_subscribed' => trans(\Locales::getNamespace() . '/datatables.newsletterSubscription'),
            'outstanding_bills' => trans(\Locales::getNamespace() . '/datatables.outstandingBills'),
            'letting_offer' => trans(\Locales::getNamespace() . '/datatables.lettingOffer'),
            'srioc' => trans(\Locales::getNamespace() . '/datatables.srioc'),
            'comments' => trans(\Locales::getNamespace() . '/datatables.comments'),
        ]);

        $data = collect($data);

        if ($group) {
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
            'E' => -1,
            'F' => -1,
            'G' => -1,
            'H' => -1,
            'I' => -1,
            'J' => -1,
            'K' => -1,
            'L' => -1,
            'M' => -1,
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
                    'C' => '@',
                    'D' => '@',
                    'E' => '@',
                    'F' => '@',
                    'G' => '@',
                    'H' => '@',
                    'I' => '@',
                    'J' => '@',
                    'K' => '@',
                    'L' => '@',
                    'M' => '@',
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
                                'E' => '@',
                                'F' => '@',
                                'G' => '@',
                                'H' => '@',
                                'I' => '@',
                                'J' => '@',
                                'K' => '@',
                                'L' => '@',
                                'M' => '@',
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
        return response()->download(storage_path('app/reports/owners-' . $request->input('uuid') . '.xlsx'))->deleteFileAfterSend(true);
    }
}
