<?php

namespace Unusualify\Modularous\Services;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JoeDixon\Translation\Drivers\File;

class FileTranslation extends File
{
    public $disk;

    public $languageFilesPath;

    public $sourceLanguage;

    public $scanner;

    public function __construct(Filesystem $disk, $languageFilesPath, $sourceLanguage, $scanner)
    {
        parent::__construct($disk, $languageFilesPath, $sourceLanguage, $scanner);
        $this->disk = $disk;
        $this->languageFilesPath = $languageFilesPath;
        $this->sourceLanguage = $sourceLanguage;
        $this->scanner = $scanner;

    }

    public function getLanguagesExcept(array $languages)
    {
        return array_diff($this->allLanguages()->toArray(), $languages);
    }

    public function getLanguagesOnly(array $languages)
    {
        return array_intersect($this->allLanguages()->toArray(), $languages);
    }

    /**
     * Get all translations from a different language path.
     *
     * @param string $languageFilesPath
     * @param string $language
     * @return Collection
     */
    public function getTranslationsFromPath($languageFilesPath, $language)
    {
        $tempInstance = new static($this->disk, $languageFilesPath, $this->sourceLanguage, $this->scanner);

        return $tempInstance->allTranslationsFor($language);
    }

    /**
     * Find translation keys that exist in source path but not in target path.
     *
     * @param string $sourcePath
     * @param string $targetPath
     * @param string $language
     * @return array
     */
    public function findMissingKeysFromPath($sourcePath, $targetPath, $language)
    {
        $sourceTranslations = $this->getTranslationsFromPath($sourcePath, $language);
        $targetTranslations = $this->getTranslationsFromPath($targetPath, $language);

        return $this->compareTranslations($sourceTranslations, $targetTranslations);
    }

    /**
     * Compare two translation collections and find missing keys.
     *
     * @return array
     */
    protected function compareTranslations(Collection $source, Collection $target)
    {
        $missing = [];

        foreach ($source as $type => $groups) {
            foreach ($groups as $group => $translations) {
                $targetGroup = $target->get($type, collect())->get($group, collect());

                foreach ($translations as $key => $value) {
                    if (! $targetGroup->has($key)) {
                        if (! isset($missing[$type])) {
                            $missing[$type] = [];
                        }
                        if (! isset($missing[$type][$group])) {
                            $missing[$type][$group] = [];
                        }
                        $missing[$type][$group][$key] = $value;
                    }
                }
            }
        }

        return $missing;
    }

    /**
     * Find all missing translation keys across all languages.
     *
     * @param string $sourcePath
     * @param string $targetPath
     * @return array
     */
    public function findAllMissingKeys($sourcePath, $targetPath)
    {
        $sourceInstance = new static($this->disk, $sourcePath, $this->sourceLanguage, $this->scanner);
        $languages = $sourceInstance->allLanguages();

        $allMissing = [];

        foreach ($languages as $language) {
            $missing = $this->findMissingKeysFromPath($sourcePath, $targetPath, $language);

            if (! empty($missing)) {
                $allMissing[$language] = $missing;
            }
        }

        return $allMissing;
    }

    /**
     * Sync missing keys from source path to target path for a specific language.
     *
     * @param string $sourcePath
     * @param string $targetPath
     * @param string $language
     * @param array $missingKeys
     * @return void
     */
    public function syncMissingKeysToPath($sourcePath, $targetPath, $language, $missingKeys)
    {
        $targetInstance = new static($this->disk, $targetPath, $this->sourceLanguage, $this->scanner);

        foreach ($missingKeys as $type => $groups) {
            foreach ($groups as $group => $translations) {
                if ($type === 'single') {
                    // Handle JSON translations
                    $this->syncSingleTranslations($targetInstance, $language, $group, $translations);
                } else {
                    // Handle group (PHP file) translations
                    $this->syncGroupTranslations($targetInstance, $language, $group, $translations);
                }
            }
        }
    }

    /**
     * Sync group translations to target path.
     *
     * @param FileTranslation $targetInstance
     * @param string $language
     * @param string $group
     * @param array $translations
     * @return void
     */
    protected function syncGroupTranslations($targetInstance, $language, $group, $translations)
    {
        // Get existing translations from target
        $existingTranslations = $targetInstance->getGroupTranslationsFor($language)->get($group, collect());

        // Merge with new translations
        $merged = $existingTranslations->merge($translations)->toArray();

        // Save to target path
        $targetInstance->saveGroupTranslations($language, $group, $merged);
    }

    /**
     * Sync single (JSON) translations to target path.
     *
     * @param FileTranslation $targetInstance
     * @param string $language
     * @param string $group
     * @param array $translations
     * @return void
     */
    protected function syncSingleTranslations($targetInstance, $language, $group, $translations)
    {
        // Get existing single translations from target
        $existingTranslations = $targetInstance->getSingleTranslationsFor($language)->get($group, collect());

        // Merge with new translations
        $merged = $existingTranslations->merge($translations);

        // Save to target path using parent's saveSingleTranslations method
        $this->saveSingleTranslationsToPath($targetInstance, $language, $group, $merged);
    }

    /**
     * Save single translations to specific path.
     *
     * @param FileTranslation $targetInstance
     * @param string $language
     * @param string $group
     * @return void
     */
    protected function saveSingleTranslationsToPath($targetInstance, $language, $group, Collection $translations)
    {
        $vendor = Str::before($group, '::single');
        $languageFilePath = $vendor !== 'single'
            ? 'vendor' . DIRECTORY_SEPARATOR . "{$vendor}" . DIRECTORY_SEPARATOR . "{$language}.json"
            : "{$language}.json";

        $targetPath = $targetInstance->languageFilesPath . DIRECTORY_SEPARATOR . $languageFilePath;
        $directory = dirname($targetPath);

        // Create directory if it doesn't exist
        if (! $this->disk->exists($directory)) {
            $this->disk->makeDirectory($directory, 0755, true);
        }

        $this->disk->put(
            $targetPath,
            json_encode($translations->toArray(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }

    /**
     * Sync all missing keys from source path to target path.
     *
     * @param string $sourcePath
     * @param string $targetPath
     * @return array Statistics about synced keys
     */
    public function syncAllMissingKeys($sourcePath, $targetPath)
    {
        $allMissingKeys = $this->findAllMissingKeys($sourcePath, $targetPath);
        $stats = [
            'languages' => [],
            'total_keys' => 0,
        ];

        foreach ($allMissingKeys as $language => $missingKeys) {
            $keyCount = 0;

            foreach ($missingKeys as $type => $groups) {
                foreach ($groups as $group => $translations) {
                    $keyCount += count($translations);
                }
            }

            $this->syncMissingKeysToPath($sourcePath, $targetPath, $language, $missingKeys);

            $stats['languages'][$language] = $keyCount;
            $stats['total_keys'] += $keyCount;
        }

        return $stats;
    }

    /**
     * Save namespaced group type language translations.
     *
     * @param string $language
     * @param string $group
     * @param array $translations
     * @return void
     */
    private function saveNamespacedGroupTranslations($language, $group, $translations)
    {
        [$namespace, $group] = explode('::', $group);
        $directory = "{$this->languageFilesPath}" . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . "{$namespace}" . DIRECTORY_SEPARATOR . "{$language}";

        if (! $this->disk->exists($directory)) {
            $this->disk->makeDirectory($directory, 0755, true);
        }

        // $this->disk->put("$directory".DIRECTORY_SEPARATOR."{$group}.php", "<?php\n\nreturn ".var_export($translations, true).';'.\PHP_EOL);
        $this->disk->put("$directory" . DIRECTORY_SEPARATOR . "{$group}.php", php_array_file_content($translations));
    }

    /**
     * Save group type language translations.
     *
     * @param string $language
     * @param string $group
     * @param array $translations
     */
    public function saveGroupTranslations($language, $group, $translations)
    {
        // here we check if it's a namespaced translation which need saving to a
        // different path
        $translations = $translations instanceof Collection ? $translations->toArray() : $translations;
        ksort($translations);
        $translations = array_undot($translations);
        if (Str::contains($group, '::')) {
            return $this->saveNamespacedGroupTranslations($language, $group, $translations);
        }

        // $this->disk->put("{$this->languageFilesPath}".DIRECTORY_SEPARATOR."{$language}".DIRECTORY_SEPARATOR."{$group}.php", "<?php\n\nreturn ".var_export($translations, true).';'.\PHP_EOL);
        $this->disk->put("{$this->languageFilesPath}" . DIRECTORY_SEPARATOR . "{$language}" . DIRECTORY_SEPARATOR . "{$group}.php", php_array_file_content($translations));
    }
}
