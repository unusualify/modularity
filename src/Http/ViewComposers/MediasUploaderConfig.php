<?php

namespace Unusualify\Modularity\Http\ViewComposers;

use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Session\Store as SessionStore;
use Illuminate\Support\Facades\Route;

class MediasUploaderConfig
{
    /**
     * @var UrlGenerator
     */
    protected $urlGenerator;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var SessionStore
     */
    protected $sessionStore;

    public function __construct(UrlGenerator $urlGenerator, Config $config, SessionStore $sessionStore)
    {
        $this->urlGenerator = $urlGenerator;
        $this->config = $config;
        $this->sessionStore = $sessionStore;
    }

    /**
     * Binds data to the view.
     *
     * @return void
     */
    public function compose(View $view)
    {
        $libraryDisk = $this->config->get(modularityBaseKey() . '.media_library.disk');
        $endpointType = $this->config->get(modularityBaseKey() . '.media_library.endpoint_type');
        $allowedExtensions = $this->config->get(modularityBaseKey() . '.media_library.allowed_extensions');

        // anonymous functions are used to let configuration dictate
        // the execution of the appropriate  implementation
        $endpointByType = [
            'local' => function () {
                return $this->urlGenerator->route(Route::hasAdmin('media-library.media.store'));
            },
            's3' => function () use ($libraryDisk) {
                return s3Endpoint($libraryDisk);
            },
            'azure' => function () use ($libraryDisk) {
                return azureEndpoint($libraryDisk);
            },
        ];

        $signatureEndpointByType = [
            'local' => null,
            's3' => $this->urlGenerator->route(Route::hasAdmin('media-library.sign-s3-upload')),
            'azure' => $this->urlGenerator->route(Route::hasAdmin('media-library.sign-azure-upload')),
        ];

        $mediasUploaderConfig = [
            'endpointType' => $endpointType,
            'endpoint' => $endpointByType[$endpointType](),
            'successEndpoint' => $this->urlGenerator->route(Route::hasAdmin('media-library.media.store')),
            'signatureEndpoint' => $signatureEndpointByType[$endpointType],
            'endpointBucket' => $this->config->get('filesystems.disks.' . $libraryDisk . '.bucket', 'none'),
            'endpointRegion' => $this->config->get('filesystems.disks.' . $libraryDisk . '.region', 'none'),
            'endpointRoot' => $endpointType === 'local' ? '' : $this->config->get('filesystems.disks.' . $libraryDisk . '.root', ''),
            'accessKey' => $this->config->get('filesystems.disks.' . $libraryDisk . '.key', 'none'),
            'csrfToken' => $this->sessionStore->token(),
            'acl' => $this->config->get(modularityBaseKey() . '.media_library.acl'),
            'filesizeLimit' => $this->config->get(modularityBaseKey() . '.media_library.filesize_limit'),
            'allowedExtensions' => $allowedExtensions,
        ];

        $view->with(compact('mediasUploaderConfig'));
    }
}
