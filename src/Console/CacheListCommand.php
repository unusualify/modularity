<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Cache\FileStore;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;

class CacheListCommand extends Command
{
    /**
     * Indicates if the command should be hidden from the list of available commands.
     *
     * @var bool
     */
    protected $hidden = true;

    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'cache:list {--limit=20 : Limit the number of items to display}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List items in the file cache, including deeply nested folders';

    /**
     * Placeholder for files.
     *
     * @var mixed
     */
    protected $files;

    /**
     * Rows to be displayed.
     *
     * @var array
     */
    protected $rows = [];

    /**
     * Limit for the number of items to display.
     *
     * @var int
     */
    protected $limit;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Handle the cache list command.
     *
     * Retrieves the cache store and checks if it is a FileStore.
     * If not, displays an error message and exits.
     * Scans the cache directory, sets the limit based on the option provided,
     * and lists the cache items in a table format with key, expiration, and value columns.
     * If no cache items are found, a message is displayed.
     * The number of cache items shown can be limited using the --limit option.
     */
    public function handle()
    {
        $store = Cache::getStore();

        if (! $store instanceof FileStore) {
            $this->error('This command only works with the file cache driver.');

            return;
        }

        $cacheDir = $store->getDirectory();
        $this->limit = $this->option('limit');

        $this->scanDirectory($cacheDir);

        if (empty($this->rows)) {
            $this->info('The cache is empty or all items have expired.');

            return;
        }
        $headers = ['Key', 'Expiration', 'Value'];
        $this->table($headers, $this->rows);
        $this->info(sprintf('Showing %d cache items. Use --limit option to show more.', count($this->rows)));
    }

    /**
     * Recursively scans a directory for cache files and processes them.
     *
     * This method iterates through directories and files within the given directory.
     * It processes each file by extracting key, expiration, and value information.
     * The processing includes checking expiration time and displaying cache items in a table format.
     */
    protected function scanDirectory($directory)
    {
        foreach ($this->files->directories($directory) as $subdir) {
            $this->scanDirectory($subdir);
        }

        foreach ($this->files->files($directory) as $file) {
            if (count($this->rows) >= $this->limit) {
                return;
            }

            $this->processFile($file);
            // if ($file->getExtension() === 'cache') {
            // }
        }
    }

    /**
     * Process a cache file.
     *
     * Extracts key, expiration, and value information from the cache file.
     * Checks if the cache item has expired or has a specific expiration time.
     * Adds the cache item to the rows list if it meets the expiration criteria.
     */
    protected function processFile($file)
    {
        $key = str_replace('.cache', '', $file->getFilename());
        $contents = $this->files->get($file->getPathname());
        $expire = mb_substr($contents, 0, 10);
        $value = unserialize(mb_substr($contents, 10));

        if ($expire == 9999999999 || $expire > time()) {
            $this->rows[] = [
                $key,
                date('Y-m-d H:i:s', $expire),
                json_encode($value, JSON_PRETTY_PRINT),
            ];
        }
    }
}
