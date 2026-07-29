<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Traits;

use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularous\Tests\TestCase;
use Unusualify\Modularous\Traits\SerializeModel;

class SerializeModelTest extends TestCase
{
    /** @test */
    public function it_serializes_and_unserializes_models_with_relations(): void
    {
        $subject = new class
        {
            use SerializeModel;
        };

        $child = new SerializeModelChildStub(['id' => 2, 'title' => 'Child']);
        $parent = new SerializeModelParentStub(['id' => 1, 'title' => 'Parent']);
        $parent->setRelation('child', $child);
        $parent->setRelation('items', collect([$child]));
        $parent->setRelation('flag', true);

        $serialized = $subject->serializeModel($parent);

        $this->assertSame(SerializeModelParentStub::class, $serialized['class']);
        $this->assertSame('model', $serialized['relations']['child']['type']);
        $this->assertSame('collection', $serialized['relations']['items']['type']);
        $this->assertSame('other', $serialized['relations']['flag']['type']);

        $restored = $subject->unserializeModel($serialized);
        $this->assertInstanceOf(SerializeModelParentStub::class, $restored);
        $this->assertSame(1, (int) $restored->getAttribute('id'));
        $this->assertInstanceOf(SerializeModelChildStub::class, $restored->getRelation('child'));
        $this->assertTrue($restored->getRelation('flag'));
    }
}

class SerializeModelParentStub extends Model
{
    protected $guarded = [];

    public $timestamps = false;
}

class SerializeModelChildStub extends Model
{
    protected $guarded = [];

    public $timestamps = false;
}
