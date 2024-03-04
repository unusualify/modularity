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
    protected $signature = 'unusual:superadmin {email?} {password?} {--default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Creates the superadmin account";

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


        $email = $this->setEmail($this->argument('email'));
        $password = $this->setPassword($this->argument('password'));

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => 'Administrator',
                'email' => $email,
                // 'published' => true,
            ]);

            $user->roles()->sync(1);
            $user->password = Hash::make($password);

            if ($user->save()) {
                DB::commit();
                $this->info('Your account has been created');
                return 0;
            }



        } catch (\Throwable $th) {
            $this->error($th);
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
    private function setEmail(String $email=null)
    {

        if($this->option('default')){
            $email = env('UNUSUAL_ADMIN_EMAIL', 'software-dev@unusualgrowth.com');
            $this->info('Email configured for super-admin as '.$email);
            return $email;
        }


        while (!$this->validateEmail($email)) {

            if (!filled($email)) {
                $this->info('You can use default configuration for super-admin e-mail address. You can change/ set it in .env file.');
                if ($this->confirm('Do you want to use default configuration for super-admin e-mail? Y/N')) {
                    $email = env('UNUSUAL_ADMIN_EMAIL', 'software-dev@unusualgrowth.com');
                    $this->info('Email configured for super-admin as '.$email);
                }else {
                    $email = $this->ask('Please enter a valid e-mail address for super-admin:\t');
                };
            }
        }

        return $email;





        // if (filled($email = $this->argument('email'))) {
        //     return $email;
        // }
        // $email = $this->ask('Enter an email');
        // if ($this->validateEmail($email)) {
        //     return $email;
        // } else {
        //     $this->error("Your email is not valid");
        //     return $this->setEmail();
        // }
    }

    /**
     * Prompt user to enter password, confirm and validate it.
     *
     * @return string $password
     */
    private function setPassword(String $password = null)
    {

        if($this->option('default') && $password = getValueOrNull(env('UNUSUAL_ADMIN_PASSWORD', 'w@123456')) ){
            $this->info('Password configured for super-admin as '.$password);
            return $password;
        }

        while(!$this->validatePassword($password)){
            if(!filled($password)){
                $this->info('You can use default configuration for super-admin password. You can change/ set it in .env file.');
                if ($this->confirm('Do you want to use default configuration for super-admin password? Y/N') && $password = getValueOrNull(env('UNUSUAL_ADMIN_PASSWORD', 'w@123456'))) {
                    $this->info('Password configured for super-admin as '.$password);
                    return $password;
                }else {
                    $password = $this->secret('Please enter a valid password address for super-admin with minimum 6 characters:\t');
                };
            }
        }

        $confirmPassword= $this->secret('Confirm the given password');
        if ($confirmPassword == $password) {
            return $password;
        }else{
            $this->error('Passwords do not match.');
            $this->setPassword();
        }


        // if (filled($email = $this->argument('password'))) {
        //     return $email;
        // }

        // $password = $this->secret('Enter a password');
        // if ($this->validatePassword($password)) {
        //     $confirmPassword = $this->secret('Confirm the password');
        //     if ($password === $confirmPassword) {
        //         return $password;
        //     } else {
        //         $this->error('Password does not match the confirm password');
        //         return $this->setPassword();
        //     }
        // } else {
        //     $this->error("Your password is not valid, at least 6 characters");
        //     return $this->setPassword();
        // }
    }

    /**
     * Determine if the email address given valid.
     *
     * @param  string  $email
     * @return boolean
     */
    private function validateEmail($email)
    {
        $admin_user_table = $this->config->get(env('UNUSUAL_BASE_NAME', 'modularity') . '.table.users', 'admin_users');
        return $this->validatorFactory->make(['email' => $email], [
            'email' => 'required|email|max:255|unique:' . $admin_user_table,
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
