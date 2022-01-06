<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Services\FineUploader;
use Storage;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Models\Sky\Owner;
use App\Models\Sky\OwnerFiles;
use App\Http\Requests\Sky\OwnerFilesRequest;

class OwnerFilesController extends Controller
{

    protected $route = 'owner-files';
    protected $uploadDirectory = 'owners';
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
                'title' => trans(\Locales::getNamespace() . '/datatables.titleFiles'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'columns' => [
                    [
                        'selector' => 'owner_files.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center vertical-center',
                    ],
                    [
                        'selector' => 'owner_files.file',
                        'id' => 'file',
                        'name' => trans(\Locales::getNamespace() . '/datatables.file'),
                        'order' => false,
                        'class' => 'vertical-center',
                        'file' => [
                            'selector' => ['owner_files.extension'],
                            'extension' => 'extension',
                            'route' => $this->route . '/download',
                            'keep' => true,
                        ],
                    ],
                    [
                        'selector' => 'owner_files.name',
                        'id' => 'name',
                        'order' => false,
                        'name' => trans(\Locales::getNamespace() . '/datatables.name'),
                        'class' => 'text-center vertical-center',
                    ],
                    [
                        'selector' => 'owner_files.size',
                        'id' => 'size',
                        'name' => trans(\Locales::getNamespace() . '/datatables.size'),
                        'filesize' => true,
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                ],
                'orderByColumn' => 'name',
                'order' => 'asc',
                'buttons' => [
                    'upload' => [
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

    public function index(DataTable $datatable, Request $request, $id = null)
    {
        $breadcrumbs = [];

        $owner = Owner::findOrFail($id);
        $breadcrumbs[] = ['id' => $owner->id, 'slug' => $owner->id, 'name' => $owner->full_name];
        $breadcrumbs[] = ['id' => 'files', 'slug' => 'files', 'name' => trans(\Locales::getNamespace() . '/multiselect.ownerProperties.files')];

        $uploadDirectory = $this->uploadDirectory;
        if (!Storage::disk('local-public')->exists($this->uploadDirectory)) {
            Storage::disk('local-public')->makeDirectory($this->uploadDirectory);
        }

        $uploadDirectory .= DIRECTORY_SEPARATOR . $owner->id;
        if (!Storage::disk('local-public')->exists($uploadDirectory)) {
            Storage::disk('local-public')->makeDirectory($uploadDirectory);
        }

        $datatable->setOption('uploadDirectory', $this->uploadDirectory . '/' . $owner->id, $this->route);
        $datatable->setup(OwnerFiles::where('owner_id', $owner->id), $this->route, $this->datatables[$this->route]);
        $datatable->setOption('datatablesId', $owner->id);

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

        $uploader->uploadDirectory = $this->uploadDirectory . DIRECTORY_SEPARATOR . $request->input('id') . DIRECTORY_SEPARATOR . \Config::get('upload.filesDirectory');
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

                $file = OwnerFiles::findOrFail($request->input('row'));

                Storage::disk('local-public')->deleteDirectory($uploader->uploadDirectory . '/' . $file->uuid);
            } else {
                $file = new OwnerFiles;
                $file->owner_id = $request->input('id');
            }

            $file->file = $response['fileName'];
            $file->uuid = $response['uuid'];
            $file->extension = $response['fileExtension'];
            $file->size = $response['fileSize'];
            $file->name = str_replace('.' . $response['fileExtension'], '', title_case($response['fileName']));
            $file->save();

            $route = '';
            $keep = false;
            foreach ($this->datatables[$this->route]['columns'] as $column) {
                if ($column['id'] == 'file') {
                    $route = $column['file']['route'];
                    $keep = isset($column['file']['keep']) ? true : false;
                    break;
                }
            }

            $response['data'] = [
                'id' => $file->id,
                'name' => $file->name,
                'file' => '<a href="' . \Locales::route($route, $file->id) . '">' . \HTML::image(\App\Helpers\autover('/img/' . \Locales::getNamespace() . '/ext/' . $response['fileExtension'] . '.png'), $response['fileName']) . ($keep ? ' ' . $file->file : '') . '</a>',
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

    public function destroy(DataTable $datatable, OwnerFiles $file, Request $request)
    {
        $count = count($request->input('id'));

        $uuids = OwnerFiles::find($request->input('id'))->lists('owner_id', 'uuid');

        if ($count > 0 && $file->destroy($request->input('id'))) {
            foreach ($uuids as $uuid => $owner_id) {
                Storage::disk('local-public')->deleteDirectory($this->uploadDirectory . DIRECTORY_SEPARATOR . $owner_id . DIRECTORY_SEPARATOR . \Config::get('upload.filesDirectory') . DIRECTORY_SEPARATOR . $uuid);
            }

            $datatable->setOption('uploadDirectory', $this->uploadDirectory . '/' . $owner_id, $request->input('table'));
            $datatable->setup(OwnerFiles::where('owner_id', $owner_id), $request->input('table'), $this->datatables[$request->input('table')], true);
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
        $file = OwnerFiles::findOrFail($id);

        $table = $request->input('table');

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.edit', compact('file', 'table'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, OwnerFiles $files, OwnerFilesRequest $request)
    {
        $file = OwnerFiles::findOrFail($request->input('id'))->first();

        if ($file->update($request->all())) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityFiles', 1)]);

            $datatable->setOption('uploadDirectory', $this->uploadDirectory . '/' . $file->owner_id, $request->input('table'));
            $datatable->setup($files->where('owner_id', $file->owner_id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityFiles', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function download(Request $request, $id)
    {
        $file = OwnerFiles::findOrFail($id);

        $uploadDirectory = public_path('upload') . DIRECTORY_SEPARATOR . $this->uploadDirectory . DIRECTORY_SEPARATOR . $file->owner_id . DIRECTORY_SEPARATOR . \Config::get('upload.filesDirectory') . DIRECTORY_SEPARATOR . $file->uuid . DIRECTORY_SEPARATOR . $file->file;

        return response()->download($uploadDirectory);
    }
}
