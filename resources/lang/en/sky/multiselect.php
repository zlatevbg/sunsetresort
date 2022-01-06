<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Multiselect options
    |--------------------------------------------------------------------------
    |
    */

    'navigationPageTypes' => [
        'notices' => 'Notices',
        'rental-options' => 'Rental Options',
        'cm' => 'Condominium & Management',
    ],

    'sex' => [
        'female' => 'Female',
        'male' => 'Male',
        'not-applicable' => 'Not Applicable',
        'not-known' => 'Not Known',
    ],

    'rentalPeriodsTypes' => [
        'open' => 'Open',
        'close' => 'Close',
        'personal-usage' => 'Personal Usage',
    ],

    'mmTaxFormula' => [
        0 => 'Phase I = ((Apartment + Common + Balcony) * MMTax) + (ExtraBalcony * (MMTax / 2))',
        1 => 'Phase II = Total * MMTax',
    ],

    'exceptions' => [
        0 => 'No',
        1 => 'Yes',
    ],

    'questions' => [
        '' => '-',
        '1' => '✓',
    ],

    'reportExceptions' => [
        '' => 'Not set',
        0 => 'No',
        1 => 'Yes',
    ],

    'reportOverlapDates' => [
        0 => 'No',
        1 => 'Yes',
    ],

    'reportMmFeesType' => [
        'due' => 'MM Fees Due',
        'due-by-owner' => 'MM Fees Due by Owner',
        'due-by-rental' => 'MM Fees Due by Rental',
        'paid' => 'MM Fees Paid',
        'paid-by-owner' => 'MM Fees Paid by Owner',
        'paid-by-rental' => 'MM Fees Paid by Rental',
    ],

    'reportCommunalFeesType' => [
        'due' => 'Communal Fees Due',
        'due-by-owner' => 'Communal Fees Due by Owner',
        'due-by-rental' => 'Communal Fees Due by Rental',
        'paid' => 'Communal Fees Paid',
        'paid-by-owner' => 'Communal Fees Paid by Owner',
        'paid-by-rental' => 'Communal Fees Paid by Rental',
    ],

    'reportPoolUsageType' => [
        'due' => 'Pool Usage Tax Due',
        'due-by-owner' => 'Pool Usage Tax Due by Owner',
        'due-by-rental' => 'Pool Usage Tax Due by Rental',
        'paid' => 'Pool Usage Tax Paid',
        'paid-by-owner' => 'Pool Usage Tax Paid by Owner',
        'paid-by-rental' => 'Pool Usage Tax Paid by Rental',
    ],

    'reportRentalPaymentsType' => [
        'due' => 'Rental Due',
        'due-all' => 'Rental Due All (for accounting)',
        'paid' => 'Rental Paid',
    ],

    'reportGroupBy' => [
        '' => 'Not set',
        'project' => 'Project',
        'building' => 'Building',
    ],

    'reportMode' => [
        'compact' => 'Compact',
        'extended' => 'Extended',
    ],

    'reportGroupByAgents' => [
        '' => 'Not set',
        'apartment' => 'Apartment',
        'agent' => 'Agent',
        'project' => 'Project',
        'building' => 'Building',
    ],

    'reportGroupByKeyholders' => [
        '' => 'Not set',
        'apartment' => 'Apartment',
        'keyholder' => 'Keyholder',
        'project' => 'Project',
        'building' => 'Building',
    ],

    'reportGroupByOwnership' => [
        '' => 'Not set',
        'project' => 'Project',
        'building' => 'Building',
        'owner' => 'Owner',
    ],

    'reportGroupByLegalRepresentatives' => [
        '' => 'Not set',
        'apartment' => 'Apartment',
        'legalRepresentative' => 'Legal Representative',
        'project' => 'Project',
        'building' => 'Building',
    ],

    'reportRental' => [
        '' => 'Not set',
        0 => 'Rental',
        1 => 'Non-Rental',
    ],

    'reportPoaStatus' => [
        '' => 'Not set',
        0 => 'Unsigned',
        1 => 'Signed',
    ],

    'reportRentalOptions' => [
        'rental' => 'All Rental Options',
        'non-rental' => 'Non-Rental',
    ],

    'reportRCT' => [
        '' => 'Not set',
        'no-poa' => 'No POA',
        'no-contract' => 'No Contract',
        'no-contract-no-poa' => 'No Contract & No POA',
        'contract-no-poa' => 'Contract & No POA',
    ],

    'reportCFCT' => [
        '' => 'Not set',
        'no-contract' => 'No Contract',
        'contract' => 'Contract',
    ],

    'reportPUCT' => [
        '' => 'Not set',
        'no-contract' => 'No Contract',
        'contract' => 'Contract',
    ],

    'applyWt' => [
        0 => 'No',
        1 => 'Yes',
    ],

    'feesTypes' => [
        'annual_communal_tax' => 'Annual Communal Tax',
        'daily_communal_tax' => 'Daily Communal Tax',
        'pool_tax' => 'Pool Tax',
        'pool_bracelets' => 'Pool Bracelets',
        'aquapark_tax' => 'Aquapark Tax',
        'pool_aquapark_tax' => 'Pool & Aquapark Tax',
    ],

    'outstandingBills' => [
        0 => 'No',
        1 => 'Yes',
    ],

    'lettingOffer' => [
        0 => 'No',
        1 => 'Yes',
    ],

    'srioc' => [
        0 => 'No',
        1 => 'Yes',
    ],

    'newsletterSubscription' => [
        0 => 'No',
        1 => 'Yes',
    ],

    'ownerProperties' => [
        'apartments' => 'Apartments',
        'former-apartments' => 'Former Apartments',
        'bank-accounts' => 'Bank Accounts',
        'council-tax' => 'Council Tax',
        'notices' => 'Notices',
        'files' => 'Files',
    ],

    'apartmentProperties' => [
        'contracts' => 'Rental Contracts',
        'mm-fees' => 'MM Fees',
        'communal-fees' => 'Communal Fees',
        'pool-usage' => 'Pool Usage',
        'owners' => 'Owners',
        'former-owners' => 'Former Owners',
        'agents' => 'Agents',
        'keyholders' => 'Keyholders',
        'legal-representatives' => 'Legal Representatives',
        'maintenance-issues' => 'Maintenance Issues',
    ],

    'contractProperties' => [
        'documents' => 'Documents',
        'deductions' => 'Deductions',
        'payments' => 'Payments',
    ],

    'buildingProperties' => [
        'floors' => 'Floors',
        'mm' => 'Maintenance & Management',
        'condominium' => 'Condominium',
    ],

    'newsletterProperties' => [
        'attachments' => 'Attachments',
        'attachments-apartment' => 'Attachments By Apartment',
        'attachments-owner' => 'Attachments By Owner',
        'images' => 'Inline Images',
    ],

    'newsletterFilters' => [
        'projects' => 'Projects',
        'buildings' => 'Buildings',
        'floors' => 'Floors',
        'rooms' => 'Rooms',
        'furniture' => 'Furniture',
        'views' => 'Views',
        'apartments' => 'Apartments',
        'owners' => 'Owners',
        'countries' => 'Countries',
        'language' => 'Language',
        'recipients' => 'Recipients',
        'year' => 'Year',
    ],

    'newsletterTemplates' => [
        '' => 'Newsletter',
        'booking-form' => 'Booking Form (automatic)',
        'communal-fee-contract' => 'Communal Fee Contract (automatic)',
        'newsletter-bank-account-details' => 'Newsletter: Bank Account Details (automatic)',
        'newsletter-council-tax-letter' => 'Newsletter: Council Tax Letter (automatic)',
        'newsletter-first-reminder-rental-campaign' => 'Newsletter: First Reminder Rental Campaign',
        'newsletter-last-reminder-to-return-rental-contract' => 'Newsletter: Last Reminder To Return Rental Contract',
        'newsletter-mm-fees' => 'Newsletter: MM Fees (automatic)',
        'newsletter-mm-fees-first-reminder' => 'Newsletter: MM Fees First Reminder (automatic)',
        'newsletter-mm-fees-second-reminder' => 'Newsletter: MM Fees Second Reminder (automatic)',
        'newsletter-mm-fees-final-reminder' => 'Newsletter: MM Fees Final Reminder (automatic)',
        'newsletter-mm-payment-confirmation' => 'Newsletter: MM Payment Confirmation (automatic)',
        'newsletter-occupancy' => 'Newsletter: Occupancy and generated rental income report (automatic)',
        'newsletter-reminder-returned-contract-but-no-lor' => 'Newsletter: Reminder Returned Contract But No LOR',
        'newsletter-reminder-rental-request-but-no-return' => 'Newsletter: Reminder Rental Request But No Return',
        'newsletter-reminder-rental-options-no-decision' => 'Newsletter: Reminder Rental Options No Decision',
        'newsletter-rental-options' => 'Newsletter: Rental Options',
        'newsletter-rental-payment-confirmation' => 'Newsletter: Rental Payment Confirmation (automatic)',
        'newsletter-rental-payment-income-tax-only' => 'Newsletter: Rental Payment - Income Tax Only (automatic)',
        'newsletter-self-renting-apartments' => 'Newsletter: Self Renting Apartments',
        'new-profile' => 'New Owner Profile (automatic)',
        'pf' => 'Password Forgotten (automatic)',
        'pool-usage-contract' => 'Pool Usage Contract (automatic)',
        'poa' => 'POA',
        'rental-contract' => 'Rental Contract',
        'rental-contract-no-poa' => 'Rental Contract No POA',
    ],

    'mmCoveredOptions' => [
        '0' => 'No',
        '50' => 'Yes - 50%',
        '100' => 'Yes - 100%',
    ],

    'rentalAmountOptions' => [
        '0' => '0',
        '34' => '34%',
        '50' => '50%',
        '66' => '66%',
        '100' => '100%',
    ],

    'buildingMMDocuments' => [
        'rules' => 'Rules & Regulations',
        'insurance' => 'Insurance Certificate',
        'receipts' => 'Insurance Certificate Payment Receipts',
        'accounts' => 'Audited Accounts',
        'ier' => 'Income and Expenditure Report',
        'budget' => 'Budget',
        'electricity' => 'EVN Invoices Electricity',
        'water' => 'Water and Sewer Invoices',
        'eur-account' => 'Management Company EUR account',
        'bgn-account' => 'Management Company BGN account',
        'communal-fee-en' => 'Communal Fee Contract (English)',
        'communal-fee-ru' => 'Communal Fee Contract (Russian)',
        'court-decision' => 'Court Decision',
        'audit-report-condominium' => 'Audit Report NRA 2015-2020 Condominium',
        'audit-conclusion-condominium' => 'Audit Conclusion NRA 2015-2020 Condominium',
        'audit-report-management' => 'Audit Report NRA 2015-2020 Management Company',
        'audit-conclusion-management' => 'Audit Conclusion NRA 2015-2020 Management Company',
    ],

    'condominiumDocuments' => [
        'minutes' => 'General Assembly Meeting Minutes',
    ],

    'contractDocuments' => [
        'contract' => 'Rental Contract',
        'annex' => 'Annex',
    ],

    'contractPaymentDocuments' => [
        'invoice' => 'Invoice',
    ],

    'mmPaymentDocuments' => [
        'invoice' => 'Invoice',
    ],

    'communalPaymentDocuments' => [
        'invoice' => 'Invoice',
    ],

    'poolPaymentDocuments' => [
        'invoice' => 'Invoice',
    ],

    'maintenanceStatusOptions' => [
        'open' => 'Open',
        'pending' => 'Pending',
        'completed' => 'Completed',
    ],

    'maintenanceResponsibilityOptions' => [
        'owner' => 'Owner',
        'condominium' => 'Condominium',
        'rental-company' => 'Rental Company',
    ],

];
