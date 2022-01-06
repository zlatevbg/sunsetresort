<?php namespace App\Http\Controllers\Owners;

use App\Http\Controllers\Controller;
use App\Services\DataTable;
use Illuminate\Http\Request;
use App\Models\Owners\Booking;
use App\Models\Owners\Year;
use App\Models\Owners\ExtraService;
use App\Models\Owners\NewsletterTemplates;
use App\Models\Owners\NewsletterTemplateAttachments;
use App\Services\Newsletter as NewsletterService;
use Carbon\Carbon;
use Dompdf\Dompdf;
use File;

class BookingController extends Controller {

    protected $route = 'bookings';
    protected $datatables;
    protected $uploadDirectory = 'newsletter-templates';

    public function __construct()
    {
        $this->datatables = [
            $this->route => [
                'class' => 'table-striped table-bordered table-hover',
                'columns' => [
                    [
                        'id' => 'id',
                        'name' => trans(\Locales::getNamespace() . '/datatables.id'),
                        'search' => true,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'number',
                        'name' => trans(\Locales::getNamespace() . '/datatables.apartment'),
                        'search' => true,
                        'order' => false,
                        'class' => 'text-center vertical-center',
                    ],
                    [
                        'id' => 'owner',
                        'name' => trans(\Locales::getNamespace() . '/datatables.owner'),
                        'search' => true,
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'guests',
                        'name' => trans(\Locales::getNamespace() . '/datatables.guests'),
                        'search' => true,
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'arrive_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.arrival'),
                        'class' => 'text-center vertical-center',
                        'search' => true,
                        'render' => 'sort',
                    ],
                    [
                        'id' => 'departure_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.departure'),
                        'class' => 'text-center vertical-center',
                        'search' => true,
                        'render' => 'sort',
                    ],
                    [
                        'id' => 'adults',
                        'name' => trans(\Locales::getNamespace() . '/datatables.adults'),
                        'class' => 'text-center vertical-center',
                        'order' => false,
                    ],
                    [
                        'id' => 'children',
                        'name' => trans(\Locales::getNamespace() . '/datatables.children'),
                        'class' => 'text-center vertical-center',
                        'order' => false,
                    ],
                ],
                'orderByColumn' => 0,
                'order' => 'desc',
            ],
        ];
    }

    public function index(DataTable $datatable, Booking $booking, Request $request, NewsletterService $newsletterService, $id = null)
    {
        if ($id) {
            $owner = \Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user();

            $booking = $booking->where('bookings.id', $id)->where('bookings.owner_id', $owner->id)->firstOrFail();

            $apartment = $booking->apartment;
            $owners = $apartment->owners;
            $locale = $owner->locale->locale;

            $content = file_get_contents(storage_path('app/templates/booking-form-' . $locale . '.html'));
            if (preg_match('/<body.*?>(.*)<\/body>/sm', $content, $matches)) {
                $content = $matches[1];
                $content = preg_replace('/<p class="text-center"><img width="149" height="100" src="\${LOGO}" alt="Sunset Resort Logo"><\/p>/', '', $content);
                $content = preg_replace('/<h1>BOOKING FORM # <strong>\${ID} - \${OWNER}<\/strong><\/h1>/', '', $content);
            }

            $content = preg_replace('/\${ID}/', $booking->id, $content);
            $content = preg_replace('/\${OWNER}/', $owner->full_name, $content);
            $content = preg_replace('/\${BUILDING}/', trim(preg_replace('/^(.*)\((.*)\)(.*)$/', '$2', $apartment->building->translate($locale)->name)), $content);
            $content = preg_replace('/\${APARTMENT}/', $apartment->number, $content);
            $content = preg_replace('/\${TYPE}/', $apartment->room->translate($locale)->name, $content);
            $content = preg_replace('/\${FURNITURE}/', $apartment->furniture->translate($locale)->name, $content);
            $content = preg_replace('/\${VIEW}/', $apartment->view->translate($locale)->name, $content);

            $contracts = [];
            $mm = [];
            foreach ($apartment->contracts as $contract) {
                array_push($contracts, $contract->rentalContract->translate($locale)->name);
                array_push($mm, \Lang::get('bookings.mmCoveredOptions.' . $contract->rentalContract->mm_covered, [], $locale));
            }

            $year = substr($booking->arrive_at, -4);
            $communalFeeBalance = 0;
            $poolUsageBalance = 0;
            $year = Year::where('year', $year)->first();
            if ($year) {
                $fees = $year->fees->where('room_id', $apartment->room_id)->first();
                if ($fees) {
                    $communalFeeTax = round($fees->annual_communal_tax / 1.95583);
                    $communalFeeBalance = round($communalFeeTax, 2) - round($apartment->communalFeesPayments->where('year_id', $year->id)->sum('amount'), 2);

                    $poolUsageTax = round($fees->pool_tax / 1.95583);
                    $poolUsageBalance = round($poolUsageTax, 2) - round($apartment->poolUsagePayments->where('year_id', $year->id)->sum('amount'), 2);
                }
            }

            $content = preg_replace('/\${RC}/', implode('<br>', $contracts), $content);
            $content = preg_replace('/\${MM}/', implode('<br>', $mm), $content);
            $content = preg_replace('/\${CT}/', $communalFeeBalance ? \Lang::get('bookings.feesOptions.no', [], $locale) : \Lang::get('bookings.feesOptions.yes', [], $locale), $content);
            $content = preg_replace('/\${PU}/', $poolUsageBalance ? \Lang::get('bookings.feesOptions.no', [], $locale) : \Lang::get('bookings.feesOptions.yes', [], $locale), $content);
            $content = preg_replace('/\${KI}/', is_null($booking->kitchen_items) ? '' : \Lang::get('bookings.kitchenItemsOptions.' . $booking->kitchen_items, [], $locale), $content);
            $content = preg_replace('/\${LC}/', is_null($booking->loyalty_card) ? '' : \Lang::get('bookings.loyaltyCardOptions.' . $booking->loyalty_card, [], $locale), $content);
            $content = preg_replace('/\${CC}/', is_null($booking->club_card) ? '' : \Lang::get('bookings.clubCardOptions.' . $booking->club_card, [], $locale), $content);
            $content = preg_replace('/\${EX}/', is_null($booking->exception) ? '' : \Lang::get('bookings.exceptionOptions.' . $booking->exception, [], $locale), $content);
            $content = preg_replace('/\${DP}/', is_null($booking->deposit_paid) ? '' : \Lang::get('bookings.depositPaidOptions.' . $booking->deposit_paid, [], $locale), $content);
            $content = preg_replace('/\${HC}/', is_null($booking->hotel_card) ? '' : \Lang::get('bookings.hotelCardOptions.' . $booking->hotel_card, [], $locale), $content);
            $content = preg_replace('/\${ADATE}/', $booking->arrive_at . ($booking->arrival_time ? ' ' . $booking->arrival_time : ''), $content);
            $content = preg_replace('/\${AFLIGHT}/', $booking->arrival_flight, $content);
            $content = preg_replace('/\${AAIRPORT}/', $booking->arrivalAirport ? $booking->arrivalAirport->translate($locale)->name : '', $content);
            $content = preg_replace('/\${ATRANSFER}/', $booking->arrival_transfer ? \Lang::get('bookings.transferOptions.' . $booking->arrival_transfer, [], $locale) : '', $content);
            $content = preg_replace('/\${DDATE}/', $booking->departure_at . ($booking->departure_time ? ' ' . $booking->departure_time : ''), $content);
            $content = preg_replace('/\${DFLIGHT}/', $booking->departure_flight, $content);
            $content = preg_replace('/\${DAIRPORT}/', $booking->departureAirport ? $booking->departureAirport->translate($locale)->name : '', $content);
            $content = preg_replace('/\${DTRANSFER}/', $booking->departure_transfer ? \Lang::get('bookings.transferOptions.' . $booking->departure_transfer, [], $locale) : '', $content);
            $content = preg_replace('/\${ADULTS}/', $booking->adults->count(), $content);
            $content = preg_replace('/\${CHILDREN}/', $booking->children->count(), $content);

            $guests = max($booking->adults->count(), $booking->children->count());
            if ($guests > 1) {
                $content = preg_replace('/<!--guests-start-->(.*)<!--guests-end-->/s', str_repeat('$1', $guests), $content);
            }

            foreach ($booking->adults as $adult) {
                $content = preg_replace('/\${ADULT}/', $adult->name, $content, 1);
            }

            foreach ($booking->children as $child) {
                $content = preg_replace('/\${CHILD}/', $child->name, $content, 1);
            }

            $content = preg_replace('/\${ADULT}/', '', $content);
            $content = preg_replace('/\${CHILD}/', '', $content);

            $services = ExtraService::withTranslation()->select('extra_service_translations.name', 'extra_services.id')->leftJoin('extra_service_translations', 'extra_service_translations.extra_service_id', '=', 'extra_services.id')->where('extra_service_translations.locale', \Locales::getCurrent())->whereIn('extra_services.id', explode(',', $booking->services))->orderBy('extra_service_translations.name')->get()->pluck('name')->toArray();
            $costs = '<strong>' . \Lang::get('bookings.costsLabel', [], $locale) . '</strong>: ' . \Lang::get('bookings.accommodationCostsLabel', [], $locale) . ' <u>' . \Lang::get('bookings.touristsOptions.' . $booking->accommodation_costs, [], $locale) . '</u>.';
            if ($booking->arrival_transfer || $booking->departure_transfer) {
                $costs .= ' ' . \Lang::get('bookings.transferCostsLabel', [], $locale) . ' <u>' . \Lang::get('bookings.touristsOptions.' . $booking->transfer_costs, [], $locale) . '</u>.';
            }

            if ($services) {
                $costs .= ' ' . \Lang::get('bookings.servicesCostsLabel', [], $locale) . ' <u>' . \Lang::get('bookings.touristsOptions.' . $booking->services_costs, [], $locale) . '</u>.';
            }

            if ($services) {
                $content = preg_replace('/\${SERVICES}/', $costs . '<br><strong>' . \Lang::get('bookings.servicesLabel', [], $locale) . '</strong>: ' . implode(', ', $services), $content);
            } else {
                $content = preg_replace('/<!--services-start-->.*<!--services-end-->/s', '', $content);
            }

            if ($booking->message) {
                $content = preg_replace('/\${REMARKS}/', $booking->message, $content);
            } else {
                $content = preg_replace('/<!--remarks-start-->.*<!--remarks-end-->/s', '', $content);
            }

            $content = preg_replace('/\${DATE}/', '', $content);
            $content = preg_replace('/\${NOTE}/', $contracts ? \Lang::get('bookings.booking-note-pdf', [], $locale) : '', $content);
            $content = preg_replace('/\${USER}/', '', $content);

            $template = NewsletterTemplates::where('template', 'booking-form')->where('locale_id', $owner->locale_id)->firstOrFail();

            $directory = public_path('upload') . DIRECTORY_SEPARATOR . 'newsletter-templates' . DIRECTORY_SEPARATOR . $template->id . DIRECTORY_SEPARATOR;

            $note = '';
            if (!$contracts) {
                $note = \Lang::get('bookings.booking-note-body', [], $locale);
            }

            $body = $newsletterService->replaceHtml($template->body . $note . '<div class="booking-form-content">' . $content . '</div>');

            $body = preg_replace('/\[\[ID\]\]/', $booking->id, $body);

            foreach ($newsletterService->patterns() as $key => $pattern) {
                if (strpos($body, $pattern) !== false) {
                    $body = preg_replace('/' . $pattern . '/', $owner->{$newsletterService->columns()[$key]}, $body);
                }
            }

            $signature = $template->signature->translate($locale)->content;

            $onlineView = '';
            $links = \Lang::get(\Locales::getPublicNamespace() . '/newsletters.links', [], $locale);
            $copyright = \Lang::get(\Locales::getPublicNamespace() . '/newsletters.copyright', [], $locale);
            $disclaimer = \Lang::get(\Locales::getPublicNamespace() . '/newsletters.disclaimer', [], $locale);

            $html = \View::make(env('APP_SKY_SUBDOMAIN') . '.newsletters.templates.default', compact('body', 'signature', 'onlineView', 'links', 'copyright', 'disclaimer'))->with('newsletter', $template)->render();

            if (preg_match('/<body.*?>(.*)<\/body>/sm', $html, $matches)) {
                $html = $matches[1];
            }

            $images = [];
            array_push($images, ['filePath' => \App\Helpers\autover('/img/' . \Locales::getNamespace() . '/logo.png'), 'filename' => 'newsletter-logo.png']);
            /*foreach ($template->images as $image) {
                $path = $directory . \Config::get('upload.imagesDirectory') . DIRECTORY_SEPARATOR . $image->uuid . DIRECTORY_SEPARATOR . $image->file;
                if (strpos($html, '{IMAGE}') !== false) {
                    if (strpos($html, '<td class="leftColumnContent">{IMAGE}</td>') !== false || strpos($html, '<td class="rightColumnContent">{IMAGE}</td>') !== false) {
                        $html = preg_replace('/{IMAGE}/', '<img src="cid:' . $image->file . '" class="columnImage" style="height:auto !important;max-width:260px !important;" />', $html, 1);
                    } else {
                        $html = preg_replace('/{IMAGE}/', '<img src="cid:' . $image->file . '" class="responsiveImage" style="height:auto !important;max-width:' . \Image::make($path)->width() . 'px !important;" />', $html, 1);
                    }

                    array_push($images, $path);
                }
            }*/

            /*$directorySignatures = public_path('upload') . DIRECTORY_SEPARATOR . 'signatures' . DIRECTORY_SEPARATOR . $template->signature->id . DIRECTORY_SEPARATOR;
            foreach ($template->signature->images as $image) {
                $path = $directorySignatures . $image->uuid . DIRECTORY_SEPARATOR . $image->file;
                if (strpos($html, '{SIGNATURE}') !== false) {
                    $html = preg_replace('/{SIGNATURE}/', '<img src="cid:' . $image->file . '" class="responsiveImage" style="height:auto !important;max-width:' . \Image::make($path)->width() . 'px !important;" />', $html, 1);
                    array_push($images, $path);
                }
            }*/

            foreach ($images as $image) {
                $html = preg_replace('/cid:' . preg_quote($image['filename']) . '/', $image['filePath'], $html);
            }

            $metaTitle = $template->subject;
            $metaDescription = $template->subject;

            $breadcrumbs = [];
            $breadcrumbs[] = ['id' => $booking->id, 'slug' => $booking->id, 'name' => $booking->id];

            if ($request->ajax() || $request->wantsJson()) {
                $view = \View::make(\Locales::getNamespace() . '/' . $this->route . '.view', compact('breadcrumbs', 'html', 'booking', 'template'));
                $sections = $view->renderSections();
                return response()->json([$sections['content']]);
            } else {
                return view(\Locales::getNamespace() . '/' . $this->route . '.view', compact('breadcrumbs', 'html', 'booking', 'template', 'metaTitle', 'metaDescription'));
            }
        } else {
            $owner_id = \Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user()->id;

            $bookings = $booking->with(['adultsCount', 'childrenCount'])
            ->selectRaw('bookings.id, bookings.arrive_at, bookings.departure_at, apartments.number, CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as owner, GROUP_CONCAT(booking_guests.name SEPARATOR ", ") as guests')
            ->leftJoin('apartments', function($join) {
                $join->on('apartments.id', '=', 'bookings.apartment_id');
            })
            ->leftJoin('owners', function($join) {
                $join->on('owners.id', '=', 'bookings.owner_id');
            })
            ->leftJoin('booking_guests', function($join) {
                $join->on('booking_guests.booking_id', '=', 'bookings.id');
            })
            ->where('booking_guests.type', '=', 'adult')
            ->where('bookings.owner_id', $owner_id)
            ->groupBy('bookings.id')
            ->orderBy('bookings.id', 'desc')
            ->get();

            $datatable->setup(null, $this->route, $this->datatables[$this->route]);
            $data = [];
            foreach ($bookings as $booking) {
                array_push($data, [
                    'id' => '<a class="js-popup" href="' . \Locales::route($this->route, $booking->id) . '"><span class="glyphicon glyphicon-file glyphicon-left"></span>' . $booking->id . '</a>',
                    'number' => $booking->number,
                    'owner' => $booking->owner,
                    'guests' => $booking->guests,
                    'arrive_at' => [
                        'display' => $booking->arrive_at,
                        'sort' => Carbon::parse($booking->arrive_at)->format('Ymd'),
                    ],
                    'departure_at' => [
                        'display' => $booking->departure_at,
                        'sort' => Carbon::parse($booking->departure_at)->format('Ymd'),
                    ],
                    'adults' => $booking->adultsCount,
                    'children' => $booking->childrenCount,
                ]);
            }
            $datatable->setOption('data', $data);

            $datatables = $datatable->getTables();

            return view(\Locales::getNamespace() . '/' . $this->route . '.index', compact('datatables'));
        }
    }

    public function download(Booking $booking, Request $request, $id)
    {
        $owner = \Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user();

        $booking = $booking->where('bookings.id', $id)->where('bookings.owner_id', $owner->id)->firstOrFail();

        $apartment = $booking->apartment;
        $owners = $apartment->owners;
        $locale = $owner->locale->locale;

        define("DOMPDF_ENABLE_AUTOLOAD", false);
        $dompdf = new Dompdf();
        $dompdf->set_paper('A4');
        $dompdf->set_option('isFontSubsettingEnabled', true);

        $html = file_get_contents(storage_path('app/templates/booking-form-' . $locale . '.html'));

        $html = preg_replace('/\${LOGO}/', storage_path('app/templates/logo.png'), $html);
        $html = preg_replace('/\${ID}/', $booking->id, $html);
        $html = preg_replace('/\${OWNER}/', $owner->full_name, $html);
        $html = preg_replace('/\${BUILDING}/', trim(preg_replace('/^(.*)\((.*)\)(.*)$/', '$2', $apartment->building->translate($locale)->name)), $html);
        $html = preg_replace('/\${APARTMENT}/', $apartment->number, $html);
        $html = preg_replace('/\${TYPE}/', $apartment->room->translate($locale)->name, $html);
        $html = preg_replace('/\${FURNITURE}/', $apartment->furniture->translate($locale)->name, $html);
        $html = preg_replace('/\${VIEW}/', $apartment->view->translate($locale)->name, $html);

        $contracts = [];
        $mm = [];
        foreach ($apartment->contracts as $contract) {
            array_push($contracts, $contract->rentalContract->translate($locale)->name);
            array_push($mm, \Lang::get('bookings.mmCoveredOptions.' . $contract->rentalContract->mm_covered, [], $locale));
        }

        $year = substr($booking->arrive_at, -4);
        $communalFeeBalance = 0;
        $poolUsageBalance = 0;
        $year = Year::where('year', $year)->first();
        if ($year) {
            $fees = $year->fees->where('room_id', $apartment->room_id)->first();
            if ($fees) {
                $communalFeeTax = round($fees->annual_communal_tax / 1.95583);
                $communalFeeBalance = round($communalFeeTax, 2) - round($apartment->communalFeesPayments->where('year_id', $year->id)->sum('amount'), 2);

                $poolUsageTax = round($fees->pool_tax / 1.95583);
                $poolUsageBalance = round($poolUsageTax, 2) - round($apartment->poolUsagePayments->where('year_id', $year->id)->sum('amount'), 2);
            }
        }

        $html = preg_replace('/\${RC}/', implode('<br>', $contracts), $html);
        $html = preg_replace('/\${MM}/', implode('<br>', $mm), $html);
        $html = preg_replace('/\${CT}/', $communalFeeBalance ? \Lang::get('bookings.feesOptions.no', [], $locale) : \Lang::get('bookings.feesOptions.yes', [], $locale), $html);
        $html = preg_replace('/\${PU}/', $poolUsageBalance ? \Lang::get('bookings.feesOptions.no', [], $locale) : \Lang::get('bookings.feesOptions.yes', [], $locale), $html);
        $html = preg_replace('/\${KI}/', is_null($booking->kitchen_items) ? '' : \Lang::get('bookings.kitchenItemsOptions.' . $booking->kitchen_items, [], $locale), $html);
        $html = preg_replace('/\${LC}/', is_null($booking->loyalty_card) ? '' : \Lang::get('bookings.loyaltyCardOptions.' . $booking->loyalty_card, [], $locale), $html);
        $html = preg_replace('/\${CC}/', is_null($booking->club_card) ? '' : \Lang::get('bookings.clubCardOptions.' . $booking->club_card, [], $locale), $html);
        $html = preg_replace('/\${EX}/', is_null($booking->exception) ? '' : \Lang::get('bookings.exceptionOptions.' . $booking->exception, [], $locale), $html);
        $html = preg_replace('/\${DP}/', is_null($booking->deposit_paid) ? '' : \Lang::get('bookings.depositPaidOptions.' . $booking->deposit_paid, [], $locale), $html);
        $html = preg_replace('/\${HC}/', is_null($booking->hotel_card) ? '' : \Lang::get('bookings.hotelCardOptions.' . $booking->hotel_card, [], $locale), $html);
        $html = preg_replace('/\${ADATE}/', $booking->arrive_at . ($booking->arrival_time ? ' ' . $booking->arrival_time : ''), $html);
        $html = preg_replace('/\${AFLIGHT}/', $booking->arrival_flight, $html);
        $html = preg_replace('/\${AAIRPORT}/', $booking->arrivalAirport ? $booking->arrivalAirport->translate($locale)->name : '', $html);
        $html = preg_replace('/\${ATRANSFER}/', $booking->arrival_transfer ? \Lang::get('bookings.transferOptions.' . $booking->arrival_transfer, [], $locale) : '', $html);
        $html = preg_replace('/\${DDATE}/', $booking->departure_at . ($booking->departure_time ? ' ' . $booking->departure_time : ''), $html);
        $html = preg_replace('/\${DFLIGHT}/', $booking->departure_flight, $html);
        $html = preg_replace('/\${DAIRPORT}/', $booking->departureAirport ? $booking->departureAirport->translate($locale)->name : '', $html);
        $html = preg_replace('/\${DTRANSFER}/', $booking->departure_transfer ? \Lang::get('bookings.transferOptions.' . $booking->departure_transfer, [], $locale) : '', $html);
        $html = preg_replace('/\${ADULTS}/', $booking->adults->count(), $html);
        $html = preg_replace('/\${CHILDREN}/', $booking->children->count(), $html);

        $guests = max($booking->adults->count(), $booking->children->count());
        if ($guests > 1) {
            $html = preg_replace('/<!--guests-start-->(.*)<!--guests-end-->/s', str_repeat('$1', $guests), $html);
        }

        foreach ($booking->adults as $adult) {
            $html = preg_replace('/\${ADULT}/', $adult->name, $html, 1);
        }

        foreach ($booking->children as $child) {
            $html = preg_replace('/\${CHILD}/', $child->name, $html, 1);
        }

        $html = preg_replace('/\${ADULT}/', '', $html);
        $html = preg_replace('/\${CHILD}/', '', $html);

        $services = ExtraService::withTranslation()->select('extra_service_translations.name', 'extra_services.id')->leftJoin('extra_service_translations', 'extra_service_translations.extra_service_id', '=', 'extra_services.id')->where('extra_service_translations.locale', \Locales::getCurrent())->whereIn('extra_services.id', explode(',', $booking->services))->orderBy('extra_service_translations.name')->get()->pluck('name')->toArray();
        $costs = '<strong>' . \Lang::get('bookings.costsLabel', [], $locale) . '</strong>: ' . \Lang::get('bookings.accommodationCostsLabel', [], $locale) . ' <u>' . \Lang::get('bookings.touristsOptions.' . $booking->accommodation_costs, [], $locale) . '</u>.';
        if ($booking->arrival_transfer || $booking->departure_transfer) {
            $costs .= ' ' . \Lang::get('bookings.transferCostsLabel', [], $locale) . ' <u>' . \Lang::get('bookings.touristsOptions.' . $booking->transfer_costs, [], $locale) . '</u>.';
        }

        if ($services) {
            $costs .= ' ' . \Lang::get('bookings.servicesCostsLabel', [], $locale) . ' <u>' . \Lang::get('bookings.touristsOptions.' . $booking->services_costs, [], $locale) . '</u>.';
        }

        if ($services) {
            $html = preg_replace('/\${SERVICES}/', $costs . '<br><strong>' . \Lang::get('bookings.servicesLabel', [], $locale) . '</strong>: ' . implode(', ', $services), $html);
        } else {
            $html = preg_replace('/<!--services-start-->.*<!--services-end-->/s', '', $html);
        }

        if ($booking->message) {
            $html = preg_replace('/\${REMARKS}/', $booking->message, $html);
        } else {
            $html = preg_replace('/<!--remarks-start-->.*<!--remarks-end-->/s', '', $html);
        }

        $html = preg_replace('/\${DATE}/', '', $html);
        $html = preg_replace('/\${NOTE}/', $contracts ? \Lang::get('bookings.booking-note-pdf', [], $locale) : '', $html);
        $html = preg_replace('/\${USER}/', '', $html);

        $dompdf->load_html($html);
        $dompdf->render();
        $dompdf->stream('booking-form.pdf');

        /*$data = $dompdf->output();
        return response()->stream(function () use ($data) {
            echo $data;
        }, 200, [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Expires' => '0',
            'Pragma' => 'no-cache',
            'Content-Type' => 'application/pdf',
            'Content-Length' => strlen($data),
            'Content-Disposition' => 'attachment; filename="booking-form.pdf"'
        ]);*/
    }

    public function downloadAttachments(NewsletterTemplateAttachments $attachments, $uuid)
    {
        $file = $attachments->select('newsletter_template_attachments.template_id', 'newsletter_template_attachments.uuid', 'newsletter_template_attachments.file')->where('newsletter_template_attachments.uuid', $uuid)->firstOrFail();

        return response()->download(public_path('upload') . DIRECTORY_SEPARATOR . $this->uploadDirectory . DIRECTORY_SEPARATOR . $file->template_id . DIRECTORY_SEPARATOR . \Config::get('upload.attachmentsDirectory') . DIRECTORY_SEPARATOR . $file->uuid . DIRECTORY_SEPARATOR . $file->file);
    }
}
