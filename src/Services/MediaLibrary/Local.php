<?php

namespace Unusualify\Modularous\Services\MediaLibrary;

use Illuminate\Support\Facades\Storage;

class Local implements ImageServiceInterface
{
    use ImageServiceDefaults;

    /**
     * @param string $id
     * @return string
     */
    public function getUrl($id, array $params = [])
    {
        return $this->getRawUrl($id);
    }

    /**
     * @param string $id
     * @return string
     */
    public function getUrlWithCrop($id, array $crop_params, array $params = [])
    {
        return $this->getRawUrl($id);
    }

    /**
     * @param string $id
     * @param int $width
     * @param int $height
     * @return string
     */
    public function getUrlWithFocalCrop($id, array $cropParams, $width, $height, array $params = [])
    {
        return $this->getRawUrl($id);
    }

    /**
     * @param string $id
     * @return string
     */
    public function getLQIPUrl($id, array $params = [])
    {
        return $this->getRawUrl($id);
    }

    /**
     * @param string $id
     * @return string
     */
    public function getSocialUrl($id, array $params = [])
    {
        return $this->getRawUrl($id);
    }

    /**
     * @param string $id
     * @return string
     */
    public function getCmsUrl($id, array $params = [])
    {
        return $this->getRawUrl($id);
    }

    /**
     * @param string $id
     * @return string
     */
    public function getFrontendUrl($id, array $params = [])
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(config(modularousBaseKey() . '.media_library.disk'));
        $storageUrl = $disk->url($id);
        $endpoint = parse_url($storageUrl, PHP_URL_PATH) ?: $storageUrl;

        return rtrim(modularousConfig('app_url'), '/') . '/' . ltrim($endpoint, '/');
    }

    /**
     * @param string $id
     * @return string
     */
    public function getRawUrl($id)
    {
        return Storage::disk(config(modularousBaseKey() . '.media_library.disk'))->url($id);
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function getDimensions($id)
    {
        return null;
    }
}
