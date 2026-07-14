<?php

namespace Unusualify\Modularous\Tests\Repositories\Logic;

use Unusualify\Modularous\Repositories\Logic\RelationshipHelpers;
use Unusualify\Modularous\Repositories\Logic\Relationships;
use Unusualify\Modularous\Tests\RepositoryTestCase;

class RelationshipsTest extends RepositoryTestCase
{
    public function test_relation_getters_delegate_to_model_defined_relations(): void
    {
        $repo = new RelationshipsStub(new RelationshipsTestModel);

        $this->assertSame(['tags'], $repo->getBelongsToManyRelations());
        $this->assertSame(['comments'], $repo->getHasManyRelations());
        $this->assertSame(['images'], $repo->getMorphManyRelations());
        $this->assertSame(['categories'], $repo->getMorphToManyRelations());
    }

    public function test_prepare_fields_before_save_relationships_passthrough(): void
    {
        $repo = new RelationshipsStub(new RelationshipsTestModel);
        $fields = ['tags' => [1, 2]];

        $this->assertSame($fields, $repo->prepareFieldsBeforeSaveRelationships(new \stdClass, $fields));
    }
}

class RelationshipsTestModel
{
    public function definedRelations(?string $type = null): array
    {
        return match ($type) {
            'BelongsToMany' => ['tags'],
            'HasMany' => ['comments'],
            'MorphMany' => ['images'],
            'MorphToMany' => ['categories'],
            default => [],
        };
    }
}

class RelationshipsStub
{
    use Relationships, RelationshipHelpers;

    public function __construct(public RelationshipsTestModel $model) {}

    public function getModel(): RelationshipsTestModel
    {
        return $this->model;
    }

    public function getSnakeCase(string $value): string
    {
        return mb_strtolower($value);
    }

    public function getForeignKey(): string
    {
        return 'id';
    }
}
