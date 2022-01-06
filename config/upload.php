<?php

return [

    'imagesDirectory' => 'images',
    'attachmentsDirectory' => 'attachments',
    'filesDirectory' => 'files',
    'chunksDirectory' => 'chunks',
    'originalDirectory' => 'o',
    'thumbnailDirectory' => 't',
    'thumbnailSmallDirectory' => 's',
    'thumbnailMediumDirectory' => 'm',
    'thumbnailLargeDirectory' => 'l',

    'imageExtensions' => ['jpg', 'png', 'gif', 'jpeg'],
    'fileExtensions' => ['jpg', 'png', 'gif', 'jpeg', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip', 'rar', 'ppt', 'pptx', 'odt', 'csv'],
    'quality' => 90,

    'imageMaxWidth' => 1920,
    'imageMaxHeight' => 1920,

    'newsletterWidth' => 600,

    'signatureWidth' => 600,
    'signatureHeight' => 480,

    'thumbnailWidth' => 120,
    'thumbnailHeight' => 90,
    'thumbnailSmallWidth' => 320,
    'thumbnailSmallHeight' => 240,
    'thumbnailMediumWidth' => 600,
    'thumbnailMediumHeight' => 480,
    'thumbnailLargeWidth' => 800,
    'thumbnailLargeHeight' => 600,

    'watermarkImage' => storage_path('app/images/watermark-logo.png'),
    'watermarkPosition' => 'center',
    'watermarkOffsetX' => 0,
    'watermarkOffsetY' => 0,

];
