<?php

namespace Unusualify\Modularity\Tests\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Unusualify\Modularity\Entities\Spread;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Tests\ModelTestCase;

class SpreadTest extends ModelTestCase
{
    use RefreshDatabase;

    public function test_get_table_spread()
    {
        $spread = new Spread;
        $this->assertEquals(modularityConfig('tables.spreads', 'modularity_spreads'), $spread->getTable());
    }

    public function test_fillable_attributes()
    {
        $expectedFillable = [
            'spreadable_id',
            'spreadable_type',
            'content',
        ];

        $spread = new Spread;
        $this->assertEquals($expectedFillable, $spread->getFillable());
    }

    public function test_casts()
    {
        $expectedCasts = [
            'content' => 'array',
        ];

        $spread = new Spread;
        $casts = $spread->getCasts();

        foreach ($expectedCasts as $key => $value) {
            $this->assertArrayHasKey($key, $casts);
            $this->assertEquals($value, $casts[$key]);
        }
    }

    public function test_create_spread()
    {
        $user = User::factory()->create();
        $content = [
            'title' => 'Welcome to Our Site',
            'subtitle' => 'Discover amazing features',
            'sections' => [
                ['type' => 'hero', 'data' => ['image' => 'hero.jpg', 'text' => 'Hero text']],
                ['type' => 'features', 'data' => ['items' => ['Feature 1', 'Feature 2']]],
            ],
        ];

        $spread = Spread::create([
            'spreadable_id' => $user->id,
            'spreadable_type' => get_class($user),
            'content' => $content,
        ]);

        $this->assertEquals($user->id, $spread->spreadable_id);
        $this->assertEquals(get_class($user), $spread->spreadable_type);
        $this->assertEquals($content, $spread->content);
    }

    public function test_update_spread()
    {
        $user = User::factory()->create();
        $initialContent = [
            'title' => 'Initial Title',
            'sections' => [['type' => 'intro', 'data' => ['text' => 'Initial text']]],
        ];

        $updatedContent = [
            'title' => 'Updated Title',
            'subtitle' => 'New Subtitle',
            'sections' => [
                ['type' => 'intro', 'data' => ['text' => 'Updated text']],
                ['type' => 'gallery', 'data' => ['images' => ['img1.jpg', 'img2.jpg']]],
            ],
        ];

        $spread = Spread::create([
            'spreadable_id' => $user->id,
            'spreadable_type' => get_class($user),
            'content' => $initialContent,
        ]);

        $newUser = User::factory()->create();
        $spread->update([
            'spreadable_id' => $newUser->id,
            'spreadable_type' => get_class($newUser),
            'content' => $updatedContent,
        ]);

        $this->assertEquals($newUser->id, $spread->spreadable_id);
        $this->assertEquals(get_class($newUser), $spread->spreadable_type);
        $this->assertEquals($updatedContent, $spread->content);
    }

    public function test_delete_spread()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $spread1 = Spread::create([
            'spreadable_id' => $user1->id,
            'spreadable_type' => get_class($user1),
            'content' => ['title' => 'Page 1'],
        ]);

        $spread2 = Spread::create([
            'spreadable_id' => $user2->id,
            'spreadable_type' => get_class($user2),
            'content' => ['title' => 'Page 2'],
        ]);

        $this->assertCount(2, Spread::all());

        $spread2->delete();

        $this->assertFalse(Spread::all()->contains('id', $spread2->id));
        $this->assertTrue(Spread::all()->contains('id', $spread1->id));
        $this->assertCount(1, Spread::all());
    }

    public function test_extends_base_model()
    {
        $spread = new Spread;
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Model::class, $spread);
    }

    public function test_has_timestamps()
    {
        $user = User::factory()->create();

        $spread = Spread::create([
            'spreadable_id' => $user->id,
            'spreadable_type' => get_class($user),
            'content' => ['title' => 'Timestamp Test'],
        ]);

        $this->assertTrue($spread->timestamps);
        $this->assertNotNull($spread->created_at);
        $this->assertNotNull($spread->updated_at);
    }

    public function test_spreadable_relationship()
    {
        $user = User::factory()->create();

        $spread = Spread::create([
            'spreadable_id' => $user->id,
            'spreadable_type' => get_class($user),
            'content' => ['title' => 'Relationship Test'],
        ]);

        $relation = $spread->spreadable();
        $this->assertInstanceOf(MorphTo::class, $relation);
        $this->assertInstanceOf(User::class, $spread->spreadable);
        $this->assertEquals($user->id, $spread->spreadable->id);
    }

    public function test_content_array_casting()
    {
        $user = User::factory()->create();
        $content = [
            'page_settings' => [
                'layout' => 'full-width',
                'theme' => 'dark',
                'animations' => true,
            ],
            'sections' => [
                [
                    'type' => 'hero',
                    'data' => [
                        'title' => 'Hero Title',
                        'subtitle' => 'Hero Subtitle',
                        'background' => 'hero-bg.jpg',
                        'buttons' => [
                            ['text' => 'Get Started', 'url' => '/start'],
                            ['text' => 'Learn More', 'url' => '/learn'],
                        ],
                    ],
                ],
                [
                    'type' => 'features',
                    'data' => [
                        'title' => 'Our Features',
                        'items' => [
                            ['icon' => 'mdi-rocket', 'title' => 'Fast', 'description' => 'Lightning fast'],
                            ['icon' => 'mdi-shield', 'title' => 'Secure', 'description' => 'Bank-level security'],
                        ],
                    ],
                ],
            ],
            'meta' => [
                'seo_title' => 'Page Title',
                'seo_description' => 'Page Description',
                'og_image' => 'og-image.jpg',
            ],
        ];

        $spread = Spread::create([
            'spreadable_id' => $user->id,
            'spreadable_type' => get_class($user),
            'content' => $content,
        ]);

        // Test that content is properly cast to array
        $this->assertIsArray($spread->content);
        $this->assertEquals($content, $spread->content);
        $this->assertEquals('full-width', $spread->content['page_settings']['layout']);
        $this->assertTrue($spread->content['page_settings']['animations']);
        $this->assertEquals('Hero Title', $spread->content['sections'][0]['data']['title']);
        $this->assertCount(2, $spread->content['sections'][0]['data']['buttons']);
        $this->assertEquals('Fast', $spread->content['sections'][1]['data']['items'][0]['title']);
    }

    public function test_polymorphic_spreadable_relationship()
    {
        $user = User::factory()->create();

        $spread = Spread::create([
            'spreadable_id' => $user->id,
            'spreadable_type' => get_class($user),
            'content' => ['title' => 'Polymorphic Test'],
        ]);

        // Test polymorphic type storage
        $this->assertEquals(get_class($user), $spread->spreadable_type);
        $this->assertEquals($user->id, $spread->spreadable_id);

        // Test querying by polymorphic type
        $userSpreads = Spread::where('spreadable_type', get_class($user))->get();
        $this->assertTrue($userSpreads->contains('id', $spread->id));
    }

    public function test_multiple_spreads_for_same_model()
    {
        $user = User::factory()->create();

        $homeSpread = Spread::create([
            'spreadable_id' => $user->id,
            'spreadable_type' => get_class($user),
            'content' => [
                'page_type' => 'home',
                'sections' => [
                    ['type' => 'hero', 'data' => ['title' => 'Welcome Home']],
                    ['type' => 'features', 'data' => ['items' => ['Feature 1', 'Feature 2']]],
                ],
            ],
        ]);

        $aboutSpread = Spread::create([
            'spreadable_id' => $user->id,
            'spreadable_type' => get_class($user),
            'content' => [
                'page_type' => 'about',
                'sections' => [
                    ['type' => 'story', 'data' => ['title' => 'Our Story']],
                    ['type' => 'team', 'data' => ['members' => ['John', 'Jane']]],
                ],
            ],
        ]);

        $userSpreads = Spread::where('spreadable_id', $user->id)->get();

        $this->assertCount(2, $userSpreads);
        $this->assertNotEquals($homeSpread->content['page_type'], $aboutSpread->content['page_type']);
        $this->assertNotEquals($homeSpread->content['sections'], $aboutSpread->content['sections']);
    }

    public function test_spread_complex_content_structures()
    {
        $user = User::factory()->create();
        $landingPageContent = [
            'settings' => [
                'layout' => 'landing',
                'full_width' => true,
                'sticky_header' => true,
            ],
            'sections' => [
                [
                    'type' => 'hero',
                    'order' => 1,
                    'data' => [
                        'title' => 'Transform Your Business',
                        'subtitle' => 'With our innovative solutions',
                        'cta_text' => 'Get Started Today',
                        'cta_url' => '/signup',
                        'background_video' => 'hero-video.mp4',
                        'features' => [
                            'Easy to use',
                            'Secure & reliable',
                            '24/7 support',
                        ],
                    ],
                ],
                [
                    'type' => 'testimonials',
                    'order' => 2,
                    'data' => [
                        'title' => 'What Our Clients Say',
                        'items' => [
                            [
                                'name' => 'John Smith',
                                'company' => 'Tech Corp',
                                'quote' => 'Amazing service and support!',
                                'rating' => 5,
                                'avatar' => 'john.jpg',
                            ],
                            [
                                'name' => 'Jane Doe',
                                'company' => 'Innovation Inc',
                                'quote' => 'Transformed our workflow completely.',
                                'rating' => 5,
                                'avatar' => 'jane.jpg',
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'pricing',
                    'order' => 3,
                    'data' => [
                        'title' => 'Choose Your Plan',
                        'plans' => [
                            [
                                'name' => 'Starter',
                                'price' => 29,
                                'features' => ['Feature 1', 'Feature 2'],
                                'popular' => false,
                            ],
                            [
                                'name' => 'Pro',
                                'price' => 99,
                                'features' => ['All Starter features', 'Feature 3', 'Feature 4'],
                                'popular' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $spread = Spread::create([
            'spreadable_id' => $user->id,
            'spreadable_type' => get_class($user),
            'content' => $landingPageContent,
        ]);

        // Test accessing complex nested content
        $this->assertEquals('landing', $spread->content['settings']['layout']);
        $this->assertTrue($spread->content['settings']['full_width']);
        $this->assertEquals('Transform Your Business', $spread->content['sections'][0]['data']['title']);
        $this->assertCount(3, $spread->content['sections'][0]['data']['features']);
        $this->assertEquals('John Smith', $spread->content['sections'][1]['data']['items'][0]['name']);
        $this->assertEquals(5, $spread->content['sections'][1]['data']['items'][0]['rating']);
        $this->assertTrue($spread->content['sections'][2]['data']['plans'][1]['popular']);
        $this->assertEquals(99, $spread->content['sections'][2]['data']['plans'][1]['price']);
    }

    public function test_spread_content_modification()
    {
        $user = User::factory()->create();

        $spread = Spread::create([
            'spreadable_id' => $user->id,
            'spreadable_type' => get_class($user),
            'content' => [
                'title' => 'Original Title',
                'sections' => [
                    ['type' => 'intro', 'data' => ['text' => 'Original text']],
                    ['type' => 'gallery', 'data' => ['images' => ['img1.jpg']]],
                ],
                'settings' => ['theme' => 'light'],
            ],
        ]);

        // Test modifying content
        $content = $spread->content;
        $content['title'] = 'Updated Title';
        $content['sections'][0]['data']['text'] = 'Updated text';
        $content['sections'][1]['data']['images'][] = 'img2.jpg';
        $content['sections'][] = ['type' => 'contact', 'data' => ['email' => 'contact@example.com']];
        $content['settings']['theme'] = 'dark';
        $content['settings']['animations'] = true;

        $spread->update(['content' => $content]);

        $this->assertEquals('Updated Title', $spread->content['title']);
        $this->assertEquals('Updated text', $spread->content['sections'][0]['data']['text']);
        $this->assertCount(2, $spread->content['sections'][1]['data']['images']);
        $this->assertCount(3, $spread->content['sections']);
        $this->assertEquals('contact@example.com', $spread->content['sections'][2]['data']['email']);
        $this->assertEquals('dark', $spread->content['settings']['theme']);
        $this->assertTrue($spread->content['settings']['animations']);
    }

    public function test_spread_query_operations()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create multiple spreads
        Spread::create([
            'spreadable_id' => $user1->id,
            'spreadable_type' => get_class($user1),
            'content' => ['title' => 'Home Page', 'type' => 'landing'],
        ]);

        Spread::create([
            'spreadable_id' => $user1->id,
            'spreadable_type' => get_class($user1),
            'content' => ['title' => 'About Page', 'type' => 'content'],
        ]);

        Spread::create([
            'spreadable_id' => $user2->id,
            'spreadable_type' => get_class($user2),
            'content' => ['title' => 'User 2 Page', 'type' => 'content'],
        ]);

        // Test querying all spreads
        $allSpreads = Spread::all();
        $this->assertCount(3, $allSpreads);

        // Test querying spreads by user
        $user1Spreads = Spread::where('spreadable_id', $user1->id)->get();
        $this->assertCount(2, $user1Spreads);

        $user2Spreads = Spread::where('spreadable_id', $user2->id)->get();
        $this->assertCount(1, $user2Spreads);

        // Test querying by polymorphic type
        $userSpreads = Spread::where('spreadable_type', get_class($user1))->get();
        $this->assertCount(3, $userSpreads);
    }

    public function test_spread_page_builder_simulation()
    {
        $user = User::factory()->create();

        // Simulate a page builder with different block types
        $pageBuilderContent = [
            'blocks' => [
                [
                    'id' => 'block-1',
                    'type' => 'text',
                    'data' => [
                        'content' => '<h1>Welcome to Our Website</h1><p>This is a text block.</p>',
                        'alignment' => 'center',
                    ],
                    'settings' => [
                        'padding' => ['top' => 20, 'bottom' => 20],
                        'background' => 'transparent',
                    ],
                ],
                [
                    'id' => 'block-2',
                    'type' => 'image',
                    'data' => [
                        'src' => 'featured-image.jpg',
                        'alt' => 'Featured Image',
                        'caption' => 'Our featured image',
                    ],
                    'settings' => [
                        'width' => '100%',
                        'alignment' => 'center',
                    ],
                ],
                [
                    'id' => 'block-3',
                    'type' => 'columns',
                    'data' => [
                        'columns' => [
                            [
                                'content' => '<h3>Column 1</h3><p>First column content</p>',
                                'width' => 6,
                            ],
                            [
                                'content' => '<h3>Column 2</h3><p>Second column content</p>',
                                'width' => 6,
                            ],
                        ],
                    ],
                    'settings' => [
                        'gap' => 20,
                        'responsive' => true,
                    ],
                ],
            ],
            'page_settings' => [
                'container_width' => 1200,
                'mobile_responsive' => true,
                'seo_optimized' => true,
            ],
        ];

        $spread = Spread::create([
            'spreadable_id' => $user->id,
            'spreadable_type' => get_class($user),
            'content' => $pageBuilderContent,
        ]);

        // Test page builder content structure
        $this->assertCount(3, $spread->content['blocks']);
        $this->assertEquals('text', $spread->content['blocks'][0]['type']);
        $this->assertStringContainsString('<h1>Welcome', $spread->content['blocks'][0]['data']['content']);
        $this->assertEquals('featured-image.jpg', $spread->content['blocks'][1]['data']['src']);
        $this->assertCount(2, $spread->content['blocks'][2]['data']['columns']);
        $this->assertEquals(6, $spread->content['blocks'][2]['data']['columns'][0]['width']);
        $this->assertEquals(1200, $spread->content['page_settings']['container_width']);
        $this->assertTrue($spread->content['page_settings']['mobile_responsive']);
    }

    public function test_create_spread_with_minimum_fields()
    {
        $user = User::factory()->create();

        $spread = Spread::create([
            'spreadable_id' => $user->id,
            'spreadable_type' => get_class($user),
            'content' => ['title' => 'Minimal Content'],
        ]);

        $this->assertNotNull($spread->id);
        $this->assertEquals($user->id, $spread->spreadable_id);
        $this->assertEquals(get_class($user), $spread->spreadable_type);
        $this->assertEquals(['title' => 'Minimal Content'], $spread->content);
    }

    public function test_spread_json_serialization()
    {
        $user = User::factory()->create();
        $content = [
            'page_title' => 'JSON Test Page',
            'sections' => [
                [
                    'type' => 'hero',
                    'data' => [
                        'title' => 'Hero Section',
                        'buttons' => [
                            ['text' => 'Button 1', 'url' => '/link1'],
                            ['text' => 'Button 2', 'url' => '/link2'],
                        ],
                    ],
                ],
            ],
            'metadata' => [
                'author' => 'admin',
                'last_modified' => '2024-01-01',
                'version' => '1.0',
            ],
        ];

        $spread = Spread::create([
            'spreadable_id' => $user->id,
            'spreadable_type' => get_class($user),
            'content' => $content,
        ]);

        // Test JSON serialization
        $spreadArray = $spread->toArray();
        $this->assertIsArray($spreadArray['content']);
        $this->assertEquals($content, $spreadArray['content']);

        // Test JSON string conversion
        $jsonString = $spread->toJson();
        $this->assertIsString($jsonString);

        $decodedJson = json_decode($jsonString, true);
        $this->assertEquals($content, $decodedJson['content']);
    }

    public function test_spread_template_scenarios()
    {
        $user = User::factory()->create();

        // Test different page templates
        $templates = [
            'homepage' => [
                'template' => 'home',
                'sections' => ['hero', 'features', 'testimonials', 'cta'],
                'layout' => 'full-width',
            ],
            'product-page' => [
                'template' => 'product',
                'sections' => ['product-hero', 'specifications', 'reviews'],
                'layout' => 'container',
            ],
            'blog-post' => [
                'template' => 'article',
                'sections' => ['article-header', 'content', 'related-posts'],
                'layout' => 'sidebar',
            ],
        ];

        $spreads = [];
        foreach ($templates as $name => $templateData) {
            $spreads[] = Spread::create([
                'spreadable_id' => $user->id,
                'spreadable_type' => get_class($user),
                'content' => $templateData,
            ]);
        }

        // Test that each template is stored correctly
        $this->assertCount(3, $spreads);
        $this->assertEquals('home', $spreads[0]->content['template']);
        $this->assertContains('hero', $spreads[0]->content['sections']);
        $this->assertEquals('full-width', $spreads[0]->content['layout']);

        $this->assertEquals('product', $spreads[1]->content['template']);
        $this->assertContains('specifications', $spreads[1]->content['sections']);
    }

    public function test_spread_versioning_simulation()
    {
        $user = User::factory()->create();

        $spread = Spread::create([
            'spreadable_id' => $user->id,
            'spreadable_type' => get_class($user),
            'content' => [
                'version' => '1.0',
                'title' => 'Version 1.0 Title',
                'sections' => [['type' => 'intro', 'data' => ['text' => 'Version 1.0 content']]],
            ],
        ]);

        // Simulate version updates
        $versions = ['1.1', '1.2', '2.0'];
        foreach ($versions as $version) {
            $content = $spread->content;
            $content['version'] = $version;
            $content['title'] = "Version {$version} Title";
            $content['sections'][0]['data']['text'] = "Version {$version} content";
            $content['last_updated'] = now()->toDateString();

            $spread->update(['content' => $content]);
        }

        $this->assertEquals('2.0', $spread->content['version']);
        $this->assertEquals('Version 2.0 Title', $spread->content['title']);
        $this->assertEquals('Version 2.0 content', $spread->content['sections'][0]['data']['text']);
        $this->assertArrayHasKey('last_updated', $spread->content);
    }

    public function test_spread_multilingual_content()
    {
        $user = User::factory()->create();
        $multilingualContent = [
            'default_locale' => 'en',
            'locales' => ['en', 'tr', 'fr'],
            'content' => [
                'en' => [
                    'title' => 'English Title',
                    'description' => 'English description',
                ],
                'tr' => [
                    'title' => 'Turkish Title',
                    'description' => 'Turkish description',
                ],
                'fr' => [
                    'title' => 'French Title',
                    'description' => 'French description',
                ],
            ],
            'shared_settings' => [
                'layout' => 'standard',
                'theme' => 'light',
            ],
        ];

        $spread = Spread::create([
            'spreadable_id' => $user->id,
            'spreadable_type' => get_class($user),
            'content' => $multilingualContent,
        ]);

        // Test multilingual content structure
        $this->assertEquals('en', $spread->content['default_locale']);
        $this->assertCount(3, $spread->content['locales']);
        $this->assertEquals('English Title', $spread->content['content']['en']['title']);
        $this->assertEquals('Turkish Title', $spread->content['content']['tr']['title']);
        $this->assertEquals('French Title', $spread->content['content']['fr']['title']);
        $this->assertEquals('standard', $spread->content['shared_settings']['layout']);
    }

    public function test_spread_cascade_deletion_simulation()
    {
        $user = User::factory()->create();

        $spread = Spread::create([
            'spreadable_id' => $user->id,
            'spreadable_type' => get_class($user),
            'content' => ['title' => 'Test Page'],
        ]);

        $spreadId = $spread->id;

        // Test that spread can be deleted independently
        $spread->delete();

        $this->assertDatabaseMissing(modularityConfig('tables.spreads', 'modularity_spreads'), ['id' => $spreadId]);

        // Original user should still exist
        $this->assertDatabaseHas('um_users', ['id' => $user->id]);
    }

    public function test_spread_with_different_models()
    {
        $user = User::factory()->create();
        Event::fake();
        $company = \Unusualify\Modularity\Entities\Company::factory()->create();

        // Create spread for user
        $userSpread = Spread::create([
            'spreadable_id' => $user->id,
            'spreadable_type' => get_class($user),
            'content' => ['title' => 'User Page'],
        ]);

        // Create spread for company
        $companySpread = Spread::create([
            'spreadable_id' => $company->id,
            'spreadable_type' => get_class($company),
            'content' => ['title' => 'Company Page'],
        ]);

        // Test that spreads are associated with correct models
        $this->assertEquals($user->id, $userSpread->spreadable_id);
        $this->assertEquals(get_class($user), $userSpread->spreadable_type);
        $this->assertEquals($company->id, $companySpread->spreadable_id);
        $this->assertEquals(get_class($company), $companySpread->spreadable_type);

        // Test querying by model type
        $userSpreads = Spread::where('spreadable_type', get_class($user))->get();
        $companySpreads = Spread::where('spreadable_type', get_class($company))->get();

        $this->assertCount(1, $userSpreads);
        $this->assertCount(1, $companySpreads);
        $this->assertTrue($userSpreads->contains('id', $userSpread->id));
        $this->assertTrue($companySpreads->contains('id', $companySpread->id));
    }
}
