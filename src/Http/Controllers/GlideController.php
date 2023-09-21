<?php

namespace OoBook\CRM\Base\Http\Controllers;

use OoBook\CRM\Base\Services\MediaLibrary\Glide;
use Illuminate\Foundation\Application;

class GlideController
{
    public function __invoke($path, Application $app)
    {
        return $app->make(Glide::class)->render($path);
    }
}
