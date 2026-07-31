<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\ArtisanRunner;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Unusualify\Modularous\Http\Controllers\ArtisanRunner\ArtisanRunnerController;
use Unusualify\Modularous\Services\ArtisanRunner\Contracts\ArtisanRunnerInterface;
use Unusualify\Modularous\Services\ArtisanRunner\Exceptions\ArtisanRunnerException;
use Unusualify\Modularous\Services\ArtisanRunner\Exceptions\CommandNotAllowedException;
use Unusualify\Modularous\Tests\TestCase;

class ArtisanRunnerControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function commands_returns_catalog_for_authorized_user(): void
    {
        $user = $this->makeUser();
        $runner = Mockery::mock(ArtisanRunnerInterface::class);
        $runner->shouldReceive('userCanAccess')->with($user)->andReturn(true);
        $runner->shouldReceive('catalogForUser')->with($user)->andReturn([['name' => 'demo']]);

        $controller = new ArtisanRunnerController($runner);
        $request = Request::create('/artisan-runner/commands');
        $request->setUserResolver(static fn () => $user);

        $response = $controller->commands($request);

        $this->assertSame([['name' => 'demo']], $response->getData(true)['commands']);
    }

    /** @test */
    public function definition_maps_command_exceptions_to_forbidden(): void
    {
        $user = $this->makeUser();
        $runner = Mockery::mock(ArtisanRunnerInterface::class);
        $runner->shouldReceive('userCanAccess')->with($user)->andReturn(true);
        $runner->shouldReceive('definitionForUser')
            ->once()
            ->andThrow(new CommandNotAllowedException('nope'));

        $controller = new ArtisanRunnerController($runner);
        $request = Request::create('/artisan-runner/commands/demo');
        $request->setUserResolver(static fn () => $user);

        $response = $controller->definition($request, 'demo');
        $this->assertSame(403, $response->getStatusCode());

        $runner2 = Mockery::mock(ArtisanRunnerInterface::class);
        $runner2->shouldReceive('userCanAccess')->with($user)->andReturn(true);
        $runner2->shouldReceive('definitionForUser')
            ->once()
            ->andThrow(new ArtisanRunnerException('disabled'));

        $response2 = (new ArtisanRunnerController($runner2))->definition($request, 'demo');
        $this->assertSame(403, $response2->getStatusCode());
    }

    /** @test */
    public function answer_delegates_to_runner(): void
    {
        $user = $this->makeUser();
        $runner = Mockery::mock(ArtisanRunnerInterface::class);
        $runner->shouldReceive('userCanAccess')->with($user)->andReturn(true);
        $runner->shouldReceive('answerPrompt')->once()->with('run-1', 'prompt-1', 'yes');

        $controller = new ArtisanRunnerController($runner);
        $request = Request::create('/artisan-runner/runs/run-1/answer', 'POST', [
            'prompt_id' => 'prompt-1',
            'answer' => 'yes',
        ]);
        $request->setUserResolver(static fn () => $user);

        $response = $controller->answer($request, 'run-1');

        $this->assertTrue($response->getData(true)['ok']);
    }

    /** @test */
    public function authorize_user_aborts_when_unauthenticated_or_denied(): void
    {
        $runner = Mockery::mock(ArtisanRunnerInterface::class);
        $controller = new ArtisanRunnerController($runner);
        $request = Request::create('/artisan-runner/commands');
        $request->setUserResolver(static fn () => null);

        try {
            $controller->commands($request);
            $this->fail('Expected HttpException');
        } catch (HttpException $e) {
            $this->assertSame(401, $e->getStatusCode());
        }

        $user = $this->makeUser();
        $runnerDenied = Mockery::mock(ArtisanRunnerInterface::class);
        $runnerDenied->shouldReceive('userCanAccess')->with($user)->andReturn(false);
        $request->setUserResolver(static fn () => $user);

        try {
            (new ArtisanRunnerController($runnerDenied))->commands($request);
            $this->fail('Expected HttpException');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    private function makeUser(): Authenticatable
    {
        return new class extends Authenticatable
        {
            public function getAuthIdentifier()
            {
                return 1;
            }
        };
    }
}
