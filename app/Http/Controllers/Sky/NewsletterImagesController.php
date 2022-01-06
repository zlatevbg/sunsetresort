<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Services\FineUploader;
use Storage;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Models\Sky\NewsletterImages;
use App\Models\Sky\Newsletter;
use App\Http\Requests\Sky\NewsletterImagesRequest;

class NewsletterImagesController extends Controller {

    protected $route = 'newsletter-images';
    protected $uploadDirectory = 'newsletters';
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
                'title' => trans(\Locales::getNamespace() . '/datatables.titleInlineImages'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover table-thumbnails popup-gallery',
                'columns' => [
                    [
                        'selector' => 'newsletter_images.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center vertical-center',
                    ],
                    [
                        'selector' => 'newsletter_images.order',
                        'id' => 'order',
                        'order' => false,
                        'name' => trans(\Locales::getNamespace() . '/datatables.order'),
                        'class' => 'text-center vertical-center',
                    ],
                    [
                        'selector' => 'newsletter_images.file',
                        'id' => 'file',
                        'name' => trans(\Locales::getNamespace() . '/datatables.file'),
                        'order' => false,
                        'class' => 'text-center vertical-center',
                        'thumbnail' => [
                            'selector' => ['newsletter_images.uuid'],
                            'id' => 'uuid',
                        ],
                    ],
                    [
                        'selector' => 'newsletter_images.size',
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
                        'upload' => true,
                        'id' => 'fine-uploader-upload',
                        'url' => \Locales::route($this->route . '/upload'),
                        'class' => 'btn-primary js-upload',
                        'icon' => 'upload',
                        'name' => trans(\Locales::getNamespace() . '/forms.uploadButton'),
                    ],
                    [
                        'reupload' => true,
                        'id' => 'fine-uploader-reupload',
                        'url' => \Locales::route($this->route . '/upload'),
                        'class' => 'btn-primary disabled js-reupload',
                        'icon' => 'refresh',
                        'name' => trans(\Locales::getNamespace() . '/forms.replaceImageButton'),
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

    public function index(DataTable $datatable, Newsletter $newsletter, Request $request, $id = null)
    {
        $breadcrumbs = [];

        $newsletter = $newsletter->findOrFail($id);
        $breadcrumbs[] = ['id' => 'newsletters', 'slug' => $newsletter->id, 'name' => $newsletter->subject];
        $breadcrumbs[] = ['id' => 'images', 'slug' => 'images', 'name' => trans(\Locales::getNamespace() . '/multiselect.newsletterProperties.images')];

        $uploadDirectory = $this->uploadDirectory;
        if (!Storage::disk('local-public')->exists($uploadDirectory)) {
            Storage::disk('local-public')->makeDirectory($uploadDirectory);
        }

        $uploadDirectory .= DIRECTORY_SEPARATOR . $newsletter->id;
        if (!Storage::disk('local-public')->exists($uploadDirectory)) {
            Storage::disk('local-public')->makeDirectory($uploadDirectory);
        }

        $datatable->setOption('uploadDirectory', $this->uploadDirectory . '/' . $newsletter->id, $this->route);
        $datatable->setup(NewsletterImages::where('newsletter_id', $newsletter->id), $this->route, $this->datatables[$this->route]);
        $datatable->setOption('datatablesId', $newsletter->id);

        $datatables = $datatable->getTables();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($datatables);
        } else {
            return view(\Locales::getNamespace() . '.' . $this->route . '.index', compact('datatables', 'breadcrumbs'));
        }
    }

    public function upload(Request $request, FineUploader $uploader, $chunk = null)
    {
        $uploader->newsletter = true;

        $uploader->uploadDirectory = $this->uploadDirectory . DIRECTORY_SEPARATOR . $request->input('id') . DIRECTORY_SEPARATOR . \Config::get('upload.imagesDirectory');
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

                $image = NewsletterImages::findOrFail($request->input('row'));

                Storage::disk('local-public')->deleteDirectory($uploader->uploadDirectory . '/' . $image->uuid);
            } else {
                $image = new NewsletterImages;
                $image->order = NewsletterImages::where('newsletter_id', $request->input('id'))->max('order') + 1;
                $image->newsletter_id = $request->input('id');
            }

            $image->file = $response['fileName'];
            $image->uuid = $response['uuid'];
            $image->extension = $response['fileExtension'];
            $image->size = $response['fileSize'];
            $image->save();

            $directory = asset('upload/' . $uploader->uploadDirectory . '/' . $response['uuid']);

            $response['data'] = [
                'id' => $image->id,
                'order' => $image->order,
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

    public function destroy(DataTable $datatable, NewsletterImages $image, Request $request)
    {
        $count = count($request->input('id'));

        $uuids = NewsletterImages::find($request->input('id'))->lists('newsletter_id', 'uuid');

        if ($count > 0 && $image->destroy($request->input('id'))) {
            foreach ($uuids as $uuid => $newsletter_id) {
                Storage::disk('local-public')->deleteDirectory($this->uploadDirectory . DIRECTORY_SEPARATOR . $newsletter_id . DIRECTORY_SEPARATOR . \Config::get('upload.imagesDirectory') . DIRECTORY_SEPARATOR . $uuid);
            }

            \DB::statement('SET @pos := 0');
            \DB::update('update ' . $image->getTable() . ' SET `order` = (SELECT @pos := @pos + 1) WHERE newsletter_id = ? ORDER BY `order`', [$newsletter_id]);

            $datatable->setOption('uploadDirectory', $this->uploadDirectory . '/' . $newsletter_id, $request->input('table'));
            $datatable->setup(NewsletterImages::where('newsletter_id', $newsletter_id), $request->input('table'), $this->datatables[$request->input('table')], true);
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
        $image = NewsletterImages::findOrFail($id);

        $table = $request->input('table');

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.edit', compact('image', 'table'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, NewsletterImages $images, NewsletterImagesRequest $request)
    {
        $image = NewsletterImages::findOrFail($request->input('id'))->first();

        $order = $request->input('order');
        if (!$order || $order < 0) {
            $order = $image->order;
        } elseif ($order) {
            $maxOrder = $images->where('newsletter_id', $image->newsletter_id)->max('order');

            if ($order > $maxOrder) {
                $order = $maxOrder;
            } elseif ($order < $image->order) {
                $images->where('newsletter_id', $image->newsletter_id)->where('order', '>=', $order)->where('order', '<', $image->order)->increment('order');
            } elseif ($order > $image->order) {
                $images->where('newsletter_id', $image->newsletter_id)->where('order', '<=', $order)->where('order', '>', $image->order)->decrement('order');
            }
        }

        $request->merge([
            'order' => $order,
        ]);

        if ($image->update($request->all())) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityImages', 1)]);

            $datatable->setOption('uploadDirectory', $this->uploadDirectory . '/' . $image->newsletter_id, $request->input('table'));
            $datatable->setup($image->where('newsletter_id', $image->newsletter_id), $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatable->setOption('url', \Locales::route($this->route));
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityImages', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }
}
