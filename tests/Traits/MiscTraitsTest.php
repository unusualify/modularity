<?php

namespace Unusualify\Modularous\Tests\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Output\OutputInterface;
use Unusualify\Modularous\Tests\TestCase;
use Unusualify\Modularous\Traits\CheckSnapshot;
use Unusualify\Modularous\Traits\Pretending;
use Unusualify\Modularous\Traits\ReplacementTrait;
use Unusualify\Modularous\Traits\SerializeModel;
use Unusualify\Modularous\Traits\Traitify;
use Unusualify\Modularous\Traits\Verbosity;

class MiscTraitsTest extends TestCase
{
    /** @test */
    public function it_can_use_pretending_trait()
    {
        $tester = new class
        {
            use Pretending;
        };

        $this->assertFalse($tester->pretending());
        $tester->setPretending(true);
        $this->assertTrue($tester->pretending());
    }

    /** @test */
    public function it_can_use_verbosity_trait()
    {
        $tester = new class
        {
            use Verbosity;
        };

        $this->assertEquals(OutputInterface::VERBOSITY_NORMAL, $tester->getVerbosity());

        $tester->setVerbosity('vv');
        $this->assertEquals(OutputInterface::VERBOSITY_VERY_VERBOSE, $tester->getVerbosity());
        $this->assertTrue($tester->isVeryVerbose());
        $this->assertFalse($tester->isDebug());

        $tester->setVerbosity('quiet');
        $this->assertTrue($tester->isQuiet());
    }

    /** @test */
    public function it_can_set_verbosity_via_string_map()
    {
        $tester = new class
        {
            use Verbosity;
        };

        $tester->setVerbosity('v');
        $this->assertEquals(OutputInterface::VERBOSITY_VERBOSE, $tester->getVerbosity());
        $this->assertTrue($tester->isVerbose());

        $tester->setVerbosity('vvv');
        $this->assertEquals(OutputInterface::VERBOSITY_DEBUG, $tester->getVerbosity());
        $this->assertTrue($tester->isDebug());

        $tester->setVerbosity('normal');
        $this->assertEquals(OutputInterface::VERBOSITY_NORMAL, $tester->getVerbosity());
    }

    /** @test */
    public function it_can_set_verbosity_via_integer()
    {
        $tester = new class
        {
            use Verbosity;
        };

        $tester->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        $this->assertEquals(OutputInterface::VERBOSITY_DEBUG, $tester->getVerbosity());
    }

    /** @test */
    public function it_keeps_current_verbosity_when_set_with_invalid_string()
    {
        $tester = new class
        {
            use Verbosity;
        };
        $tester->setVerbosity('vv');

        $tester->setVerbosity('invalid');
        $this->assertEquals(OutputInterface::VERBOSITY_VERY_VERBOSE, $tester->getVerbosity());
    }

    /** @test */
    public function it_returns_fluent_from_set_verbosity()
    {
        $tester = new class
        {
            use Verbosity;
        };
        $result = $tester->setVerbosity('quiet');
        $this->assertSame($tester, $result);
    }

    /** @test */
    public function it_can_use_traitify_trait()
    {
        // Using a named class to have a predictable class_basename
        $tester = new class extends \stdClass
        {
            use Traitify;

            public function test_method_traitify()
            {
                return 'success';
            }

            public function run()
            {
                return $this->traitsMethods('testMethod');
            }
        };

        $methods = $tester->run();
        $this->assertContains('testMethodTraitify', $methods);
    }

    /** @test */
    public function it_can_use_traitify_trait_properties()
    {
        $tester = new class extends \stdClass
        {
            use Traitify;

            public $testPropTraitify = 'value';

            public function run()
            {
                return $this->traitProperties('testProp');
            }
        };

        $properties = $tester->run();
        $this->assertContains('testPropTraitify', $properties);
    }

    /** @test */
    public function it_can_use_replacement_trait()
    {
        Config::set('modularous.stubs.files', ['file1', 'file2']);
        Config::set('modularous.stubs.replacements', ['json' => ['NAME', 'LOWER_NAME']]);

        $tester = new class
        {
            use ReplacementTrait;

            public function setName($name)
            {
                $this->name = $name;
            }

            public function getName()
            {
                return $this->name;
            }

            protected function getNameReplacement()
            {
                return 'TestModule';
            }

            protected function getLowerNameReplacement()
            {
                return 'testmodule';
            }
        };
        $tester->setName('TestModule');

        $this->assertEquals(['file1', 'file2'], $tester->getFiles());
        $this->assertEquals(['json' => ['NAME', 'LOWER_NAME']], $tester->getReplacements());

        $replaces = $tester->makeReplaces(['LOWER_NAME', 'STUDLY_NAME']);
        $this->assertEquals('testmodule', $replaces['LOWER_NAME']);
        $this->assertEquals('TestModule', $replaces['STUDLY_NAME']);

        $replaced = $tester->replaceString('Hello $LOWER_NAME$');
        $this->assertEquals('Hello testmodule', $replaced);
    }

    /** @test */
    public function it_can_serialize_and_unserialize_models()
    {
        $tester = new class
        {
            use SerializeModel;
        };

        $model = new class extends Model
        {
            protected $guarded = [];

            protected $attributes = ['name' => 'Original'];
        };

        $serialized = $tester->serializeModel($model);

        $this->assertEquals('Original', $serialized['attributes']['name']);
        $this->assertEquals(get_class($model), $serialized['class']);

        $unserialized = $tester->unserializeModel($serialized);
        $this->assertInstanceOf(get_class($model), $unserialized);
        $this->assertEquals('Original', $unserialized->name);
        $this->assertTrue($unserialized->exists);
    }

    /** @test */
    public function it_can_serialize_model_with_single_relation()
    {
        $tester = new class
        {
            use SerializeModel;
        };

        $related = new class extends Model
        {
            protected $guarded = [];

            protected $attributes = ['id' => 1, 'title' => 'Related'];
        };

        $model = new class extends Model
        {
            protected $guarded = [];

            protected $attributes = ['id' => 1, 'name' => 'Parent'];
        };
        $model->setRelation('related', $related);

        $serialized = $tester->serializeModel($model);
        $this->assertArrayHasKey('relations', $serialized);
        $this->assertArrayHasKey('related', $serialized['relations']);
        $this->assertEquals('model', $serialized['relations']['related']['type']);
        $this->assertEquals('Related', $serialized['relations']['related']['data']['attributes']['title']);

        $unserialized = $tester->unserializeModel($serialized);
        $this->assertTrue($unserialized->relationLoaded('related'));
        $this->assertEquals('Related', $unserialized->related->title);
    }

    /** @test */
    public function it_can_serialize_model_with_collection_relation()
    {
        $tester = new class
        {
            use SerializeModel;
        };

        $item1 = new class extends Model
        {
            protected $guarded = [];

            protected $attributes = ['id' => 1, 'name' => 'Item1'];
        };
        $item2 = new class extends Model
        {
            protected $guarded = [];

            protected $attributes = ['id' => 2, 'name' => 'Item2'];
        };

        $model = new class extends Model
        {
            protected $guarded = [];

            protected $attributes = ['id' => 1];
        };
        $model->setRelation('items', collect([$item1, $item2]));

        $serialized = $tester->serializeModel($model);
        $this->assertEquals('collection', $serialized['relations']['items']['type']);
        $this->assertCount(2, $serialized['relations']['items']['data']);

        $unserialized = $tester->unserializeModel($serialized);
        $items = $unserialized->getRelation('items');
        $this->assertInstanceOf(Collection::class, $items);
        $this->assertCount(2, $items);
        $this->assertEquals('Item1', $items->first()->name);
        $this->assertEquals('Item2', $items->last()->name);
    }

    /** @test */
    public function it_can_serialize_model_with_other_type_relation()
    {
        $tester = new class
        {
            use SerializeModel;
        };

        $model = new class extends Model
        {
            protected $guarded = [];

            protected $attributes = ['id' => 1];
        };
        $model->setRelation('count', 42);
        $model->setRelation('nullable', null);

        $serialized = $tester->serializeModel($model);
        $this->assertEquals('other', $serialized['relations']['count']['type']);
        $this->assertEquals(42, $serialized['relations']['count']['data']);
        $this->assertEquals('other', $serialized['relations']['nullable']['type']);
        $this->assertNull($serialized['relations']['nullable']['data']);

        $unserialized = $tester->unserializeModel($serialized);
        $this->assertEquals(42, $unserialized->getRelation('count'));
        $this->assertNull($unserialized->getRelation('nullable'));
    }

    /** @test */
    public function it_can_use_check_snapshot_trait()
    {
        $tester = new class
        {
            use CheckSnapshot;

            public function runIsSnapshot($m)
            {
                return $this->isSnapshotRelation($m);
            }

            public function runGetFk($m)
            {
                return $this->getSnapshotSourceForeignKey($m);
            }
        };

        $modelWithoutSnapshot = new class extends Model
        {
            protected $guarded = [];
        };
        $this->assertFalse($tester->runIsSnapshot($modelWithoutSnapshot));

        $modelWithSnapshotFk = new class extends Model
        {
            protected $guarded = [];

            public function getSnapshotSourceForeignKey()
            {
                return 'source_id';
            }
        };
        $this->assertEquals('source_id', $tester->runGetFk($modelWithSnapshotFk));
    }
}
