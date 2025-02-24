<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Support\Facades\File;
use Nwidart\Modules\Support\Stub;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;

class CreateHorizonSupervisorCommand extends BaseCommand
{
    protected $hidden = true;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:create:horizon:supervisor';

    protected $aliases = [];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Horizon Supervisor configuration file';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /*
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {

        // Check if supervisor is installed
        $supervisorCheck = shell_exec('which supervisord');
        // Detect OS
        $os = PHP_OS;
        if (empty($supervisorCheck)) {

            $this->info("Detected operating system: $os");

            if (mb_strtoupper(mb_substr($os, 0, 3)) === 'WIN') {
                $this->error('Supervisor is not supported on Windows systems.');

                return 1;
            }

            $installCommand = '';
            if (str_contains($os, 'Darwin')) {
                // macOS
                $this->info('For macOS, install supervisor via Homebrew:');
                $this->line('brew install supervisor');
            } elseif (str_contains($os, 'Linux')) {
                // Check Linux distribution
                $distro = '';
                if (file_exists('/etc/os-release')) {
                    $osRelease = file_get_contents('/etc/os-release');
                    if (preg_match('/^ID=(.*?)$/m', $osRelease, $matches)) {
                        $distro = trim($matches[1], '"');
                    }
                }

                switch (mb_strtolower($distro)) {
                    case 'ubuntu':
                    case 'debian':
                        $this->info('For Ubuntu/Debian, run:');
                        $this->line('sudo apt-get update');
                        $this->line('sudo apt-get install supervisor');

                        break;
                    case 'centos':
                    case 'rhel':
                    case 'fedora':
                        $this->info('For CentOS/RHEL/Fedora, run:');
                        $this->line('sudo yum install epel-release');
                        $this->line('sudo yum install supervisor');

                        break;
                    default:
                        $this->info('Please install supervisor using your distribution\'s package manager');

                        break;
                }
            }
        }

        // Find supervisor configuration path based on OS
        $supervisorConfigPath = '';
        if (str_contains($os, 'Darwin')) {
            // macOS supervisor config locations
            $possiblePaths = [
                '/usr/local/etc/supervisor/conf.d',
                '/usr/local/etc/supervisor.d',
                '/opt/homebrew/etc/supervisor.d',
            ];
        } else {
            // Linux supervisor config locations
            $possiblePaths = [
                '/etc/supervisor/conf.d',
                '/etc/supervisord.d',
                '/etc/supervisord/conf.d',
            ];
        }

        // Check which path exists and is writable
        foreach ($possiblePaths as $path) {
            if (is_dir($path) && is_writable($path)) {
                $supervisorConfigPath = $path;

                break;
            }
        }

        if (empty($supervisorConfigPath)) {
            $this->error('Could not find a writable supervisor configuration directory.');
            $this->info('Common supervisor configuration paths checked:');
            foreach ($possiblePaths as $path) {
                $this->line('- ' . $path);
            }

            return 1;
        }

        $this->info('Supervisor is installed and available.');

        $appName = text('What is the name of supervisor config?', default: 'b2press-app');
        $programName = "{$appName}-" . uniqid() . '-horizon';
        $php = text('What is the script of php?', default: 'php');
        $appPath = rtrim(text('What is the path of your app?', default: base_path()), '//');

        // Check if a process with appName already exists
        $existingProcesses = shell_exec('sudo supervisorctl status');
        if ($existingProcesses && str_contains($existingProcesses, $appName)) {
            if (! confirm("A supervisor process containing '{$appName}' already exists. Do you want to continue?", default: false)) {
                $this->info('Operation cancelled by user.');

                return 1;
            }
        }

        $command = text('What is the command to run?', default: 'artisan horizon');
        $command = $appPath . '/' . $command;
        $replacements = [
            'PROGRAM_NAME' => $programName,
            'PHP' => $php,
            'COMMAND' => $command,
            'USER' => text('What is the user?', default: 'root'),
            'LOG_FILE_NAME' => text('What is the name of the log file?', default: 'horizon'),
        ];

        $content = (new Stub('/supervisor.stub', $replacements))->render();
        $this->line('sudo supervisorctl reread');
        $this->line('sudo supervisorctl update');
        $this->line('sudo supervisorctl start ' . $programName);
        $path = concatenate_path($supervisorConfigPath, $programName . '-horizon.conf');

        if (confirm('Do you want to see supervisor config content to create?', default: true)) {
            $this->line($content);
        }

        if (confirm('Do you want to create supervisor config file at ' . $path . '?', default: true)) {
            File::put($path, $content);
            $this->info("Supervisor config file created successfully: {$path}");
        } else {
            $this->info('Supervisor config file not created');

            return 1;
        }

        return 0;
    }
}
