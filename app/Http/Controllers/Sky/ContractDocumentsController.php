<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Services\FineUploader;
use Storage;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Models\Sky\Year;
use App\Models\Sky\Apartment;
use App\Models\Sky\Contract;
use App\Models\Sky\ContractYear;
use App\Models\Sky\ContractDocuments;
use App\Http\Requests\Sky\ContractDocumentsRequest;

class ContractDocumentsController extends Controller {

    protected $route = 'contract-documents';
    protected $uploadDirectory = 'apartments';
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
                'title' => trans(\Locales::getNamespace() . '/datatables.titleContractDocuments'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'columns' => [
                    [
                        'selector' => 'contract_documents.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center vertical-center',
                    ],
                    [
                        'selector' => 'contract_documents.type',
                        'id' => 'type',
                        'name' => trans(\Locales::getNamespace() . '/datatables.type'),
                        'order' => false,
                        'replace' => [
                            'array' => trans(\Locales::getNamespace() . '/multiselect.contractDocuments'),
                        ],
                        'class' => 'vertical-center',
                    ],
                    [
                        'selector' => 'contract_documents.signed_at',
                        'id' => 'signed_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.signedAt'),
                        'order' => false,
                    ],
                    [
                        'selector' => 'contract_documents.file',
                        'id' => 'file',
                        'name' => trans(\Locales::getNamespace() . '/datatables.file'),
                        'order' => false,
                        'class' => 'text-center vertical-center',
                        'file' => [
                            'selector' => ['contract_documents.extension'],
                            'extension' => 'extension',
                            'route' => $this->route . '/download',
                        ],
                    ],
                    [
                        'selector' => 'contract_documents.size',
                        'id' => 'size',
                        'name' => trans(\Locales::getNamespace() . '/datatables.size'),
                        'filesize' => true,
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                ],
                'orderByColumn' => 'id',
                'order' => 'asc',
                'buttons' => [
                    [
                        'upload-file' => true,
                        'upload' => true,
                        'id' => 'fine-uploader-upload',
                        'url' => \Locales::route($this->route . '/upload'),
                        'class' => 'btn-primary js-upload',
                        'icon' => 'upload',
                        'name' => trans(\Locales::getNamespace() . '/forms.uploadButton'),
                    ],
                    [
                        'upload-file' => true,
                        'reupload' => true,
                        'id' => 'fine-uploader-reupload',
                        'url' => \Locales::route($this->route . '/upload'),
                        'class' => 'btn-primary disabled js-reupload',
                        'icon' => 'refresh',
                        'name' => trans(\Locales::getNamespace() . '/forms.replaceFileButton'),
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
    }

    public function index(DataTable $datatable, Request $request, $apartment = null, $contractsSlug = null, $contract = null, $yearsSlug = null, $contractYear = null, $documentsSlug = null)
    {
        $breadcrumbs = [];

        $apartment = Apartment::findOrFail($apartment);
        $contract = Contract::withTrashed()->findOrFail($contract);
        $contractYear = ContractYear::withTrashed()->findOrFail($contractYear);
        $year = Year::where('year', $contractYear->year)->firstOrFail();
        $name = $contract->rentalContract->name;

        $breadcrumbs[] = ['id' => 'apartments', 'slug' => $apartment->id, 'name' => $apartment->number];
        $breadcrumbs[] = ['id' => $contractsSlug, 'slug' => $contractsSlug, 'name' => trans(\Locales::getNamespace() . '/multiselect.apartmentProperties.' . $contractsSlug)];
        $breadcrumbs[] = ['id' => 'contract', 'slug' => $contract->id . '/' . $yearsSlug, 'name' => $name];
        $breadcrumbs[] = ['id' => 'year', 'slug' => $contractYear->id, 'name' => $contractYear->year];
        $breadcrumbs[] = ['id' => $documentsSlug, 'slug' => $documentsSlug, 'name' => trans(\Locales::getNamespace() . '/multiselect.contractProperties.' . $documentsSlug)];

        $datatable->setup(ContractDocuments::where('contract_year_id', $contractYear->id), $this->route, $this->datatables[$this->route]);
        $datatable->setOption('datatablesId', $contractYear->id);
        $datatables = $datatable->getTables();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($datatables);
        } else {
            return view(\Locales::getNamespace() . '.' . $this->route . '.index', compact('datatables', 'breadcrumbs'));
        }
    }

    public function upload(Request $request, FineUploader $uploader, $chunk = null)
    {
        $uploader->isImage = false;
        $uploader->isFile = true;
        $uploader->allowedExtensions = \Config::get('upload.fileExtensions');

        $year = ContractYear::withTrashed()->findOrFail($request->input('id'));

        $uploader->uploadDirectory = $this->uploadDirectory . DIRECTORY_SEPARATOR . $year->contract->apartment_id . DIRECTORY_SEPARATOR . 'documents';
        if (!Storage::disk('local-public')->exists($uploader->uploadDirectory)) {
            Storage::disk('local-public')->makeDirectory($uploader->uploadDirectory);
        }

        if ($chunk) {
            $response = $uploader->combineChunks();
        } else {
            $response = $uploader->handleUpload();
        }

        $reupload = filter_var($request->input('reupload'), FILTER_VALIDATE_BOOLEAN);
        if (isset($response['success']) && $response['success'] && isset($response['fileName'])) {
            if ($reupload) {
                $response['reupload'] = true;
                $response['row'] = $request->input('row');

                $file = ContractDocuments::findOrFail($request->input('row'));

                Storage::disk('local-public')->deleteDirectory($uploader->uploadDirectory . '/' . $file->uuid);
            } else {
                $file = new ContractDocuments;
                $file->contract_year_id = $request->input('id');
            }

            $file->file = $response['fileName'];
            $file->uuid = $response['uuid'];
            $file->extension = $response['fileExtension'];
            $file->size = $response['fileSize'];
            $file->save();

            $route = '';
            foreach ($this->datatables[$this->route]['columns'] as $column) {
                if ($column['id'] == 'file') {
                    $route = $column['file']['route'];
                    break;
                }
            }

            $response['data'] = [
                'id' => $file->id,
                'type' => $file->type ? trans(\Locales::getNamespace() . '/multiselect.contractDocuments.' . $file->type) : null,
                'signed_at' => null,
                'file' => '<a href="' . \Locales::route($route, $file->id) . '">' . \HTML::image(\App\Helpers\autover('/img/' . \Locales::getNamespace() . '/ext/' . $response['fileExtension'] . '.png'), $response['fileName']) . '</a>',
                'size' => \App\Helpers\formatBytes($response['fileSize']),
            ];
        }

        return response()->json($response, $uploader->getStatus())->header('Content-Type', 'text/plain');
    }

    public function delete(Request $request)
    {
        $table = $request->input('table');

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.delete', compact('table'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function destroy(DataTable $datatable, ContractDocuments $file, Request $request)
    {
        $count = count($request->input('id'));

        $uuids = ContractDocuments::find($request->input('id'))->lists('contract_year_id', 'uuid');
        $year = ContractYear::withTrashed()->findOrFail($uuids->first());
        $contract = $year->contract;

        if ($count > 0 && $file->destroy($request->input('id'))) {
            foreach ($uuids as $uuid => $contract_year_id) {
                Storage::disk('local-public')->deleteDirectory($this->uploadDirectory . DIRECTORY_SEPARATOR . $contract->apartment_id . DIRECTORY_SEPARATOR . 'documents' . DIRECTORY_SEPARATOR . $uuid);
            }

            $datatable->setup(ContractDocuments::where('contract_year_id', $contract_year_id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => trans(\Locales::getNamespace() . '/forms.destroyedSuccessfully'),
                'closePopup' => true,
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
        $file = ContractDocuments::findOrFail($id);

        $table = $request->input('table');

        $types[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $types = array_merge($types, trans(\Locales::getNamespace() . '/multiselect.contractDocuments'));

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.edit', compact('file', 'table', 'types'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, ContractDocumentsRequest $request)
    {
        $file = ContractDocuments::findOrFail($request->input('id'))->first();

        if ($file->update($request->all())) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityDocuments', 1)]);

            $datatable->setup($file->where('contract_year_id', $file->contract_year_id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityDocuments', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function download(Request $request, $id)
    {
        $file = ContractDocuments::findOrFail($id);
        $apartment = $file->contractYear->contract->apartment_id;

        $uploadDirectory = public_path('upload') . DIRECTORY_SEPARATOR . $this->uploadDirectory . DIRECTORY_SEPARATOR . $apartment . DIRECTORY_SEPARATOR . 'documents' . DIRECTORY_SEPARATOR . $file->uuid . DIRECTORY_SEPARATOR . $file->file;

        return response()->download($uploadDirectory);
    }
}
