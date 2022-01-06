<?php namespace App\Http\Controllers\Owners;

use App\Http\Controllers\Controller;
use App\Services\DataTable;
use App\Services\Newsletter as NewsletterService;
use Illuminate\Http\Request;
use App\Models\Owners\Apartment;
use App\Models\Owners\CouncilTax;
use App\Models\Owners\Newsletter;
use App\Models\Owners\BankAccount;
use App\Models\Owners\RentalRatesPeriod;
use App\Models\Owners\KeyLog;
use App\Models\Owners\NewsletterAttachments;
use App\Models\Owners\NewsletterAttachmentsApartment;
use App\Models\Owners\NewsletterAttachmentsOwner;
use Carbon\Carbon;
use File;
use Illuminate\Support\Str;

class NewsletterController extends Controller {

    protected $route = 'newsletters';
    protected $datatables;
    protected $uploadDirectory = 'newsletters';
    protected $owner;

    public function __construct()
    {
        $this->owner = \Auth::guard(env('APP_OWNERS_SUBDOMAIN'))->user();

        $this->datatables = [
            $this->route => [
                'class' => 'table-striped table-bordered table-hover',
                'columns' => [
                    [
                        'id' => 'subject',
                        'name' => trans(\Locales::getNamespace() . '/datatables.subject'),
                        'search' => true,
                        'order' => false,
                        'class' => 'vertical-center',
                    ],
                    [
                        'id' => 'sender',
                        'name' => trans(\Locales::getNamespace() . '/datatables.sender'),
                        'search' => true,
                        'order' => false,
                        'class' => 'vertical-center text-nowrap',
                    ],
                    [
                        'id' => 'sent_at',
                        'name' => trans(\Locales::getNamespace() . '/datatables.dateSent'),
                        'class' => 'vertical-center text-nowrap sent-at',
                        'search' => true,
                        'render' => 'sort',
                    ],
                ],
                'orderByColumn' => 2,
                'order' => 'desc',
            ],
        ];
    }

    public function index(DataTable $datatable, Newsletter $newsletter, NewsletterService $newsletterService, Request $request, $id = null, $apartment = null)
    {
        if ($id) {
            $attachmentsApartment = [];
            $attachmentsOwner = [];

            if ($apartment) {
                $apartment = Apartment::findOrFail($apartment);

                $newsletter = $newsletter->with(['archives' => function ($query) use ($apartment) {
                    $query->where('newsletter_archive.apartment_id', $apartment->id);
                }])->select('newsletters.*', 'newsletter_archive.apartment_id')->join('newsletter_archive', function ($join) use ($apartment) {
                    $join->on('newsletters.id', '=', 'newsletter_archive.newsletter_id')->where('newsletter_archive.owner_id', '=', $this->owner->id)->where('newsletter_archive.apartment_id', '=', $apartment->id);
                })->findOrFail($id);

                if (!\Auth::check()) {
                    $newsletter->archives()->where('owner_id', $this->owner->id)->where('apartment_id', $apartment->id)->update(['is_read' => 1]);
                }
            } else {
                $newsletter = $newsletter->with(['archives' => function ($query) {
                    $query->where('newsletter_archive.owner_id', $this->owner->id);
                }])->select('newsletters.*', 'newsletter_archive.apartment_id')->join('newsletter_archive', function ($join) {
                    $join->on('newsletters.id', '=', 'newsletter_archive.newsletter_id')->where('newsletter_archive.owner_id', '=', $this->owner->id);
                })->where('newsletters.id', $id)->get();

                if ($newsletter->count() == 1) {
                    $newsletter = $newsletter->first();
                    if (!\Auth::check()) {
                        $newsletter->archives()->where('owner_id', $this->owner->id)->update(['is_read' => 1]);
                    }
                } else {
                    abort(404);
                }
            }

            $metaTitle = $newsletter->subject;
            $metaDescription = $newsletter->teaser;

            $breadcrumbs = [];
            $breadcrumbs[] = ['id' => $newsletter->id, 'slug' => $newsletter->id, 'name' => $newsletter->subject];

            if ($newsletter->merge_by) {
                if ($newsletter->attachmentsApartment->count()) {
                    $file = Str::lower($newsletter->apartment->number);
                    $attachmentsApartment = $newsletter->attachmentsApartment->filter(function ($value, $key) use ($file) {
                        return File::name($value->file) == $file;
                    });
                }

                if ($newsletter->attachmentsOwner->count()) {
                    $file = Str::lower($newsletter->apartment->number . '-' . $this->owner->id);
                    $attachmentsOwner = $newsletter->attachmentsOwner->filter(function ($value, $key) use ($file) {
                        return File::name($value->file) == $file;
                    });
                }

                $merge = $newsletter->merge;
                $mergeApartments = explode('|', Str::lower($merge->first()->merge));
                if ($newsletter->merge_by == 2) { // owners
                    $mergeOwners = explode('|', Str::lower($merge->get(1)->merge));
                }

                $index = false;
                foreach ($mergeApartments as $key => $value) {
                    if (Str::lower($newsletter->apartment->number) == $value) {
                        if ($newsletter->merge_by == 2) { // owners
                            if ($mergeOwners[$key] == $this->owner->id) {
                                $index = $key;
                                break;
                            }
                        } else {
                            $index = $key;
                            break;
                        }
                    }
                }

                if ($index !== false) {
                    $newsletter->body = preg_replace('/{MERGE_APARTMENT}/', $newsletter->apartment->number, $newsletter->body);
                    if ($newsletter->merge_by == 2) { // owners
                        $newsletter->body = preg_replace('/{MERGE_OWNER}/', $this->owner->full_name, $newsletter->body);
                    }

                    foreach ($merge->slice($newsletter->merge_by) as $key => $value) {
                        $values = explode('|', $value->merge);
                        $value = '';
                        if (isset($values[$index])) {
                            $value = $values[$index];
                        }
                        $newsletter->body = preg_replace('/{MERGE}/', $value, $newsletter->body, 1);
                    }
                }
            }

            $newsletter->body = \HTML::image(asset('img/' . env('APP_OWNERS_SUBDOMAIN') . '/newsletter-logo.png')) . $newsletter->body;

            foreach ($newsletterService->patterns() as $key => $pattern) {
                if (strpos($newsletter->body, $pattern) !== false) {
                    $newsletter->body = preg_replace('/' . $pattern . '/', $this->owner->{$newsletterService->columns()[$key]}, $newsletter->body);
                }
            }

            $language = \Locales::getPublicLocales()->filter(function ($value, $key) use ($newsletter) {
                return $value->id == $newsletter->locale_id;
            })->first()->locale;

            if (!count($newsletter->archives->first()->merge)) {
                if (in_array($newsletter->template, ['newsletter-mm-fees', 'newsletter-mm-fees-first-reminder', 'newsletter-mm-fees-second-reminder', 'newsletter-mm-fees-final-reminder'])) {
                    $apartments = Apartment::select('id', 'mm_tax_formula', 'apartment_area', 'common_area', 'balcony_area', 'extra_balcony_area', 'total_area', 'building_id', 'room_id')
                    ->with(['buildingMM' => function ($query) use ($newsletter) {
                        // $query->where('building_mm.year_id', $newsletter->year->id); // I need all BuildingMM years, not just the selected one!!!
                    }, 'mmFeesPayments', 'contracts' => function ($query) use ($newsletter) {
                        if ($newsletter->year->year < date('Y')) {
                            $query->withTrashed();
                        }
                    }, 'contracts.contractYears' => function ($query) use ($newsletter) {
                        $query->where('contract_years.year', $newsletter->year->year);

                        if ($newsletter->year->year < date('Y')) {
                            $query->withTrashed();
                        }
                    }, 'contracts.rentalContract' => function ($query) {
                        $query->withTrashed();
                    }, 'rooms'])
                    ->where('id', $newsletter->apartment->id)
                    ->groupBy('id')
                    ->get();

                    $apartmentsMM = DataTable::calculateMmFees($apartments, 'due-by-owner', $newsletter->year->year)->keyBy('id');
                } elseif (in_array($newsletter->template, ['newsletter-rental-payment-confirmation', 'newsletter-rental-payment-income-tax-only'])) {
                    $apartments = Apartment::with(['mmFeesPayments', 'buildingMM' => function ($query) use ($newsletter) {
                        // $query->where('building_mm.year_id', $newsletter->year->id); // I need all BuildingMM years, not just the selected one!!!
                    }, 'contracts' => function ($query) use ($newsletter) {
                        if ($newsletter->year->year < date('Y')) {
                            $query->withTrashed();
                        }
                    }, 'contracts.contractYears' => function ($query) use ($newsletter) {
                        $query->where('contract_years.year', $newsletter->year->year);

                        if ($newsletter->year->year < date('Y')) {
                            $query->withTrashed();
                        }
                    }, 'contracts.rentalContract' => function ($query) {
                        $query->withTrashed()->withTranslation();
                    }, 'contracts.contractYears.payments', 'contracts.contractYears.deductions', 'owners' => function ($query) use ($newsletter) {
                        if ($newsletter->year->year < date('Y')) {
                            $query->withTrashed();
                        }
                    }, 'rooms'])->selectRaw('apartments.id, apartments.number, apartments.mm_tax_formula, apartments.apartment_area, apartments.common_area, apartments.balcony_area, apartments.extra_balcony_area, apartments.total_area, apartments.building_id, apartments.room_id, project_translations.slug AS projectSlug, room_translations.slug AS roomSlug, view_translations.slug AS viewSlug')->leftJoin('project_translations', function($join) use ($language) {
                        $join->on('project_translations.project_id', '=', 'apartments.project_id')->where('project_translations.locale', '=', $language);
                    })->leftJoin('room_translations', function($join) use ($language) {
                        $join->on('room_translations.room_id', '=', 'apartments.room_id')->where('room_translations.locale', '=', $language);
                    })->leftJoin('view_translations', function($join) use ($language) {
                        $join->on('view_translations.view_id', '=', 'apartments.view_id')->where('view_translations.locale', '=', $language);
                    })->where('apartments.id', $newsletter->apartment->id)->get();

                    $apartmentsMM = DataTable::calculateRentalOptions($apartments, $newsletter->year->year, 'rental', 'paid', null, false)->keyBy('id');
                    if ($newsletter->template == 'newsletter-rental-payment-confirmation') {
                        $apartmentsMM = $apartmentsMM->filter(function ($item) { return $item->payments > 0; });
                    } elseif ($newsletter->template == 'newsletter-rental-payment-income-tax-only') {
                        $apartmentsMM = $apartmentsMM->filter(function ($item) { return $item->payments <= 0; });
                    }
                } elseif ($newsletter->template == 'newsletter-mm-payment-confirmation') {
                    $apartments = Apartment::select('id', 'mm_tax_formula', 'apartment_area', 'common_area', 'balcony_area', 'extra_balcony_area', 'total_area', 'building_id', 'room_id')
                    ->with(['buildingMM' => function ($query) use ($newsletter) {
                        // $query->where('building_mm.year_id', $newsletter->year->id); // I need all BuildingMM years, not just the selected one!!!
                    }, 'mmFeesPayments', 'contracts' => function ($query) use ($newsletter) {
                        if ($newsletter->year->year < date('Y')) {
                            $query->withTrashed();
                        }
                    }, 'contracts.contractYears' => function ($query) use ($newsletter) {
                        $query->where('contract_years.year', $newsletter->year->year);

                        if ($contractYear->year < date('Y')) {
                            $query->withTrashed();
                        }
                    }, 'contracts.rentalContract' => function ($query) {
                        $query->withTrashed();
                    }, 'rooms'])
                    ->where('id', $newsletter->apartment->id)
                    ->groupBy('id')
                    ->get();

                    $apartmentsMM = DataTable::calculateMmFees($apartments, 'paid-by-rental', $newsletter->year->year)->keyBy('id');
                } elseif ($newsletter->template == 'newsletter-occupancy') {
                    $apartments = Apartment::with(['mmFeesPayments', 'buildingMM' => function ($query) use ($newsletter) {
                        // $query->where('building_mm.year_id', $newsletter->year->id); // I need all BuildingMM years, not just the selected one!!!
                    }, 'contracts' => function ($query) use ($newsletter) {
                        if ($newsletter->year->year < date('Y')) {
                            $query->withTrashed();
                        }
                    }, 'contracts.contractYears' => function ($query) use ($newsletter) {
                        $query->where('contract_years.year', $newsletter->year->year);

                        if ($newsletter->year->year < date('Y')) {
                            $query->withTrashed();
                        }
                    }, 'contracts.rentalContract' => function ($query) {
                        $query->withTrashed()->withTranslation();
                    }, 'contracts.contractYears.payments', 'contracts.contractYears.deductions', 'owners' => function ($query) use ($newsletter) {
                        if ($newsletter->year->year < date('Y')) {
                            $query->withTrashed();
                        }
                    }, 'rooms'])->selectRaw('apartments.id, apartments.mm_tax_formula, apartments.apartment_area, apartments.common_area, apartments.balcony_area, apartments.extra_balcony_area, apartments.total_area, apartments.building_id, apartments.room_id, project_translations.slug AS projectSlug, room_translations.slug AS roomSlug, view_translations.slug AS viewSlug')->leftJoin('project_translations', function($join) use ($language) {
                        $join->on('project_translations.project_id', '=', 'apartments.project_id')->where('project_translations.locale', '=', $language);
                    })->leftJoin('room_translations', function($join) use ($language) {
                        $join->on('room_translations.room_id', '=', 'apartments.room_id')->where('room_translations.locale', '=', $language);
                    })->leftJoin('view_translations', function($join) use ($language) {
                        $join->on('view_translations.view_id', '=', 'apartments.view_id')->where('view_translations.locale', '=', $language);
                    })->where('apartments.id', $newsletter->apartment->id)->get();

                    $apartmentsMM = DataTable::calculateRentalOptions($apartments, $newsletter->year->year, 'rental', 'due')->keyBy('id');
                }
            }

            if (in_array($newsletter->template, ['newsletter-mm-fees', 'newsletter-mm-fees-first-reminder', 'newsletter-mm-fees-second-reminder', 'newsletter-mm-fees-final-reminder', 'newsletter-mm-payment-confirmation'])) {
                $newsletter->body = preg_replace('/{MERGE_APARTMENT}/', $newsletter->apartment->number, $newsletter->body);
                $newsletter->body = preg_replace('/\[\[MERGE_BLOCK\]\]/', $newsletter->apartment->building->translate($language)->name, $newsletter->body);

                if (count($newsletter->archives->first()->merge)) {
                    foreach ($newsletter->archives->first()->merge as $merge) {
                        $newsletter->body = preg_replace('/\[\[' . $merge->key . '\]\]/', $merge->value, $newsletter->body);
                    }
                } else {
                    $newsletter->body = preg_replace('/\[\[MERGE_YEAR\]\]/', $apartmentsMM[$newsletter->apartment->id]->mm_for_year_name, $newsletter->body);
                    $newsletter->body = preg_replace('/\[\[MERGE_DUE_YEAR\]\]/', substr($apartmentsMM[$newsletter->apartment->id]->buildingMM->where('year_id', $newsletter->year->id)->first()->deadline_at, -4), $newsletter->body);
                    $newsletter->body = preg_replace('/\[\[MERGE_MM_AMOUNT\]\]/', (isset($apartmentsMM[$newsletter->apartment->id]) ? number_format(ceil((float) str_replace(',', '', $apartmentsMM[$newsletter->apartment->id]->amount) * 1.95583), 2) : ''), $newsletter->body);
                    $newsletter->body = preg_replace('/\[\[MERGE_MM_FEE\]\]/', number_format(isset($apartmentsMM[$newsletter->apartment->id]) ? $apartmentsMM[$newsletter->apartment->id]->buildingMM->where('year_id', $newsletter->year->id)->first()->mm_tax : 0, 2), $newsletter->body);

                    if ($newsletter->template == 'newsletter-mm-fees-final-reminder') {
                        if (isset($apartmentsMM[$newsletter->apartment->id])) {
                            $cleanAmount = ceil((float) str_replace(',', '', $apartmentsMM[$newsletter->apartment->id]->amount) * 1.95583);
                        }

                        $newsletter->body = preg_replace('/\[\[MERGE_INTEREST\]\]/', (isset($apartmentsMM[$newsletter->apartment->id]) ? number_format($cleanAmount * 1.4 - $cleanAmount, 2) : ''), $newsletter->body);
                        $newsletter->body = preg_replace('/\[\[MERGE_TOTAL\]\]/', (isset($apartmentsMM[$newsletter->apartment->id]) ? number_format($cleanAmount * 1.4, 2) : ''), $newsletter->body);
                    }
                }
            } elseif (in_array($newsletter->template, ['newsletter-rental-payment-confirmation', 'newsletter-rental-payment-income-tax-only'])) {
                $newsletter->body = preg_replace('/{MERGE_APARTMENT}/', $newsletter->apartment->number, $newsletter->body);
                $newsletter->body = preg_replace('/\[\[MERGE_BLOCK\]\]/', $newsletter->apartment->building->translate($language)->name, $newsletter->body);

                if (count($newsletter->archives->first()->merge)) {
                    foreach ($newsletter->archives->first()->merge as $merge) {
                        $newsletter->body = preg_replace('/\[\[' . $merge->key . '\]\]/', $merge->value, $newsletter->body);
                    }
                } else {
                    $newsletter->body = preg_replace('/\[\[MERGE_YEAR\]\]/', $newsletter->year->year, $newsletter->body);
                    $newsletter->body = preg_replace('/\[\[MERGE_AMOUNT\]\]/', ($newsletter->template == 'newsletter-rental-payment-confirmation' ? $apartmentsMM[$newsletter->apartment->id]->paymentsValue : number_format($apartmentsMM[$newsletter->apartment->id]->rentAmount - $apartmentsMM[$newsletter->apartment->id]->tax, 2)), $newsletter->body);
                    $newsletter->body = preg_replace('/\[\[MERGE_WT_AMOUNT\]\]/', $apartmentsMM[$newsletter->apartment->id]->taxValue, $newsletter->body);
                }
            } elseif ($newsletter->template == 'newsletter-bank-account-details') {
                $newsletter->body = preg_replace('/{MERGE_APARTMENT}/', $newsletter->apartment->number, $newsletter->body);

                if (count($newsletter->archives->first()->merge)) {
                    foreach ($newsletter->archives->first()->merge as $merge) {
                        $newsletter->body = preg_replace('/\[\[' . $merge->key . '\]\]/', $merge->value, $newsletter->body);
                    }
                } else {
                    $bank = BankAccount::select('bank_accounts.bank_iban', 'bank_accounts.bank_bic', 'bank_accounts.bank_name', 'bank_accounts.bank_beneficiary')->join('ownership', 'ownership.bank_account_id', '=', 'bank_accounts.id')->where('bank_accounts.owner_id', $this->owner->id)->where('ownership.owner_id', $this->owner->id)->where('ownership.apartment_id', $newsletter->apartment->id)->whereNull('ownership.deleted_at')->first();

                    $newsletter->body = preg_replace('/\[\[MERGE_BANK\]\]/', $bank->bank_name, $newsletter->body);
                    $newsletter->body = preg_replace('/\[\[MERGE_BENEFICIARY\]\]/', $bank->bank_beneficiary, $newsletter->body);
                    $newsletter->body = preg_replace('/\[\[MERGE_IBAN\]\]/', $bank->bank_iban, $newsletter->body);
                    $newsletter->body = preg_replace('/\[\[MERGE_BIC\]\]/', $bank->bank_bic, $newsletter->body);
                }
            } elseif ($newsletter->template == 'newsletter-council-tax-letter') {
                $total = 0;
                $rows = '';
                $taxes = CouncilTax::selectRaw('owners.bulstat, owners.tax_pin, CONCAT(COALESCE(owners.first_name, ""), " ", COALESCE(owners.last_name, "")) as full_name, council_tax.tax')->leftJoin('owners', 'owners.id', '=', 'council_tax.owner_id')->join('ownership', 'ownership.owner_id', '=', 'owners.id')->where('owners.is_active', 1)->whereNull('ownership.deleted_at')->where('council_tax.apartment_id', $newsletter->apartment->id)->where('ownership.apartment_id', $newsletter->apartment->id)->get();
                foreach ($taxes as $tax) {
                    $total += $tax->tax;

                    $rows .= '<tr>
                        <td class="text-left" style="border-color:rgb(221, 221, 221); vertical-align:middle">' . $tax->full_name . '</td>
                        <td class="text-center" style="border-color:rgb(221, 221, 221); vertical-align:middle">' . $tax->bulstat . '</td>
                        <td class="text-center" style="border-color:rgb(221, 221, 221); vertical-align:middle">' . $tax->tax_pin . '</td>
                        <td class="text-right" style="border-color:rgb(221, 221, 221); vertical-align:middle; white-space: nowrap;">' . number_format($tax->tax, 2) . ' лв.</td>
                    </tr>';
                }

                $newsletter->body = preg_replace('/\[\[MERGE_APARTMENT\]\]/', $newsletter->apartment->number, $newsletter->body);
                $newsletter->body = preg_replace('/\[\[MERGE_DATE\]\]/', Carbon::parse($taxes->first()->checked_at)->format('d.m.Y'), $newsletter->body);
                $newsletter->body = preg_replace('/\[\[MERGE_TOTAL\]\]/', number_format($total, 2) . ' лв.', $newsletter->body);
                $newsletter->body = preg_replace('/<tbody>(.*?)<\/tbody>/s', '<tbody>' . $rows . '</tbody>', $newsletter->body, 1);
            } elseif ($newsletter->template == 'newsletter-occupancy') {
                $apartmentSlugs = $newsletter->apartment->selectRaw('project_translations.slug AS projectSlug, room_translations.slug AS roomSlug, view_translations.slug AS viewSlug')->leftJoin('project_translations', function($join) use ($language) {
                    $join->on('project_translations.project_id', '=', 'apartments.project_id')->where('project_translations.locale', '=', $language);
                })->leftJoin('room_translations', function($join) use ($language) {
                    $join->on('room_translations.room_id', '=', 'apartments.room_id')->where('room_translations.locale', '=', $language);
                })->leftJoin('view_translations', function($join) use ($language) {
                    $join->on('view_translations.view_id', '=', 'apartments.view_id')->where('view_translations.locale', '=', $language);
                })->where('apartments.id', $newsletter->apartment->id)->firstOrFail();

                $newsletter->body = preg_replace('/{MERGE_APARTMENT}/', $newsletter->apartment->number, $newsletter->body);
                $newsletter->body = preg_replace('/\[\[MERGE_YEAR\]\]/', $newsletter->year->year, $newsletter->body);

                $rentAmount = 0;
                $rows = '';
                $rentalRates = RentalRatesPeriod::with('rates')->whereYear('dfrom', '=', $newsletter->year->year)->get();
                foreach ($rentalRates as $period) {
                    $nights = KeyLog::whereBetween('occupied_at', [Carbon::parse($period->dfrom), Carbon::parse($period->dto)])->where('apartment_id', $newsletter->apartment->id)->count();

                    if ($period->type == 'personal-usage') {
                        $nights = $nights - 53; // personal usage period
                        $newsletter->body = preg_replace('/\[\[MERGE_PERSONAL_USAGE_PERIOD\]\]/', $period->dfrom . ' - ' . $period->dto, $newsletter->body);
                    }

                    if ($nights < 0) {
                        $nights = 0;
                    }

                    $rates = $period->rates->where('project', $apartmentSlugs->projectSlug)->where('room', $apartmentSlugs->roomSlug)->where('view', $apartmentSlugs->viewSlug)->first();
                    if ($rates) {
                        $rate = $rates->rate;
                        if ($nights > 0) {
                            $rentAmount += $nights * $rate;
                        }
                    } else {
                        $rate = 0;
                    }

                    $rows .= '<tr>
                        <td class="text-left" style="border-color:rgb(221, 221, 221); vertical-align:middle">' . $period->dfrom . ' - ' . $period->dto . ($period->type == 'personal-usage' ? ' *' : '') . '</td>
                        <td class="text-right" style="border-color:rgb(221, 221, 221); vertical-align:middle">&euro; ' . $rate . '</td>
                        <td class="text-right" style="border-color:rgb(221, 221, 221); vertical-align:middle">' . $nights . '</td>
                        <td class="text-right" style="border-color:rgb(221, 221, 221); vertical-align:middle;white-space:nowrap;">&euro; ' . number_format($nights * $rate, 2) . '</td>
                    </tr>';
                }

                $rows .= '<tr>
                    <td colspan="3" class="text-left" style="border-color:rgb(221, 221, 221); vertical-align:middle;font-weight:bold;">' . trans(\Locales::getNamespace() . '/messages.total') . '</td>
                    <td class="text-right" style="border-color:rgb(221, 221, 221); vertical-align:middle;white-space:nowrap;font-weight:bold;">&euro; ' . number_format($rentAmount, 2) . '</td>
                </tr>';

                $newsletter->body = preg_replace('/<tbody>(.*?)<\/tbody>/s', '<tbody>' . $rows . '</tbody>', $newsletter->body, 1);

                if (count($newsletter->archives->first()->merge)) {
                    foreach ($newsletter->archives->first()->merge as $merge) {
                        $newsletter->body = preg_replace('/\[\[' . $merge->key . '\]\]/', $merge->value, $newsletter->body);
                    }
                } else {
                    $apartment = $apartmentsMM[$newsletter->apartment->id];
                    $newsletter->body = preg_replace('/\[\[MERGE_MM_FEE\]\]/', number_format($apartment->mmFee, 2), $newsletter->body);
                    $newsletter->body = preg_replace('/\[\[MERGE_RENT_AMOUNT\]\]/', number_format($rentAmount, 2), $newsletter->body);
                    $newsletter->body = preg_replace('/\[\[MERGE_INCOME_AMOUNT\]\]/', number_format($apartment->mmFee + $rentAmount, 2), $newsletter->body);
                    $newsletter->body = preg_replace('/\[\[MERGE_WT\]\]/', number_format($apartment->tax, 2), $newsletter->body);
                    $newsletter->body = preg_replace('/\[\[MERGE_MM_YEAR\]\]/', $apartment->mm_for_year, $newsletter->body);
                    $newsletter->body = preg_replace('/\[\[MERGE_DEDUCTIONS\]\]/', number_format($apartment->deductions, 2), $newsletter->body);
                    $newsletter->body = preg_replace('/\[\[MERGE_TOTAL_EXPENDITURE\]\]/', number_format($apartment->tax + $apartment->mmFee + $apartment->deductions, 2), $newsletter->body);
                    $newsletter->body = preg_replace('/\[\[MERGE_NET_RENT\]\]/', number_format($apartment->netRent, 2), $newsletter->body);
                }
            }

            $uploadDirectory = asset('upload/' . $this->uploadDirectory . '/' . $newsletter->id . '/' . \Config::get('upload.imagesDirectory') . '/');
            foreach ($newsletter->images as $image) {
                $newsletter->body = preg_replace('/{IMAGE}/', \HTML::image($uploadDirectory . '/' . $image->uuid . '/' . $image->file), $newsletter->body, 1);
            }

            $signature = $newsletter->signature->translate($language)->content;

            $directory = asset('upload/signatures/' . $newsletter->signature->id) . '/';
            foreach ($newsletter->signature->images as $image) {
                if (strpos($signature, '{SIGNATURE}') !== false) {
                    $signature = preg_replace('/{SIGNATURE}/', '<img class="signature" src="' . $directory . $image->uuid . '/' . $image->file . '" />', $signature, 1);
                }
            }

            $newsletter->body .= $signature;

            $owner = $this->owner;

            if ($request->ajax() || $request->wantsJson()) {
                $ajax = true;
                $view = \View::make(\Locales::getNamespace() . '/' . $this->route . '.view', compact('ajax', 'newsletter', 'breadcrumbs', 'attachmentsApartment', 'attachmentsOwner', 'language', 'owner'));
                $sections = $view->renderSections();
                return response()->json([$sections['content']]);
            } else {
                return view(\Locales::getNamespace() . '/' . $this->route . '.view', compact('newsletter', 'breadcrumbs', 'metaTitle', 'metaDescription', 'attachmentsApartment', 'attachmentsOwner', 'language', 'owner'));
            }
        } else {
            $newsletters = $newsletter->with('signature')->selectRaw('COUNT(*) as total, GROUP_CONCAT(newsletter_archive.is_read ORDER BY apartments.number SEPARATOR ",") AS status, GROUP_CONCAT(apartments.number ORDER BY apartments.number SEPARATOR ",") AS numbers, GROUP_CONCAT(apartments.id ORDER BY apartments.number SEPARATOR ",") AS ids, newsletter_archive.is_read, newsletters.id, newsletters.locale_id, newsletters.signature_id, newsletters.subject, newsletters.teaser, newsletters.sent_at')->join('newsletter_archive', function($join) {
                $join->on('newsletters.id', '=', 'newsletter_archive.newsletter_id')->where('newsletter_archive.owner_id', '=', $this->owner->id);
            })->leftJoin('apartments', function($join) {
                $join->on('apartments.id', '=', 'newsletter_archive.apartment_id');
            })->leftJoin('ownership', function($join) {
                $join->on('ownership.apartment_id', '=', 'apartments.id')->where('ownership.owner_id', '=', $this->owner->id);
            })->whereNull('ownership.deleted_at')->when(last(\Slug::getSlugs()) == 'steering-committee-newsletters', function ($query) {
                return $query->where('newsletters.signature_id', 2);
            })->groupBy('newsletters.id')->orderBy('newsletters.sent_at', 'desc')->get();

            $datatable->setup(null, $this->route, $this->datatables[$this->route]);
            $data = [];
            foreach ($newsletters as $newsletter) {
                $details = '';
                if ($newsletter->total > 1 && $newsletter->status && $newsletter->numbers && $newsletter->ids) {
                    $status = explode(',', $newsletter->status);
                    $numbers = explode(',', $newsletter->numbers);
                    $ids = explode(',', $newsletter->ids);
                    $details = '<table class="table row-details-table"><tbody>';
                    for ($i = 0; $i < $newsletter->total; $i++) {
                        $details .= '<tr><td><a class="js-popup' . ($status[$i] ? '' : ' js-newsletter-unread') . '" href="' . \Locales::route($this->route, [$newsletter->id, $ids[$i]]) . '"><span class="glyphicon glyphicon-file glyphicon-left"></span>' . $numbers[$i] . '</a></td></tr>';
                    }
                    $details .= '</tbody></table>';
                }

                $language = \Locales::getPublicLocales()->filter(function($value, $key) use ($newsletter) {
                    return $value->id == $newsletter->locale_id;
                })->first()->locale;

                array_push($data, [
                    'subject' => (($newsletter->total > 1 && $newsletter->status && $newsletter->numbers && $newsletter->ids) ? '<a class="row-details' . (in_array(0, $status) ? ' js-newsletter-unread' : '') . '"><span class="glyphicon glyphicon-plus glyphicon-left"></span>' . $newsletter->subject . '</a><br><span class="teaser">' . $newsletter->teaser . '</span>' : '<a class="js-popup' . ($newsletter->is_read ? '' : ' js-newsletter-unread') . '" href="' . \Locales::route($this->route, $newsletter->id) . '"><span class="glyphicon glyphicon-file glyphicon-left"></span>' . $newsletter->subject . '</a><br><span class="teaser">' . $newsletter->teaser . '</span>'),
                    'sender' => $newsletter->signature->translate($language)->name,
                    'sent_at' => [
                        'display' => \App\Helpers\displayWindowsDate(Carbon::parse($newsletter->sent_at)->formatLocalized('%d.%m.%Y')),
                        'sort' => Carbon::parse($newsletter->sent_at)->format('Ymd'),
                    ],
                    'row_details' => $details,
                ]);
            }
            $datatable->setOption('data', $data);

            $datatables = $datatable->getTables();

            return view(\Locales::getNamespace() . '/' . $this->route . '.index', compact('datatables'));
        }
    }

    public function download(NewsletterAttachments $attachments, $uuid)
    {
        $file = $attachments->select('newsletter_attachments.newsletter_id', 'newsletter_attachments.uuid', 'newsletter_attachments.file')->leftJoin('newsletter_archive', 'newsletter_archive.newsletter_id', '=', 'newsletter_attachments.newsletter_id')->where('newsletter_attachments.uuid', $uuid)->where('newsletter_archive.owner_id', $this->owner->id)->firstOrFail();

        return response()->download(public_path('upload') . DIRECTORY_SEPARATOR . $this->uploadDirectory . DIRECTORY_SEPARATOR . $file->newsletter_id . DIRECTORY_SEPARATOR . \Config::get('upload.attachmentsDirectory') . DIRECTORY_SEPARATOR . $file->uuid . DIRECTORY_SEPARATOR . $file->file);
    }

    public function downloadApartment(NewsletterAttachmentsApartment $attachments, $uuid)
    {
        $file = $attachments->select('newsletter_attachments_apartment.newsletter_id', 'newsletter_attachments_apartment.uuid', 'newsletter_attachments_apartment.file')->leftJoin('newsletter_archive', 'newsletter_archive.newsletter_id', '=', 'newsletter_attachments_apartment.newsletter_id')->where('newsletter_attachments_apartment.uuid', $uuid)->where('newsletter_archive.owner_id', $this->owner->id)->firstOrFail();

        return response()->download(public_path('upload') . DIRECTORY_SEPARATOR . $this->uploadDirectory . DIRECTORY_SEPARATOR . $file->newsletter_id . DIRECTORY_SEPARATOR . \Config::get('upload.attachmentsDirectory') . '-apartment' . DIRECTORY_SEPARATOR . $file->uuid . DIRECTORY_SEPARATOR . $file->file);
    }

    public function downloadOwner(NewsletterAttachmentsOwner $attachments, $uuid)
    {
        $file = $attachments->select('newsletter_attachments_owner.newsletter_id', 'newsletter_attachments_owner.uuid', 'newsletter_attachments_owner.file')->leftJoin('newsletter_archive', 'newsletter_archive.newsletter_id', '=', 'newsletter_attachments_owner.newsletter_id')->where('newsletter_attachments_owner.uuid', $uuid)->where('newsletter_archive.owner_id', $this->owner->id)->firstOrFail();

        return response()->download(public_path('upload') . DIRECTORY_SEPARATOR . $this->uploadDirectory . DIRECTORY_SEPARATOR . $file->newsletter_id . DIRECTORY_SEPARATOR . \Config::get('upload.attachmentsDirectory') . '-owner' . DIRECTORY_SEPARATOR . $file->uuid . DIRECTORY_SEPARATOR . $file->file);
    }

}
