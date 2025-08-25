<?php

namespace Unusualify\Modularity\Tests\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Unusualify\Modularity\Entities\Singleton;
use Unusualify\Modularity\Tests\ModelTestCase;

class SingletonTest extends ModelTestCase
{
    use RefreshDatabase;

    public function test_get_table_singleton()
    {
        $singleton = new Singleton;
        $this->assertEquals(\Unusualify\Modularity\Facades\Modularity::config('tables.singletons', 'modularity_singletons'), $singleton->getTable());
    }

    public function test_fillable_attributes()
    {
        $expectedFillable = [
            'id',
            'singleton_type',
            'content',
        ];

        $singleton = new Singleton;
        $this->assertEquals($expectedFillable, $singleton->getFillable());
    }

    public function test_casts()
    {
        $expectedCasts = [
            'content' => 'array',
        ];

        $singleton = new Singleton;
        $casts = $singleton->getCasts();

        foreach ($expectedCasts as $key => $value) {
            $this->assertArrayHasKey($key, $casts);
            $this->assertEquals($value, $casts[$key]);
        }
    }

    public function test_create_singleton()
    {
        $content = [
            'site_name' => 'My Website',
            'tagline' => 'Welcome to our site',
            'settings' => [
                'maintenance_mode' => false,
                'max_users' => 1000
            ]
        ];

        $singleton = Singleton::create([
            'singleton_type' => 'site_settings',
            'content' => $content,
        ]);

        $this->assertEquals('site_settings', $singleton->singleton_type);
        $this->assertEquals($content, $singleton->content);
    }

    public function test_update_singleton()
    {
        $initialContent = [
            'site_name' => 'Initial Site',
            'theme' => 'light'
        ];

        $updatedContent = [
            'site_name' => 'Updated Site',
            'theme' => 'dark',
            'features' => ['feature1', 'feature2']
        ];

        $singleton = Singleton::create([
            'singleton_type' => 'app_config',
            'content' => $initialContent,
        ]);

        $singleton->update([
            'singleton_type' => 'application_config',
            'content' => $updatedContent,
        ]);

        $this->assertEquals('application_config', $singleton->singleton_type);
        $this->assertEquals($updatedContent, $singleton->content);
    }

    public function test_delete_singleton()
    {
        $singleton1 = Singleton::create([
            'singleton_type' => 'config_1',
            'content' => ['setting' => 'value1'],
        ]);

        $singleton2 = Singleton::create([
            'singleton_type' => 'config_2',
            'content' => ['setting' => 'value2'],
        ]);

        $this->assertCount(2, Singleton::all());

        $singleton2->delete();

        $this->assertFalse(Singleton::all()->contains('id', $singleton2->id));
        $this->assertTrue(Singleton::all()->contains('id', $singleton1->id));
        $this->assertCount(1, Singleton::all());
    }

    public function test_extends_base_model()
    {
        $singleton = new Singleton;
        $this->assertInstanceOf(\Unusualify\Modularity\Entities\Model::class, $singleton);
    }

    public function test_has_timestamps()
    {
        $singleton = Singleton::create([
            'singleton_type' => 'timestamp_test',
            'content' => ['test' => 'value'],
        ]);

        $this->assertTrue($singleton->timestamps);
        $this->assertNotNull($singleton->created_at);
        $this->assertNotNull($singleton->updated_at);
    }

    public function test_content_array_casting()
    {
        $content = [
            'site_config' => [
                'name' => 'My Application',
                'version' => '1.0.0',
                'features' => [
                    'user_management' => true,
                    'notifications' => false,
                    'analytics' => true
                ]
            ],
            'ui_settings' => [
                'theme' => 'dark',
                'language' => 'en',
                'timezone' => 'UTC'
            ],
            'integrations' => [
                'payment' => ['stripe', 'paypal'],
                'email' => 'mailgun',
                'storage' => 's3'
            ]
        ];

        $singleton = Singleton::create([
            'singleton_type' => 'app_settings',
            'content' => $content,
        ]);

        // Test that content is properly cast to array
        $this->assertIsArray($singleton->content);
        $this->assertEquals($content, $singleton->content);
        $this->assertEquals('My Application', $singleton->content['site_config']['name']);
        $this->assertTrue($singleton->content['site_config']['features']['user_management']);
        $this->assertEquals('dark', $singleton->content['ui_settings']['theme']);
        $this->assertContains('stripe', $singleton->content['integrations']['payment']);
    }

    public function test_singleton_types()
    {
        $singletonTypes = [
            'site_settings' => ['site_name' => 'Website', 'logo' => 'logo.png'],
            'api_config' => ['base_url' => 'https://api.example.com', 'timeout' => 30],
            'email_settings' => ['smtp_host' => 'mail.example.com', 'port' => 587],
            'seo_config' => ['meta_title' => 'SEO Title', 'meta_description' => 'SEO Description'],
            'social_media' => ['facebook' => 'fb_page', 'twitter' => 'twitter_handle']
        ];

        $singletons = [];
        foreach ($singletonTypes as $type => $content) {
            $singletons[] = Singleton::create([
                'singleton_type' => $type,
                'content' => $content,
            ]);
        }

        $this->assertCount(5, $singletons);

        // Test querying by singleton type
        foreach ($singletonTypes as $type => $expectedContent) {
            $typeSingletons = Singleton::where('singleton_type', $type)->get();
            $this->assertCount(1, $typeSingletons);
            $this->assertEquals($type, $typeSingletons->first()->singleton_type);
            $this->assertEquals($expectedContent, $typeSingletons->first()->content);
        }
    }

    public function test_singleton_pattern_simulation()
    {
        // Test that typically only one record exists per singleton type
        $type = 'global_settings';

        $singleton1 = Singleton::create([
            'singleton_type' => $type,
            'content' => ['version' => '1.0.0'],
        ]);

        sleep(1);
        // In a real singleton pattern, this might be prevented, but the model allows it
        $singleton2 = Singleton::create([
            'singleton_type' => $type,
            'content' => ['version' => '2.0.0'],
        ]);

        $typeSingletons = Singleton::where('singleton_type', $type)->get();
        $this->assertCount(2, $typeSingletons);

        // Test getting the latest singleton for a type
        $latestSingleton = Singleton::where('singleton_type', $type)
            ->orderBy('created_at', 'desc')
            ->first();

        $this->assertEquals($singleton2->id, $latestSingleton->id);
        $this->assertEquals('2.0.0', $latestSingleton->content['version']);
    }

    public function test_singleton_complex_content_structures()
    {
        $complexContent = [
            'application' => [
                'name' => 'My CMS',
                'version' => '2.1.0',
                'environment' => 'production',
                'debug' => false
            ],
            'database' => [
                'connections' => [
                    'mysql' => [
                        'host' => 'localhost',
                        'port' => 3306,
                        'database' => 'cms_db'
                    ],
                    'redis' => [
                        'host' => 'localhost',
                        'port' => 6379,
                        'database' => 0
                    ]
                ]
            ],
            'services' => [
                'mail' => [
                    'driver' => 'smtp',
                    'host' => 'smtp.example.com',
                    'port' => 587,
                    'encryption' => 'tls'
                ],
                'queue' => [
                    'driver' => 'redis',
                    'retry_after' => 90
                ]
            ],
            'features' => [
                'user_registration' => true,
                'email_verification' => true,
                'two_factor_auth' => false,
                'api_rate_limiting' => true
            ]
        ];

        $singleton = Singleton::create([
            'singleton_type' => 'system_configuration',
            'content' => $complexContent,
        ]);

        // Test accessing nested content
        $this->assertEquals('My CMS', $singleton->content['application']['name']);
        $this->assertEquals('production', $singleton->content['application']['environment']);
        $this->assertEquals(3306, $singleton->content['database']['connections']['mysql']['port']);
        $this->assertEquals('smtp', $singleton->content['services']['mail']['driver']);
        $this->assertTrue($singleton->content['features']['user_registration']);
        $this->assertFalse($singleton->content['features']['two_factor_auth']);
    }

    public function test_singleton_content_modification()
    {
        $singleton = Singleton::create([
            'singleton_type' => 'app_preferences',
            'content' => [
                'theme' => 'light',
                'notifications' => [
                    'email' => true,
                    'push' => false
                ],
                'features' => ['feature1', 'feature2']
            ],
        ]);

        // Test modifying content
        $content = $singleton->content;
        $content['theme'] = 'dark';
        $content['notifications']['push'] = true;
        $content['features'][] = 'feature3';
        $content['new_setting'] = 'new_value';

        $singleton->update(['content' => $content]);

        $this->assertEquals('dark', $singleton->content['theme']);
        $this->assertTrue($singleton->content['notifications']['push']);
        $this->assertCount(3, $singleton->content['features']);
        $this->assertContains('feature3', $singleton->content['features']);
        $this->assertEquals('new_value', $singleton->content['new_setting']);
    }

    public function test_singleton_query_operations()
    {
        // Create multiple singletons
        Singleton::create([
            'singleton_type' => 'site_config',
            'content' => ['maintenance' => false, 'version' => '1.0'],
        ]);

        Singleton::create([
            'singleton_type' => 'user_settings',
            'content' => ['default_role' => 'user', 'max_login_attempts' => 3],
        ]);

        Singleton::create([
            'singleton_type' => 'payment_config',
            'content' => ['currency' => 'USD', 'tax_rate' => 0.08],
        ]);

        // Test querying all singletons
        $allSingletons = Singleton::all();
        $this->assertCount(3, $allSingletons);

        // Test finding by type
        $siteConfig = Singleton::where('singleton_type', 'site_config')->first();
        $this->assertNotNull($siteConfig);
        $this->assertEquals('1.0', $siteConfig->content['version']);

        // Test finding by content (this would be database-specific)
        // In real scenarios, you might search JSON content differently
        $singletons = Singleton::all();
        $paymentSingleton = $singletons->first(function ($singleton) {
            return isset($singleton->content['currency']) && $singleton->content['currency'] === 'USD';
        });

        $this->assertNotNull($paymentSingleton);
        $this->assertEquals('payment_config', $paymentSingleton->singleton_type);
    }

    public function test_create_singleton_with_minimum_fields()
    {
        $singleton = Singleton::create([
            'singleton_type' => 'minimal_config',
            'content' => ['key' => 'value'],
        ]);

        $this->assertNotNull($singleton->id);
        $this->assertEquals('minimal_config', $singleton->singleton_type);
        $this->assertEquals(['key' => 'value'], $singleton->content);
    }

    public function test_singleton_json_serialization()
    {
        $content = [
            'app_name' => 'Test App',
            'settings' => [
                'debug' => true,
                'cache_enabled' => false,
                'features' => ['auth', 'api', 'admin']
            ],
            'metadata' => [
                'created_by' => 'system',
                'last_updated' => '2024-01-01',
                'version' => '1.0.0'
            ]
        ];

        $singleton = Singleton::create([
            'singleton_type' => 'json_test',
            'content' => $content,
        ]);

        // Test JSON serialization
        $singletonArray = $singleton->toArray();
        $this->assertIsArray($singletonArray['content']);
        $this->assertEquals($content, $singletonArray['content']);

        // Test JSON string conversion
        $jsonString = $singleton->toJson();
        $this->assertIsString($jsonString);

        $decodedJson = json_decode($jsonString, true);
        $this->assertEquals($content, $decodedJson['content']);
    }

    public function test_singleton_configuration_scenarios()
    {
        // Test different configuration scenarios
        $configurations = [
            'database_config' => [
                'default' => 'mysql',
                'connections' => [
                    'mysql' => ['host' => 'localhost', 'database' => 'app_db'],
                    'sqlite' => ['database' => 'storage/database.sqlite']
                ]
            ],
            'mail_config' => [
                'default' => 'smtp',
                'mailers' => [
                    'smtp' => ['transport' => 'smtp', 'host' => 'localhost'],
                    'log' => ['transport' => 'log']
                ]
            ],
            'cache_config' => [
                'default' => 'redis',
                'stores' => [
                    'redis' => ['driver' => 'redis', 'connection' => 'cache'],
                    'file' => ['driver' => 'file', 'path' => 'storage/cache']
                ]
            ]
        ];

        $singletons = [];
        foreach ($configurations as $type => $config) {
            $singletons[] = Singleton::create([
                'singleton_type' => $type,
                'content' => $config,
            ]);
        }

        $this->assertCount(3, $singletons);

        // Test that each configuration is stored correctly
        $dbConfig = Singleton::where('singleton_type', 'database_config')->first();
        $this->assertEquals('mysql', $dbConfig->content['default']);
        $this->assertEquals('app_db', $dbConfig->content['connections']['mysql']['database']);

        $mailConfig = Singleton::where('singleton_type', 'mail_config')->first();
        $this->assertEquals('smtp', $mailConfig->content['default']);
        $this->assertEquals('log', $mailConfig->content['mailers']['log']['transport']);
    }

    public function test_singleton_update_scenarios()
    {
        $singleton = Singleton::create([
            'singleton_type' => 'feature_flags',
            'content' => [
                'new_ui' => false,
                'beta_features' => false,
                'maintenance_mode' => false
            ],
        ]);

        // Test enabling features one by one
        $content = $singleton->content;
        $content['new_ui'] = true;
        $singleton->update(['content' => $content]);
        $this->assertTrue($singleton->content['new_ui']);

        $content = $singleton->content;
        $content['beta_features'] = true;
        $singleton->update(['content' => $content]);
        $this->assertTrue($singleton->content['beta_features']);

        // Test adding new feature flag
        $content = $singleton->content;
        $content['experimental_api'] = true;
        $singleton->update(['content' => $content]);
        $this->assertTrue($singleton->content['experimental_api']);
        $this->assertArrayHasKey('experimental_api', $singleton->content);
    }

    public function test_singleton_type_uniqueness_simulation()
    {
        // While the model doesn't enforce uniqueness, we can test the pattern
        $type = 'app_settings';

        $singleton1 = Singleton::create([
            'singleton_type' => $type,
            'content' => ['version' => '1.0'],
        ]);

        // Simulate updating instead of creating new
        $existingSingleton = Singleton::where('singleton_type', $type)->first();
        if ($existingSingleton) {
            $content = $existingSingleton->content;
            $content['version'] = '1.1';
            $existingSingleton->update(['content' => $content]);
        }

        $singletons = Singleton::where('singleton_type', $type)->get();
        $this->assertCount(1, $singletons);
        $this->assertEquals('1.1', $singletons->first()->content['version']);
    }
}
