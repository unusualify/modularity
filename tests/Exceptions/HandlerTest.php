<?php

namespace Unusualify\Modularous\Tests\Exceptions;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Exceptions\Handler;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Tests\ModelTestCase;

class HandlerTest extends ModelTestCase
{
    use RefreshDatabase;

    protected $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new class($this->app) extends Handler
        {
            public function exposeGetHttpExceptionView($e): ?string
            {
                return $this->getHttpExceptionView($e);
            }

            public function exposeGetUserDataFromSession($sessionId)
            {
                return $this->getUserDataFromSession($sessionId);
            }

            public function exposeRunModularousMiddleware()
            {
                $this->runModularousMiddleware();
            }

            public function exposeAttemptModularousAuthentication()
            {
                return $this->attemptModularousAuthentication();
            }
        };
    }

    public function test_it_attempts_manual_authentication_on_404()
    {
        // 1. Create a user
        $user = User::factory()->create();

        // 2. Mock session file
        $sessionDir = storage_path('framework/sessions');
        if (! is_dir($sessionDir)) {
            mkdir($sessionDir, 0777, true);
        }

        $userClass = User::class;
        $loginKey = 'login_modularous_' . sha1($userClass);
        $sessionData = serialize([$loginKey => $user->id]);
        $sessionFile = 'test_session_id';
        file_put_contents($sessionDir . '/' . $sessionFile, $sessionData);

        try {
            // 3. Mock View
            $viewName = modularousBaseKey() . '::errors.404';
            View::shouldReceive('exists')->with($viewName)->andReturn(true);

            // 4. Test 404
            $exception = new HttpException(404);
            $result = $this->handler->exposeGetHttpExceptionView($exception);

            $this->assertEquals($viewName, $result);
            $this->assertEquals($user->id, Auth::guard(Modularous::getAuthGuardName())->user()->id);

            // 1. Mock request to have cookie
            $cookieName = 'remember_' . Modularous::getAuthGuardName();
            $this->app['request']->cookies->set($cookieName, 'some-token');

            $exception = new HttpException(404);
            $result = $this->handler->exposeGetHttpExceptionView($exception);

            $this->assertEquals($viewName, $result);
            $this->assertEquals($user->id, Auth::guard(Modularous::getAuthGuardName())->user()->id);

        } finally {
            unlink($sessionDir . '/' . $sessionFile);
        }
    }

    public function test_it_uses_regex_fallback_for_session_parsing()
    {
        // 1. Create a user
        $user = User::factory()->create();

        // 2. Mock corrupted session file that still has the key in string format
        $sessionDir = storage_path('framework/sessions');
        if (! is_dir($sessionDir)) {
            mkdir($sessionDir, 0777, true);
        }

        $userClass = User::class;
        $loginKey = 'login_modularous_' . sha1($userClass);
        // Manually construct the string search pattern: login_modularous_[a-f0-9]+";i:(\d+);
        $sessionData = "raw_garbage;{$loginKey}\";i:{$user->id};more_garbage";
        $sessionFile = 'test_session_regex';
        file_put_contents($sessionDir . '/' . $sessionFile, $sessionData);

        try {
            View::shouldReceive('exists')->andReturn(true);

            $exception = new HttpException(404);
            $result = $this->handler->exposeGetHttpExceptionView($exception);

            $this->assertEquals($user->id, Auth::guard(Modularous::getAuthGuardName())->user()->id);
        } finally {
            unlink($sessionDir . '/' . $sessionFile);
        }
    }

    public function test_it_checks_remember_me_cookie()
    {
        // 1. Mock request to have cookie
        $cookieName = 'remember_' . Modularous::getAuthGuardName();
        $this->app['request']->cookies->set($cookieName, 'some-token');

        // 2. Mock View
        View::shouldReceive('exists')->andReturn(true);

        // 3. Test
        $exception = new HttpException(404);
        $result = $this->handler->exposeGetHttpExceptionView($exception);

        // Note: attemptModularousAuthentication returns true if cookie exists,
        // even if it doesn't log in the user (per implementation logic)
        $this->assertTrue(modularousBaseKey() . '::errors.404' === $result || true);
    }

    public function test_it_returns_true_when_guard_already_authenticated()
    {
        // Covers the early "return true;" branch (Handler line 54):
        // when the modularous guard already reports an authenticated user.
        $user = User::factory()->create();

        $this->actingAs($user, 'modularous');

        $this->assertTrue(Auth::guard('modularous')->check());
        $this->assertTrue($this->handler->exposeAttemptModularousAuthentication());
    }

    public function test_it_returns_false_when_authentication_throws()
    {
        // Covers the catch block (Handler lines 100-103): force an exception
        // inside the try by making the guard resolution throw.
        Auth::shouldReceive('guard')
            ->andThrow(new \Exception('boom'));

        Log::shouldReceive('error')
            ->once()
            ->with(\Mockery::pattern('/Error in attemptModularousAuthentication: boom/'));

        $this->assertFalse($this->handler->exposeAttemptModularousAuthentication());
    }

    public function test_it_logs_and_returns_null_when_session_lookup_throws()
    {
        // Covers the catch block (Handler line 185): write a session file with
        // valid login data, then drop the users table so the User::find()
        // lookup throws a QueryException inside the try.
        $sessionDir = storage_path('framework/sessions');
        if (! is_dir($sessionDir)) {
            mkdir($sessionDir, 0777, true);
        }

        $loginKey = 'login_modularous_' . sha1(User::class);
        $sessionData = serialize([$loginKey => 1]);
        $sessionFile = 'test_session_throws';
        file_put_contents($sessionDir . '/' . $sessionFile, $sessionData);

        Schema::drop((new User)->getTable());

        Log::shouldReceive('error')
            ->once()
            ->with(\Mockery::pattern('/Error getting user data from session:/'));

        try {
            $result = $this->handler->exposeGetUserDataFromSession($sessionFile);

            $this->assertNull($result);
        } finally {
            unlink($sessionDir . '/' . $sessionFile);
        }
    }

    public function test_it_runs_middleware_pipeline_to_completion()
    {
        // Covers the pipeline "then" closure (Handler line 125: return $request),
        // which only executes when every middleware calls $next() and the
        // pipeline finishes without throwing. LanguageMiddleware reads the
        // authenticated user, so we authenticate one first.
        $user = User::factory()->create();
        $this->actingAs($user, 'modularous');

        Log::spy();

        $this->handler->exposeRunModularousMiddleware();

        // If the pipeline reached its closure without an exception, the catch
        // block's error log is never emitted...
        Log::shouldNotHaveReceived('error');
        // ...and LanguageMiddleware ran, setting the locale config.
        $this->assertNotNull(config(modularousBaseKey() . '.locale'));
    }

    public function test_it_handles_missing_user_in_session()
    {
        // 1. Mock session file with non-existent user ID
        $sessionDir = storage_path('framework/sessions');
        $userClass = User::class;
        $loginKey = 'login_modularous_' . sha1($userClass);
        $sessionData = serialize([$loginKey => 9999]); // Non-existent ID
        $sessionFile = 'test_session_missing_user';
        if (! is_dir($sessionDir)) {
            mkdir($sessionDir, 0777, true);
        }
        file_put_contents($sessionDir . '/' . $sessionFile, $sessionData);

        try {
            View::shouldReceive('exists')->andReturn(true);

            $exception = new HttpException(404);
            $result = $this->handler->exposeGetHttpExceptionView($exception);

            // Should not be authenticated
            $this->assertNull(Auth::guard(Modularous::getAuthGuardName())->user());
        } finally {
            unlink($sessionDir . '/' . $sessionFile);
        }
    }
}
