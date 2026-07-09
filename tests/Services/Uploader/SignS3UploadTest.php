<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Uploader;

use Illuminate\Config\Repository as Config;
use Unusualify\Modularous\Services\Uploader\SignS3Upload;
use Unusualify\Modularous\Services\Uploader\SignUploadListener;
use Unusualify\Modularous\Tests\TestCase;

class SignS3UploadTest extends TestCase
{
    public function test_from_policy_signs_valid_bucket_policy(): void
    {
        $config = new Config([
            'filesystems' => [
                'disks' => [
                    'libraries' => [
                        'bucket' => 'test-bucket',
                        'secret' => 'test-secret-key',
                    ],
                ],
            ],
        ]);

        $policy = json_encode([
            'conditions' => [
                ['bucket' => 'test-bucket'],
                ['content-length-range', 0, ''],
                ['x-amz-credential' => 'AKIATEST/20260709/us-east-1/s3/aws4_request'],
            ],
        ], JSON_THROW_ON_ERROR);

        $listener = new class implements SignUploadListener
        {
            public ?array $signature = null;

            public function uploadIsSigned($signature, $isJsonResponse = true)
            {
                $this->signature = $signature;

                return ['signed' => true];
            }

            public function uploadIsNotValid()
            {
                return ['signed' => false];
            }
        };

        $service = new SignS3Upload($config);
        $result = $service->fromPolicy($policy, $listener, 'libraries');

        $this->assertSame(['signed' => true], $result);
        $this->assertIsArray($listener->signature);
        $this->assertArrayHasKey('policy', $listener->signature);
        $this->assertArrayHasKey('signature', $listener->signature);
        $this->assertNotEmpty($listener->signature['signature']);
    }

    public function test_from_policy_rejects_mismatched_bucket(): void
    {
        $config = new Config([
            'filesystems' => [
                'disks' => [
                    'libraries' => [
                        'bucket' => 'expected-bucket',
                        'secret' => 'test-secret-key',
                    ],
                ],
            ],
        ]);

        $policy = json_encode([
            'conditions' => [
                ['bucket' => 'other-bucket'],
                ['content-length-range', 0, ''],
            ],
        ], JSON_THROW_ON_ERROR);

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

        $service = new SignS3Upload($config);
        $result = $service->fromPolicy($policy, $listener, 'libraries');

        $this->assertSame(['signed' => false], $result);
    }
}
