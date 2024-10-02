<?php

namespace Unusualify\Modularity\Http\Controllers;

use App\Http\Controllers\Controller;
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
        return $this->filepondManager->createTemporaryFilepond($request);

        return response($this->filepondManager->createTemporaryFilepond($request));
    }

    public function revert(Request $request)
    {
        return $this->filepondManager->deleteTemporaryFilepond($request);
    }

    public function preview(Request $request, $folder)
    {
        // dd($folder);
        return $this->filepondManager->previewFile($folder);
    }
}
