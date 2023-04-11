<?php

namespace OoBook\CRM\Base\Services;

use Illuminate\Support\Facades\Cache;

class Assets
{
    function asset($file)
    {
        return $this->devAsset($file) ?? $this->prodAsset($file);
    }

    public function prodAsset($file)
    {
        $manifest = $this->readManifest();

        if (isset($manifest[$file])) {
            return $manifest[$file];
        }

        return '/' . config('base.public_dir', 'unusual') . '/' . $file;
    }

    public function getManifestFilename()
    {
        $fileName =
            public_path(config('base.public_dir', 'unusual')) .
            '/' .
            config('base.manifest', 'unusual-manifest.json');

        if (file_exists($fileName)) {
            return $fileName;
        }

        return base_path(
            config('base.vendor_path') . '/vue/dist/' . config('base.public_dir') . '/' . config('base.manifest')
        );
    }

    public function devAsset($file)
    {
        if (!$this->devMode()) {
            return null;
        }

        $devServerUrl = config('base.development_url', 'http://localhost:8080');
        try {
            $manifest = $this->readJson(
                'http://workspace:8080'.
                    '/' .
                    config('base.public_dir') .
                    '/' .
                    config('base.manifest', 'unusual-manifest.json')
            );
        } catch (\Exception $e) {
            dd(
                $devServerUrl .
                '/' .
                config('base.public_dir') .
                '/' .
                config('base.manifest', 'unusual-manifest.json'),

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
            config('base.is_development', false);
    }
}
