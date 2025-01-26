<?php

use Illuminate\Console\Concerns\InteractsWithIO;
use Symfony\Component\Console\Output\ConsoleOutput;
use TimoKoerber\LaravelOneTimeOperations\OneTimeOperation;
use Illuminate\Support\Facades\DB;
use Unusualify\Modularity\Facades\Modularity;

return new class extends OneTimeOperation
{
    use InteractsWithIO;

    public function __construct()
    {
        $this->output = new ConsoleOutput();
    }

    /**
     * Determine if the operation is being processed asynchronously.
     */
    protected bool $async = false;

    /**
     * The queue that the job will be dispatched to.
     */
    protected string $queue = 'default';

    /**
     * A tag name, that this operation can be filtered by.
     */
    protected ?string $tag = 'modularity';

    /**
     * Process the operation.
     */
    public function process(): void
    {
        $this->info("\n\tUpdating Modularity User Guard");

        $modularityAuthGuardName = Modularity::getAuthGuardName();
        $permissionsTable = config('permission.table_names.permissions', 'permissions');
        DB::table($permissionsTable)
            ->where('guard_name', 'unusual_users')
            ->update(['guard_name' => $modularityAuthGuardName]);

        $rolesTable = config('permission.table_names.roles', 'roles');
        DB::table($rolesTable)
            ->where('guard_name', 'unusual_users')
            ->update(['guard_name' => $modularityAuthGuardName]);
        // $result = DB::pretend(function () {
        // });
        // dd($result);

        $this->info("\tPermissions guard names updated from 'unusual_users' to '{$modularityAuthGuardName}'");
        $this->output->writeln('');
    }
};
