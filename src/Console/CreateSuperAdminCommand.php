<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Support\Facades\Hash;
use Illuminate\Config\Repository as Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Factory as ValidatorFactory;
use Unusualify\Modularity\Entities\User;

class CreateSuperAdminCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unusual:superadmin {email?} {password?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Create the superadmin account";

    /**
     * @var ValidatorFactory
     */
    protected $validatorFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param ValidatorFactory $validatorFactory
     * @param Config $config
     */
    public function __construct(ValidatorFactory $validatorFactory, Config $config)
    {
        parent::__construct();

        $this->validatorFactory = $validatorFactory;
        $this->config = $config;
    }

    /**
     * Create super admin account.
     *
     * @return void
     */
    public function handle() :int
    {
        $this->info("Let's create a superadmin account!");
        $email = $this->setEmail();
        $password = $this->setPassword();

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => 'Administrator',
                'email' => $email,
                // 'published' => true,
            ]);

            $user->roles()->sync('superadmin');
            $user->password = Hash::make($password);

            if ($user->save()) {
                DB::commit();
                $this->info('Your account has been created');
                return 0;
            }
        } catch (\Throwable $th) {
            DB::rollback();
        }

        $this->error('Failed creating user. Things you can check: Database permissions, run migrations');

        return -1;
    }

    /**
     * Prompt user to enter email and validate it.
     *
     * @return string $email
     */
    private function setEmail()
    {
        if (filled($email = $this->argument('email'))) {
            return $email;
        }
        $email = $this->ask('Enter an email');
        if ($this->validateEmail($email)) {
            return $email;
        } else {
            $this->error("Your email is not valid");
            return $this->setEmail();
        }
    }

    /**
     * Prompt user to enter password, confirm and validate it.
     *
     * @return string $password
     */
    private function setPassword()
    {
        if (filled($email = $this->argument('password'))) {
            return $email;
        }
        $password = $this->secret('Enter a password');
        if ($this->validatePassword($password)) {
            $confirmPassword = $this->secret('Confirm the password');
            if ($password === $confirmPassword) {
                return $password;
            } else {
                $this->error('Password does not match the confirm password');
                return $this->setPassword();
            }
        } else {
            $this->error("Your password is not valid, at least 6 characters");
            return $this->setPassword();
        }
    }

    /**
     * Determine if the email address given valid.
     *
     * @param  string  $email
     * @return boolean
     */
    private function validateEmail($email)
    {
        return $this->validatorFactory->make(['email' => $email], [
            'email' => 'required|email|max:255|unique:' . $this->config->get('unusual.users_table_name'),
        ])->passes();
    }

    /**
     * Determine if the password given valid.
     *
     * @param  string  $password
     * @return boolean
     */
    private function validatePassword($password)
    {
        return $this->validatorFactory->make(['password' => $password], [
            'password' => 'required|min:6',
        ])->passes();
    }
}
