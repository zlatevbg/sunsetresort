<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Services\FineUploader;
use Storage;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Models\Sky\NewsletterTemplateAttachments;
use App\Models\Sky\NewsletterTemplates;
use App\Http\Requests\Sky\NewsletterTemplateAttachmentsRequest;

class NewsletterTemplateAttachmentsController extends Controller {

    protected $route = 'newsletter-template-attachments';
    protected $uploadDirectory = 'newsletter-templates';
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
                'title' => trans(\Locales::getNamespace() . '/datatables.titleAttachments'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'columns' => [
                    [
                        'selector' => 'newsletter_template_attachments.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center vertical-center',
                    ],
                    [
                        'selector' => 'newsletter_template_attachments.order',
                        'id' => 'order',
                        'order' => false,
                        'name' => trans(\Locales::getNamespace() . '/datatables.order'),
                        'class' => 'text-center vertical-center',
                    ],
                    [
                        'selector' => 'newsletter_template_attachments.file',
                        'id' => 'file',
                        'name' => trans(\Locales::getNamespace() . '/datatables.file'),
                        'order' => false,
                        'class' => 'text-center vertical-center',
                        'file' => [
                            'selector' => ['newsletter_template_attachments.extension'],
                            'extension' => 'extension',
                            'route' => $this->route . '/download',
                            'keep' => true,
                        ],
                    ],
                    [
                        'selector' => 'newsletter_template_attachments.size',
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

    public function index(DataTable $datatable, NewsletterTemplates $template, Request $request, $id = null)
    {
        $breadcrumbs = [];

        $template = $template->findOrFail($id);
        $breadcrumbs[] = ['id' => 'templates', 'slug' => $template->id, 'name' => $template->subject];
        $breadcrumbs[] = ['id' => 'attachments', 'slug' => 'attachments', 'name' => trans(\Locales::getNamespace() . '/multiselect.newsletterProperties.attachments')];

        $uploadDirectory = $this->uploadDirectory;
        if (!Storage::disk('local-public')->exists($uploadDirectory)) {
            Storage::disk('local-public')->makeDirectory($uploadDirectory);
        }

        $uploadDirectory .= DIRECTORY_SEPARATOR . $template->id;
        if (!Storage::disk('local-public')->exists($uploadDirectory)) {
            Storage::disk('local-public')->makeDirectory($uploadDirectory);
        }

        $datatable->setOption('uploadDirectory', $this->uploadDirectory . '/' . $template->id, $this->route);
        $datatable->setup(NewsletterTemplateAttachments::where('template_id', $template->id), $this->route, $this->datatables[$this->route]);
        $datatable->setOption('datatablesId', $template->id);

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

        $uploader->uploadDirectory = $this->uploadDirectory . DIRECTORY_SEPARATOR . $request->input('id') . DIRECTORY_SEPARATOR . \Config::get('upload.attachmentsDirectory');
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

                $file = NewsletterTemplateAttachments::findOrFail($request->input('row'));

                Storage::disk('local-public')->deleteDirectory($uploader->uploadDirectory . '/' . $file->uuid);
            } else {
                $file = new NewsletterTemplateAttachments;
                $file->order = NewsletterTemplateAttachments::where('template_id', $request->input('id'))->max('order') + 1;
                $file->template_id = $request->input('id');
            }

            $file->file = $response['fileName'];
            $file->uuid = $response['uuid'];
            $file->extension = $response['fileExtension'];
            $file->size = $response['fileSize'];
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
                'order' => $file->order,
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

    public function destroy(DataTable $datatable, NewsletterTemplateAttachments $file, Request $request)
    {
        $count = count($request->input('id'));

        $uuids = NewsletterTemplateAttachments::find($request->input('id'))->lists('template_id', 'uuid');

        if ($count > 0 && $file->destroy($request->input('id'))) {
            foreach ($uuids as $uuid => $template_id) {
                Storage::disk('local-public')->deleteDirectory($this->uploadDirectory . DIRECTORY_SEPARATOR . $template_id . DIRECTORY_SEPARATOR . \Config::get('upload.attachmentsDirectory') . DIRECTORY_SEPARATOR . $uuid);
            }

            \DB::statement('SET @pos := 0');
            \DB::update('update ' . $file->getTable() . ' SET `order` = (SELECT @pos := @pos + 1) WHERE template_id = ? ORDER BY `order`', [$template_id]);

            $datatable->setOption('uploadDirectory', $this->uploadDirectory . '/' . $template_id, $request->input('table'));
            $datatable->setup(NewsletterTemplateAttachments::where('template_id', $template_id), $request->input('table'), $this->datatables[$request->input('table')], true);
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
        $file = NewsletterTemplateAttachments::findOrFail($id);

        $table = $request->input('table');

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.edit', compact('file', 'table'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, NewsletterTemplateAttachments $files, NewsletterTemplateAttachmentsRequest $request)
    {
        $file = NewsletterTemplateAttachments::findOrFail($request->input('id'))->first();

        $order = $request->input('order');
        if (!$order || $order < 0) {
            $order = $file->order;
        } elseif ($order) {
            $maxOrder = $files->where('template_id', $file->template_id)->max('order');

            if ($order > $maxOrder) {
                $order = $maxOrder;
            } elseif ($order < $file->order) {
                $files->where('template_id', $file->template_id)->where('order', '>=', $order)->where('order', '<', $file->order)->increment('order');
            } elseif ($order > $file->order) {
                $files->where('template_id', $file->template_id)->where('order', '<=', $order)->where('order', '>', $file->order)->decrement('order');
            }
        }

        $request->merge([
            'order' => $order,
        ]);

        if ($file->update($request->all())) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityAttachments', 1)]);

            $datatable->setOption('uploadDirectory', $this->uploadDirectory . '/' . $file->template_id, $request->input('table'));
            $datatable->setup($file->where('template_id', $file->template_id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityAttachments', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function download(Request $request, $id)
    {
        $file = NewsletterTemplateAttachments::findOrFail($id);

        $uploadDirectory = public_path('upload') . DIRECTORY_SEPARATOR . $this->uploadDirectory . DIRECTORY_SEPARATOR . $file->template_id . DIRECTORY_SEPARATOR . \Config::get('upload.attachmentsDirectory') . DIRECTORY_SEPARATOR . $file->uuid . DIRECTORY_SEPARATOR . $file->file;

        return response()->download($uploadDirectory);
    }
}
