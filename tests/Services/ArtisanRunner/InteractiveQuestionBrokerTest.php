<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\ArtisanRunner;

use Unusualify\Modularous\Services\ArtisanRunner\Exceptions\ArtisanRunnerException;
use Unusualify\Modularous\Services\ArtisanRunner\Streaming\InteractiveQuestionBroker;
use Unusualify\Modularous\Tests\TestCase;

class InteractiveQuestionBrokerTest extends TestCase
{
    /** @test */
    public function it_publishes_and_consumes_answers(): void
    {
        $broker = new InteractiveQuestionBroker(5);
        $broker->publishAnswer('run-1', 'prompt-1', 'yes');

        $this->assertSame('yes', $broker->waitForAnswer('run-1', 'prompt-1'));
    }

    /** @test */
    public function it_times_out_when_no_answer_arrives(): void
    {
        $broker = new InteractiveQuestionBroker(1);

        $this->expectException(ArtisanRunnerException::class);
        $this->expectExceptionMessage('Timed out waiting for interactive prompt answer');

        $broker->waitForAnswer('run-timeout', 'prompt-timeout');
    }
}
