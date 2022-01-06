<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Services\FineUploader;
use Storage;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Models\Sky\SignatureFiles;
use App\Models\Sky\Signature;
use App\Http\Requests\Sky\SignatureFilesRequest;

class SignatureFilesController extends Controller {

    protected $route = 'signature-files';
    protected $uploadDirectory = 'signatures';
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
                'class' => 'table-checkbox table-striped table-bordered table-hover table-thumbnails popup-gallery',
                'uploadDirectory' => $this->uploadDirectory,
                'expandDirectory' => '',
                'columns' => [
                    [
                        'selector' => 'signature_files.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center vertical-center',
                    ],
                    [
                        'selector' => 'signature_files.order',
                        'id' => 'order',
                        'order' => false,
                        'name' => trans(\Locales::getNamespace() . '/datatables.order'),
                        'class' => 'text-center vertical-center',
                    ],
                    [
                        'selector' => 'signature_files.file',
                        'id' => 'file',
                        'name' => trans(\Locales::getNamespace() . '/datatables.file'),
                        'order' => false,
                        'class' => 'text-center vertical-center',
                        'thumbnail' => [
                            'selector' => ['signature_files.uuid'],
                            'id' => 'uuid',
                            'root' => true,
                        ],
                    ],
                    [
                        'selector' => 'signature_files.size',
                        'id' => 'size',
                        'name' => trans(\Locales::getNamespace() . '/datatables.size'),
                        'filesize' => true,
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                ],
                'orderByColumn' => 'order',
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

    public function index(DataTable $datatable, Signature $signature, Request $request, $id = null)
    {
        $breadcrumbs = [];

        $signature = $signature->findOrFail($id);
        $breadcrumbs[] = ['id' => 'files', 'slug' => $signature->id, 'name' => $signature->name];

        $uploadDirectory = $this->uploadDirectory;
        if (!Storage::disk('local-public')->exists($uploadDirectory)) {
            Storage::disk('local-public')->makeDirectory($uploadDirectory);
        }

        $uploadDirectory .= DIRECTORY_SEPARATOR . $signature->id;
        if (!Storage::disk('local-public')->exists($uploadDirectory)) {
            Storage::disk('local-public')->makeDirectory($uploadDirectory);
        }

        $datatable->setOption('uploadDirectory', $this->uploadDirectory . '/' . $signature->id, $this->route);
        $datatable->setup(SignatureFiles::where('signature_id', $signature->id), $this->route, $this->datatables[$this->route]);
        $datatable->setOption('datatablesId', $signature->id);

        $datatables = $datatable->getTables();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($datatables);
        } else {
            return view(\Locales::getNamespace() . '.' . $this->route . '.index', compact('datatables', 'breadcrumbs'));
        }
    }

    public function upload(Request $request, FineUploader $uploader, $chunk = null)
    {
        $uploader->signature = true;

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

                $file = SignatureFiles::findOrFail($request->input('row'));

                Storage::disk('local-public')->deleteDirectory($uploader->uploadDirectory . '/' . $file->uuid);
            } else {
                $file = new SignatureFiles;
                $file->order = SignatureFiles::max('order') + 1;
                $file->signature_id = $request->input('id');
            }

            $file->file = $response['fileName'];
            $file->uuid = $response['uuid'];
            $file->extension = $response['fileExtension'];
            $file->size = $response['fileSize'];
            $file->save();

            $directory = asset('upload/' . str_replace(DIRECTORY_SEPARATOR, '/', $uploader->uploadDirectory) . '/' . $response['uuid']);

            $response['data'] = [
                'id' => $file->id,
                'order' => $file->order,
                'file' => '<a class="popup" href="' . asset($directory . '/' . $response['fileName']) . '">' . \HTML::image($directory . '/' . \Config::get('upload.thumbnailDirectory') . '/' . $response['fileName']) . '</a>',
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

    public function destroy(DataTable $datatable, SignatureFiles $file, Request $request)
    {
        $count = count($request->input('id'));

        $uuids = SignatureFiles::find($request->input('id'))->lists('signature_id', 'uuid');

        if ($count > 0 && $file->destroy($request->input('id'))) {
            foreach ($uuids as $uuid => $signature_id) {
                Storage::disk('local-public')->deleteDirectory($this->uploadDirectory . DIRECTORY_SEPARATOR . $signature_id . DIRECTORY_SEPARATOR . $uuid);
            }

            \DB::statement('SET @pos := 0');
            \DB::update('update ' . $file->getTable() . ' SET `order` = (SELECT @pos := @pos + 1) ORDER BY `order`');

            $datatable->setOption('uploadDirectory', $this->uploadDirectory . '/' . $signature_id, $request->input('table'));
            $datatable->setup(SignatureFiles::where('signature_id', $signature_id), $request->input('table'), $this->datatables[$request->input('table')], true);
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
        $file = SignatureFiles::findOrFail($id);

        $table = $request->input('table');

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.edit', compact('file', 'table'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, SignatureFiles $files, SignatureFilesRequest $request)
    {
        $file = SignatureFiles::findOrFail($request->input('id'))->first();

        $order = $request->input('order');
        if (!$order || $order < 0) {
            $order = $file->order;
        } elseif ($order) {
            $maxOrder = $files->max('order');

            if ($order > $maxOrder) {
                $order = $maxOrder;
            } elseif ($order < $file->order) {
                $files->where('order', '>=', $order)->where('order', '<', $file->order)->increment('order');
            } elseif ($order > $file->order) {
                $files->where('order', '<=', $order)->where('order', '>', $file->order)->decrement('order');
            }
        }

        $request->merge([
            'order' => $order,
        ]);

        if ($file->update($request->all())) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityFiles', 1)]);

            $datatable->setOption('uploadDirectory', $this->uploadDirectory . '/' . $file->signature_id, $request->input('table'));
            $datatable->setup($file->where('signature_id', $file->signature_id), $request->input('table'), $this->datatables[$request->input('table')], true);
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
}
