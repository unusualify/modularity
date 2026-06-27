<?php

namespace Modules\Cms\Console;

use Illuminate\Console\Command;

class OptimizeClearCmsPublicUrlRegistryCommand extends Command
{
    protected $signature = 'cms:public-url-registry:optimize-clear';

    protected $description = 'Clear all CMS public URL registry caches (revisions, front routes, sitemap).';

    public function handle(): int
    {
        return $this->call(ClearCmsPublicUrlRegistryCacheCommand::class);
    }
}
