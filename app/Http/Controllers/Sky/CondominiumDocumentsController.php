<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Services\FineUploader;
use Storage;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Models\Sky\CondominiumDocuments;
use App\Models\Sky\Condominium;
use App\Models\Sky\Project;
use App\Models\Sky\Building;
use App\Models\Sky\Year;
use App\Http\Requests\Sky\CondominiumDocumentsRequest;

class CondominiumDocumentsController extends Controller {

    protected $route = 'condominium-documents';
    protected $uploadDirectory = 'buildings' . DIRECTORY_SEPARATOR . 'condominium-documents';
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
                'title' => trans(\Locales::getNamespace() . '/datatables.titleCondominiumDocuments'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'columns' => [
                    [
                        'selector' => 'condominium_documents.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center vertical-center',
                    ],
                    [
                        'selector' => 'condominium_documents.type',
                        'id' => 'type',
                        'name' => trans(\Locales::getNamespace() . '/datatables.type'),
                        'order' => false,
                        'replace' => [
                            'array' => trans(\Locales::getNamespace() . '/multiselect.condominiumDocuments'),
                        ],
                        'class' => 'vertical-center',
                    ],
                    [
                        'selector' => 'condominium_documents.file',
                        'id' => 'file',
                        'name' => trans(\Locales::getNamespace() . '/datatables.file'),
                        'order' => false,
                        'class' => 'text-center vertical-center',
                        'file' => [
                            'selector' => ['condominium_documents.extension'],
                            'extension' => 'extension',
                            'route' => $this->route . '/download',
                        ],
                    ],
                    [
                        'selector' => 'condominium_documents.size',
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

    public function index(DataTable $datatable, Request $request, $project = null, $buildingsSlug = null, $building = null, $cSlug = null, $year = null, $condominium = null)
    {
        $breadcrumbs = [];

        $project = Project::findOrFail($project);
        $building = Building::findOrFail($building);
        $year = Year::where('year', $year)->firstOrFail();
        $condominium = Condominium::findOrFail($condominium);
        $breadcrumbs[] = ['id' => 'projects', 'slug' => $project->id . '/' . $buildingsSlug, 'name' => $project->name];
        $breadcrumbs[] = ['id' => 'buildings', 'slug' => $building->id, 'name' => $building->name];
        $breadcrumbs[] = ['id' => 'condominium', 'slug' => $cSlug, 'name' => trans(\Locales::getNamespace() . '/multiselect.buildingProperties.' . $cSlug)];
        $breadcrumbs[] = ['id' => 'year', 'slug' => $year->year, 'name' => $year->year];
        $breadcrumbs[] = ['id' => 'condominium-documents', 'slug' => $condominium->id, 'name' => $condominium->assembly_at];

        $uploadDirectory = $this->uploadDirectory;
        if (!Storage::disk('local-public')->exists($uploadDirectory)) {
            Storage::disk('local-public')->makeDirectory($uploadDirectory);
        }

        $uploadDirectory .= DIRECTORY_SEPARATOR . $condominium->id;
        if (!Storage::disk('local-public')->exists($uploadDirectory)) {
            Storage::disk('local-public')->makeDirectory($uploadDirectory);
        }

        $datatable->setup(CondominiumDocuments::where('condominium_id', $condominium->id), $this->route, $this->datatables[$this->route]);
        $datatable->setOption('datatablesId', $condominium->id);

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

        $uploader->uploadDirectory = $this->uploadDirectory . DIRECTORY_SEPARATOR . $request->input('id');
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

                $file = CondominiumDocuments::findOrFail($request->input('row'));

                Storage::disk('local-public')->deleteDirectory($uploader->uploadDirectory . '/' . $file->uuid);
            } else {
                $file = new CondominiumDocuments;
                $file->condominium_id = $request->input('id');
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
                'type' => $file->type ? trans(\Locales::getNamespace() . '/multiselect.condominiumDocuments.' . $file->type) : null,
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

    public function destroy(DataTable $datatable, CondominiumDocuments $file, Request $request)
    {
        $count = count($request->input('id'));

        $uuids = CondominiumDocuments::find($request->input('id'))->lists('condominium_id', 'uuid');

        if ($count > 0 && $file->destroy($request->input('id'))) {
            foreach ($uuids as $uuid => $condominium_id) {
                Storage::disk('local-public')->deleteDirectory($this->uploadDirectory . DIRECTORY_SEPARATOR . $condominium_id . DIRECTORY_SEPARATOR . $uuid);
            }

            $datatable->setup(CondominiumDocuments::where('condominium_id', $condominium_id), $request->input('table'), $this->datatables[$request->input('table')], true);
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
        $file = CondominiumDocuments::findOrFail($id);

        $table = $request->input('table');

        $types[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $types = array_merge($types, trans(\Locales::getNamespace() . '/multiselect.condominiumDocuments'));

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.edit', compact('file', 'table', 'types'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, CondominiumDocumentsRequest $request)
    {
        $file = CondominiumDocuments::findOrFail($request->input('id'))->first();

        if ($file->update($request->all())) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityDocuments', 1)]);

            $file = $file->where('condominium_id', $file->condominium_id);

            $datatable->setup($file, $request->input('table'), $this->datatables[$request->input('table')], true);
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
        $file = CondominiumDocuments::findOrFail($id);

        $uploadDirectory = public_path('upload') . DIRECTORY_SEPARATOR . $this->uploadDirectory . DIRECTORY_SEPARATOR . $file->condominium_id . DIRECTORY_SEPARATOR . $file->uuid . DIRECTORY_SEPARATOR . $file->file;

        return response()->download($uploadDirectory);
    }
}
