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

        return '/' . unusualConfig('public_dir', 'unusual') . '/' . $file;
    }

    public function getManifestFilename()
    {
        $fileName =
            public_path(unusualConfig('public_dir', 'unusual')) .
            '/' .
            unusualConfig('manifest', 'unusual-manifest.json');

        if (file_exists($fileName)) {
            return $fileName;
        }

        return base_path(
            unusualConfig('vendor_path') . '/vue/dist/' . unusualConfig('public_dir') . '/' . unusualConfig('manifest')
        );
    }

    public function devAsset($file)
    {
        if (! $this->devMode()) {
            return null;
        }

        $devServerUrl = unusualConfig('development_url', 'http://localhost:8080');
        try {
            $manifest = $this->readJson(
                'http://workspace:8080' .
                    '/' .
                    unusualConfig('public_dir') .
                    '/' .
                    unusualConfig('manifest', 'unusual-manifest.json')
            );
        } catch (\Exception $e) {
            dd(
                $devServerUrl .
                '/' .
                unusualConfig('public_dir') .
                '/' .
                unusualConfig('manifest', 'unusual-manifest.json'),

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
            unusualConfig('is_development', false);
    }
}
