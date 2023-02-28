<?php

namespace Unusual\CRM\Base\Services\Uploader;

interface SignUploadListener
{
    public function uploadIsSigned($signature, $isJsonResponse = true);

    public function uploadIsNotValid();
}
