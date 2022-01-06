<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'tokenMismatchException' => 'Seems you could not submit form for a longtime. Please try again.',

    'accepted'             => 'The :attribute must be accepted.',
    'active_url'           => 'The :attribute is not a valid URL.',
    'after'                => 'The :attribute must be a date after :date.',
    'alpha'                => 'The :attribute may only contain letters.',
    'alpha_dash'           => 'The :attribute may only contain letters, numbers, and dashes.',
    'alpha_num'            => 'The :attribute may only contain letters and numbers.',
    'array'                => 'The :attribute must be an array.',
    'before'               => 'The :attribute must be a date before :date.',
    'between'              => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file'    => 'The :attribute must be between :min and :max kilobytes.',
        'string'  => 'The :attribute must be between :min and :max characters.',
        'array'   => 'The :attribute must have between :min and :max items.',
    ],
    'boolean'              => 'The :attribute field must be true or false.',
    'confirmed'            => 'The :attribute confirmation does not match.',
    'date'                 => 'The :attribute is not a valid date.',
    'date_format'          => 'The :attribute does not match the format :format.',
    'different'            => 'The :attribute and :other must be different.',
    'digits'               => 'The :attribute must be :digits digits.',
    'digits_between'       => 'The :attribute must be between :min and :max digits.',
    'distinct'             => 'The :attribute field has a duplicate value.',
    'email'                => 'The :attribute must be a valid email address.',
    'exists'               => 'The selected :attribute is invalid.',
    'filled'               => 'The :attribute field is required.',
    'image'                => 'The :attribute must be an image.',
    'in'                   => 'The selected :attribute is invalid.',
    'in_array'             => 'The :attribute field does not exist in :other.',
    'integer'              => 'The :attribute must be an integer.',
    'ip'                   => 'The :attribute must be a valid IP address.',
    'json'                 => 'The :attribute must be a valid JSON string.',
    'max'                  => [
        'numeric' => 'The :attribute may not be greater than :max.',
        'file'    => 'The :attribute may not be greater than :max kilobytes.',
        'string'  => 'The :attribute may not be greater than :max characters.',
        'array'   => 'The :attribute may not have more than :max items.',
    ],
    'mimes'                => 'The :attribute must be a file of type: :values.',
    'min'                  => [
        'numeric' => 'The :attribute must be at least :min.',
        'file'    => 'The :attribute must be at least :min kilobytes.',
        'string'  => 'The :attribute must be at least :min characters.',
        'array'   => 'The :attribute must have at least :min items.',
    ],
    'not_in'               => 'The selected :attribute is invalid.',
    'numeric'              => 'The :attribute must be a number.',
    'present'              => 'The :attribute field must be present.',
    'regex'                => 'The :attribute format is invalid.',
    'required'             => 'The :attribute field is required.',
    'required_if'          => 'The :attribute field is required when :other is :value.',
    'required_unless'      => 'The :attribute field is required unless :other is in :values.',
    'required_with'        => 'The :attribute field is required when :values is present.',
    'required_with_all'    => 'The :attribute field is required when :values is present.',
    'required_without'     => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same'                 => 'The :attribute and :other must match.',
    'size'                 => [
        'numeric' => 'The :attribute must be :size.',
        'file'    => 'The :attribute must be :size kilobytes.',
        'string'  => 'The :attribute must be :size characters.',
        'array'   => 'The :attribute must contain :size items.',
    ],
    'string'               => 'The :attribute must be a string.',
    'timezone'             => 'The :attribute must be a valid zone.',
    'unique'               => 'The :attribute has already been taken.',
    'url'                  => 'The :attribute format is invalid.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

   'poa' => 'POA for selected apartment, owner and years has already been used!',

    'custom' => [
        'g-recaptcha-response' => [
            'required' => 'Please, click on the checkbox "I\'m not a robot"!',
        ],
        'q1' => [
            'required_without' => 'q1',
        ],
        'q2' => [
            'required_without' => 'q2',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'name' => 'Name',
        'manager' => 'Manager',
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'sex' => 'Sex',
        'countries' => 'Countries',
        'apartments' => 'Apartments',
        'languages' => 'Languages',
        'subject' => 'Subject',
        'teaser' => 'Teaser',
        'template' => 'Template',
        'body' => 'Message body',
        'year' => 'Year',
        'phone' => 'Phone',
        'mobile' => 'Mobile',
        'number' => 'Number',
        'apartment_area' => 'Apartment Area',
        'balcony_area' => 'Balcony Area',
        'extra_balcony_area' => 'Extra Balcony Area',
        'common_area' => 'Common Area',
        'total_area' => 'Total Area',
        'room_id' => 'Room Type',
        'furniture_id' => 'Furniture',
        'view_id' => 'View',
        'project_id' => 'Project',
        'building_id' => 'Building',
        'apartment_id' => 'Apartment',
        'owner_id' => 'Owner',
        'floor_id' => 'Floor',
        'language' => 'Language',
        'capacity' => 'Capacity',
        'country' => 'Country',
        'country_id' => 'Country',
        'locale_id' => 'Language',
        'city' => 'City',
        'postcode' => 'Postcode',
        'address' => 'Address',
        'address1' => 'Address Line 1',
        'address2' => 'Address Line 2',
        'bank_name' => 'Bank Name',
        'bank_bic' => 'BIC',
        'bank_iban' => 'IBAN',
        'bank_beneficiary' => 'Beneficiary',
        'namespace' => 'Namespace',
        'email' => 'E-mail Address',
        'email_cc' => 'CC E-mail Addresses',
        'password' => 'Password',
        'slug' => 'Slug',
        'dfrom' => 'Date From',
        'from' => 'From',
        'dto' => 'Date To',
        'to' => 'To',
        'order' => 'Order',
        'egn' => 'ЕГН',
        'id_card' => 'ID Card / Passport',
        'route' => 'Route',
        'locale' => 'Locale',
        'locales' => 'Locales',
        'default_locale_id' => 'Default Locale',
        'native' => 'Native Name',
        'script' => 'Script',
        'content' => 'Content',
        'comments' => 'Comments',
        'message' => 'Message',
        'bulstat' => 'Bulstat',
        'tax_pin' => 'Tax PIN',
        'tax' => 'Tax',
        'amount' => 'Amount',
        'price' => 'Price',
        'checked_at' => 'Checked At',
        'deadline_at' => 'Deadline At',
        'issued_at' => 'Issued At',
        'issued_by' => 'Issued By',
        'signed_at' => 'Signed At',
        'paid_at' => 'Paid At',
        'assembly_at' => 'General Assembly Date',
        'arrive_at' => 'Arrival Date',
        'departure_at' => 'Departure Date',
        'arrival_time' => 'Arrival Time',
        'departure_time' => 'Departure Time',
        'arrival_flight' => 'Arrival Flight',
        'departure_flight' => 'Departure Flight',
        'arrival_transfer' => 'Arrival Transfer',
        'departure_transfer' => 'Departure Transfer',
        'arrival_airport_id' => 'Arrival Airport',
        'departure_airport_id' => 'Departure Airport',
        'is_taxable' => 'Taxable?',
        'is_company' => 'Is this a company?',
        'corporate_tax' => 'Corporate Tax',
        'mm_tax' => 'MM Tax',
        'mm_covered' => 'Contract Covers MM Fees?',
        'max_duration' => 'Max Contract Duration (years)',
        'duration' => 'Contract Duration (years)',
        'contract_dfrom1' => 'Contract Date From: 1',
        'contract_dto1' => 'Contract Date To: 1',
        'contract_dfrom2' => 'Contract Date From: 2',
        'contract_dto2' => 'Contract Date To: 2',
        'personal_dfrom1' => 'Personal Date From: 1',
        'personal_dto1' => 'Personal Date To: 1',
        'personal_dfrom2' => 'Personal Date From: 2',
        'personal_dto2' => 'Personal Date To: 2',
        'rental_payment_id' => 'Rental Payment Option',
        'rental_contract_id' => 'Rental Contract',
        'proxy_id' => 'Proxy',
        'mm_for_year' => 'MM Fees for Year',
        'deduction_id' => 'Deduction',
        'signature_id' => 'Signature',
        'payment_method_id' => 'Payment Method',
        'rental_company_id' => 'Rental Company',
        'companies' => 'Rental Company',
        'management_company_id' => 'Management Company',
        'recipients' => 'Recipients',
        'projects' => 'Projects',
        'owners' => 'Owners',
        'rooms' => 'Rooms',
        'furniture' => 'Furniture',
        'views' => 'Views',
        'services' => 'Extra Services',
        'kitchen_items' => 'Kitchen Items',
        'loyalty_card' => 'Loyalty Card',
        'club_card' => 'Club Card',
        'exception' => 'Exception',
        'mergeField' => 'Merge Field',
        'adultField' => 'Adult Name',
        'childField' => 'Child Name',
        'is_exception' => 'Exception',
        'apply_wt' => 'Apply WT on Rental Income?',
        'mm_tax_formula' => 'MM Tax Formula',
        'merge_by' => 'Merge By',
        'merge' => [
            0 => 'Merge Field 1',
        ],
        'adults' => [
            0 => 'Adult',
        ],
        'children' => [
            0 => 'Child',
        ],
    ],

];
