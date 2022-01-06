<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Models\Sky\KeyLog;
use App\Models\Sky\Apartment;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Http\Requests\Sky\KeyLogRequest;
use App\Services\FineUploader;
use Storage;
use Carbon\Carbon;

class KeyLogController extends Controller {

    protected $route = 'key-log';
    protected $datatables;
    protected $uploadDirectory = 'keylog';
    protected $multiselect;

    public function __construct()
    {
        $this->datatables = [
            $this->route => [
                'title' => trans(\Locales::getNamespace() . '/datatables.titleKeyLog'),
                'url' => \Locales::route($this->route),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'columns' => [
                    [
                        'selector' => 'key_log.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center vertical-center',
                    ],
                    [
                        'selector' => 'key_log.occupied_at',
                        'id' => 'occupied_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.date'),
                        'class' => 'vertical-center',
                        'search' => true,
                        'date' => [
                            'format' => '%d.%m.%Y',
                        ],
                        'data' => [
                            'type' => 'sort',
                            'id' => 'occupied_at',
                            'date' => 'YYmmdd',
                        ],
                    ],
                    [
                        'selector' => 'apartments.number',
                        'id' => 'number',
                        'name' => trans(\Locales::getNamespace() . '/datatables.apartment'),
                        'search' => true,
                        'class' => 'vertical-center',
                        'join' => [
                            'table' => 'apartments',
                            'localColumn' => 'apartments.id',
                            'constrain' => '=',
                            'foreignColumn' => 'key_log.apartment_id',
                        ],
                    ],
                    [
                        'selector' => 'key_log.people',
                        'id' => 'people',
                        'name' => trans(\Locales::getNamespace() . '/datatables.people'),
                        'class' => 'vertical-center',
                        'order' => false,
                    ],
                ],
                'orderByRaw' => 'CONCAT(SUBSTR(apartments.number, 1, LOCATE("-", apartments.number) - 1), LPAD(SUBSTR(apartments.number, LOCATE("-", apartments.number) + 1), 3, "0"))',
                'orderByColumn' => 1,
                'order' => 'desc',
                'buttons' => [
                    'upload' => [
                        'upload-file' => true,
                        'upload' => true,
                        'single-file' => true,
                        'id' => 'fine-uploader-upload',
                        'url' => \Locales::route($this->route . '/upload'),
                        'class' => 'btn-primary js-upload',
                        'icon' => 'upload',
                        'name' => trans(\Locales::getNamespace() . '/forms.uploadButton'),
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
            ],
        ];
    }

    public function index(DataTable $datatable, KeyLog $log, Request $request)
    {
        $datatable->setup($log, $this->route, $this->datatables[$this->route]);
        $datatables = $datatable->getTables();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($datatables);
        } else {
            return view(\Locales::getNamespace() . '.' . $this->route . '.index', compact('datatables'));
        }
    }

    public function create(Request $request)
    {
        $table = $this->route;
        if ($request->input('table')) {
            $table = $request->input('table');
        }

        $this->multiselect['apartments']['options'] = array_merge([['id' => '', 'number' => trans(\Locales::getNamespace() . '/forms.selectOption')]], Apartment::distinct()->select('apartments.id', 'apartments.number')->join('ownership', 'ownership.apartment_id', '=', 'apartments.id')->whereNull('ownership.deleted_at')->orderBy('number')->get()->toArray());
        $this->multiselect['apartments']['selected'] = '';

        $multiselect = $this->multiselect;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('table', 'multiselect'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function store(DataTable $datatable, KeyLogRequest $request)
    {
        $apartment = Apartment::findOrFail($request->input('apartment_id'));

        $log = KeyLog::create($request->all());

        if ($log->id) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.storedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityKeyLog', 1)]);

            $datatable->setup($log, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.createError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityKeyLog', 1)]);
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

    public function destroy(DataTable $datatable, KeyLog $log, Request $request)
    {
        $count = count($request->input('id'));

        if ($count > 0 && $log->destroy($request->input('id'))) {
            $datatable->setup($log, $request->input('table'), $this->datatables[$request->input('table')], true);
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
        $log = KeyLog::findOrFail($id);

        $table = $this->route;
        if ($request->input('table')) {
            $table = $request->input('table');
        }

        $this->multiselect['apartments']['options'] = array_merge([['id' => '', 'number' => trans(\Locales::getNamespace() . '/forms.selectOption')]], Apartment::distinct()->select('apartments.id', 'apartments.number')->join('ownership', 'ownership.apartment_id', '=', 'apartments.id')->whereNull('ownership.deleted_at')->orderBy('number')->get()->toArray());
        $this->multiselect['apartments']['selected'] = $log->apartment_id;

        $multiselect = $this->multiselect;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('log', 'table', 'multiselect'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, KeyLogRequest $request)
    {
        $log = KeyLog::findOrFail($request->input('id'))->first();

        if ($log->update($request->all())) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityKeyLog', 1)]);

            $datatable->setup($log, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityKeyLog', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function upload(Request $request, KeyLog $log, DataTable $datatable, FineUploader $uploader, $chunk = null)
    {
        $uploader->isImage = false;
        $uploader->isFile = true;
        $uploader->allowedExtensions = \Config::get('upload.fileExtensions');

        $uploader->uploadDirectory = $this->uploadDirectory;
        if (!Storage::disk('local-public')->exists($uploader->uploadDirectory)) {
            Storage::disk('local-public')->makeDirectory($uploader->uploadDirectory);
        }

        if ($chunk) {
            $response = $uploader->combineChunks(false);
        } else {
            $response = $uploader->handleUpload(null, false);
        }

        if (isset($response['success']) && $response['success'] && isset($response['fileName'])) {
            $rows = array_map(function($row) {
                return str_getcsv($row, ';');
            }, file(public_path('upload') . DIRECTORY_SEPARATOR . $this->uploadDirectory . DIRECTORY_SEPARATOR . $response['fileName']));

            Storage::disk('local-public')->delete($this->uploadDirectory . DIRECTORY_SEPARATOR . $response['fileName']);

            $apartments = Apartment::select('number', 'id')->pluck('id', 'number');
            $latestDate = KeyLog::select('occupied_at')->orderBy('occupied_at', 'desc')->limit(1)->first();
            /*$date = isset($latestDate->occupied_at) ? Carbon::parse($latestDate->occupied_at) : Carbon::now();
            $date = $date->format('Y-m-d');*/
            $date = null;

            $errorsFound = [];
            $errorsUnique = [];
            $unique = [];
            foreach($rows as $row) {
                $row = array_filter($row);
                $row = array_values($row);

                if (!isset($row[0]) || !preg_match('/^[A|B|C|D|E|F|G|H]\d+.*?/', $row[0]) || strpos($row[0], 'ЛП') !== false) { // ignore empty rows, not apartment numbers and personal bookings
                    if (!$date && preg_match('/от (\d+)\/(\d+)\/(\d+) до \d+\/\d+\/\d+/', $row[0], $matches)) {
                        $date = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
                    }

                    continue;
                }

                if (!$date) {
                    return response()->json([
                        'error' => trans(\Locales::getNamespace() . '/forms.parseDateError'),
                        'preventRetry' => true,
                    ]);
                }

                array_walk($row, function(&$value) {
                    $value = preg_replace('/\(\d+\)/', '', $value); // remove number of nights (xx)
                    $value = str_replace('-0', '-', $value); // remove apartment number leading zeros
                    $value = preg_replace('/F\d+ /', 'F', $value);
                    $value = preg_replace('/G\d+ /', 'G', $value);
                    $value = preg_replace('/H\d+ /', 'H', $value);
                    $value = trim(html_entity_decode($value), " \t\n\r\0\x0B\xC2\xA0"); // \xC2\xA0 = &nbsp;

                    if (($index = strpos($value, '/')) !== false) {
                        $value = substr($value, 0, $index);
                    }

                    if ($value == 'C5-7') {
                        $value = 'C5-7-8';
                    } elseif ($value == 'C5-9') {
                        $value = 'C5-9-10';
                    } elseif ($value == 'E1-8') {
                        $value = 'E1-8-9';
                    } elseif ($value == 'C3-9') {
                        $value = 'C3-9-10';
                    } elseif ($value == 'E5-7') {
                        $value = 'E5-7-8';
                    }
                });

                $apartment = $row[0];
                $people = $row[1];
                // $date = $row[2] . '.' . date('Y');

                if (in_array($date . $apartment, $unique)) {
                    array_push($errorsUnique, $apartment);
                } else {
                    array_push($unique, $date . $apartment);

                    if (isset($apartments[$apartment])) {
                        $model = KeyLog::withTrashed()->firstOrNew(
                        [
                            'occupied_at' => $date, // Carbon::parse($date)->format('Y-m-d'),
                            'apartment_id' => $apartments[$apartment],
                        ]);

                        if ($model->id && !$model->deleted_at) {
                            array_push($errorsUnique, $apartment);
                        } else {
                            $model->people = $people;
                            $model->deleted_at = null;
                            $model->save();
                        }
                    } else {
                        array_push($errorsFound, $apartment);
                    }
                }
            }

            $table = $this->route;
            if ($request->input('table')) {
                $table = $request->input('table');
            }

            $datatable->setup($log, $table, $this->datatables[$table], true);
            $datatable->setOption('url', \Locales::route($this->route));
            $datatables = $datatable->getTables();

            if ($errorsFound || $errorsUnique) {
                $error = '';

                if ($errorsFound) {
                    array_unshift($errorsFound, trans(\Locales::getNamespace() . '/forms.importApartmentsNotFoundError'));
                    $error .= implode('<br>', $errorsFound);
                }

                if ($errorsUnique) {
                    array_unshift($errorsUnique, trans(\Locales::getNamespace() . '/forms.importApartmentsUniqueError'));
                    $error .= implode('<br>', $errorsUnique);
                }

                return response()->json($datatables + [
                    'error' => $error,
                    'preventRetry' => true,
                    'updateRows' => true,
                ]);
            } else {
                return response()->json($datatables + [
                    'success' => trans(\Locales::getNamespace() . '/forms.uploadedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityKeyLog', 1)]),
                    'updateRows' => true,
                ]);
            }
        }

        return response()->json($response, $uploader->getStatus())->header('Content-Type', 'text/plain');
    }
}
