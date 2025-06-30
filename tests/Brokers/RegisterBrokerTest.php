<?php

namespace Unusualify\Modularity\Tests\Brokers;

use Carbon\Carbon;
use Illuminate\Auth\Passwords\DatabaseTokenRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Unusualify\Modularity\Brokers\RegisterBroker;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Notifications\EmailVerification;
use Unusualify\Modularity\Tests\ModelTestCase;

class RegisterBrokerTest extends ModelTestCase
{
    use RefreshDatabase;

    protected RegisterBroker $broker;

    protected $hasher;

    protected $table;

    protected $hashKey;

    protected array $brokerConfig;

    protected DatabaseTokenRepository $tokens;

    protected function setUp(): void
    {
        parent::setUp();

        config(['auth.passwords.modularity_users' => [
            'provider' => 'modularity_users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ]]);

        config(['auth.passwords.register_verified_users' => [
            'provider' => 'modularity_users',
            'table' => 'um_email_verification_tokens',
            'expire' => 60,
            'throttle' => 60,
        ]]);

        $this->brokerConfig = Config::get('auth.passwords.register_verified_users');

        $this->tokens = new DatabaseTokenRepository(
            $this->app['db']->connection(),
            $this->app['hash'],
            $this->brokerConfig['table'],
            '12345678',
            $this->brokerConfig['expire'],
            $this->brokerConfig['throttle'] ?? 0
        );

        $this->broker = new RegisterBroker(
            $this->tokens,
            $this->app['auth']->createUserProvider('modularity_users'),
            $this->app['db']->connection(),
            $this->brokerConfig,
        );

    }

    public function test_email_is_registered()
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $registeredEmail = $user->email;
        $notRegisteredEmail = 'celikerdem@gmail.com';

        $this->assertTrue($this->broker->emailIsRegistered($registeredEmail));
        $this->assertFalse($this->broker->emailIsRegistered($notRegisteredEmail));
    }

    public function test_token_expired()
    {
        $createdAt = now();

        $tokenIsExpired = Carbon::parse($createdAt)->addSeconds($this->brokerConfig['expire'] * 60)->isPast();
        $this->assertFalse($tokenIsExpired);

        $tokenIsExpired = Carbon::parse($createdAt)->subSeconds($this->brokerConfig['expire'] * 60)->isPast();
        $this->assertTrue($tokenIsExpired);

    }

    public function test_delete_token()
    {
        $user = DB::table('um_email_verification_tokens')->insert([
            'email' => 'john@example.com',
            'token' => '12345678',
            'created_at' => now(),
        ]);

        $user = DB::table('um_email_verification_tokens')->where('email', 'john@example.com')->first();
        $this->assertTrue(DB::table('um_email_verification_tokens')->where('email', $user->email)->exists());

        $this->broker->deleteToken($user->email);
        $this->assertFalse(DB::table('um_email_verification_tokens')->where('email', $user->email)->exists());
    }

    public function test_email_token_exists()
    {

        $plainToken = '12345678';
        $hashedToken = $this->app['hash']->make($plainToken); // Hash the token first

        $userHasToken = DB::table('um_email_verification_tokens')->insert([
            'email' => 'john@example.com',
            'token' => $hashedToken,  // Store the HASHED token
            'created_at' => now(),
        ]);

        $userHasToken = DB::table('um_email_verification_tokens')->where('email', 'john@example.com')->first();
        $token = $plainToken; // Use the plain token for verification

        $this->assertTrue($this->broker->emailTokenExists($userHasToken->email, $token));

        $token = null;

        $this->assertFalse($this->broker->emailTokenExists($userHasToken->email, $token));
    }

    public function test_validate_register()
    {
        $registeredUser = User::create([
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $credentials = [
            'email' => $registeredUser->email,
            'name' => $registeredUser->name,
            'surname' => $registeredUser->surname,
            'company' => 'Example Company',
            'password' => $registeredUser->password,
            'password_confirmation' => 'password123',
            'token' => '12345678',
        ];

        $this->assertEquals(RegisterBroker::ALREADY_REGISTERED, $this->broker->validateRegister($credentials));

        $plainToken = '1234567890';
        $hashedToken = $this->app['hash']->make($plainToken); // Hash the token first

        DB::table('um_email_verification_tokens')->insert([
            'email' => 'adam@example.com',
            'token' => $hashedToken,  // Store the HASHED token
            'created_at' => now()->subSeconds($this->brokerConfig['expire'] * 60),
        ]);

        $newUser = DB::table('um_email_verification_tokens')->get()->first();

        $this->assertEquals(RegisterBroker::INVALID_VERIFICATION_TOKEN, $this->broker->validateRegister((array) $newUser));

        DB::table('um_email_verification_tokens')
            ->where('email', 'adam@example.com')
            ->update(['created_at' => now()]);

        $newUser = DB::table('um_email_verification_tokens')->where('email', 'adam@example.com')->first();

        $userWithValidTokenCredentials = [
            'email' => $newUser->email,
            'token' => $plainToken,
        ];

        $this->assertEquals(RegisterBroker::VERIFICATION_SUCCESS, $this->broker->validateRegister($userWithValidTokenCredentials));
    }

    public function test_register()
    {
        $plainToken = '1234567890';
        $hashedToken = $this->app['hash']->make($plainToken);

        DB::table('um_email_verification_tokens')->insert([
            'email' => 'jeff@example.com',
            'token' => $hashedToken,  // Store the HASHED token
            'created_at' => now(),
        ]);

        $user = DB::table('um_email_verification_tokens')->where('email', 'jeff@example.com')->first();

        $credentials = [
            'email' => $user->email,
            'name' => 'Jeff',
            'surname' => 'Doe',
            'company' => 'Example Company',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'token' => $plainToken,
        ];

        $tokenCounts = DB::table('um_email_verification_tokens')->get()->count();
        $this->assertEquals(1, $tokenCounts);

        // Create a callback that tracks if it was called
        $callbackCalled = false;
        $callbackCredentials = null;

        $callback = function ($creds) use (&$callbackCalled, &$callbackCredentials) {
            $callbackCalled = true;
            $callbackCredentials = $creds;
        };

        $isVerificationSuccess = $this->broker->register($credentials, $callback);
        $this->assertEquals(RegisterBroker::VERIFICATION_SUCCESS, $isVerificationSuccess);
        // Should be 0 because the token is deleted
        $this->assertEquals(0, DB::table('um_email_verification_tokens')->get()->count());
    }

    public function test_send_verification_link()
    {

        $registeredUser = User::create([
            'name' => 'Erdem',
            'surname' => 'Celik',
            'email' => 'celikerdem@gmail.com',
            'password' => 'password123',
        ]);

        $credentials = [
            'email' => $registeredUser->email,
        ];

        $alreadyRegisteredUser = $this->broker->sendVerificationLink($credentials);
        $this->assertEquals(RegisterBroker::ALREADY_REGISTERED, $alreadyRegisteredUser);

        Notification::fake();
        $newUserCredentials = [
            'email' => 'newuser@example.com',
        ];

        $this->assertEquals(
            RegisterBroker::VERIFICATION_LINK_SENT,
            $this->broker->sendVerificationLink($newUserCredentials)
        );

        Notification::assertSentTimes(EmailVerification::class, 1);

    }
}
