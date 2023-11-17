<?php

namespace Unusualify\Modularity\Services\Uploader;

interface SignUploadListener
{
    public function uploadIsSigned($signature, $isJsonResponse = true);

    public function uploadIsNotValid();
}
