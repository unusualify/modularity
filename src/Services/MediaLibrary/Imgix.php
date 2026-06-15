<?php

namespace Unusualify\Modularous\Services\MediaLibrary;

use Illuminate\Config\Repository as Config;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Imgix\UrlBuilder;

class Imgix implements ImageServiceInterface
{
    use ImageServiceDefaults;

    /**
     * @var UrlBuilder|null Null when `IMGIX_SOURCE_HOST` is missing —
     *                      see the constructor for why we don't fatally throw in that case.
     */
    private $urlBuilder;

    /**
     * @var Config
     */
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;

        // The Imgix UrlBuilder rejects null/empty domains with
        // `InvalidArgumentException: UrlBuilder must be passed a string
        // domain`. Until recently this was thrown during model
        // serialisation (e.g. PaymentService::button_logo_url accessor),
        // taking out every index/show endpoint that touched a model with
        // image accessors when `IMGIX_SOURCE_HOST` was missing. Skip
        // constructing the builder in that case; `getUrl` and friends
        // return '' instead of fatally throwing.
        $sourceHost = $this->config->get(modularousBaseKey() . '.imgix.source_host');
        if (! is_string($sourceHost) || $sourceHost === '') {
            $this->urlBuilder = null;

            return;
        }

        $urlBuilder = new UrlBuilder(
            $sourceHost,
            $this->config->get(modularousBaseKey() . '.imgix.use_https'),
            '',
            false
        );

        if ($this->config->get(modularousBaseKey() . '.imgix.use_signed_urls')) {
            $urlBuilder->setSignKey($this->config->get(modularousBaseKey() . '.imgix.sign_key'));
        }

        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param string $id
     * @return string
     */
    public function getUrl($id, array $params = [])
    {
        if ($this->urlBuilder === null) {
            return '';
        }

        $defaultParams = $this->config->get(modularousBaseKey() . '.imgix.default_params');
        $addParamsToSvgs = modularousConfig('imgix.add_params_to_svgs', false);

        if (! $addParamsToSvgs && Str::endsWith($id, '.svg')) {
            return $this->urlBuilder->createURL($id);
        }

        return $this->urlBuilder->createURL($id, array_replace($defaultParams, $params));
    }

    /**
     * @param string $id
     * @return string
     */
    public function getUrlWithCrop($id, array $cropParams, array $params = [])
    {
        return $this->getUrl($id, $this->getCrop($cropParams) + $params);
    }

    /**
     * @param string $id
     * @param mixed $width
     * @param mixed $height
     * @return string
     */
    public function getUrlWithFocalCrop($id, array $cropParams, $width, $height, array $params = [])
    {
        return $this->getUrl($id, $this->getFocalPointCrop($cropParams, $width, $height) + $params);
    }

    /**
     * @param string $id
     * @return string
     */
    public function getLQIPUrl($id, array $params = [])
    {
        $defaultParams = $this->config->get(modularousBaseKey() . '.imgix.lqip_default_params');

        $cropParams = Arr::has($params, $this->cropParamsKeys) ? $this->getCrop($params) : [];

        $params = Arr::except($params, $this->cropParamsKeys);

        return $this->getUrl($id, array_replace($defaultParams, $params + $cropParams));
    }

    /**
     * @param string $id
     * @return string
     */
    public function getSocialUrl($id, array $params = [])
    {
        $defaultParams = $this->config->get(modularousBaseKey() . '.imgix.social_default_params');

        $cropParams = Arr::has($params, $this->cropParamsKeys) ? $this->getCrop($params) : [];

        $params = Arr::except($params, $this->cropParamsKeys);

        return $this->getUrl($id, array_replace($defaultParams, $params + $cropParams));
    }

    /**
     * @param string $id
     * @return string
     */
    public function getCmsUrl($id, array $params = [])
    {
        $defaultParams = $this->config->get(modularousBaseKey() . '.imgix.cms_default_params');

        $cropParams = Arr::has($params, $this->cropParamsKeys) ? $this->getCrop($params) : [];

        $params = Arr::except($params, $this->cropParamsKeys);

        return $this->getUrl($id, array_replace($defaultParams, $params + $cropParams));
    }

    /**
     * @param string $id
     * @return string
     */
    public function getRawUrl($id)
    {
        if ($this->urlBuilder === null) {
            return '';
        }

        return $this->urlBuilder->createURL($id);
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function getDimensions($id)
    {
        if ($this->urlBuilder === null) {
            return ['width' => 0, 'height' => 0];
        }

        $url = $this->urlBuilder->createURL($id, ['fm' => 'json']);

        try {
            $imageMetadata = json_decode(file_get_contents($url), true);

            return [
                'width' => $imageMetadata['PixelWidth'],
                'height' => $imageMetadata['PixelHeight'],
            ];
        } catch (\Exception $e) {
            try {
                [$width, $height] = getimagesize($url);

                return [
                    'width' => $width,
                    'height' => $height,
                ];
            } catch (\Exception $e) {
                return [
                    'width' => 0,
                    'height' => 0,
                ];
            }
        }
    }

    /**
     * @param array $crop_params
     * @return array
     */
    protected function getCrop($crop_params)
    {
        if (! empty($crop_params)) {
            return ['rect' => $crop_params['crop_x'] . ',' .
                $crop_params['crop_y'] . ',' .
                $crop_params['crop_w'] . ',' .
                $crop_params['crop_h'],
            ];
        }

        return [];
    }

    /**
     * @param array $crop_params
     * @param int $width
     * @param int $height
     * @return array
     */
    protected function getFocalPointCrop($crop_params, $width, $height)
    {
        if (! empty($crop_params)) {
            // determine center coordinates of user crop and express it in term of original image width and height percentage
            $fpX = ($crop_params['crop_w'] / 2 + $crop_params['crop_x']) / $width;
            $fpY = ($crop_params['crop_h'] / 2 + $crop_params['crop_y']) / $height;

            // determine focal zoom
            if ($crop_params['crop_w'] > $crop_params['crop_h']) {
                $fpZ = $width / $crop_params['crop_w'];
            } else {
                $fpZ = $height / $crop_params['crop_h'];
            }

            $params = ['fp-x' => $fpX, 'fp-y' => $fpY, 'fp-z' => $fpZ];

            return array_map(function ($param) {
                return number_format($param, 4, '.', '');
            }, $params) + ['crop' => 'focalpoint', 'fit' => 'crop'];
        }

        return [];
    }
}
