<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Foundation\Application;
use Unusualify\Modularity\Services\MediaLibrary\Glide;

class GlideController
{
    public function __invoke($path, Application $app)
    {
        return $app->make(Glide::class)->render($path);
    }
}
