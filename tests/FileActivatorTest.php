<?php

namespace Unusualify\Modularity\Tests;

use Nwidart\Modules\Activators\FileActivator as ActivatorsFileActivator;
use Unusualify\Modularity\Activators\FileActivator;

class FileActivatorTest extends TestCase
{
    public function test_file_activator_initialization()
    {
        $fileActivator = new FileActivator($this->app);

        $this->assertInstanceOf(ActivatorsFileActivator::class, $fileActivator);
    }

    public function test_handle_missing_statuses_file()
    {

        $fileActivator = new FileActivator($this->app);
        $fileActivator->setModule('SystemPayment', $this->umoduleDirectory('SystemPayment'));

        $statuses = $fileActivator->getRoutesStatuses();

        $this->assertIsArray($statuses);
    }
}
