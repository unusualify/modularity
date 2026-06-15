<?php

/**
 * Dosya İsimleri Değiştirme Scripti - Exclude Destekli
 */
$targetDirectory = __DIR__; // Proje kök dizini
$dryRun = true;

// Hariç tutulacak klasör yolları (Relative path olarak)
$excludePaths = [
    'docs/build',
    'vue/dist',
    'node_modules',
    'vendor',
    '.git',
    'storage',
];

$replacements = [
    'Modularity' => 'Modularous',
    'modularity' => 'modularous',
];

$rootPath = realpath(getcwd());

if (! is_dir($targetDirectory)) {
    exit("Hata: Hedef dizin bulunamadı.\n");
}

echo '--- ' . ($dryRun ? 'DRY-RUN MODU' : 'CANLI MOD') . " ---\n";

$directory = new RecursiveDirectoryIterator($targetDirectory, RecursiveDirectoryIterator::SKIP_DOTS);
$iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::CHILD_FIRST);

$renameCount = 0;

foreach ($iterator as $fileInfo) {
    $oldPath = $fileInfo->getRealPath();

    // Kendi script dosyamızı ve exclude edilen yolları atla
    if ($oldPath === __FILE__) {
        continue;
    }

    // Exclude kontrolü
    $shouldSkip = false;
    foreach ($excludePaths as $exclude) {
        // Yolun içinde exclude edilen kelime geçiyor mu?
        if (str_contains($oldPath, DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $exclude))) {
            $shouldSkip = true;

            break;
        }
    }
    if ($shouldSkip) {
        continue;
    }

    $oldName = $fileInfo->getFilename();
    $newName = $oldName;

    foreach ($replacements as $search => $replace) {
        $newName = str_replace($search, $replace, $newName);
    }

    if ($newName !== $oldName) {
        $newPath = $fileInfo->getPath() . DIRECTORY_SEPARATOR . $newName;
        $relativePath = str_replace($rootPath, '.', $oldPath);

        if ($dryRun) {
            echo "[PREVIEW] $relativePath  ->  $newName\n";
        } else {
            if (rename($oldPath, $newPath)) {
                echo "[RENAMED] $relativePath  ->  $newName\n";
            } else {
                echo "[ERROR] Adlandırılamadı: $relativePath\n";
            }
        }
        $renameCount++;
    }
}

echo "\n--- İşlem Tamamlandı. Toplam $renameCount dosya/klasör tespit edildi. ---\n";
