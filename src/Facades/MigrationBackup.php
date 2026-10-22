<?php

namespace Unusualify\Modularous\Facades;

use Illuminate\Support\Facades\Facade;
use Unusualify\Modularous\Services\MigrationBackupService;

/**
 * @method static void backup(string $table, ?array $columns = null)
 * @method static bool restore(?string $table = null)
 * @method static array|null getBackup()
 * @method static void clearBackup()
 * @method static string getBackupKey()
 *
 * @see MigrationBackupService
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
