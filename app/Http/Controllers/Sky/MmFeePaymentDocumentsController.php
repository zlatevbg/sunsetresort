<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Services\FineUploader;
use Storage;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Models\Sky\Apartment;
use App\Models\Sky\Year;
use App\Models\Sky\MmFeePayment;
use App\Models\Sky\MmFeePaymentDocuments;
use App\Http\Requests\Sky\MmFeePaymentDocumentsRequest;

class MmFeePaymentDocumentsController extends Controller {

    protected $route = 'mm-fees-payment-documents';
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
                'title' => trans(\Locales::getNamespace() . '/datatables.titleMMPaymentDocuments'),
                'url' => \Locales::route($this->route, true),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'columns' => [
                    [
                        'selector' => 'mm_fees_payment_documents.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center vertical-center',
                    ],
                    [
                        'selector' => 'mm_fees_payment_documents.type',
                        'id' => 'type',
                        'name' => trans(\Locales::getNamespace() . '/datatables.type'),
                        'order' => false,
                        'replace' => [
                            'array' => trans(\Locales::getNamespace() . '/multiselect.mmPaymentDocuments'),
                        ],
                        'class' => 'vertical-center',
                    ],
                    [
                        'selector' => 'mm_fees_payment_documents.file',
                        'id' => 'file',
                        'name' => trans(\Locales::getNamespace() . '/datatables.file'),
                        'order' => false,
                        'class' => 'text-center vertical-center',
                        'file' => [
                            'selector' => ['mm_fees_payment_documents.extension'],
                            'extension' => 'extension',
                            'route' => $this->route . '/download',
                        ],
                    ],
                    [
                        'selector' => 'mm_fees_payment_documents.size',
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

    public function index(DataTable $datatable, Request $request, $apartment = null, $mmFeesSlug = null, $year = null, $payment = null)
    {
        $breadcrumbs = [];

        $apartment = Apartment::findOrFail($apartment);
        $year = Year::where('year', $year)->firstOrFail();
        $payment = MmFeePayment::findOrFail($payment);

        $breadcrumbs[] = ['id' => 'apartments', 'slug' => $apartment->id, 'name' => $apartment->number];
        $breadcrumbs[] = ['id' => $mmFeesSlug, 'slug' => $mmFeesSlug, 'name' => trans(\Locales::getNamespace() . '/multiselect.apartmentProperties.' . $mmFeesSlug)];
        $breadcrumbs[] = ['id' => 'year', 'slug' => $year->year, 'name' => $year->year];
        $breadcrumbs[] = ['id' => 'payment', 'slug' => $payment->id, 'name' => $payment->paid_at];

        $datatable->setup(MmFeePaymentDocuments::where('mm_fees_payment_id', $payment->id), $this->route, $this->datatables[$this->route]);
        $datatable->setOption('datatablesId', $payment->id);
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

        $payment = MmFeePayment::findOrFail($request->input('id'));

        $uploader->uploadDirectory = $this->uploadDirectory . DIRECTORY_SEPARATOR . $payment->apartment->id . DIRECTORY_SEPARATOR . 'payments';
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

                $file = MmFeePaymentDocuments::findOrFail($request->input('row'));

                Storage::disk('local-public')->deleteDirectory($uploader->uploadDirectory . '/' . $file->uuid);
            } else {
                $file = new MmFeePaymentDocuments;
                $file->mm_fees_payment_id = $request->input('id');
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
                'type' => $file->type ? trans(\Locales::getNamespace() . '/multiselect.mmPaymentDocuments.' . $file->type) : null,
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

    public function destroy(DataTable $datatable, MmFeePaymentDocuments $file, Request $request)
    {
        $count = count($request->input('id'));

        $uuids = MmFeePaymentDocuments::find($request->input('id'))->lists('mm_fees_payment_id', 'uuid');
        $payment = MmFeePayment::findOrFail($uuids->first());

        if ($count > 0 && $file->destroy($request->input('id'))) {
            foreach ($uuids as $uuid => $mm_fees_payment_id) {
                Storage::disk('local-public')->deleteDirectory($this->uploadDirectory . DIRECTORY_SEPARATOR . $payment->apartment->id . DIRECTORY_SEPARATOR . 'payments' . DIRECTORY_SEPARATOR . $uuid);
            }

            $datatable->setup(MmFeePaymentDocuments::where('mm_fees_payment_id', $mm_fees_payment_id), $request->input('table'), $this->datatables[$request->input('table')], true);
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
        $file = MmFeePaymentDocuments::findOrFail($id);

        $table = $request->input('table');

        $types[''] = trans(\Locales::getNamespace() . '/forms.selectOption');
        $types = array_merge($types, trans(\Locales::getNamespace() . '/multiselect.mmPaymentDocuments'));

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.edit', compact('file', 'table', 'types'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, MmFeePaymentDocumentsRequest $request)
    {
        $file = MmFeePaymentDocuments::findOrFail($request->input('id'))->first();

        if ($file->update($request->all())) {
            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityDocuments', 1)]);

            $datatable->setup($file->where('mm_fees_payment_id', $file->mm_fees_payment_id), $request->input('table'), $this->datatables[$request->input('table')], true);
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
        $file = MmFeePaymentDocuments::findOrFail($id);
        $apartment = $file->mmFeePayment->apartment_id;

        $uploadDirectory = public_path('upload') . DIRECTORY_SEPARATOR . $this->uploadDirectory . DIRECTORY_SEPARATOR . $apartment . DIRECTORY_SEPARATOR . 'payments' . DIRECTORY_SEPARATOR . $file->uuid . DIRECTORY_SEPARATOR . $file->file;

        return response()->download($uploadDirectory);
    }
}
