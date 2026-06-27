<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\Library;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrphanUploadCleanup
{
    /**
     * @return array{scanned: int, deleted: int, orphans: string[], libraries: string[]}
     */
    public function cleanup(bool $dryRun = false): array
    {
        $summary = [
            'scanned' => 0,
            'deleted' => 0,
            'orphans' => [],
            'libraries' => [],
        ];

        foreach ($this->resolveStorageTargets() as $target) {
            $knownFolders = $this->collectKnownFolders($target['libraries']);
            $orphanFolders = $this->findOrphanFolders($target['disk'], $knownFolders);

            $summary['scanned'] += count($orphanFolders);
            $summary['libraries'] = array_values(array_unique(array_merge(
                $summary['libraries'],
                $target['libraries']
            )));

            foreach ($orphanFolders as $folder) {
                $summary['orphans'][] = $folder;

                if (! $dryRun) {
                    Storage::disk($target['disk'])->deleteDirectory($folder);
                    $summary['deleted']++;
                }
            }
        }

        $summary['orphans'] = array_values(array_unique($summary['orphans']));

        return $summary;
    }

    /**
     * @return array<int, array{disk: string, root: string, libraries: string[]}>
     */
    private function resolveStorageTargets(): array
    {
        $targets = [];

        foreach (['media', 'file'] as $libraryType) {
            if (modularousConfig($libraryType . '_library.endpoint_type') !== 'local') {
                continue;
            }

            $disk = (string) modularousConfig($libraryType . '_library.disk');

            if ($disk === '') {
                continue;
            }

            $root = rtrim(Storage::disk($disk)->path(''), DIRECTORY_SEPARATOR);

            if (! isset($targets[$root])) {
                $targets[$root] = [
                    'disk' => $disk,
                    'root' => $root,
                    'libraries' => [],
                ];
            }

            $targets[$root]['libraries'][] = $libraryType;
        }

        return array_values($targets);
    }

    /**
     * @param string[] $libraryTypes
     */
    private function collectKnownFolders(array $libraryTypes): Collection
    {
        $folders = collect();

        foreach ($libraryTypes as $libraryType) {
            $table = $libraryType === 'media'
                ? modularousConfig('tables.medias', 'medias')
                : modularousConfig('tables.files', 'files');

            DB::table($table)
                ->pluck('uuid')
                ->each(function ($uuid) use ($libraryType, $folders) {
                    $folder = $this->extractStorageFolder((string) $uuid, $libraryType);

                    if ($folder !== null) {
                        $folders->push($folder);
                    }
                });
        }

        return $folders->unique()->values();
    }

    /**
     * @return string[]
     */
    private function findOrphanFolders(string $disk, Collection $knownFolders): array
    {
        $known = $knownFolders->flip();
        $orphans = [];

        foreach (Storage::disk($disk)->directories() as $directory) {
            $folder = basename(str_replace('\\', '/', $directory));

            if (! Str::isUuid($folder)) {
                continue;
            }

            if ($known->has($folder)) {
                continue;
            }

            $orphans[] = $directory;
        }

        return $orphans;
    }

    private function extractStorageFolder(string $uuid, string $libraryType): ?string
    {
        $prefixWithLocalPath = (bool) modularousConfig($libraryType . '_library.prefix_uuid_with_local_path', false);

        if ($prefixWithLocalPath) {
            $prefix = trim((string) modularousConfig($libraryType . '_library.local_path', 'uploads'), '/ ') . '/';

            if (str_starts_with($uuid, $prefix)) {
                $uuid = mb_substr($uuid, mb_strlen($prefix));
            }
        }

        if (! str_contains($uuid, '/')) {
            return null;
        }

        $folder = explode('/', $uuid, 2)[0];

        return Str::isUuid($folder) ? $folder : null;
    }
}
