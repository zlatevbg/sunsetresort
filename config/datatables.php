<?php

return [

    'sizes' => ['small', 'medium', 'large'],

    'clientSideLimit' => 100000,

    // number of pages to pipeline when using server side ajax loading
    'pipeline' => 10,

    // min number of records to enable paging
    'paging' => 10,

    // ajax search delay in milliseconds
    'searchDelay' => 400,

    'pagingType' => [
        'small' => 'numbers',
        'medium' => 'simple_numbers',
        'large' => 'full_numbers',
    ],

    'pageLength' => [
        'small' => 25,
        'medium' => 50,
        'large' => 100,
    ],

    'lengthMenu' => [
        'small' => '[[10, 25, 50, -1], [10, 25, 50, "all"]]',
        'medium' => '[[10, 25, 50, 100, 250, 500, -1], [10, 25, 50, 100, 250, 500, "all"]]',
        'large' => '[[10, 25, 50, 100, 250, 500, 1000], [10, 25, 50, 100, 250, 500, 1000]]',
    ],

];
