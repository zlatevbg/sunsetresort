<?php

namespace App\Services;

use Illuminate\Http\Request;
use Storage;
use File;

class FineUploader
{
    public $request;
    public $status = 200;
    public $allowedExtensions = [];
    public $sizeLimit = null;
    public $inputName = 'qqfile';
    public $chunksDirectory;
    public $chunksPath;
    public $chunksDisk = 'local-private';
    public $uploadDirectory;
    public $uploadPath;
    public $uploadDisk = 'local-public';
    public $isImage = false;
    public $isFile = false;
    public $thumbnail = true;
    public $thumbnailSmall = false;
    public $thumbnailMedium = false;
    public $thumbnailLarge = false;
    public $watermark = false;
    public $resize = false;
    public $newsletter = false;
    public $signature = false;

    public $chunksCleanupProbability = 0.001; // Once in 1000 requests on avg
    public $chunksExpireIn = 604800; // One week

    protected $uploadName;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->isImage = true;
        $this->chunksDirectory = \Config::get('upload.chunksDirectory');
        $this->allowedExtensions = \Config::get('upload.imageExtensions');
        $this->chunksPath = $this->getDiskPath($this->chunksDisk);
        $this->uploadPath = $this->getDiskPath($this->uploadDisk);
    }

    /**
     * Get the original filename
     */
    public function getName()
    {
        $name = null;
        if ($this->request->has('qqfilename')) {
            $name = $this->request->input('qqfilename');
        } elseif ($this->request->hasFile($this->inputName)) {
            $name = $this->request->file($this->inputName)->getClientOriginalName();
        }

        $ext = strtolower(File::extension($name));
        $name = str_slug(File::name($name)) . '.' . $ext; // use lowercase extension

        return $name;
    }

    /**
     * Get the name of the uploaded file
     */
    public function getUploadName()
    {
        return $this->uploadName;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function combineChunks($uuid = true)
    {
        if ($uuid) {
            $uuid = $this->request->input('qquuid');
        }

        $chunksDirectory = $this->chunksDirectory . ($uuid ? DIRECTORY_SEPARATOR . $uuid : '');
        if (Storage::disk($this->chunksDisk)->exists($chunksDirectory)) {
            $this->uploadName = $this->getName();
            $totalParts = (int)$this->request->input('qqtotalparts', 1);

            $rootDirectory = $this->uploadDirectory . ($uuid ? DIRECTORY_SEPARATOR . $uuid : '');
            if (!Storage::disk($this->uploadDisk)->exists($rootDirectory)) {
                Storage::disk($this->uploadDisk)->makeDirectory($rootDirectory);
            }

            if ($this->isImage) {
                $uploadDirectory = $rootDirectory . DIRECTORY_SEPARATOR . \Config::get('upload.originalDirectory');
                if (!Storage::disk($this->uploadDisk)->exists($uploadDirectory)) {
                    Storage::disk($this->uploadDisk)->makeDirectory($uploadDirectory);
                }
            } else {
                $uploadDirectory = $rootDirectory;
            }

            $uploadFile = $uploadDirectory . DIRECTORY_SEPARATOR . $this->getUploadName();

            $destination = fopen($this->uploadPath . $uploadFile, 'wb');

            for ($i = 0; $i < $totalParts; $i++) {
                $source = fopen($this->chunksPath . $chunksDirectory . DIRECTORY_SEPARATOR . $i, 'rb');
                stream_copy_to_stream($source, $destination);
                fclose($source);
            }

            fclose($destination);

            Storage::disk($this->chunksDisk)->deleteDirectory($chunksDirectory);

            $size = Storage::disk($this->uploadDisk)->size($uploadFile);
            if (!is_null($this->sizeLimit) && $size > $this->sizeLimit) {
                Storage::disk($this->uploadDisk)->delete($uploadFile);
                $this->status = 413;
                return ['success' => false, 'uuid' => $uuid, 'preventRetry' => true];
            }

            if ($this->isImage) {
                $size = $this->processUploaded($rootDirectory, $this->getUploadName());
            }

            return [
                'success'=> true,
                'uuid' => $uuid,
                'fileName' => $this->getUploadName(),
                'fileExtension' => File::extension($this->getUploadName()),
                'fileSize' => $size,
            ];
        }
    }

    /**
     * Process the upload.
     * @param string $name Overwrites the name of the file.
     */
    public function handleUpload($name = null, $uuid = true)
    {
        clearstatcache();

        if (File::isWritable($this->chunksPath . $this->chunksDirectory) && 1 == mt_rand(1, 1 / $this->chunksCleanupProbability)) {
            $this->cleanupChunks();
        }

        // Check that the max upload size specified in class configuration does not exceed size allowed by server config
        if ($this->toBytes(ini_get('post_max_size')) < $this->sizeLimit || $this->toBytes(ini_get('upload_max_filesize')) < $this->sizeLimit) {
            $neededRequestSize = max(1, $this->sizeLimit / 1024 / 1024) . 'M';
            return ['error' => trans(\Locales::getNamespace() . '/fineuploader.errorServerMaxSize', ['size' => $neededRequestSize]), 'preventRetry' => true];
        }

        if (!File::isWritable($this->uploadPath . $this->uploadDirectory) && !is_executable($this->uploadPath . $this->uploadDirectory)) {
            return ['error' => trans(\Locales::getNamespace() . '/fineuploader.errorUploadDirectoryNotWritable'), 'preventRetry' => true];
        }

        $type = $this->request->server('HTTP_CONTENT_TYPE', $this->request->server('CONTENT_TYPE'));

        if (!$type) {
            return ['error' => trans(\Locales::getNamespace() . '/fineuploader.errorUpload')];
        } else if (strpos(strtolower($type), 'multipart/') !== 0) {
            return ['error' => trans(\Locales::getNamespace() . '/fineuploader.errorMultipart')];
        }

        $file = $this->request->file($this->inputName);
        $size = $this->request->input('qqtotalfilesize', $file->getSize());

        if (is_null($name)) {
            $name = $this->getName();
        }

        if (is_null($name) || empty($name)) {
            return ['error' => trans(\Locales::getNamespace() . '/fineuploader.errorFileNameEmpty')];
        }

        if (empty($size)) {
            return ['error' => trans(\Locales::getNamespace() . '/fineuploader.errorFileEmpty')];
        }

        if (!is_null($this->sizeLimit) && $size > $this->sizeLimit) {
            return ['error' => trans(\Locales::getNamespace() . '/fineuploader.errorFileSize'), 'preventRetry' => true];
        }

        $ext = strtolower(File::extension($name));
        $this->uploadName = $name;

        if ($this->allowedExtensions && !in_array($ext, array_map('strtolower', $this->allowedExtensions))) {
            $these = implode(', ', $this->allowedExtensions);
            return ['error' => trans(\Locales::getNamespace() . '/fineuploader.errorFileExtension', ['extensions' => $these]), 'preventRetry' => true];
        }

        $totalParts = (int)$this->request->input('qqtotalparts', 1);
        if ($uuid) {
            $uuid = $this->request->input('qquuid');
        }

        if ($totalParts > 1) { // chunked upload
            $partIndex = (int)$this->request->input('qqpartindex');

            if (!File::isWritable($this->chunksPath . $this->chunksDirectory) && !is_executable($this->chunksPath . $this->chunksDirectory)){
                return ['error' => trans(\Locales::getNamespace() . '/fineuploader.errorChunksDirectoryNotWritable'), 'preventRetry' => true];
            }

            $chunksDirectory = $this->chunksDirectory . ($uuid ? DIRECTORY_SEPARATOR . $uuid : '');

            if (!Storage::disk($this->chunksDisk)->exists($chunksDirectory)) {
                Storage::disk($this->chunksDisk)->makeDirectory($chunksDirectory);
            }

            $file->move($this->chunksPath . $chunksDirectory, $partIndex);

            return ['success' => true, 'uuid' => $uuid];
        } else { // non-chunked upload
            $rootDirectory = $this->uploadDirectory . ($uuid ? DIRECTORY_SEPARATOR . $uuid : '');
            if (!Storage::disk($this->uploadDisk)->exists($rootDirectory)) {
                Storage::disk($this->uploadDisk)->makeDirectory($rootDirectory);
            }

            if ($this->isImage) {
                $uploadDirectory = $rootDirectory . DIRECTORY_SEPARATOR . \Config::get('upload.originalDirectory');
                if (!Storage::disk($this->uploadDisk)->exists($uploadDirectory)) {
                    Storage::disk($this->uploadDisk)->makeDirectory($uploadDirectory);
                }
            } else {
                $uploadDirectory = $rootDirectory;
            }

            if (($response = $file->move($this->uploadPath . $uploadDirectory, $this->getUploadName())) !== false) {
                if ($this->isImage) {
                    $size = $this->processUploaded($rootDirectory, $this->getUploadName());
                }

                return [
                    'success'=> true,
                    'uuid' => $uuid,
                    'fileName' => $response->getFilename(),
                    'fileExtension' => $response->getExtension(),
                    'fileSize' => $size,
                ];
            }

            return ['error' => trans(\Locales::getNamespace() . '/fineuploader.errorSave')];
        }
    }

    protected function processUploaded($directory, $filename) {
        $file = $this->uploadPath . $directory . DIRECTORY_SEPARATOR . \Config::get('upload.originalDirectory') . DIRECTORY_SEPARATOR . $filename;

        if ($this->resize) {
            $img = \Image::make($file)->orientate();

            if ($img->width() > \Config::get('upload.imageMaxWidth') || $img->height() > \Config::get('upload.imageMaxHeight')) {
                $img->resize(\Config::get('upload.imageMaxWidth'), \Config::get('upload.imageMaxHeight'), function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            if ($this->watermark) {
                $img->insert(\Config::get('upload.watermarkImage'), \Config::get('upload.watermarkPosition'), \Config::get('upload.watermarkOffsetX'), \Config::get('upload.watermarkOffsetY'));
            }

            $img->save($this->uploadPath . $directory . DIRECTORY_SEPARATOR . $filename, \Config::get('upload.quality'));
            $size = $img->filesize();
        }

        if ($this->thumbnail) {
            $thumbnailDirectory = $directory . DIRECTORY_SEPARATOR . \Config::get('upload.thumbnailDirectory');
            if (!Storage::disk($this->uploadDisk)->exists($thumbnailDirectory)) {
                Storage::disk($this->uploadDisk)->makeDirectory($thumbnailDirectory);
            }

            $thumb = \Image::make($file)->orientate();

            $thumb->resize(\Config::get('upload.thumbnailWidth'), \Config::get('upload.thumbnailHeight'), function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $thumb->save($this->uploadPath . $thumbnailDirectory . DIRECTORY_SEPARATOR . $filename, \Config::get('upload.quality'));
        }

        if ($this->thumbnailSmall) {
            $thumbnailSmallDirectory = $directory . DIRECTORY_SEPARATOR . \Config::get('upload.thumbnailSmallDirectory');
            if (!Storage::disk($this->uploadDisk)->exists($thumbnailSmallDirectory)) {
                Storage::disk($this->uploadDisk)->makeDirectory($thumbnailSmallDirectory);
            }

            $thumb = \Image::make($file)->orientate();

            $thumb->resize(\Config::get('upload.thumbnailSmallWidth'), \Config::get('upload.thumbnailSmallHeight'), function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $thumb->save($this->uploadPath . $thumbnailSmallDirectory . DIRECTORY_SEPARATOR . $filename, \Config::get('upload.quality'));
        }

        if ($this->thumbnailMedium) {
            $thumbnailMediumDirectory = $directory . DIRECTORY_SEPARATOR . \Config::get('upload.thumbnailMediumDirectory');
            if (!Storage::disk($this->uploadDisk)->exists($thumbnailMediumDirectory)) {
                Storage::disk($this->uploadDisk)->makeDirectory($thumbnailMediumDirectory);
            }

            $thumb = \Image::make($file)->orientate();

            $thumb->resize(\Config::get('upload.thumbnailMediumWidth'), \Config::get('upload.thumbnailMediumHeight'), function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $thumb->save($this->uploadPath . $thumbnailMediumDirectory . DIRECTORY_SEPARATOR . $filename, \Config::get('upload.quality'));
        }

        if ($this->thumbnailLarge) {
            $thumbnailLargeDirectory = $directory . DIRECTORY_SEPARATOR . \Config::get('upload.thumbnailLargeDirectory');
            if (!Storage::disk($this->uploadDisk)->exists($thumbnailLargeDirectory)) {
                Storage::disk($this->uploadDisk)->makeDirectory($thumbnailLargeDirectory);
            }

            $thumb = \Image::make($file)->orientate();

            $thumb->resize(\Config::get('upload.thumbnailLargeWidth'), \Config::get('upload.thumbnailLargeHeight'), function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $thumb->save($this->uploadPath . $thumbnailLargeDirectory . DIRECTORY_SEPARATOR . $filename, \Config::get('upload.quality'));
        }

        if ($this->newsletter) {
            $newsletter = \Image::make($file)->orientate();

            $newsletter->widen(\Config::get('upload.newsletterWidth'), function ($constraint) {
                $constraint->upsize();
            });

            $newsletter->save($this->uploadPath . $directory . DIRECTORY_SEPARATOR . $filename, \Config::get('upload.quality'));
            if (!$this->resize) {
                $size = $newsletter->filesize();
            }
        }

        if ($this->signature) {
            $signature = \Image::make($file)->orientate();

            $signature->resize(\Config::get('upload.signatureWidth'), \Config::get('upload.signatureHeight'), function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $signature->save($this->uploadPath . $directory . DIRECTORY_SEPARATOR . $filename, \Config::get('upload.quality'));
            if (!$this->resize) {
                $size = $signature->filesize();
            }
        }

        return $size;
    }

    /**
     * Deletes all file parts in the chunks directory for files uploaded
     * more than chunksExpireIn seconds ago
     */
    protected function cleanupChunks()
    {
        foreach (Storage::disk($this->chunksDisk)->directories($this->chunksDirectory) as $dir) {
            $path = $this->chunksDirectory . DIRECTORY_SEPARATOR . $dir;

            if ($time = @filemtime($this->chunksPath . $path)) {
                if (time() - $time > $this->chunksExpireIn) {
                    Storage::disk($this->chunksDisk)->deleteDirectory($path);
                }
            }
        }
    }

    /**
     * Converts a given size with units to bytes.
     * @param string $str
     */
    protected function toBytes($str)
    {
        $val = (int)trim($str);
        $last = strtolower($str[strlen($str) - 1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;
        }
        return $val;
    }

    protected function getDiskPath($disk = null)
    {
        if ($disk) {
            return Storage::disk($disk)->getDriver()->getAdapter()->getPathPrefix();
        } else {
            return Storage::getDriver()->getAdapter()->getPathPrefix();
        }
    }
}
