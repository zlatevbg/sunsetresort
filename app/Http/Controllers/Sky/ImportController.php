<?php namespace App\Http\Controllers\Sky;

use App\Http\Controllers\Controller;
use App\Models\Sky\CouncilTax;
use App\Models\Sky\Room;
use App\Models\Sky\RoomTranslations;
use App\Models\Sky\Furniture;
use App\Models\Sky\FurnitureTranslations;
use App\Models\Sky\View;
use App\Models\Sky\ViewTranslations;
use App\Models\Sky\Building;
use App\Models\Sky\BuildingTranslations;
use App\Models\Sky\Floor;
use App\Models\Sky\FloorTranslations;
use App\Models\Sky\Country;
use App\Models\Sky\CountryTranslations;
use App\Models\Sky\Owner;
use App\Models\Sky\Apartment;
use App\Models\Sky\Agent;
use App\Models\Sky\AgentAccess;
use App\Models\Sky\Keyholder;
use App\Models\Sky\KeyholderAccess;
use App\Models\Sky\BankAccount;
use App\Models\Sky\Ownership;
use App\Models\Sky\Year;
use App\Models\Sky\ManagementCompany;
use App\Models\Sky\ManagementCompanyTranslations;
use App\Models\Sky\BuildingMm;
use App\Models\Sky\BuildingMmDocuments;
use App\Models\Sky\Deduction;
use App\Models\Sky\DeductionTranslations;
use App\Models\Sky\PaymentMethod;
use App\Models\Sky\PaymentMethodTranslations;
use App\Models\Sky\RentalCompany;
use App\Models\Sky\RentalCompanyTranslations;
use App\Models\Sky\RentalContract;
use App\Models\Sky\RentalContractTranslations;
use App\Models\Sky\RentalPayment;
use App\Models\Sky\RentalPaymentPrices;
use App\Models\Sky\Contract;
use App\Models\Sky\ContractYear;
use App\Models\Sky\ContractDocuments;
use App\Models\Sky\ContractDeduction;
use App\Models\Sky\ContractPayment;
use App\Models\Sky\MmFeePayment;
use App\Models\Sky\Signature;
use App\Models\Sky\SignatureTranslations;
use App\Models\Sky\Recipient;
use App\Models\Sky\Booking;
use App\Models\Sky\BookingGuest;
use Storage;
use Carbon\Carbon;

class ImportController extends Controller {

    public function __construct()
    {
        ini_set('max_execution_time', 0);
    }

    public function taxes()
    {
        $rows = array_map(function($row) {
            return str_getcsv($row, "\t");
        }, file('C:\\Users\\Unikat\\Desktop\\data.txt'));

        foreach($rows as $row) {
            array_walk($row, function(&$value) {
                $value = trim(html_entity_decode($value), " \t\n\r\0\x0B\xC2\xA0"); // \xC2\xA0 = &nbsp;
            });

            $dataApartment = $row[0];
            $dataBulstat = $row[1];
            $dataPin = $row[2];
            $dataOwner = $row[3];
            $dataTax = str_replace(' ', '', $row[4]);
            $number = null;
            $found = false;

            if ($number != $dataApartment) {
                $apartment = Apartment::where('number', $dataApartment)->first();
            }

            if ($apartment) {
                foreach ($apartment->owners as $owner) {
                    if ($owner->owner->full_name == $dataOwner) {
                        $found = true;

                        $owner->owner->bulstat = $dataBulstat ?: null;
                        $owner->owner->tax_pin = $dataPin ?: null;
                        $owner->owner->save();

                        CouncilTax::create([
                            'created_at' => Carbon::now(),
                            'tax' => $dataTax ?: null,
                            'checked_at' => '2017-05-01 00:00:00',
                            'owner_id' => $owner->owner->id,
                            'apartment_id' => $apartment->id,
                        ]);

                        break;
                    }
                }

                if (!$found) {
                    echo 'Invalid Owner: ' . $dataOwner . '<br>';
                }
            } else {
                echo 'Invalid Apartment: ' . $dataApartment . '<br>';
            }
        }

        return 'Done';
    }

    public function rooms()
    {
        $rooms = \DB::connection('mysql-old')->select('select id, max_people from apartments_types');

        $data = [];
        foreach($rooms as $room) {
            array_push($data, [
                'id' => $room->id,
                'capacity' => $room->max_people,
            ]);
        }

        Room::insert($data);

        $rooms = \DB::connection('mysql-old')->select('select id, name from apartments_types_description');

        $data = [];
        foreach($rooms as $room) {
            array_push($data, [
                'name' => $room->name,
                'locale' => 'en',
                'room_id' => $room->id,
            ]);
        }

        RoomTranslations::insert($data);

        return 'Done';
    }

    public function furniture()
    {
        $furnitures = \DB::connection('mysql-old')->select('select id from apartments_furniture');

        $data = [];
        foreach($furnitures as $furniture) {
            array_push($data, [
                'id' => $furniture->id,
            ]);
        }

        Furniture::insert($data);

        $furnitures = \DB::connection('mysql-old')->select('select id, name from apartments_furniture_description');

        $data = [];
        foreach($furnitures as $furniture) {
            array_push($data, [
                'name' => $furniture->name,
                'locale' => 'en',
                'furniture_id' => $furniture->id,
            ]);
        }

        FurnitureTranslations::insert($data);

        return 'Done';
    }

    public function views()
    {
        $views = \DB::connection('mysql-old')->select('select id from apartments_views');

        $data = [];
        foreach($views as $view) {
            array_push($data, [
                'id' => $view->id,
            ]);
        }

        View::insert($data);

        $views = \DB::connection('mysql-old')->select('select id, name from apartments_views_description');

        $data = [];
        foreach($views as $view) {
            array_push($data, [
                'name' => $view->name,
                'locale' => 'en',
                'view_id' => $view->id,
            ]);
        }

        ViewTranslations::insert($data);

        return 'Done';
    }

    public function buildings()
    {
        $buildings = \DB::connection('mysql-old')->select('select id from buildings where parent = "0"');

        $data = [];
        foreach($buildings as $building) {
            array_push($data, [
                'id' => $building->id,
                'project_id' => $building->id > 5 ? 2 : 1,
            ]);
        }

        Building::insert($data);

        $buildings = \DB::connection('mysql-old')->select('select buildings_description.id, buildings_description.name from buildings_description left join buildings on buildings_description.id = buildings.id where buildings.parent = "0"');

        $data = [];
        foreach($buildings as $building) {
            array_push($data, [
                'name' => $building->name,
                'locale' => 'en',
                'building_id' => $building->id,
            ]);
        }

        BuildingTranslations::insert($data);

        return 'Done';
    }

    public function floors()
    {
        $floors = \DB::connection('mysql-old')->select('select id, parent from buildings where parent > "0"');

        $data = [];
        foreach($floors as $floor) {
            array_push($data, [
                'id' => $floor->id,
                'building_id' => $floor->parent,
            ]);
        }

        Floor::insert($data);

        $floors = \DB::connection('mysql-old')->select('select buildings_description.id, buildings_description.name from buildings_description left join buildings on buildings_description.id = buildings.id where buildings.parent > "0"');

        $data = [];
        foreach($floors as $floor) {
            array_push($data, [
                'name' => $floor->name,
                'locale' => 'en',
                'floor_id' => $floor->id,
            ]);
        }

        FloorTranslations::insert($data);

        return 'Done';
    }

    public function countries()
    {
        $countries = \DB::connection('mysql-old')->select('select id from countries');

        $data = [];
        foreach($countries as $country) {
            array_push($data, [
                'id' => $country->id,
            ]);
        }

        Country::insert($data);

        $countries = \DB::connection('mysql-old')->select('select id, name from countries_description');

        $data = [];
        foreach($countries as $country) {
            array_push($data, [
                'name' => $country->name,
                'locale' => 'en',
                'country_id' => $country->id,
            ]);
        }

        CountryTranslations::insert($data);

        return 'Done';
    }

    public function owners()
    {
        $owners = \DB::connection('mysql-old')->select('select owners.id, owners.date_added, owners.email, owners.email2, owners.newsletter_type, owners.phone, owners.mobile, owners.countries_id1, owners.postcode1, owners.nationalities_id, owners.sex, owners_description.fname, owners_description.lname, owners_description.city1, owners_description.street1_line1, owners_description.street1_line2, owners_description.comments from owners left join owners_description on owners.id = owners_description.id');

        $data = [];
        foreach($owners as $owner) {
            $password = $owner->email ? \App\Helpers\randomStr() : null;
            array_push($data, [
                'id' => $owner->id,
                'created_at' => $owner->date_added,
                'is_subscribed' => !$owner->newsletter_type,
                'first_name' => $owner->fname ?: null,
                'last_name' => $owner->lname ?: null,
                'phone' => $owner->phone ?: null,
                'mobile' => $owner->mobile ?: null,
                'email' => $owner->email ?: null,
                'email_cc' => $owner->email2 ?: null,
                'password' => $password ? bcrypt($password) : null,
                'temp_password' => $password,
                'sex' => $owner->sex == 1 ? 'm' : 'f',
                'city' => $owner->city1 ?: null,
                'postcode' => $owner->postcode1 ?: null,
                'address1' => $owner->street1_line1 ?: null,
                'address2' => $owner->street1_line2 ?: null,
                'comments' => $owner->comments ?: null,
                'locale_id' => ($owner->nationalities_id == 2 ? 211 : ($owner->nationalities_id == 3 ? 216 : 37)),
                'country_id' => $owner->countries_id1,
            ]);
        }

        Owner::insert($data);

        return 'Done';
    }

    public function apartments()
    {
        $apartments = \DB::connection('mysql-old')->select('select apartments_description.comments, apartments.id, apartments.number, apartments.apartment_area, apartments.balcony_area, apartments.extra_balcony_area, apartments.common_area, apartments.total_area, apartments.apartments_types_id, apartments.apartments_furniture_id, apartments.apartments_views_id, apartments.buildings_id, apartments.floors_id from apartments left join apartments_description on apartments.id = apartments_description.id');

        $data = [];
        foreach($apartments as $apartment) {
            Storage::disk('local-public')->makeDirectory('apartments' . DIRECTORY_SEPARATOR . $apartment->id);

            array_push($data, [
                'id' => $apartment->id,
                'number' => $apartment->number,
                'apartment_area' => $apartment->apartment_area,
                'balcony_area' => $apartment->balcony_area,
                'extra_balcony_area' => $apartment->extra_balcony_area,
                'common_area' => $apartment->common_area,
                'total_area' => $apartment->total_area,
                'comments' => $apartment->comments ?: null,
                'room_id' => $apartment->apartments_types_id,
                'furniture_id' => $apartment->apartments_furniture_id,
                'view_id' => $apartment->apartments_views_id,
                'project_id' => $apartment->buildings_id > 5 ? 2 : 1,
                'building_id' => $apartment->buildings_id,
                'floor_id' => $apartment->floors_id,
            ]);
        }

        Apartment::insert($data);

        return 'Done';
    }

    public function agentAccess()
    {
        $accesses = \DB::connection('mysql-old')->select('select id, apartments_id, agent, date_added from apartments_access');

        $data1 = [];
        $data2 = [];
        $agents = [];
        foreach($accesses as $access) {
            if (!array_key_exists($access->agent, $agents)) {
                array_push($data1, [
                    'id' => $access->id,
                    'name' => $access->agent,
                ]);

                $agents[$access->agent] = $access->id;
            }

            array_push($data2, [
                'created_at' => $access->date_added,
                'apartment_id' => $access->apartments_id,
                'agent_id' => $agents[$access->agent],
            ]);
        }

        Agent::insert($data1);
        AgentAccess::insert($data2);

        return 'Done';
    }

    public function keyholderAccess()
    {
        $accesses = \DB::connection('mysql-old')->select('select id, apartments_id, keyholder, date_added from apartments_locks');

        $data1 = [];
        $data2 = [];
        $keyholders = [];
        foreach($accesses as $access) {
            if (!array_key_exists($access->keyholder, $keyholders)) {
                array_push($data1, [
                    'id' => $access->id,
                    'name' => $access->keyholder,
                ]);

                $keyholders[$access->keyholder] = $access->id;
            }

            array_push($data2, [
                'created_at' => $access->date_added,
                'apartment_id' => $access->apartments_id,
                'keyholder_id' => $keyholders[$access->keyholder],
            ]);
        }

        Keyholder::insert($data1);
        KeyholderAccess::insert($data2);

        return 'Done';
    }

    public function keyholders()
    {
        $apartments = Apartment::distinct()->select('apartments.id', 'ownership.created_at')->leftJoin('ownership', 'apartments.id', '=', 'ownership.apartment_id')->whereNotIn('apartments.id', [4,29,46,65,75,76,84,93,111,114,119,142,186,215,221,228,262,327,367,454,475,484,506,517,519,520,532,533,538,566,572,574,586,588,606,613,628,632,635,655,681,685,697,702,713,714,730,731,733,763,764])->groupBy('apartments.id')->orderBy('ownership.id')->get();

        $data = [];
        foreach($apartments as $apartment) {
            array_push($data, [
                'created_at' => $apartment->created_at,
                'apartment_id' => $apartment->id,
                'keyholder_id' => 22,
            ]);
        }

        KeyholderAccess::insert($data);

        return 'Done';
    }

    public function bankAccounts()
    {
        $accounts = \DB::connection('mysql-old')->select('select distinct owners_to_apartments.owners_id as owner1, former_owners_to_apartments.owners_id as owner2, bank_accounts.id, bank_accounts.date_added, bank_accounts.iban, bank_accounts.bic, bank_accounts.beneficiary, bank_accounts.bank from bank_accounts left join owners_to_apartments on bank_accounts.id = owners_to_apartments.bank_accounts_id left join former_owners_to_apartments on bank_accounts.id = former_owners_to_apartments.bank_accounts_id group by bank_accounts.id');

        $data = [];
        foreach($accounts as $account) {
            array_push($data, [
                'id' => $account->id,
                'created_at' => $account->date_added,
                'bank_iban' => $account->iban,
                'bank_bic' => $account->bic,
                'bank_beneficiary' => $account->beneficiary,
                'bank_name' => $account->bank,
                'owner_id' => $account->owner1 ?: $account->owner2,
            ]);
        }

        BankAccount::insert($data);

        return 'Done';
    }

    public function ownership()
    {
        $ownerships = \DB::connection('mysql-old')->select('select owners_to_apartments.owners_id, owners_to_apartments.apartments_id, owners_to_apartments.date_added, owners_to_apartments.bank_accounts_id from owners_to_apartments');

        $data = [];
        $current = [];
        foreach($ownerships as $ownership) {
            array_push($data, [
                'created_at' => $ownership->date_added,
                'apartment_id' => $ownership->apartments_id,
                'owner_id' => $ownership->owners_id,
                'bank_account_id' => $ownership->bank_accounts_id ?: null,
            ]);

            array_push($current, $ownership->owners_id);
        }

        Ownership::insert($data);

        $ownerships = \DB::connection('mysql-old')->select('select former_owners_to_apartments.owners_id, former_owners_to_apartments.apartments_id, former_owners_to_apartments.dfrom, former_owners_to_apartments.dto, former_owners_to_apartments.bank_accounts_id from former_owners_to_apartments');

        $data = [];
        $former = [];
        foreach($ownerships as $ownership) {
            array_push($data, [
                'deleted_at' => $ownership->dto,
                'created_at' => $ownership->dfrom,
                'apartment_id' => $ownership->apartments_id,
                'owner_id' => $ownership->owners_id,
                'bank_account_id' => $ownership->bank_accounts_id ?: null,
            ]);

            array_push($former, $ownership->owners_id);
        }

        Ownership::insert($data);

        $current = array_unique($current);
        $former = array_unique($former);

        $diff = array_diff($former, $current);
        sort($diff);

        Owner::destroy($diff);

        return 'Done';
    }

    public function years()
    {
        $years = \DB::connection('mysql-old')->select('select years.id, years.corporate_tax, years_description.name from years left join years_description on years.id = years_description.id');

        $data = [];
        foreach($years as $year) {
            array_push($data, [
                'id' => $year->id,
                'year' => $year->name,
                'corporate_tax' => $year->corporate_tax,
            ]);
        }

        Year::insert($data);

        return 'Done';
    }

    public function buildingsMm()
    {
        $buildings = \DB::connection('mysql-old')->select('select years_description.name, buildings_id, years_id, tax, ga_date, manager_details, file, file_ext, file_size, file3, file3_ext, file3_size, file4, file4_ext, file4_size, file5, file5_ext, file5_size, file6, file6_ext, file6_size from buildings_to_years left join years on buildings_to_years.years_id = years.id left join years_description on years.id = years_description.id');

        $data1 = [];
        $data2 = [];
        $companies = [];
        $i = 1;
        foreach($buildings as $building) {
            if ($building->manager_details && !array_key_exists($building->manager_details, $companies)) {
                array_push($data1, [
                    'id' => $i,
                ]);

                array_push($data2, [
                    'id' => $i,
                    'name' => $building->manager_details,
                    'locale' => 'en',
                    'management_company_id' => $i,
                ]);

                $companies[$building->manager_details] = $i;

                $i++;
            }
        }

        ManagementCompany::insert($data1);
        ManagementCompanyTranslations::insert($data2);

        $data1 = [];
        $data2 = [];
        $i = 1;
        foreach($buildings as $building) {
            if ($building->manager_details) {
                array_push($data1, [
                    'id' => $i,
                    'mm_tax' => $building->tax,
                    'assembly_at' => $building->ga_date ?: null,
                    'deadline_at' => Carbon::createFromDate($building->name, 11, 30),
                    'building_id' => $building->buildings_id,
                    'year_id' => $building->years_id,
                    'management_company_id' => $companies[$building->manager_details],
                ]);

                $directory = 'buildings' . DIRECTORY_SEPARATOR . 'mm-documents' . DIRECTORY_SEPARATOR . $i;
                Storage::disk('local-public')->makeDirectory($directory);

                if ($building->file) {
                    $uuid = \Uuid::generate(4);
                    Storage::disk('local-public')->makeDirectory($directory . DIRECTORY_SEPARATOR . $uuid);

                    array_push($data2, [
                        'type' => 'rules',
                        'file' => $building->file,
                        'uuid' => $uuid,
                        'extension' => $building->file_ext,
                        'size' => $building->file_size,
                        'building_mm_id' => $i,
                    ]);

                    copy('D:\\Server\\www\\sky\\uploads\\buildings\\' . $building->buildings_id . DIRECTORY_SEPARATOR . $building->file, 'D:\\Server\\www\\sunsetresort\\public\\upload\\' . $directory . DIRECTORY_SEPARATOR . $uuid . DIRECTORY_SEPARATOR . $building->file);
                }

                if ($building->file3) {
                    $uuid = \Uuid::generate(4);
                    Storage::disk('local-public')->makeDirectory($directory . DIRECTORY_SEPARATOR . $uuid);

                    array_push($data2, [
                        'type' => 'minutes',
                        'file' => $building->file3,
                        'uuid' => $uuid,
                        'extension' => $building->file3_ext,
                        'size' => $building->file3_size,
                        'building_mm_id' => $i,
                    ]);

                    copy('D:\\Server\\www\\sky\\uploads\\buildings\\' . $building->buildings_id . DIRECTORY_SEPARATOR . $building->file3, 'D:\\Server\\www\\sunsetresort\\public\\upload\\' . $directory . DIRECTORY_SEPARATOR . $uuid . DIRECTORY_SEPARATOR . $building->file3);
                }

                if ($building->file4) {
                    $uuid = \Uuid::generate(4);
                    Storage::disk('local-public')->makeDirectory($directory . DIRECTORY_SEPARATOR . $uuid);

                    array_push($data2, [
                        'type' => 'insurance',
                        'file' => $building->file4,
                        'uuid' => $uuid,
                        'extension' => $building->file4_ext,
                        'size' => $building->file4_size,
                        'building_mm_id' => $i,
                    ]);

                    copy('D:\\Server\\www\\sky\\uploads\\buildings\\' . $building->buildings_id . DIRECTORY_SEPARATOR . $building->file4, 'D:\\Server\\www\\sunsetresort\\public\\upload\\' . $directory . DIRECTORY_SEPARATOR . $uuid . DIRECTORY_SEPARATOR . $building->file4);
                }

                if ($building->file5) {
                    $uuid = \Uuid::generate(4);
                    Storage::disk('local-public')->makeDirectory($directory . DIRECTORY_SEPARATOR . $uuid);

                    array_push($data2, [
                        'type' => 'receipts',
                        'file' => $building->file5,
                        'uuid' => $uuid,
                        'extension' => $building->file5_ext,
                        'size' => $building->file5_size,
                        'building_mm_id' => $i,
                    ]);

                    copy('D:\\Server\\www\\sky\\uploads\\buildings\\' . $building->buildings_id . DIRECTORY_SEPARATOR . $building->file5, 'D:\\Server\\www\\sunsetresort\\public\\upload\\' . $directory . DIRECTORY_SEPARATOR . $uuid . DIRECTORY_SEPARATOR . $building->file5);
                }

                if ($building->file6) {
                    $uuid = \Uuid::generate(4);
                    Storage::disk('local-public')->makeDirectory($directory . DIRECTORY_SEPARATOR . $uuid);

                    array_push($data2, [
                        'type' => 'accounts',
                        'file' => $building->file6,
                        'uuid' => $uuid,
                        'extension' => $building->file6_ext,
                        'size' => $building->file6_size,
                        'building_mm_id' => $i,
                    ]);

                    copy('D:\\Server\\www\\sky\\uploads\\buildings\\' . $building->buildings_id . DIRECTORY_SEPARATOR . $building->file6, 'D:\\Server\\www\\sunsetresort\\public\\upload\\' . $directory . DIRECTORY_SEPARATOR . $uuid . DIRECTORY_SEPARATOR . $building->file6);
                }

                $i++;
            }
        }

        BuildingMm::insert($data1);
        BuildingMmDocuments::insert($data2);

        return 'Done';
    }

    public function deductions()
    {
        $deductions = \DB::connection('mysql-old')->select('select id, tax from deduction_options');

        $data = [];
        foreach($deductions as $deduction) {
            array_push($data, [
                'id' => $deduction->id,
                'is_taxable' => !$deduction->tax,
            ]);
        }

        Deduction::insert($data);

        $deductions = \DB::connection('mysql-old')->select('select id, name from deduction_options_description');

        $data = [];
        foreach($deductions as $deduction) {
            array_push($data, [
                'name' => $deduction->name,
                'locale' => 'en',
                'deduction_id' => $deduction->id,
            ]);
        }

        DeductionTranslations::insert($data);

        return 'Done';
    }

    public function paymentMethods()
    {
        $methods = \DB::connection('mysql-old')->select('select id from payment_methods');

        $data = [];
        foreach($methods as $method) {
            array_push($data, [
                'id' => $method->id,
            ]);
        }

        PaymentMethod::insert($data);

        $methods = \DB::connection('mysql-old')->select('select id, name from payment_methods_description');

        $data = [];
        foreach($methods as $method) {
            array_push($data, [
                'name' => $method->name,
                'locale' => 'en',
                'payment_method_id' => $method->id,
            ]);
        }

        PaymentMethodTranslations::insert($data);

        return 'Done';
    }

    public function rentalCompanies()
    {
        $companies = \DB::connection('mysql-old')->select('select id from rental_companies');

        $data = [];
        foreach($companies as $company) {
            array_push($data, [
                'id' => $company->id,
            ]);
        }

        RentalCompany::insert($data);

        $companies = \DB::connection('mysql-old')->select('select id, name from rental_companies_description');

        $data = [];
        foreach($companies as $company) {
            array_push($data, [
                'name' => $company->name,
                'locale' => 'en',
                'rental_company_id' => $company->id,
            ]);
        }

        RentalCompanyTranslations::insert($data);

        return 'Done';
    }

    public function rentalContracts()
    {
        $options = [];
        $keys = [];
        $contracts = \DB::connection('mysql-old')->select('select id, name from rental_contracts_description where name not like "%experience%"');
        foreach($contracts as $contract) {
            $temp = [];
            $hasPrice = false;

            $prices = \DB::connection('mysql-old')->select('select apartments_furniture_id, apartments_types_id, apartments_views_id, rental_price from rental_contracts_prices where rental_contracts_id = "' . $contract->id . '" order by apartments_types_id, apartments_furniture_id, apartments_views_id');
            foreach($prices as $price) {
                if ($price->rental_price) {
                    $hasPrice = true;
                }

                array_push($temp,  [
                    'room' => $price->apartments_types_id,
                    'furniture' => $price->apartments_furniture_id,
                    'view' => $price->apartments_views_id,
                    'price' => $price->rental_price,
                ]);
            }

            if ($hasPrice) {
                $key = false;
                foreach ($options as $k => $value) {
                    if ($value == $temp) {
                        $key = $k;
                        break;
                    }
                }

                if ($key === false) {
                    array_push($options, $temp);
                    $keys[$contract->id] = key($options);
                } else {
                    $keys[$contract->id] = $key;
                }
            }
        }

        $i = 1;
        $data1 = [];
        $data2 = [];

        foreach($options as $key => $option) {
            array_push($data1, [
                'id' => $i,
                'name' => 'Payment Option ' . $i,
            ]);

            foreach($option as $price) {
                array_push($data2, [
                    'rental_payment_id' => $i,
                    'price' => $price['price'],
                    'room_id' => $price['room'],
                    'furniture_id' => $price['furniture'],
                    'view_id' => $price['view'],
                ]);
            }

            $i++;
        }

        RentalPayment::insert($data1);
        RentalPaymentPrices::insert($data2);

        $contracts = \DB::connection('mysql-old')->select('select rental_contracts.id, rental_contracts.date_added, rental_contracts.mm_fees, rental_contracts.duration, rental_contracts.rental_payment_date, rental_contracts.contract_dfrom1, rental_contracts.contract_dto1, rental_contracts.contract_dfrom2, rental_contracts.contract_dto2, rental_contracts.personal_dfrom1, rental_contracts.personal_dto1, rental_contracts.personal_dfrom2, rental_contracts.personal_dto2 from rental_contracts left join rental_contracts_description on rental_contracts.id = rental_contracts_description.id where rental_contracts_description.name not like "%experience%"');

        $data = [];
        foreach($contracts as $contract) {
            array_push($data, [
                'id' => $contract->id,
                'created_at' => $contract->date_added,
                'mm_covered' => ($contract->mm_fees == 1 ? 100 : ($contract->mm_fees == 2 ? 50 : 0)),
                'deadline_at' => $contract->rental_payment_date,
                'max_duration' => $contract->duration,
                'contract_dfrom1' => $contract->contract_dfrom1,
                'contract_dto1' => $contract->contract_dto1,
                'contract_dfrom2' => $contract->contract_dfrom2,
                'contract_dto2' => $contract->contract_dto2,
                'personal_dfrom1' => $contract->personal_dfrom1,
                'personal_dto1' => $contract->personal_dto1,
                'personal_dfrom2' => $contract->personal_dfrom2,
                'personal_dto2' => $contract->personal_dto2,
                'rental_payment_id' => (isset($keys[$contract->id]) ? $keys[$contract->id] + 1 : null),
            ]);
        }

        RentalContract::insert($data);

        $contracts = \DB::connection('mysql-old')->select('select id, name, benefits from rental_contracts_description where name not like "%experience%"');

        $data = [];
        foreach($contracts as $contract) {
            array_push($data, [
                'name' => $contract->name,
                'benefits' => $contract->benefits ?: null,
                'locale' => 'en',
                'rental_contract_id' => $contract->id,
            ]);
        }

        RentalContractTranslations::insert($data);

        $contracts = \DB::connection('mysql-old')->select('select apartments.number, files.parent, files.date_added as file_date, files.file, files.size, files.ext, rental_contracts_to_apartments.rental_contracts_id, rental_contracts_to_apartments.apartments_id, rental_contracts_to_apartments.year, rental_contracts_to_apartments.contract_year, rental_contracts_to_apartments.date_added, rental_contracts_to_apartments.last_modified, rental_contracts_to_apartments.contract_date, rental_contracts_to_apartments.period, rental_contracts_to_apartments.canceled_date, rental_contracts_to_apartments.rental_price, rental_contracts_to_apartments.contract_dfrom1, rental_contracts_to_apartments.contract_dto1, rental_contracts_to_apartments.contract_dfrom2, rental_contracts_to_apartments.contract_dto2, rental_contracts_to_apartments.mm_fees_year, rental_contracts_to_apartments.comments, rental_contracts_to_apartments.personal_dfrom1, rental_contracts_to_apartments.personal_dfrom2, rental_contracts_to_apartments.personal_dto1, rental_contracts_to_apartments.personal_dto2 from rental_contracts_to_apartments left join files on rental_contracts_to_apartments.rental_contracts_id = files.rental_contracts_id and rental_contracts_to_apartments.apartments_id = files.apartments_id and rental_contracts_to_apartments.year = files.year left join apartments on rental_contracts_to_apartments.apartments_id = apartments.id order by rental_contracts_to_apartments.apartments_id, rental_contracts_to_apartments.rental_contracts_id, rental_contracts_to_apartments.year');

        $i = 0;
        $j = 1;
        $data1 = [];
        $data2 = [];
        $data3 = [];
        $data4 = [];
        $data5 = [];
        $lastApartment = null;
        $lastContract = null;
        $lastContractEnd = null;
        $count = 0;
        $isOld = false;
        foreach($contracts as $contract) {
            $rentalContract = RentalContract::select('contract_dfrom1', 'contract_dto1', 'contract_dfrom2', 'contract_dto2', 'personal_dfrom1', 'personal_dto1', 'personal_dfrom2', 'personal_dto2')->where('id', $contract->rental_contracts_id)->first();

            if ($rentalContract) {
                if ($lastApartment != $contract->apartments_id || $lastContract != $contract->rental_contracts_id) {
                    if ($isOld) {
                        $data1[($count - 1)]['deleted_at'] = $contract->canceled_date ?: $lastContractEnd;
                    }

                    $isOld = true;
                    $i++;
                    $lastApartment = $contract->apartments_id;
                    $lastContract = $contract->rental_contracts_id;
                    $lastContractEnd = $contract->contract_dto1 ?: ($rentalContract->contract_dto1 ? Carbon::parse($rentalContract->contract_dto1)->year($contract->year) : null);

                    $count = array_push($data1, [
                        'id' => $i,
                        'deleted_at' => $contract->canceled_date,
                        'created_at' => $contract->date_added,
                        'updated_at' => $contract->last_modified,
                        'duration' => $contract->period,
                        'signed_at' => $contract->contract_date,
                        'comments' => $contract->comments ?: null,
                        'apartment_id' => $contract->apartments_id,
                        'rental_contract_id' => $contract->rental_contracts_id,
                    ]);
                }

                if ($contract->year >= date('Y')) {
                    $isOld = false;
                }

                if ($contract->canceled_date) {
                    $data1[($count - 1)]['deleted_at'] = $contract->canceled_date;
                }

                array_push($data2, [
                    'id' => $j,
                    'deleted_at' => $contract->canceled_date,
                    'created_at' => $contract->date_added,
                    'updated_at' => $contract->last_modified,
                    'year' => $contract->year,
                    'mm_for_year' => ($contract->year + $contract->mm_fees_year),
                    'price' => $contract->rental_price,
                    'contract_dfrom1' => $contract->contract_dfrom1 ?: ($rentalContract->contract_dfrom1 ? Carbon::parse($rentalContract->contract_dfrom1)->year($contract->year) : null),
                    'contract_dto1' => $contract->contract_dto1 ?: ($rentalContract->contract_dto1 ? Carbon::parse($rentalContract->contract_dto1)->year($contract->year) : null),
                    'contract_dfrom2' => $contract->contract_dfrom2 ?: ($rentalContract->contract_dfrom2 ? Carbon::parse($rentalContract->contract_dfrom2)->year($contract->year) : null),
                    'contract_dto2' => $contract->contract_dto2 ?: ($rentalContract->contract_dto2 ? Carbon::parse($rentalContract->contract_dto2)->year($contract->year) : null),
                    'personal_dfrom1' => $contract->personal_dfrom1 ?: ($rentalContract->personal_dfrom1 ? Carbon::parse($rentalContract->personal_dfrom1)->year($contract->year) : null),
                    'personal_dto1' => $contract->personal_dto1 ?: ($rentalContract->personal_dto1 ? Carbon::parse($rentalContract->personal_dto1)->year($contract->year) : null),
                    'personal_dfrom2' => $contract->personal_dfrom2 ?: ($rentalContract->personal_dfrom2 ? Carbon::parse($rentalContract->personal_dfrom2)->year($contract->year) : null),
                    'personal_dto2' => $contract->personal_dto2 ?: ($rentalContract->personal_dto2 ? Carbon::parse($rentalContract->personal_dto2)->year($contract->year) : null),
                    'comments' => $contract->comments ?: null,
                    'contract_id' => $i,
                ]);

                if ($contract->file) {
                    $directory = 'apartments' . DIRECTORY_SEPARATOR . $contract->apartments_id . DIRECTORY_SEPARATOR . 'documents';
                    Storage::disk('local-public')->makeDirectory($directory);

                    $uuid = \Uuid::generate(4);
                    Storage::disk('local-public')->makeDirectory($directory . DIRECTORY_SEPARATOR . $uuid);

                    array_push($data3, [
                        'created_at' => $contract->file_date,
                        'type' => $contract->parent == 3 ? 'contract' : 'annex',
                        'signed_at' => $contract->file_date,
                        'file' => $contract->file,
                        'uuid' => $uuid,
                        'extension' => $contract->ext,
                        'size' => $contract->size,
                        'contract_year_id' => $j,
                    ]);

                    copy('D:\\Server\\www\\sky\\uploads\\apartments\\' . $contract->number . DIRECTORY_SEPARATOR . 'rental-contracts' . DIRECTORY_SEPARATOR . $contract->file, 'D:\\Server\\www\\sunsetresort\\public\\upload\\' . $directory . DIRECTORY_SEPARATOR . $uuid . DIRECTORY_SEPARATOR . $contract->file);
                }

                $deductions = \DB::connection('mysql-old')->select('select date_added, last_modified, amount, deduction_options_id, comments from deductions where apartments_id = "' . $contract->apartments_id . '" and rental_contracts_id = "' . $contract->rental_contracts_id . '" and year = "' . $contract->year . '" order by apartments_id, rental_contracts_id, year');

                foreach($deductions as $deduction) {
                    array_push($data4, [
                        'created_at' => $deduction->date_added,
                        'updated_at' => $deduction->last_modified,
                        'amount' => number_format($deduction->amount, 2, '.', ''),
                        'comments' => $deduction->comments ?: null,
                        'contract_year_id' => $j,
                        'deduction_id' => $deduction->deduction_options_id,
                    ]);
                }

                $payments = \DB::connection('mysql-old')->select('select date_added, last_modified, amount, payment_date, rental_companies_id, owners_id, payment_methods_id, comments from payments where type = "1" and apartments_id = "' . $contract->apartments_id . '" and rental_contracts_id = "' . $contract->rental_contracts_id . '" and year = "' . $contract->year . '" order by apartments_id, rental_contracts_id, year');

                foreach($payments as $payment) {
                    array_push($data5, [
                        'created_at' => $payment->date_added,
                        'updated_at' => $payment->last_modified,
                        'amount' => number_format($payment->amount, 2, '.', ''),
                        'paid_at' => $payment->payment_date,
                        'comments' => $payment->comments ?: null,
                        'contract_year_id' => $j,
                        'payment_method_id' => $payment->payment_methods_id,
                        'rental_company_id' => $payment->rental_companies_id,
                        'owner_id' => $payment->owners_id ?: null,
                    ]);
                }

                $j++;
            }
        }

        Contract::insert($data1);
        ContractYear::insert($data2);
        ContractDocuments::insert($data3);
        ContractDeduction::insert($data4);
        ContractPayment::insert($data5);

        return 'Done';
    }

    public function mm()
    {
        $years = Year::all()->pluck('id', 'year');

        $payments = \DB::connection('mysql-old')->select('select apartments_id, year, date_added, last_modified, amount, payment_date, rental_companies_id, owners_id, payment_methods_id, comments from payments where type = "2" order by apartments_id, year');

        $data = [];
        foreach($payments as $payment) {
            array_push($data, [
                'created_at' => $payment->date_added,
                'updated_at' => $payment->last_modified,
                'amount' => number_format($payment->amount, 2, '.', ''),
                'paid_at' => $payment->payment_date,
                'comments' => $payment->comments ?: null,
                'apartment_id' => $payment->apartments_id,
                'year_id' => $years[$payment->year],
                'payment_method_id' => $payment->payment_methods_id,
                'rental_company_id' => $payment->rental_companies_id ?: null,
                'owner_id' => ($payment->owners_id > 0 ? $payment->owners_id : null),
            ]);
        }

        MmFeePayment::insert($data);

        return 'Done';
    }

    public function signatures()
    {
        $signatures = \DB::connection('mysql-old')->select('select newsletter_signatures_description.content, newsletter_signatures.id, newsletter_signatures.name, newsletter_signatures.sender, newsletter_signatures.email from newsletter_signatures left join newsletter_signatures_description on newsletter_signatures.id = newsletter_signatures_description.id');

        $data1 = [];
        $data2 = [];
        foreach($signatures as $signature) {
            array_push($data1, [
                'id' => $signature->id,
                'email' => $signature->email,
            ]);

            array_push($data2, [
                'id' => $signature->id,
                'name' => $signature->sender,
                'description' => $signature->name,
                'content' => $signature->content,
                'locale' => 'en',
                'signature_id' => $signature->id,
            ]);
        }

        Signature::insert($data1);
        SignatureTranslations::insert($data2);

        return 'Done';
    }

    public function recipients()
    {
        $recipients = \DB::connection('mysql-old')->select('select id, name, email from newsletter_recipients');

        $data = [];
        foreach($recipients as $recipient) {
            array_push($data, [
                'id' => $recipient->id,
                'name' => $recipient->name,
                'email' => $recipient->email,
            ]);
        }

        Recipient::insert($data);

        return 'Done';
    }

    public function bookings()
    {
        $bookings = \DB::connection('mysql-old')->select('select b.*, a.number from bookings b left join apartments a on a.id = b.apartments_id');

        $data1 = [];
        $data2 = [];
        foreach($bookings as $booking) {
            $apartment = Apartment::select('id', 'project_id', 'building_id')->where('number', $booking->number)->firstOrFail();

            array_push($data1, [
                'id' => $booking->id,
                'created_at' => $booking->date_added ?: null,
                'updated_at' => $booking->last_modified ?: null,
                'is_confirmed' => 1,
                'arrive_at' => $booking->arrival_date ?: null,
                'departure_at' => $booking->departure_date ?: null,
                'arrival_time' => $booking->arrival_time ?: null,
                'departure_time' => $booking->departure_time ?: null,
                'arrival_flight' => $booking->arrival_flight ?: null,
                'departure_flight' => $booking->departure_flight ?: null,
                'arrival_transfer' => ($booking->arrival_transfer_type == 1 ? 'car' : ($booking->arrival_transfer_type == 2 ? 'minibus' : null)),
                'departure_transfer' => ($booking->departure_transfer_type == 1 ? 'car' : ($booking->departure_transfer_type == 2 ? 'minibus' : null)),
                'loyalty_card' => ($booking->loyalty_card == 1 ? 1 : ($booking->loyalty_card == 2 ? 2 : ($booking->loyalty_card == 3 ? 0 : null))),
                'club_card' => ($booking->clubcard == 1 ? 1 : ($booking->clubcard == 2 ? 0 : null)),
                'kitchen_items' => ($booking->kitchen_items == 1 ? 1 : ($booking->kitchen_items == 2 ? 0 : null)),
                'message' => $booking->comments ?: null,
                'comments' => $booking->remarks ?: null,
                'project_id' => $apartment->project_id,
                'building_id' => $apartment->building_id,
                'apartment_id' => $apartment->id,
                'owner_id' => $booking->owners_id,
                'arrival_airport_id' => (($booking->arrival_airport == 'Бургас' || $booking->arrival_airport == 'Burgasb' || $booking->arrival_airport == 'Burgas/Burgas' || $booking->arrival_airport == 'Burgas' || $booking->arrival_airport == 'BURGAS' || $booking->arrival_airport == 'Bourgas' || $booking->arrival_airport == 'BOURGAS') ? 1 : ($booking->arrival_airport == 'Sofia' ? 4 : null)),
                'departure_airport_id' => (($booking->departure_airport == 'Burgas.' || $booking->departure_airport == 'Burgas' || $booking->departure_airport == 'BURGAS' || $booking->departure_airport == 'Bourgas' || $booking->departure_airport == 'BOURGAS' || $booking->departure_airport == 'Bougas') ? 1 : ($booking->departure_airport == 'Sofia' ? 4 : null)),
            ]);

            for ($i = 1; $i <= $booking->adults; $i++) {
                array_push($data2, [
                    'name' => $booking->{'adult_name' . $i},
                    'type' => 'adult',
                    'order' => $i,
                    'booking_id' => $booking->id,
                ]);
            }

            for ($i = 1; $i <= $booking->children; $i++) {
                array_push($data2, [
                    'name' => $booking->{'child_name' . $i},
                    'type' => 'child',
                    'order' => $i,
                    'booking_id' => $booking->id,
                ]);
            }
        }

        Booking::insert($data1);
        BookingGuest::insert($data2);

        return 'Done';
    }

}
