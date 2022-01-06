<?php

return [

	/*
	|--------------------------------------------------------------------------
	| JavaScript Plugins
	|--------------------------------------------------------------------------
	|
	*/

    'magnificPopup' => [
        'prev' => 'Previous (Left arrow key)',
        'next' => 'Next (Right arrow key)',
        'close' => 'Close (ESC key)',
        'loading' => 'Loading...',
        'ajaxError' => '<a href="%url%">The content</a> could not be loaded.',
    ],

    'multiselect' => [
        'checkAll' => 'Check all',
        'uncheckAll' => 'Uncheck all',
        'noneSelected' => 'Select options',
        'noneSelectedSingle' => 'Select option',
        'selected' => '# of # selected',
        'filterLabel' => '',
        'filterPlaceholder' => 'Enter keywords',
    ],

    'fineUploader' => [
        'uploadComplete' => 'Upload complete.',
        'defaultResponseError' => 'Upload failure reason unknown.',
        'emptyError' => '{file} is empty.',
        'maxHeightImageError' => 'Image is too tall.',
        'maxWidthImageError' => 'Image is too wide.',
        'minHeightImageError' => 'Image is not tall enough.',
        'minWidthImageError' => 'Image is not wide enough.',
        'minSizeError' => '{file} is too small, minimum file size is {minSizeLimit}.',
        'noFilesError' => 'No files to upload.',
        'onLeave' => 'The files are being uploaded, if you leave now the upload will be canceled.',
        'retryFailTooManyItemsError' => 'Retry failed - you have reached your file limit.',
        'sizeError' => '{file} is too large, maximum file size is {sizeLimit}.',
        'tooManyItemsError' => 'Too many items ({netItems}) would be uploaded. Item limit is {itemLimit}.',
        'typeError' => '{file} has an invalid extension. Valid extension(s): {extensions}.',
        'unsupportedBrowserIos8Safari' => 'Unrecoverable error - this browser does not permit file uploading of any kind due to serious bugs in iOS8 Safari. Please use iOS8 Chrome until Apple fixes these issues.',
    ],

    'datepicker' => [
        'en' => [
            'closeText' => 'Done',
            'prevText' => 'Prev',
            'nextText' => 'Next',
            'currentText' => 'Today',
            'monthNames' => ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
            'monthNamesShort' => ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
            'dayNames' => ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
            'dayNamesShort' => ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
            'dayNamesMin' => ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"],
            'weekHeader' => 'Wk',
            'dateFormat' => 'dd.mm.yy',
            'firstDay' => 1,
            'isRTL' => false,
            'showMonthAfterYear' => false,
            'yearSuffix' => '',
            'changeMonth' => true,
            'changeYear' => true,
            'showOtherMonths' => true,
        ],
    ],

];
