<?php

namespace Unusualify\Modularity\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void backup(string $table, ?array $columns = null)
 * @method static bool restore()
 * @method static array|null getBackup()
 * @method static void clearBackup()
 * @method static string getBackupKey()
 *
 * @see \Unusualify\Modularity\Services\MigrationBackupService
 */
class MigrationBackup extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'migration.backup';
    }
}
