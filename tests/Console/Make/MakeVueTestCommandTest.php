<?php

namespace Unusualify\Modularous\Tests\Console\Make;

use Illuminate\Contracts\Console\Kernel;
use Unusualify\Modularous\Tests\TestCase;

class MakeVueTestCommandTest extends TestCase
{
    /** @test */
    public function import_dir_option_accepts_a_value()
    {
        $command = $this->app->make(Kernel::class)->all()['modularous:make:vue:test'];
        $option = $command->getDefinition()->getOption('importDir');

        $this->assertTrue($option->acceptValue());
    }
}
