<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Uploader;

use Illuminate\Config\Repository as Config;
use Illuminate\Http\Request;
use MicrosoftAzure\Storage\Blob\BlobSharedAccessSignatureHelper;
use Unusualify\Modularous\Services\Uploader\SignAzureUpload;
use Unusualify\Modularous\Services\Uploader\SignUploadListener;
use Unusualify\Modularous\Tests\TestCase;

class SignAzureUploadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! function_exists('azureEndpoint')) {
            eval('function azureEndpoint($disk) { return "https://account.blob.core.windows.net/container"; }');
        }
    }

    public function test_get_sas_url_returns_signed_url_for_put_requests(): void
    {
        if (! class_exists(BlobSharedAccessSignatureHelper::class)) {
            $this->markTestSkipped('Azure storage SDK is not installed.');
        }

        $config = new Config([
            'filesystems' => [
                'disks' => [
                    'libraries' => [
                        'name' => 'account',
                        'key' => base64_encode(random_bytes(32)),
                        'container' => '/container',
                    ],
                ],
            ],
        ]);

        $listener = new class implements SignUploadListener
        {
            public mixed $result = null;

            public function uploadIsSigned($signature, $isJsonResponse = true)
            {
                $this->result = $signature;

                return ['signed' => true, 'url' => $signature];
            }

            public function uploadIsNotValid()
            {
                return ['signed' => false];
            }
        };

        $request = Request::create('/upload/sign', 'POST', [
            'bloburi' => 'https://account.blob.core.windows.net/container/file.jpg',
            '_method' => 'put',
        ]);

        $service = new SignAzureUpload($config);
        $response = $service->getSasUrl($request, $listener, 'libraries');

        $this->assertSame(['signed' => true, 'url' => $listener->result], $response);
        $this->assertIsString($listener->result);
        $this->assertStringContainsString('?', $listener->result);
    }

    public function test_get_sas_url_returns_invalid_when_request_is_incomplete(): void
    {
        if (! class_exists(BlobSharedAccessSignatureHelper::class)) {
            $this->markTestSkipped('Azure storage SDK is not installed.');
        }

        $config = new Config([
            'filesystems' => [
                'disks' => [
                    'libraries' => [
                        'name' => 'account',
                        'key' => 'invalid-key',
                        'container' => '/container',
                    ],
                ],
            ],
        ]);

        $listener = new class implements SignUploadListener
        {
            public function uploadIsSigned($signature, $isJsonResponse = true)
            {
                return ['signed' => true];
            }

            public function uploadIsNotValid()
            {
                return ['signed' => false];
            }
        };

        $request = Request::create('/upload/sign', 'POST', []);

        $service = new SignAzureUpload($config);
        $result = $service->getSasUrl($request, $listener, 'libraries');

        $this->assertSame(['signed' => false], $result);
    }
}
