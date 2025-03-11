<?php

namespace Unusualify\Modularity\Tests\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Unusualify\Modularity\Tests\TestCase;
use Unusualify\Modularity\Facades\MigrationBackup;

class MigrationBackupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test tables
        Schema::create('test_users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->timestamps();
        });

        Schema::create('test_posts', function ($table) {
            $table->id();
            $table->foreignId('user_id')->constrained('test_users')->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });

        // Insert test data
        DB::table('test_users')->insert([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('test_posts')->insert([
            'user_id' => 1,
            'title' => 'Test Post',
            'content' => 'Test Content',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Define database connections to test
     */
    public function databaseProviders(): array
    {
        return [
            'sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
            ],
            'mysql' => [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'database' => env('DB_DATABASE', 'forge'),
                'username' => env('DB_USERNAME', 'forge'),
                'password' => env('DB_PASSWORD', ''),
            ],
            'pgsql' => [
                'driver' => 'pgsql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'database' => env('DB_DATABASE', 'forge'),
                'username' => env('DB_USERNAME', 'forge'),
                'password' => env('DB_PASSWORD', ''),
            ],
        ];
    }

    public function test_service_works_across_different_databases()
    {
        // Initial backup
        MigrationBackup::backup('test_users');

        if (DB::connection()->getDriverName() === 'sqlite') {
            // For SQLite, we need to recreate the table
            $this->recreateTableWithChanges();
        } else {
            // For other databases, we can use ALTER TABLE
            Schema::table('test_users', function ($table) {
                $table->string('phone')->nullable();
                $table->dropColumn('email');
            });
        }

        // Restore and verify
        MigrationBackup::restore('test_users');

        $history = MigrationBackup::getSchemaHistory('test_users');

        $this->assertNotEmpty($history);
        $this->assertTrue(Schema::hasColumn('test_users', 'phone'));

        // Clean up
        Schema::dropIfExists('test_posts');
        Schema::dropIfExists('test_users');
    }

    public function test_can_backup_single_table()
    {
        MigrationBackup::backup('test_users');

        $backup = MigrationBackup::getBackup('test_users');

        $this->assertNotNull($backup);
        $this->assertEquals('test_users', $backup['main_table']);
        $this->assertArrayHasKey('test_users', $backup['tables']);
        $this->assertCount(1, $backup['tables']['test_users']['data']);
    }

    public function test_can_backup_related_tables()
    {
        MigrationBackup::backup('test_posts', null, true);

        $backup = MigrationBackup::getBackup('test_posts');

        // // Debug output with database-agnostic approach
        // dump([
        //     'Backup content' => $backup,
        //     'Database driver' => DB::connection()->getDriverName(),
        //     'Tables' => Schema::getAllTables(),
        //     'test_posts foreign keys' => $this->getForeignKeysForTable('test_posts'),
        //     'test_users foreign keys' => $this->getForeignKeysForTable('test_users'),
        // ]);

        $this->assertNotNull($backup);
        $this->assertArrayHasKey('test_posts', $backup['tables']);
        $this->assertArrayHasKey('test_users', $backup['tables'], 'Related table test_users not found in backup');
    }

    protected function getForeignKeysForTable(string $table): array
    {
        $driver = DB::connection()->getDriverName();

        switch ($driver) {
            case 'sqlite':
                return DB::select("PRAGMA foreign_key_list({$table})");

            case 'mysql':
                return DB::select("
                    SELECT
                        COLUMN_NAME,
                        REFERENCED_TABLE_NAME,
                        REFERENCED_COLUMN_NAME
                    FROM information_schema.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = DATABASE()
                        AND TABLE_NAME = ?
                        AND REFERENCED_TABLE_NAME IS NOT NULL
                ", [$table]);

            case 'pgsql':
                return DB::select("
                    SELECT
                        kcu.column_name,
                        ccu.table_name AS referenced_table_name,
                        ccu.column_name AS referenced_column_name
                    FROM information_schema.table_constraints AS tc
                    JOIN information_schema.key_column_usage AS kcu
                        ON tc.constraint_name = kcu.constraint_name
                    JOIN information_schema.constraint_column_usage AS ccu
                        ON ccu.constraint_name = tc.constraint_name
                    WHERE tc.constraint_type = 'FOREIGN KEY'
                        AND tc.table_name = ?
                ", [$table]);

            default:
                return [];
        }
    }

    public function test_can_track_schema_changes()
    {
        // Initial backup
        MigrationBackup::backup('test_users');

        if (DB::connection()->getDriverName() === 'sqlite') {
            // For SQLite, we need to recreate the table
            $this->recreateTableWithChanges();
        } else {
            // For other databases, we can use ALTER TABLE
            Schema::table('test_users', function ($table) {
                $table->string('phone')->nullable();
                $table->dropColumn('email');
            });
        }

        // Restore and verify
        MigrationBackup::restore('test_users');

        $history = MigrationBackup::getSchemaHistory('test_users');

        // Debug output
        // dump([
        //     'History' => $history,
        //     'Schema Snapshot' => $this->schemaSnapshot ?? 'No snapshot',
        //     'Current Schema' => Schema::getColumnListing('test_users')
        // ]);

        $this->assertArrayHasKey('changes', $history);
        $this->assertNotEmpty($history['changes']);

        $lastChange = end($history['changes']);
        $this->assertArrayHasKey('added', $lastChange);
        $this->assertArrayHasKey('removed', $lastChange);
        $this->assertArrayHasKey('phone', $lastChange['added']);
        $this->assertArrayHasKey('email', $lastChange['removed']);
    }

    protected function recreateTableWithChanges(): void
    {
        // Get current schema
        $oldTable = 'test_users';
        $tempTable = 'new_test_users';

        // Create new table with desired schema
        Schema::create($tempTable, function ($table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->timestamps();
        });

        // Copy data from old table to new table
        $oldData = DB::table($oldTable)->get();
        foreach ($oldData as $record) {
            $data = (array)$record;
            unset($data['email']); // Remove email field
            $data['phone'] = null; // Add phone field
            DB::table($tempTable)->insert($data);
        }

        // Drop old table and rename new table
        Schema::drop($oldTable);
        Schema::rename($tempTable, $oldTable);
    }

    public function test_can_restore_data_with_schema_changes()
    {
        // Insert additional test data
        DB::table('test_users')->insert([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Backup current state
        MigrationBackup::backup('test_users');

        // Get original count
        $originalCount = DB::table('test_users')->count();

        // Modify schema and data
        Schema::table('test_users', function ($table) {
            $table->string('phone')->nullable();
        });
        DB::table('test_users')->truncate();

        // Restore
        MigrationBackup::restore('test_users');

        // Check if data is restored
        $this->assertEquals($originalCount, DB::table('test_users')->count());
        $this->assertTrue(Schema::hasColumn('test_users', 'phone'));
    }

    public function test_can_handle_multiple_tables_in_single_migration()
    {
        // Backup multiple tables
        MigrationBackup::backup('test_users');
        MigrationBackup::backup('test_posts');

        // Modify both tables
        DB::table('test_users')->truncate();
        DB::table('test_posts')->truncate();

        // Restore all
        MigrationBackup::restore();

        $this->assertEquals(1, DB::table('test_users')->count());
        $this->assertEquals(1, DB::table('test_posts')->count());
    }

    public function test_handles_foreign_key_constraints()
    {
        MigrationBackup::backup('test_posts');

        // Delete all data
        DB::table('test_posts')->truncate();
        DB::table('test_users')->truncate();

        // Restore should handle foreign key constraints
        MigrationBackup::restore('test_posts');

        $this->assertEquals(1, DB::table('test_users')->count());
        $this->assertEquals(1, DB::table('test_posts')->count());
    }

    public function test_can_clear_backups()
    {
        MigrationBackup::backup('test_users');
        MigrationBackup::backup('test_posts');

        // Clear specific table backup
        MigrationBackup::clearBackup('test_users');
        $this->assertNull(MigrationBackup::getBackup('test_users'));
        $this->assertNotNull(MigrationBackup::getBackup('test_posts'));

        // Clear all backups
        MigrationBackup::clearBackup();
        $this->assertNull(MigrationBackup::getBackup('test_posts'));
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_posts');
        Schema::dropIfExists('test_users');
        parent::tearDown();
    }
}
