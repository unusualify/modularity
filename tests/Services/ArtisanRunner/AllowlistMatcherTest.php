<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\ArtisanRunner;

use Unusualify\Modularous\Services\ArtisanRunner\Support\AllowlistMatcher;
use Unusualify\Modularous\Tests\TestCase;

class AllowlistMatcherTest extends TestCase
{
    private AllowlistMatcher $matcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->matcher = new AllowlistMatcher;
    }

    /** @test */
    public function it_matches_exact_command_names(): void
    {
        $this->assertTrue($this->matcher->matches('inspire', ['inspire', 'list']));
        $this->assertFalse($this->matcher->matches('cache:clear', ['inspire']));
    }

    /** @test */
    public function it_matches_glob_patterns(): void
    {
        $this->assertTrue($this->matcher->matches(
            'modularous:cache:clear',
            ['modularous:cache:*']
        ));
        $this->assertTrue($this->matcher->matches(
            'modularous:cache:warm-presentation',
            ['modularous:cache:*']
        ));
        $this->assertFalse($this->matcher->matches(
            'modularous:sync-remote-api',
            ['modularous:cache:*']
        ));
    }

    /** @test */
    public function it_rejects_empty_patterns(): void
    {
        $this->assertFalse($this->matcher->matches('inspire', []));
        $this->assertFalse($this->matcher->matches('inspire', ['', '  ']));
    }
}
