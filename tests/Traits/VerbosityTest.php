<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Traits;

use Symfony\Component\Console\Output\OutputInterface;
use Unusualify\Modularous\Tests\TestCase;
use Unusualify\Modularous\Traits\Verbosity;

class VerbosityTest extends TestCase
{
    private object $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new class
        {
            use Verbosity;
        };
    }

    /** @test */
    public function it_maps_string_verbosity_levels(): void
    {
        $this->subject->setVerbosity('v');
        $this->assertSame(OutputInterface::VERBOSITY_VERBOSE, $this->subject->getVerbosity());
        $this->assertTrue($this->subject->isVerbose());

        $this->subject->setVerbosity('vv');
        $this->assertTrue($this->subject->isVeryVerbose());

        $this->subject->setVerbosity('vvv');
        $this->assertTrue($this->subject->isDebug());

        $this->subject->setVerbosity('quiet');
        $this->assertTrue($this->subject->isQuiet());

        $this->subject->setVerbosity('normal');
        $this->assertSame(OutputInterface::VERBOSITY_NORMAL, $this->subject->getVerbosity());
    }

    /** @test */
    public function it_keeps_current_verbosity_for_invalid_string(): void
    {
        $this->subject->setVerbosity('vvv');
        $this->subject->setVerbosity('not-a-level');

        $this->assertSame(OutputInterface::VERBOSITY_DEBUG, $this->subject->getVerbosity());
    }

    /** @test */
    public function it_accepts_integer_verbosity(): void
    {
        $this->subject->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);

        $this->assertSame(OutputInterface::VERBOSITY_VERBOSE, $this->subject->getVerbosity());
        $this->assertTrue($this->subject->isVerbose());
        $this->assertFalse($this->subject->isQuiet());
    }
}
