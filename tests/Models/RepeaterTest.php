<?php

namespace Unusualify\Modularity\Tests\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Unusualify\Modularity\Entities\Repeater;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Tests\ModelTestCase;

class RepeaterTest extends ModelTestCase
{
    use RefreshDatabase;

    public function test_get_table_repeater()
    {
        $repeater = new Repeater;
        $this->assertEquals(modularityConfig('tables.repeaters', 'repeaters'), $repeater->getTable());
    }

    public function test_fillable_attributes()
    {
        $expectedFillable = [
            'repeatable_id',
            'content',
            'repeatable_type',
            'role',
            'locale',
        ];

        $repeater = new Repeater;
        $this->assertEquals($expectedFillable, $repeater->getFillable());
    }

    public function test_casts()
    {
        $expectedCasts = [
            'content' => 'array',
        ];

        $repeater = new Repeater;
        $casts = $repeater->getCasts();

        foreach ($expectedCasts as $key => $value) {
            $this->assertArrayHasKey($key, $casts);
            $this->assertEquals($value, $casts[$key]);
        }
    }

    public function test_create_repeater()
    {
        $user = User::factory()->create();
        $content = [
            'title' => 'Test Title',
            'description' => 'Test Description',
            'items' => ['item1', 'item2', 'item3']
        ];

        $repeater = Repeater::create([
            'repeatable_id' => $user->id,
            'repeatable_type' => get_class($user),
            'content' => $content,
            'role' => 'testimonials',
            'locale' => 'en',
        ]);

        $this->assertEquals($user->id, $repeater->repeatable_id);
        $this->assertEquals(get_class($user), $repeater->repeatable_type);
        $this->assertEquals($content, $repeater->content);
        $this->assertEquals('testimonials', $repeater->role);
        $this->assertEquals('en', $repeater->locale);
    }

    public function test_update_repeater()
    {
        $user = User::factory()->create();
        $initialContent = ['title' => 'Initial Title'];
        $updatedContent = ['title' => 'Updated Title', 'subtitle' => 'New Subtitle'];

        $repeater = Repeater::create([
            'repeatable_id' => $user->id,
            'repeatable_type' => get_class($user),
            'content' => $initialContent,
            'role' => 'features',
            'locale' => 'en',
        ]);

        $repeater->update([
            'content' => $updatedContent,
            'role' => 'benefits',
            'locale' => 'tr',
        ]);

        $this->assertEquals($updatedContent, $repeater->content);
        $this->assertEquals('benefits', $repeater->role);
        $this->assertEquals('tr', $repeater->locale);
    }

    public function test_delete_repeater()
    {
        $user = User::factory()->create();

        $repeater1 = Repeater::create([
            'repeatable_id' => $user->id,
            'repeatable_type' => get_class($user),
            'content' => ['title' => 'Repeater 1'],
            'role' => 'features',
            'locale' => 'en',
        ]);

        $repeater2 = Repeater::create([
            'repeatable_id' => $user->id,
            'repeatable_type' => get_class($user),
            'content' => ['title' => 'Repeater 2'],
            'role' => 'testimonials',
            'locale' => 'en',
        ]);

        $this->assertCount(2, Repeater::all());

        $repeater2->delete();

        $this->assertFalse(Repeater::all()->contains('id', $repeater2->id));
        $this->assertTrue(Repeater::all()->contains('id', $repeater1->id));
        $this->assertCount(1, Repeater::all());
    }

    public function test_extends_base_model()
    {
        $repeater = new Repeater;
        $this->assertInstanceOf(\Unusualify\Modularity\Entities\Model::class, $repeater);
    }

    public function test_has_timestamps()
    {
        $user = User::factory()->create();

        $repeater = Repeater::create([
            'repeatable_id' => $user->id,
            'repeatable_type' => get_class($user),
            'content' => ['title' => 'Timestamp Test'],
            'role' => 'test',
            'locale' => 'en',
        ]);

        $this->assertTrue($repeater->timestamps);
        $this->assertNotNull($repeater->created_at);
        $this->assertNotNull($repeater->updated_at);
    }

    public function test_content_array_casting()
    {
        $user = User::factory()->create();
        $content = [
            'title' => 'Test Title',
            'items' => [
                ['name' => 'Item 1', 'value' => 100],
                ['name' => 'Item 2', 'value' => 200],
            ],
            'settings' => [
                'display' => true,
                'color' => '#FF5722',
                'count' => 5
            ]
        ];

        $repeater = Repeater::create([
            'repeatable_id' => $user->id,
            'repeatable_type' => get_class($user),
            'content' => $content,
            'role' => 'complex_data',
            'locale' => 'en',
        ]);

        // Test that content is properly cast to array
        $this->assertIsArray($repeater->content);
        $this->assertEquals($content, $repeater->content);
        $this->assertEquals('Test Title', $repeater->content['title']);
        $this->assertCount(2, $repeater->content['items']);
        $this->assertEquals(100, $repeater->content['items'][0]['value']);
        $this->assertTrue($repeater->content['settings']['display']);
    }

    public function test_polymorphic_repeatable_relationship()
    {
        $user = User::factory()->create();

        $repeater = Repeater::create([
            'repeatable_id' => $user->id,
            'repeatable_type' => get_class($user),
            'content' => ['title' => 'Polymorphic Test'],
            'role' => 'features',
            'locale' => 'en',
        ]);

        // Test polymorphic type storage
        $this->assertEquals(get_class($user), $repeater->repeatable_type);
        $this->assertEquals($user->id, $repeater->repeatable_id);

        // Test querying by polymorphic type
        $userRepeaters = Repeater::where('repeatable_type', get_class($user))->get();
        $this->assertTrue($userRepeaters->contains('id', $repeater->id));
    }

    public function test_multiple_repeaters_for_same_model()
    {
        $user = User::factory()->create();

        $featuresRepeater = Repeater::create([
            'repeatable_id' => $user->id,
            'repeatable_type' => get_class($user),
            'content' => [
                'items' => [
                    ['title' => 'Feature 1', 'description' => 'Description 1'],
                    ['title' => 'Feature 2', 'description' => 'Description 2'],
                ]
            ],
            'role' => 'features',
            'locale' => 'en',
        ]);

        $testimonialsRepeater = Repeater::create([
            'repeatable_id' => $user->id,
            'repeatable_type' => get_class($user),
            'content' => [
                'items' => [
                    ['name' => 'John Doe', 'quote' => 'Great service!'],
                    ['name' => 'Jane Smith', 'quote' => 'Excellent quality!'],
                ]
            ],
            'role' => 'testimonials',
            'locale' => 'en',
        ]);

        $userRepeaters = Repeater::where('repeatable_id', $user->id)->get();

        $this->assertCount(2, $userRepeaters);
        $this->assertNotEquals($featuresRepeater->role, $testimonialsRepeater->role);
        $this->assertNotEquals($featuresRepeater->content, $testimonialsRepeater->content);
    }

    public function test_repeater_roles()
    {
        $user = User::factory()->create();

        $roles = ['features', 'testimonials', 'gallery', 'faq', 'team_members'];

        $repeaters = [];
        foreach ($roles as $role) {
            $repeaters[] = Repeater::create([
                'repeatable_id' => $user->id,
                'repeatable_type' => get_class($user),
                'content' => ['title' => ucfirst($role) . ' Content'],
                'role' => $role,
                'locale' => 'en',
            ]);
        }

        $this->assertCount(5, $repeaters);

        // Test querying by role
        foreach ($roles as $role) {
            $roleRepeaters = Repeater::where('role', $role)->get();
            $this->assertCount(1, $roleRepeaters);
            $this->assertEquals($role, $roleRepeaters->first()->role);
        }
    }

    public function test_repeater_locales()
    {
        $user = User::factory()->create();
        $content = ['title' => 'Multilingual Content'];

        $locales = ['en', 'tr', 'fr', 'de', 'es'];

        $repeaters = [];
        foreach ($locales as $locale) {
            $repeaters[] = Repeater::create([
                'repeatable_id' => $user->id,
                'repeatable_type' => get_class($user),
                'content' => array_merge($content, ['locale_specific' => "Content for {$locale}"]),
                'role' => 'features',
                'locale' => $locale,
            ]);
        }

        $this->assertCount(5, $repeaters);

        // Test querying by locale
        foreach ($locales as $locale) {
            $localeRepeaters = Repeater::where('locale', $locale)->get();
            $this->assertCount(1, $localeRepeaters);
            $this->assertEquals($locale, $localeRepeaters->first()->locale);
            $this->assertEquals("Content for {$locale}", $localeRepeaters->first()->content['locale_specific']);
        }
    }

    public function test_repeater_complex_content_structures()
    {
        $user = User::factory()->create();

        // Test different content structures for different roles
        $featuresContent = [
            'items' => [
                [
                    'icon' => 'mdi-rocket',
                    'title' => 'Fast Performance',
                    'description' => 'Lightning fast loading times',
                    'benefits' => ['Speed', 'Efficiency', 'User Experience']
                ],
                [
                    'icon' => 'mdi-shield',
                    'title' => 'Secure',
                    'description' => 'Enterprise-grade security',
                    'benefits' => ['Encryption', 'Privacy', 'Compliance']
                ]
            ],
            'settings' => [
                'display_icons' => true,
                'layout' => 'grid',
                'columns' => 3
            ]
        ];

        $testimonialsContent = [
            'items' => [
                [
                    'name' => 'John Doe',
                    'position' => 'CEO',
                    'company' => 'Tech Corp',
                    'quote' => 'Amazing product that transformed our business',
                    'rating' => 5,
                    'avatar' => 'john-doe.jpg'
                ],
                [
                    'name' => 'Jane Smith',
                    'position' => 'CTO',
                    'company' => 'Innovation Ltd',
                    'quote' => 'Excellent support and great features',
                    'rating' => 5,
                    'avatar' => 'jane-smith.jpg'
                ]
            ],
            'settings' => [
                'show_ratings' => true,
                'show_avatars' => true,
                'autoplay' => false
            ]
        ];

        $featuresRepeater = Repeater::create([
            'repeatable_id' => $user->id,
            'repeatable_type' => get_class($user),
            'content' => $featuresContent,
            'role' => 'features',
            'locale' => 'en',
        ]);

        $testimonialsRepeater = Repeater::create([
            'repeatable_id' => $user->id,
            'repeatable_type' => get_class($user),
            'content' => $testimonialsContent,
            'role' => 'testimonials',
            'locale' => 'en',
        ]);

        // Test features content structure
        $this->assertCount(2, $featuresRepeater->content['items']);
        $this->assertEquals('mdi-rocket', $featuresRepeater->content['items'][0]['icon']);
        $this->assertCount(3, $featuresRepeater->content['items'][0]['benefits']);
        $this->assertTrue($featuresRepeater->content['settings']['display_icons']);

        // Test testimonials content structure
        $this->assertCount(2, $testimonialsRepeater->content['items']);
        $this->assertEquals('CEO', $testimonialsRepeater->content['items'][0]['position']);
        $this->assertEquals(5, $testimonialsRepeater->content['items'][0]['rating']);
        $this->assertTrue($testimonialsRepeater->content['settings']['show_ratings']);
    }

    public function test_repeater_query_scopes_simulation()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create repeaters for different users and roles
        Repeater::create([
            'repeatable_id' => $user1->id,
            'repeatable_type' => get_class($user1),
            'content' => ['title' => 'User 1 Features'],
            'role' => 'features',
            'locale' => 'en',
        ]);

        Repeater::create([
            'repeatable_id' => $user1->id,
            'repeatable_type' => get_class($user1),
            'content' => ['title' => 'User 1 Testimonials'],
            'role' => 'testimonials',
            'locale' => 'en',
        ]);

        Repeater::create([
            'repeatable_id' => $user2->id,
            'repeatable_type' => get_class($user2),
            'content' => ['title' => 'User 2 Features'],
            'role' => 'features',
            'locale' => 'tr',
        ]);

        // Test querying repeaters by model
        $user1Repeaters = Repeater::where('repeatable_id', $user1->id)->get();
        $this->assertCount(2, $user1Repeaters);

        // Test querying repeaters by role
        $featuresRepeaters = Repeater::where('role', 'features')->get();
        $this->assertCount(2, $featuresRepeaters);

        // Test querying repeaters by locale
        $enRepeaters = Repeater::where('locale', 'en')->get();
        $this->assertCount(2, $enRepeaters);

        // Test complex query
        $user1FeaturesEn = Repeater::where('repeatable_id', $user1->id)
            ->where('role', 'features')
            ->where('locale', 'en')
            ->first();

        $this->assertNotNull($user1FeaturesEn);
        $this->assertEquals('User 1 Features', $user1FeaturesEn->content['title']);
    }

    public function test_repeater_content_modification()
    {
        $user = User::factory()->create();

        $repeater = Repeater::create([
            'repeatable_id' => $user->id,
            'repeatable_type' => get_class($user),
            'content' => [
                'items' => [
                    ['title' => 'Item 1', 'active' => true],
                    ['title' => 'Item 2', 'active' => false],
                ]
            ],
            'role' => 'features',
            'locale' => 'en',
        ]);

        // Test modifying content
        $content = $repeater->content;
        $content['items'][1]['active'] = true;
        $content['items'][] = ['title' => 'Item 3', 'active' => true];
        $content['settings'] = ['show_all' => true];

        $repeater->update(['content' => $content]);

        $this->assertCount(3, $repeater->content['items']);
        $this->assertTrue($repeater->content['items'][1]['active']);
        $this->assertEquals('Item 3', $repeater->content['items'][2]['title']);
        $this->assertTrue($repeater->content['settings']['show_all']);
    }


    public function test_repeater_json_serialization()
    {
        $user = User::factory()->create();
        $content = [
            'title' => 'JSON Test',
            'items' => [
                ['name' => 'Item 1', 'value' => 100],
                ['name' => 'Item 2', 'value' => 200],
            ],
            'metadata' => [
                'created_by' => 'system',
                'version' => '1.0',
                'tags' => ['important', 'featured']
            ]
        ];

        $repeater = Repeater::create([
            'repeatable_id' => $user->id,
            'repeatable_type' => get_class($user),
            'content' => $content,
            'role' => 'json_test',
            'locale' => 'en',
        ]);

        // Test JSON serialization
        $repeaterArray = $repeater->toArray();
        $this->assertIsArray($repeaterArray['content']);
        $this->assertEquals($content, $repeaterArray['content']);

        // Test JSON string conversion
        $jsonString = $repeater->toJson();
        $this->assertIsString($jsonString);

        $decodedJson = json_decode($jsonString, true);
        $this->assertEquals($content, $decodedJson['content']);
    }
}
