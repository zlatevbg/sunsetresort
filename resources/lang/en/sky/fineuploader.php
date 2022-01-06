<?php

return [

	/*
	|--------------------------------------------------------------------------
	| FineUploader
	|--------------------------------------------------------------------------
	*/

    'errorServerMaxSize' => 'Server error. Increase post_max_size and upload_max_filesize to :size.',
    'errorUploadDirectoryNotWritable' => "Server error. Uploads directory isn't writable or executable.",
    'errorChunksDirectoryNotWritable' => "Server error. Chunks directory isn't writable or executable.",
    'errorUpload' => 'No files were uploaded.',
    'errorSave' => 'Could not save uploaded file. The upload was cancelled, or server error encountered.',
    'errorMultipart' => 'Server error. Not a multipart request. Please set forceMultipart to default value (true).',
    'errorFileNameEmpty' => 'File name empty.',
    'errorFileEmpty' => 'File is empty.',
    'errorFileExtension' => 'File has an invalid extension, it should be one of :extensions.',
    'errorFileSize' => 'File is too large.',

];
