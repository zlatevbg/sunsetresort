<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Models\Sky\RentalPayment;
use App\Models\Sky\Room;
use App\Models\Sky\Furniture;
use App\Models\Sky\View;
use App\Models\Sky\RentalPaymentPrices;
use App\Http\Requests\Sky\RentalPaymentRequest;

class RentalPaymentController extends Controller {

    protected $route = 'rental-payments';
    protected $datatables;

    public function __construct()
    {
        $this->datatables = [
            $this->route => [
                'title' => trans(\Locales::getNamespace() . '/datatables.titleRentalPayments'),
                'url' => \Locales::route($this->route),
                'class' => 'table-checkbox table-striped table-bordered table-hover',
                'columns' => [
                    [
                        'selector' => 'rental_payments.id',
                        'id' => 'id',
                        'checkbox' => true,
                        'order' => false,
                        'class' => 'text-center',
                    ],
                    [
                        'selector' => 'rental_payments.name',
                        'id' => 'name',
                        'name' => trans(\Locales::getNamespace() . '/datatables.name'),
                        'search' => true,
                    ],
                ],
                'orderByColumn' => 1,
                'order' => 'asc',
                'buttons' => [
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
    }

    public function index(DataTable $datatable, RentalPayment $rentalPayment, Request $request)
    {
        $datatable->setup($rentalPayment, $this->route, $this->datatables[$this->route]);

        $datatables = $datatable->getTables();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($datatables);
        } else {
            return view(\Locales::getNamespace() . '.' . $this->route . '.index', compact('datatables'));
        }
    }

    public function create(Request $request)
    {
        $table = $request->input('table') ?: $this->route;

        $rooms = Room::withTranslation()->select('room_translations.name', 'rooms.id')->leftJoin('room_translations', 'room_translations.room_id', '=', 'rooms.id')->where('room_translations.locale', \Locales::getCurrent())->orderBy('room_translations.name')->get();
        $furnitures = Furniture::withTranslation()->select('furniture_translations.name', 'furniture.id')->leftJoin('furniture_translations', 'furniture_translations.furniture_id', '=', 'furniture.id')->where('furniture_translations.locale', \Locales::getCurrent())->orderBy('furniture_translations.name')->get();
        $views = View::withTranslation()->select('view_translations.name', 'views.id')->leftJoin('view_translations', 'view_translations.view_id', '=', 'views.id')->where('view_translations.locale', \Locales::getCurrent())->orderBy('view_translations.name')->get();

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('table', 'rooms', 'furnitures', 'views'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function store(DataTable $datatable, RentalPayment $rentalPayment, RentalPaymentRequest $request)
    {
        $newRentalPayment = RentalPayment::create($request->all());

        if ($newRentalPayment->id) {
            $prices = [];
            foreach ($request->input('prices') as $room => $rooms) {
                foreach ($rooms as $view => $views) {
                    foreach ($views as $furniture => $price) {
                        array_push($prices, [
                            'rental_payment_id' => $newRentalPayment->id,
                            'room_id' => $room,
                            'view_id' => $view,
                            'furniture_id' => $furniture,
                            'price' => $price ?: 0,
                        ]);
                    }
                }
            }

            RentalPaymentPrices::insert($prices);

            $successMessage = trans(\Locales::getNamespace() . '/forms.storedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityRentalPayments', 1)]);

            $datatable->setup($rentalPayment, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'reset' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.createError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityRentalPayments', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

    public function delete(Request $request)
    {
        $table = $request->input('table') ?: $this->route;

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.delete', compact('table'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function destroy(DataTable $datatable, RentalPayment $rentalPayment, Request $request)
    {
        $count = count($request->input('id'));

        if ($count > 0 && $rentalPayment->destroy($request->input('id'))) {
            $datatable->setup($rentalPayment, $request->input('table'), $this->datatables[$request->input('table')], true);
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
        $rentalPayment = RentalPayment::with('rentalPaymentPrices')->findOrFail($id);

        $prices = [];
        foreach ($rentalPayment->rentalPaymentPrices as $price) {
            $prices[$price->room_id][$price->view_id][$price->furniture_id] = $price->price;
        }

        $table = $request->input('table') ?: $this->route;

        $rooms = Room::withTranslation()->select('room_translations.name', 'rooms.id')->leftJoin('room_translations', 'room_translations.room_id', '=', 'rooms.id')->where('room_translations.locale', \Locales::getCurrent())->orderBy('room_translations.name')->get();
        $furnitures = Furniture::withTranslation()->select('furniture_translations.name', 'furniture.id')->leftJoin('furniture_translations', 'furniture_translations.furniture_id', '=', 'furniture.id')->where('furniture_translations.locale', \Locales::getCurrent())->orderBy('furniture_translations.name')->get();
        $views = View::withTranslation()->select('view_translations.name', 'views.id')->leftJoin('view_translations', 'view_translations.view_id', '=', 'views.id')->where('view_translations.locale', \Locales::getCurrent())->orderBy('view_translations.name')->get();

        $view = \View::make(\Locales::getNamespace() . '.' . $this->route . '.create', compact('rentalPayment', 'prices', 'table', 'rooms', 'furnitures', 'views'));
        $sections = $view->renderSections();
        return response()->json([$sections['content']]);
    }

    public function update(DataTable $datatable, RentalPaymentRequest $request)
    {
        $rentalPayment = RentalPayment::findOrFail($request->input('id'))->first();

        if ($rentalPayment->update($request->all())) {
            RentalPaymentPrices::where('rental_payment_id', $rentalPayment->id)->forceDelete();

            $prices = [];
            foreach ($request->input('prices') as $room => $rooms) {
                foreach ($rooms as $view => $views) {
                    foreach ($views as $furniture => $price) {
                        array_push($prices, [
                            'rental_payment_id' => $rentalPayment->id,
                            'room_id' => $room,
                            'view_id' => $view,
                            'furniture_id' => $furniture,
                            'price' => $price ?: 0,
                        ]);
                    }
                }
            }

            RentalPaymentPrices::insert($prices);

            $successMessage = trans(\Locales::getNamespace() . '/forms.updatedSuccessfully', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityRentalPayments', 1)]);

            $datatable->setup($rentalPayment, $request->input('table'), $this->datatables[$request->input('table')], true);
            $datatables = $datatable->getTables();

            return response()->json($datatables + [
                'success' => $successMessage,
                'closePopup' => true,
            ]);
        } else {
            $errorMessage = trans(\Locales::getNamespace() . '/forms.editError', ['entity' => trans_choice(\Locales::getNamespace() . '/forms.entityRentalPayments', 1)]);
            return response()->json(['errors' => [$errorMessage]]);
        }
    }

}
