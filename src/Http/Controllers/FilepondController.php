<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Http\Request;
use Unusualify\Modularity\Services\Filepond\FilepondManager;

class FilepondController extends Controller
{
    public $filepondManager;

    public function __construct(FilepondManager $fpm)
    {
        $this->filepondManager = $fpm;
    }


    public function upload(Request $request)
    {
        return response($this->filepondManager->createTemporaryFilepond($request));
    }


    public function delete(Request $request)
    {
        return $this->filepondManager->deleteTemporaryFilepond($request);
    }


}
