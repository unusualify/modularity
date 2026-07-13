<?php

namespace TestModules\TestModule\Console;

use Illuminate\Console\Command;

class TestModuleCommand extends Command
{
    protected $signature = 'test-module:ping';

    protected $description = 'Fixture command for ModuleTest';

    public function handle(): int
    {
        return self::SUCCESS;
    }
}
