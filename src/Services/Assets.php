<?php

namespace Unusualify\Modularity\Services;

use Illuminate\Support\Facades\Cache;

class Assets
{
    public function asset($file)
    {
        return $this->devAsset($file) ?? $this->prodAsset($file);
    }

    public function prodAsset($file)
    {
        $manifest = $this->readManifest();

        if (isset($manifest[$file])) {
            return $manifest[$file];
        }

        return '/' . modularityConfig('public_dir', 'unusual') . '/' . $file;
    }

    public function getManifestFilename()
    {
        $fileName =
            public_path(modularityConfig('public_dir', 'unusual')) .
            '/' .
            modularityConfig('manifest', 'unusual-manifest.json');

        if (file_exists($fileName)) {
            return $fileName;
        }

        return base_path(
            modularityConfig('vendor_path') . '/vue/dist/' . modularityConfig('public_dir') . '/' . modularityConfig('manifest')
        );
    }

    public function devAsset($file)
    {
        if (! $this->devMode()) {
            return null;
        }

        $devServerUrl = modularityConfig('development_url', 'http://localhost:8080');
        try {
            $manifest = $this->readJson(
                'http://workspace:8080' .
                    '/' .
                    modularityConfig('public_dir') .
                    '/' .
                    modularityConfig('manifest', 'unusual-manifest.json')
            );
        } catch (\Exception $e) {
            dd(
                $devServerUrl .
                '/' .
                modularityConfig('public_dir') .
                '/' .
                modularityConfig('manifest', 'unusual-manifest.json'),

                $file,
                debug_backtrace()
            );
            throw new \Exception(
                'Twill dev assets manifest is missing. Make sure you are running the npm run serve command inside Twill.'
            );
        }

        // dd(
        //     $devServerUrl,
        //     $manifest
        // );
        return $devServerUrl . ($manifest[$file] ?? '/' . $file);
    }

    /**
     * @return mixed
     */
    private function readManifest()
    {
        return $this->readJson($this->getManifestFilename());
        try {
            return Cache::rememberForever('unusual-manifest', function () {
                return $this->readJson($this->getManifestFilename());
            });
        } catch (\Exception $e) {
            throw new \Exception(
                ''
                // 'Twill assets manifest is missing. Make sure you published/updated Twill assets using the "php artisan twill:update" command.'
            );
        }
    }

    private function readJson($fileName)
    {
        return json_decode(file_get_contents($fileName), true);
    }

    private function devMode()
    {
        return app()->environment('local', 'development') &&
            modularityConfig('is_development', false);
    }
}
