#!/usr/bin/env php
<?php

/**
 * Modularity → Modularous Migration Script
 * -----------------------------------------
 * Run from the project root:
 *   php migrate_modularity_to_modularous.php
 *
 * No Laravel bootstrap needed — reads .env directly.
 */

declare(strict_types=1);

// ══════════════════════════════════════════════════════════════════════════════
// BOOTSTRAP
// ══════════════════════════════════════════════════════════════════════════════

$scriptDir = getcwd();
global $projectRoot;
$projectRoot = $scriptDir;   // put this file in the project root

// Try to climb up until we find artisan (project root indicator)
foreach (range(0, 3) as $_) {
    if (file_exists($projectRoot . '/artisan')) {
        break;
    }
    $projectRoot = dirname($projectRoot);
}

if (! file_exists($projectRoot . '/artisan')) {
    abort('Cannot locate project root (artisan not found). Run from the Laravel project root.');
}

// ── Parse .env ────────────────────────────────────────────────────────────────
$envPath = $projectRoot . '/.env';

if (! file_exists($envPath)) {
    abort('.env file not found at: ' . $envPath);
}

$env = parseEnv($envPath);

// ── PDO connection ────────────────────────────────────────────────────────────
$pdo = makePdo($env, $projectRoot);
// ══════════════════════════════════════════════════════════════════════════════
// RUN
// ══════════════════════════════════════════════════════════════════════════════

banner();

step('1/6', 'PHP / JSON / YAML / XML file namespace replacements');
fixNamespacesInFiles($projectRoot);

step('2/6', 'Config file rename & text replacements');
fixConfigFiles($projectRoot);

step('3/6', 'Root directory rename');
renameRootDirectory($projectRoot);

step('4/6', '.env file updates');
fixEnvFile($envPath);

step('5/6', 'Database record updates');
fixDatabaseRecords($pdo, $env);

step('6/6', 'Sonraki adımlar — Manuel işlemler gerekiyor');

out('');
out('  <yellow>Aşağıdaki komutları sırasıyla çalıştırın:</yellow>');
out('');
out('  <bold>1) Vendor klasörünü silin:</bold>');
out("  <cyan>   rm -rf {$projectRoot}/vendor</cyan>");
out('');
out('  <bold>2) Composer ile yeniden kurun:</bold>');
out("  <cyan>   cd {$projectRoot} && composer install</cyan>");
out('');
out('');
out('  <bold>3) Vendor publish:</bold>');
out('  <cyan>   php artisan modularous:build</cyan>');
out('  <cyan>   php artisan vendor:publish --tag=views --force</cyan>');
out('');
out('  <yellow>⚠  Bu adımlar tamamlanmadan uygulama çalışmayacaktır.</yellow>');
out('');

out('');
out('<green>✅  All done — Modularity → Modularous migration complete.</green>');
out('');

// ══════════════════════════════════════════════════════════════════════════════
// STEP 1 — FILE NAMESPACE REPLACEMENTS
// ══════════════════════════════════════════════════════════════════════════════

function fixNamespacesInFiles(string $root): void
{
    $extensions = ['php', 'json', 'yml', 'yaml', 'xml'];
    $excludeDirs = ['vendor', 'node_modules', '.git', 'storage/logs', 'storage/framework', 'packages'];

    $files = collectFiles($root, $extensions, $excludeDirs);
    $updated = 0;

    foreach ($files as $path) {
        $original = file_get_contents($path);

        // Pass 1 — double-backslash form (serialised / JSON strings inside source)
        $modified = str_replace(
            'Unusualify\\\\Modularity',
            'Unusualify\\\\Modularous',
            $original
        );
        // Pass 2 — single-backslash form (use statements, type hints, class names)
        $modified = str_replace(
            'Unusualify\Modularity',
            'Unusualify\Modularous',
            $modified
        );

        if ($modified !== $original) {
            file_put_contents($path, $modified);
            $relative = ltrim(str_replace($root, '', $path), DIRECTORY_SEPARATOR);
            ok("Updated: {$relative}");
            $updated++;
        }
    }

    info("→ {$updated} file(s) updated.");
}

function collectFiles(string $root, array $extensions, array $excludeDirs): array
{
    $result = [];
    $dirIter = new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS);

    $filter = new RecursiveCallbackFilterIterator(
        $dirIter,
        static function (SplFileInfo $file) use ($root, $excludeDirs): bool {
            if ($file->isDir()) {
                $relative = ltrim(str_replace($root, '', $file->getRealPath()), DIRECTORY_SEPARATOR);
                foreach ($excludeDirs as $excl) {
                    if (str_starts_with($relative, $excl)) {
                        return false;
                    }
                }
            }

            return true;
        }
    );

    foreach (new RecursiveIteratorIterator($filter) as $file) {
        if ($file->isFile() && in_array($file->getExtension(), $extensions, true)) {
            $result[] = $file->getRealPath();
        }
    }

    return $result;
}

// ══════════════════════════════════════════════════════════════════════════════
// STEP 2 — CONFIG FILES
// ══════════════════════════════════════════════════════════════════════════════

function fixConfigFiles(string $root): void
{
    $configDir = $root . '/config';

    // Rename modularity.php → modularous.php
    $src = $configDir . '/modularity.php';
    $dst = $configDir . '/modularous.php';

    if (file_exists($src)) {
        if (file_exists($dst)) {
            warn('config/modularous.php already exists — rename skipped.');
        } else {
            rename($src, $dst);
            ok('Renamed: config/modularity.php → config/modularous.php');
        }
    } else {
        skip('config/modularity.php not found — rename skipped.');
    }

    // Text replacements inside specific config files
    foreach (['modules.php', 'auth.php', 'modularous.php'] as $filename) {
        $path = $configDir . '/' . $filename;
        if (! file_exists($path)) {
            continue;
        }

        $original = file_get_contents($path);
        $modified = tripleReplace($original);

        if ($modified !== $original) {
            file_put_contents($path, $modified);
            ok("Updated: config/{$filename}");
        }
    }
}

// ══════════════════════════════════════════════════════════════════════════════
// STEP 3 — ROOT DIRECTORY RENAME
// ══════════════════════════════════════════════════════════════════════════════

function renameRootDirectory(string $root): void
{
    $src = $root . '/modularity';
    $dst = $root . '/modularous';

    if (! is_dir($src)) {
        skip("No 'modularity/' directory at project root — skipped.");

        return;
    }

    if (is_dir($dst)) {
        warn("'modularous/' already exists at project root — rename skipped.");

        return;
    }

    rename($src, $dst);
    ok('Renamed: modularity/ → modularous/');
}

// ══════════════════════════════════════════════════════════════════════════════
// STEP 4 — .ENV FILE
// ══════════════════════════════════════════════════════════════════════════════

function fixEnvFile(string $envPath): void
{
    $original = file_get_contents($envPath);
    $modified = tripleReplace($original);

    if ($modified !== $original) {
        file_put_contents($envPath, $modified);
        ok('.env updated (MODULARITY / Modularity / modularity → MODULAROUS / Modularous / modularous).');
    } else {
        skip('.env had no Modularity references.');
    }
}

// ══════════════════════════════════════════════════════════════════════════════
// STEP 5 — DATABASE
// ══════════════════════════════════════════════════════════════════════════════

function fixDatabaseRecords(PDO $pdo, array $env): void
{
    $NS_OLD = 'Unusualify\Modularity';
    $NS_NEW = 'Unusualify\Modularous';
    $JSON_NS_OLD = 'Unusualify\\\\Modularity';   // two actual backslashes (JSON-encoded)
    $JSON_NS_NEW = 'Unusualify\\\\Modularous';

    // ── 5a. migrations ────────────────────────────────────────────────────────
    dbGroup('migrations table');
    if (tableExists($pdo, 'migrations')) {
        $rows = $pdo->query("SELECT migration FROM migrations WHERE migration LIKE '%modularity%'")->fetchAll(PDO::FETCH_COLUMN);
        if (empty($rows)) {
            skip('No modularity entries in migrations table.');
        } else {
            $stmt = $pdo->prepare('UPDATE migrations SET migration = ? WHERE migration = ?');
            foreach ($rows as $name) {
                $newName = str_replace('modularity', 'modularous', $name);
                $stmt->execute([$newName, $name]);
                ok("migrations: {$name} → {$newName}");
            }
        }
    } else {
        skip('Table migrations not found.');
    }

    // ── 5b. sp_activity_log ───────────────────────────────────────────────────
    dbGroup('sp_activity_log');
    if (tableExists($pdo, 'sp_activity_log')) {
        foreach (['subject_type', 'causer_type'] as $col) {
            dbReplace($pdo, 'sp_activity_log', $col, $NS_OLD, $NS_NEW);
        }
        foreach (['properties', 'description'] as $col) {
            dbReplace($pdo, 'sp_activity_log', $col, $JSON_NS_OLD, $JSON_NS_NEW);
            dbReplace($pdo, 'sp_activity_log', $col, $NS_OLD, $NS_NEW);
        }
    } else {
        skip('Table sp_activity_log not found.');
    }

    // ── 5c. sp_model_has_permissions / sp_model_has_roles ─────────────────────
    dbGroup('sp_model_has_permissions & sp_model_has_roles');
    foreach (['sp_model_has_permissions', 'sp_model_has_roles'] as $table) {
        if (tableExists($pdo, $table)) {
            dbReplace($pdo, $table, 'model_type', $NS_OLD, $NS_NEW);
        } else {
            skip("Table {$table} not found.");
        }
    }

    // ── 5d. guard_name columns ────────────────────────────────────────────────
    dbGroup('guard_name (sp_roles / sp_permissions / um_creator_records)');
    foreach (['sp_roles', 'sp_permissions', 'um_creator_records'] as $table) {
        if (tableExists($pdo, $table) && columnExists($pdo, $table, 'guard_name')) {
            dbReplace($pdo, $table, 'guard_name', 'modularity', 'modularous');
        } else {
            skip("Table/column {$table}.guard_name not found.");
        }
    }

    // ── 5e. Laravel Telescope ─────────────────────────────────────────────────
    dbGroup('Laravel Telescope');
    if (tableExists($pdo, 'telescope_entries')) {
        foreach (['content', 'family_hash'] as $col) {
            if (columnExists($pdo, 'telescope_entries', $col)) {
                dbReplace($pdo, 'telescope_entries', $col, $JSON_NS_OLD, $JSON_NS_NEW);
                dbReplace($pdo, 'telescope_entries', $col, $NS_OLD, $NS_NEW);
            }
        }
    } else {
        skip('telescope_entries not found.');
    }
    foreach (['telescope_entries_tags', 'telescope_monitoring'] as $table) {
        if (tableExists($pdo, $table) && columnExists($pdo, $table, 'tag')) {
            dbReplace($pdo, $table, 'tag', $JSON_NS_OLD, $JSON_NS_NEW);
            dbReplace($pdo, $table, 'tag', $NS_OLD, $NS_NEW);
        } else {
            skip("Table/column {$table}.tag not found.");
        }
    }

    // ── 5f. um_* tables ───────────────────────────────────────────────────────
    dbGroup('um_* namespace / type columns');
    $umMap = [
        'um_authorizations' => ['authorized_type'],
        'um_spreads' => ['spreadable_type'],
        'um_tagged' => ['taggable_type'],
        'um_tags' => ['namespace'],
        'um_creator_records' => ['creator_type', 'creatable_type'],
    ];
    foreach ($umMap as $table => $columns) {
        if (! tableExists($pdo, $table)) {
            skip("Table {$table} not found.");

            continue;
        }
        foreach ($columns as $col) {
            if (columnExists($pdo, $table, $col)) {
                dbReplace($pdo, $table, $col, $NS_OLD, $NS_NEW);
            } else {
                skip("Column {$table}.{$col} not found.");
            }
        }
    }
}

// ══════════════════════════════════════════════════════════════════════════════
// DB HELPERS
// ══════════════════════════════════════════════════════════════════════════════

function dbReplace(PDO $pdo, string $table, string $column, string $from, string $to): void
{
    $count = (int) $pdo
        ->query("SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` LIKE " . $pdo->quote('%' . $from . '%'))
        ->fetchColumn();

    if ($count === 0) {
        return;
    }

    $stmt = $pdo->prepare(
        "UPDATE `{$table}` SET `{$column}` = REPLACE(`{$column}`, ?, ?) WHERE `{$column}` LIKE ?"
    );
    $stmt->execute([$from, $to, '%' . $from . '%']);

    $display = mb_strlen($from) > 45 ? mb_substr($from, 0, 42) . '…' : $from;
    ok(sprintf('%s.%s — %d row(s)  [%s → …]', $table, $column, $count, $display));
}

function tableExists(PDO $pdo, string $table): bool
{
    try {
        $pdo->query("SELECT 1 FROM `{$table}` LIMIT 1");

        return true;
    } catch (Throwable) {
        return false;
    }
}

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    if ($driver === 'mysql') {
        $rows = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE " . $pdo->quote($column))->fetchAll();

        return ! empty($rows);
    }

    if ($driver === 'sqlite') {
        $stmt = $pdo->query("PRAGMA table_info(`{$table}`)");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
            if ($col['name'] === $column) {
                return true;
            }
        }

        return false;
    }

    if ($driver === 'pgsql') {
        $stmt = $pdo->prepare('
            SELECT column_name
            FROM information_schema.columns
            WHERE table_name = ? AND column_name = ?
        ');
        $stmt->execute([$table, $column]);

        return $stmt->rowCount() > 0;
    }

    throw new RuntimeException("Desteklenmeyen veritabanı sürücüsü: {$driver}");
}

// ══════════════════════════════════════════════════════════════════════════════
// .ENV PARSER
// ══════════════════════════════════════════════════════════════════════════════

function parseEnv(string $path): array
{
    $env = [];
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (! str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        // Strip surrounding quotes
        if (preg_match('/^(["\'])(.*)\\1$/', $value, $m)) {
            $value = $m[2];
        }

        $env[$key] = $value;
    }

    return $env;
}

// ══════════════════════════════════════════════════════════════════════════════
// PDO FACTORY
// ══════════════════════════════════════════════════════════════════════════════

function makePdo(array $env, string $projectRoot): PDO
{
    $connection = $env['DB_CONNECTION'] ?? 'mysql';
    $host = $env['DB_HOST'] ?? '127.0.0.1';
    $port = $env['DB_PORT'] ?? '3306';
    $database = $env['DB_DATABASE'] ?? '';
    $username = $env['DB_USERNAME'] ?? '';
    $password = $env['DB_PASSWORD'] ?? '';

    if ($connection !== 'mysql') {
        // Basic support for pgsql / sqlite — extend as needed
        if ($connection === 'sqlite') {
            $dbPath = $database;
            if (! str_starts_with($dbPath, '/')) {
                $dbPath = $projectRoot . '/database/database.sqlite';
            }

            return new PDO("sqlite:{$dbPath}");
        }
        if ($connection === 'pgsql') {
            $dsn = "pgsql:host={$host};port={$port};dbname={$database}";

            return new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        }
    }

    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
        ]);
    } catch (Throwable $e) {
        abort('Database connection failed: ' . $e->getMessage());
    }

    return $pdo;
}

// ══════════════════════════════════════════════════════════════════════════════
// TEXT HELPER
// ══════════════════════════════════════════════════════════════════════════════

function tripleReplace(string $content): string
{
    return str_replace(
        ['MODULARITY', 'Modularity', 'modularity'],
        ['MODULAROUS', 'Modularous', 'modularous'],
        $content
    );
}

// ══════════════════════════════════════════════════════════════════════════════
// OUTPUT HELPERS
// ══════════════════════════════════════════════════════════════════════════════

function colorize(string $text): string
{
    $isTty = function_exists('posix_isatty') && posix_isatty(STDOUT);
    if (! $isTty) {
        return preg_replace('/<[^>]+>/', '', $text);
    }
    $map = [
        '<green>' => "\033[32m", '</green>' => "\033[0m",
        '<yellow>' => "\033[33m", '</yellow>' => "\033[0m",
        '<cyan>' => "\033[36m", '</cyan>' => "\033[0m",
        '<red>' => "\033[31m", '</red>' => "\033[0m",
        '<magenta>' => "\033[35m", '</magenta>' => "\033[0m",
        '<bold>' => "\033[1m",  '</bold>' => "\033[0m",
        '<info>' => "\033[32m", '</info>' => "\033[0m",
    ];

    return str_replace(array_keys($map), array_values($map), $text);
}

function out(string $msg = ''): void
{
    echo colorize($msg) . PHP_EOL;
}
function ok(string $msg): void
{
    out("    <green>✔</green> {$msg}");
}
function skip(string $msg): void
{
    out("    <yellow>–</yellow> {$msg}");
}
function warn(string $msg): void
{
    out("    <red>⚠</red>  {$msg}");
}
function info(string $msg): void
{
    out("  <info>{$msg}</info>");
}

function banner(): void
{
    out('');
    out('<cyan>╔══════════════════════════════════════════════════════╗</cyan>');
    out('<cyan>║</cyan>  <bold>Modularity → Modularous — Upgrade Script</bold>         <cyan>║</cyan>');
    out('<cyan>╚══════════════════════════════════════════════════════╝</cyan>');
    out('');
}

function step(string $num, string $label): void
{
    out('');
    out("<yellow>▶ Step {$num}:</yellow> <bold>{$label}</bold>");
    out('<yellow>' . str_repeat('─', 54) . '</yellow>');
}

function dbGroup(string $label): void
{
    out("  <magenta>◆ {$label}</magenta>");
}

function abort(string $msg): never
{
    out("<red>FATAL: {$msg}</red>");
    exit(1);
}
