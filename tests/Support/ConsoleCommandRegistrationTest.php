<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Support;

use PHPUnit\Framework\TestCase;
use Unusualify\Modularous\Support\ConsoleCommandRegistration;

class ConsoleCommandRegistrationTest extends TestCase
{
    public function test_console_always_registers_commands(): void
    {
        $this->assertTrue(ConsoleCommandRegistration::shouldRegister(true, null));
        $this->assertTrue(ConsoleCommandRegistration::shouldRegister(true, 'dashboard'));
    }

    public function test_http_registers_only_artisan_runner_paths(): void
    {
        $this->assertTrue(ConsoleCommandRegistration::shouldRegister(false, 'api/artisan-runner/runs'));
        $this->assertTrue(ConsoleCommandRegistration::shouldRegister(false, 'admin/artisan-runner/commands'));
        $this->assertFalse(ConsoleCommandRegistration::shouldRegister(false, 'dashboard'));
        $this->assertFalse(ConsoleCommandRegistration::shouldRegister(false, null));
        $this->assertFalse(ConsoleCommandRegistration::shouldRegister(false, ''));
    }
}
