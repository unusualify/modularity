<?php

namespace Unusualify\Modularous\Tests\Traits;

use Illuminate\Support\Facades\Config;
use Unusualify\Modularous\Exceptions\ModularousException;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\UFinder;
use Unusualify\Modularous\Tests\TestCase;
use Unusualify\Modularous\Traits\RelationshipArguments;
use Unusualify\Modularous\Traits\RelationshipMap;

class RelationshipTraitsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('modularous.laravel-relationship-map', [
            'belongsTo' => [
                'related' => ['position' => 0, 'required' => true],
                'foreignKey' => ['position' => 1, 'required' => false],
                'ownerKey' => ['position' => 2, 'required' => false],
            ],
            'hasMany' => [
                'related' => ['position' => 0, 'required' => true],
                'foreignKey' => ['position' => 1, 'required' => false],
                'localKey' => ['position' => 2, 'required' => false],
            ],
            'hasOne' => [
                'related' => ['position' => 0, 'required' => true],
                'foreignKey' => ['position' => 1, 'required' => false],
                'localKey' => ['position' => 2, 'required' => false],
            ],
            'hasManyThrough' => [
                'related' => ['position' => 0, 'required' => true],
                'through' => ['position' => 1, 'required' => true],
                'firstKey' => ['position' => 2, 'required' => false],
                'secondKey' => ['position' => 3, 'required' => false],
                'localKey' => ['position' => 4, 'required' => false],
                'secondLocalKey' => ['position' => 5, 'required' => false],
            ],
            'belongsToMany' => [
                'related' => ['position' => 0, 'required' => true],
                'table' => ['position' => 1, 'required' => false],
            ],
            'morphTo' => [
                'name' => ['position' => 0, 'required' => false],
                'type' => ['position' => 1, 'required' => false],
                'id' => ['position' => 2, 'required' => false],
                'ownerKey' => ['position' => 3, 'required' => false],
            ],
            'morphOne' => [
                'related' => ['position' => 0, 'required' => true],
                'name' => ['position' => 1, 'required' => false],
            ],
            'morphMany' => [
                'related' => ['position' => 0, 'required' => true],
                'name' => ['position' => 1, 'required' => false],
            ],
            'morphToMany' => [
                'related' => ['position' => 0, 'required' => true],
                'name' => ['position' => 1, 'required' => false],
            ],
            'morphedByMany' => [
                'related' => ['position' => 0, 'required' => true],
                'name' => ['position' => 1, 'required' => false],
            ],
        ]);

        UFinder::shouldReceive('getPossibleModels')->andReturnUsing(function ($str) {
            return ['App\\Models\\' . ucfirst($str)];
        });
        Modularous::shouldReceive('getModels')->andReturn([]);
    }

    protected function createTester(string $model = 'Post'): object
    {
        $tester = new class
        {
            use RelationshipMap;

            public function boot($model)
            {
                $this->model = $model;
                $this->relationshipParametersMap = config('modularous.laravel-relationship-map');
            }

            public function runCreate($name, $rel, $args = [])
            {
                return $this->createRelationshipSchema($name, $rel, $args);
            }
        };
        $tester->boot($model);

        return $tester;
    }

    /** @test */
    public function it_can_generate_relationship_schema()
    {
        $tester = $this->createTester();
        $schema = $tester->runCreate('User', 'belongsTo');
        $this->assertEquals('belongsTo:User:user_id:id', $schema);
    }

    /** @test */
    public function it_can_parse_relationship_schema()
    {
        $tester = $this->createTester();
        $parsed = $tester->parseRelationshipSchema('belongsTo:User');

        $this->assertEquals('belongsTo', $parsed['relationship_method']);
        $this->assertEquals('user', $parsed['relationship_name']);
        $this->assertContains('\\App\\Models\\User::class', $parsed['arguments']);
    }

    /** @test */
    public function it_can_generate_relationship_arguments()
    {
        $tester = new class
        {
            use RelationshipArguments;
        };

        $arg = $tester->getRelationshipArgumentForeignKey('User', 'belongsTo', []);
        $this->assertEquals('user_id', $arg);

        $arg = $tester->getRelationshipArgumentOwnerKey('User', 'belongsTo', ['User', 'id']);
        $this->assertEquals('id', $arg);
    }

    /** @test */
    public function it_formats_relationship_with_morphed_by_many_to_morph_to_many_return_type()
    {
        $tester = $this->createTester();
        $result = $tester->relationshipFormat('Tag', 'posts', 'morphedByMany', ['\\App\\Models\\Post::class', 'taggable']);

        $this->assertEquals('Tag', $result['model_name']);
        $this->assertEquals('posts', $result['relationship_name']);
        $this->assertEquals('morphedByMany', $result['relationship_method']);
        $this->assertEquals("\Illuminate\Database\Eloquent\Relations\MorphToMany", $result['return_type']);
    }

    /** @test */
    public function it_generates_comment_structure()
    {
        $tester = $this->createTester();
        $comment = $tester->commentStructure(['First line', 'Second line']);

        $this->assertStringContainsString('/**', $comment);
        $this->assertStringContainsString('First line', $comment);
        $this->assertStringContainsString('Second line', $comment);
        $this->assertStringContainsString('*/', $comment);
    }

    /** @test */
    public function it_generates_method_comments_for_all_relationship_types()
    {
        $tester = $this->createTester();
        $base = ['model_name' => 'Post', 'relationship_name' => 'user'];

        $cases = [
            'belongsTo' => 'owns the Post',
            'hasOne' => 'associated with the Post',
            'hasMany' => 'for the Post',
            'belongsToMany' => 'belong to the Post',
            'morphTo' => 'belongs to',
            'morphMany' => "all of the Post's",
            'morphOne' => "Post's",
            'morphToMany' => 'all of',
            'morphedByMany' => 'assigned the Post',
            'hasManyThrough' => 'belong to the Post',
            'hasOneThrough' => 'owns the Post',
        ];

        foreach ($cases as $method => $expected) {
            $attr = $base + ['relationship_method' => $method];
            $comment = $tester->generateMethodComment($attr);
            $this->assertStringContainsString($expected, $comment, "Failed for {$method}");
        }
    }

    /** @test */
    public function it_gets_method_name_from_schema()
    {
        $tester = $this->createTester();
        $this->assertEquals('belongsTo', $tester->getMethodName('belongsTo:User:user_id:id'));
        $this->assertEquals('hasMany', $tester->getMethodName('hasMany:Comment'));
    }

    /** @test */
    public function it_gets_related_method_name_for_various_relationship_types()
    {
        $tester = $this->createTester();
        $this->assertEquals('user', $tester->getRelatedMethodName('belongsTo', 'belongsTo:User'));
        $this->assertEquals('comments', $tester->getRelatedMethodName('hasMany', 'hasMany:Comment'));
        $this->assertEquals('tags', $tester->getRelatedMethodName('belongsToMany', 'belongsToMany:Tag'));
    }

    /** @test */
    public function it_gets_relationship_arguments_from_schema()
    {
        $tester = $this->createTester();
        $args = $tester->getRelationshipArguments('belongsTo', 'belongsTo:User:user_id:id');
        $this->assertIsArray($args);
        $this->assertContains('\\App\\Models\\User::class', $args);
    }

    /** @test */
    public function it_gets_model_class_from_cache_when_set()
    {
        $tester = $this->createTester();
        $ref = new \ReflectionClass($tester);
        $prop = $ref->getProperty('modelClasses');
        $prop->setAccessible(true);
        $prop->setValue($tester, ['belongsTo' => ['User' => 'App\\Models\\CachedUser']]);
        $this->assertEquals('App\\Models\\CachedUser', $tester->getModelClass('User', 'belongsTo'));
    }

    /** @test */
    public function it_gets_model_class_from_ufinder_when_single_possible()
    {
        $tester = $this->createTester();
        $this->assertEquals('App\\Models\\User', $tester->getModelClass('User', 'belongsTo'));
    }

    /** @test */
    public function it_creates_morph_to_schema_with_props()
    {
        $tester = $this->createTester();
        $schema = $tester->runCreate('User', 'morphTo', ['commentable', 'postable']);
        $this->assertStringContainsString('?', $schema);
        $this->assertStringContainsString('commentable', $schema);
        $this->assertStringContainsString('postable', $schema);
    }

    /** @test */
    public function it_throws_when_required_relationship_argument_is_missing()
    {
        Config::set('modularous.laravel-relationship-map', [
            'customRel' => [
                'unknownParam' => ['position' => 0, 'required' => true],
            ],
        ]);

        $tester = new class
        {
            use RelationshipMap;

            public function boot()
            {
                $this->model = 'Post';
                $this->relationshipParametersMap = config('modularous.laravel-relationship-map');
            }
        };
        $tester->boot();

        $this->expectException(ModularousException::class);
        $this->expectExceptionMessage("Missing required argument 'unknownParam'");
        $tester->createRelationshipSchema('Foo', 'customRel', []);
    }

    /** @test */
    public function it_skips_morphed_by_many_in_parse_relationship_schema()
    {
        $tester = $this->createTester();
        $parsed = $tester->parseRelationshipSchema('morphedByMany:Post');
        $this->assertEmpty($parsed);
    }

    /** @test */
    public function it_parses_has_many_through_schema()
    {
        $tester = $this->createTester();
        $parsed = $tester->parseRelationshipSchema('hasManyThrough:Country:User');
        $this->assertEquals('hasManyThrough', $parsed['relationship_method']);
        $this->assertEquals('countries', $parsed['relationship_name']);
    }

    /** @test */
    public function it_generates_related_method_name_for_morph_one()
    {
        $tester = $this->createTester('Image');
        $name = $tester->getRelatedMethodName('morphOne', 'morphOne:Image');
        $this->assertEquals('image', $name);
    }

    /** @test */
    public function it_generates_related_method_name_for_morph_many()
    {
        $tester = $this->createTester('Comment');
        $name = $tester->getRelatedMethodName('morphMany', 'morphMany:Comment');
        $this->assertEquals('comments', $name);
    }

    /** @test */
    public function it_generates_relationship_argument_for_through_parameter()
    {
        $tester = $this->createTester();
        $args = $tester->getRelationshipArguments('hasManyThrough', 'hasManyThrough:Country:User');
        $this->assertContains('\\App\\Models\\Country::class', $args);
        $this->assertContains('\\App\\Models\\User::class', $args);
    }

    /** @test */
    public function it_generates_relationship_argument_for_morph_name_parameter()
    {
        $tester = $this->createTester();
        $args = $tester->getRelationshipArguments('morphOne', 'morphOne:Image:comment');
        $this->assertContains("'commentable'", $args);
    }

    /** @test */
    public function it_parses_reverse_relationship_schema_in_test_mode()
    {
        UFinder::shouldReceive('getModel')->andReturn(null);

        $tester = $this->createTester('Post');
        $data = $tester->parseReverseRelationshipSchema('belongsTo:User', true);

        $this->assertCount(1, $data);
        $this->assertEquals('hasMany', $data[0]['relationship_method']);
        $this->assertEquals('posts', $data[0]['relationship_name']);
        $this->assertEquals('User', $data[0]['model_name']);
    }

    /** @test */
    public function it_parses_reverse_relationship_schema_for_has_many()
    {
        $tester = $this->createTester('User');
        $data = $tester->parseReverseRelationshipSchema('hasMany:Post', true);

        $this->assertCount(1, $data);
        $this->assertEquals('belongsTo', $data[0]['relationship_method']);
        $this->assertEquals('user', $data[0]['relationship_name']);
        $this->assertEquals('Post', $data[0]['model_name']);
    }

    /** @test */
    public function it_parses_reverse_relationship_schema_for_has_one()
    {
        $tester = $this->createTester('User');
        $data = $tester->parseReverseRelationshipSchema('hasOne:Profile', true);

        $this->assertCount(1, $data);
        $this->assertEquals('belongsTo', $data[0]['relationship_method']);
        $this->assertEquals('user', $data[0]['relationship_name']);
    }

    /** @test */
    public function it_parses_reverse_relationship_schema_for_morph_to_with_props()
    {
        $tester = $this->createTester('Comment');
        $data = $tester->parseReverseRelationshipSchema('morphTo:User?commentable&postable', true);

        $this->assertNotEmpty($data);
        $this->assertEquals('morphMany', $data[0]['relationship_method']);
    }

    /** @test */
    public function it_parses_reverse_relationship_schema_for_morph_to_many()
    {
        $tester = $this->createTester('Post');
        $data = $tester->parseReverseRelationshipSchema('morphToMany:Tag:taggable', true);

        $this->assertCount(1, $data);
        $this->assertEquals('morphedByMany', $data[0]['relationship_method']);
    }

    /** @test */
    public function it_parses_reverse_relationship_schema_for_belongs_to_many()
    {
        $tester = $this->createTester('Post');
        $data = $tester->parseReverseRelationshipSchema('belongsToMany:Tag', true);

        $this->assertCount(1, $data);
        $this->assertEquals('belongsToMany', $data[0]['relationship_method']);
        $this->assertEquals('posts', $data[0]['relationship_name']);
    }

    /** @test */
    public function it_parses_reverse_relationship_schema_for_has_many_through()
    {
        $tester = $this->createTester('User');
        $data = $tester->parseReverseRelationshipSchema('hasManyThrough:Country:User:country_id:user_id:id:id', true);

        $this->assertCount(1, $data);
        $this->assertEquals('hasOneThrough', $data[0]['relationship_method']);
    }

    /** @test */
    public function it_parses_relationship_schema_with_belongs_to_many_pivot_chain()
    {
        UFinder::shouldReceive('getModel')->andReturn('App\\Models\\PostTagPivot');

        $tester = $this->createTester('Post');
        $parsed = $tester->parseRelationshipSchema('belongsToMany:Tag,withTimestamps');

        $this->assertEquals('belongsToMany', $parsed['relationship_method']);
        $this->assertNotEmpty($parsed['chain_methods']);
        $this->assertEquals('using', $parsed['chain_methods'][0]['method_name']);
    }

    /** @test */
    public function it_generates_morph_to_many_relationship_argument()
    {
        $tester = $this->createTester();
        $args = $tester->getRelationshipArguments('morphToMany', 'morphToMany:Tag:taggable');

        $this->assertContains('\\App\\Models\\Tag::class', $args);
        $this->assertContains("'taggable'", $args);
    }

    /** @test */
    public function it_generates_morph_to_argument_as_function()
    {
        $tester = $this->createTester();
        $args = $tester->getRelationshipArguments('morphTo', 'morphTo:User:commentable');

        $this->assertContains('__FUNCTION__', $args);
    }

    /** @test */
    public function it_caches_model_class_from_multiple_possibles_via_modularous()
    {
        Modularous::shouldReceive('getModels')->with('User')->andReturn(['App\\Models\\UserA', 'App\\Models\\UserB']);
        UFinder::shouldReceive('getPossibleModels')->with('User')->andReturn(['App\\Models\\UserA', 'App\\Models\\UserB']);

        $tester = $this->createTester();
        $ref = new \ReflectionClass($tester);
        $prop = $ref->getProperty('modelClasses');
        $prop->setAccessible(true);
        $prop->setValue($tester, ['belongsTo' => ['User' => 'App\\Models\\UserB']]);

        $this->assertEquals('App\\Models\\UserB', $tester->getModelClass('User', 'belongsTo'));
    }

    /** @test */
    public function it_generates_related_method_name_for_morph_to_many()
    {
        $tester = $this->createTester('Post');
        $name = $tester->getRelatedMethodName('morphToMany', 'morphToMany:Tag');

        $this->assertEquals('tags', $name);
    }

    /** @test */
    public function it_generates_related_method_name_for_morphed_by_many()
    {
        $tester = $this->createTester('Tag');
        $name = $tester->getRelatedMethodName('morphedByMany', 'morphedByMany:Post');

        $this->assertEquals('posts', $name);
    }
}
