<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Config\Repository as Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Factory as ValidatorFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Unusualify\Modularity\Entities\User;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

class CreateSuperAdminCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'modularity:create:superadmin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates the superadmin account';

    /**
     * @var ValidatorFactory
     */
    protected $validatorFactory;

    /**
     * @var Config
     */
    protected $config;

    public function __construct(ValidatorFactory $validatorFactory, Config $config)
    {
        parent::__construct();

        $this->validatorFactory = $validatorFactory;
        $this->config = $config;
    }

    /**
     * Get Console Command Options
     */
    protected function getOptions(): array
    {
        return array_merge([
            ['default', '--d', InputOption::VALUE_NONE, 'Use default options for super-admin auth. information'],
        ], modularityTraitOptions());
    }

    protected function getArguments(): array
    {
        return [
            ['email', InputArgument::OPTIONAL, 'A valid e-mail for super-admin', null],
            ['password', InputArgument::OPTIONAL, 'A valid password for super-admin', null],
        ];
    }

    /**
     * Create super admin account.
     *
     * @return void
     */
    public function handle(): int
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
                info('Your account has been created');

                return 0;
            }

        } catch (\Throwable $th) {
            $this->error($th);
            DB::rollback();
        }

        error('Failed creating user. Things you can check: Database permissions, run migrations');

        return -1;
    }

    /**
     * Prompt user to enter email and validate it.
     *
     * @return string $email
     */
    private function setEmail(?string $email = null)
    {

        if ($this->option('default')) {
            $email = getValueOrNull(env('MODULARITY_ADMIN_EMAIL', 'software-dev@unusualgrowth.com')) ?? 'software-dev@unusualgrowth.com';
            $this->info('Email configured for super-admin as ' . $email);

            return $email;
        }

        warning('E-mail configuration for super-admin account');

        $useDefault = confirm(
            label: 'Do you want to use default configuration for super-admin e-mail?',
            default: true,
            yes: 'YES',
            no: 'No, enter custom e-mail',
            hint: 'Default e-mail address: software-dev@unusualgrowth.com',
        );

        if ($useDefault) {
            $email = env('MODULARITY_ADMIN_EMAIL', 'software-dev@unusualgrowth.com');
        } else {
            $email = text(
                label: 'Please enter a valid e-mail address for super-admin',
                placeholder: 'E.g. example@exampleHost.com',
                hint: '',
                required: true,
                validate: fn ($value) => match (true) {
                    DB::table(modularityConfig('tables.users', 'admin_users'))->where('email', $value)->exists() => $value . ' is already in use. Please use an unique e-mail',
                    mb_strlen($value) > 255 => 'Please enter maximum 255 characters',
                    ! filter_var($value, FILTER_VALIDATE_EMAIL) => 'Please enter a valid email pattern',
                    default => null,
                }
            );
        }

        if ($this->validateEmail($email)) {
            info('Email configured for super-admin as ' . $email);

            return $email;
        } else {
            error('Enter a valid e-mail address');
            $this->setEmail();
        }

    }

    /**
     * Prompt user to enter password, confirm and validate it.
     *
     * @return string $password
     */
    private function setPassword(?string $password = null)
    {

        if ($this->option('default')) {
            $password = env('MODULARITY_ADMIN_PASSWORD', 'w@123456');
            info('Password configured for super-admin as ' . $password);

            return $password;
        }

        warning('Password configuration for super-admin account');
        $useDefault = confirm(
            label: 'Do you want to use default configuration for super-admin password?',
            default: true,
            yes: 'YES',
            no: 'No, enter custom password',
            hint: 'Default password is w@123456'
        );

        if ($useDefault) {
            $password = getValueOrNull(env('MODULARITY_ADMIN_PASSWORD', 'w@123456'), bool: false);
        } else {
            $password = password(
                label: 'Please enter valid password',
                hint: 'Please use at least 6 characters',
                validate: fn (string $value) => match (true) {
                    mb_strlen($value) < 6 => 'The password must be at least 6 characters',
                    default => null,
                }
            );
        }
        info('Password configured for ' . $this->argument('email') . 'is ' . $password);

        return $password;
    }

    /**
     * Determine if the email address given valid.
     *
     * @param string $email
     * @return bool
     */
    private function validateEmail($email)
    {
        $admin_user_table = $this->config->get(env('MODULARITY_BASE_NAME', 'modularity') . '.table.users', 'admin_users');

        return $this->validatorFactory->make(['email' => $email], [
            'email' => 'required|email|max:255|unique:' . $admin_user_table,
        ])->passes();
    }

    /**
     * Determine if the password given valid.
     *
     * @param string $password
     * @return bool
     */
    private function validatePassword($password)
    {
        return $this->validatorFactory->make(['password' => $password], [
            'password' => 'required|min:6',
        ])->passes();
    }
}
