<?php

namespace Unusualify\Modularity\Http\Controllers;

use Unusualify\Modularity\Services\MediaLibrary\Glide;
use Illuminate\Foundation\Application;

class GlideController
{
    public function __invoke($path, Application $app)
    {
        return $app->make(Glide::class)->render($path);
    }
}
